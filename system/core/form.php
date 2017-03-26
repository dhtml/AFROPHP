<?php
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');


class form extends \System\Base\Prototype
{
    public function __construct()
    {
        $this->load->library('session');
    }


    public function get($form_id, $form_state=array(), $object=null)
    {
        $form_state = (array) $form_state;

        if(!isset($form_state['files'])) {$form_state['files']=array();}
        if(!isset($form_state['submitted'])) {$form_state['submitted']=false;}



        return $this->build_form($form_id, $form_state, $object);
    }

    public function build_form($form_id, &$form_state, $object)
    {
      $form=array();

      $form['form'] = array(
      'type' => 'form',
      'id' => $form_id,
      'role' => 'form',
      'method' => 'POST',
      'accept-charset' => 'UTF-8',
      'action' => current_url,
      'enctype'=>'application/x-www-form-urlencoded',
      );

      //get the array of the form
      if (is_object($object)) {
          $form=call_user_func_array(array($object, $form_id), [$form,$form_state]);
      } else {
          $form=call_user_func($form_id, $form, $form_state);
      }

      //trigger form alter event
      $this->events->trigger('form_alter', array(&$form,&$form_state,$form_id,$object));

      //trigger form validation and submission
      if(isset($form['form']['ajax']) && is_array($form['form']['ajax'])) {
        $form_state['ajax']=true;
        $form_state['onerror']= isset($form['form']['ajax']['onerror']) ? $form['form']['ajax']['onerror'] : null;
        $form_state['callback']= isset($form['form']['ajax']['callback']) ? $form['form']['ajax']['callback'] : '';
        $form_state['preload']= isset($form['form']['ajax']['preload']) ? $form['form']['ajax']['preload'] : null;
        unset($form['form']['ajax'],$form['ajax']['preload']);
      } else {
        $form_state['ajax']=false;
      }

      if(isset($form['form']['onsubmit'])) {
        $form_state['onsubmit']=$form['form']['onsubmit'];
        unset($form['form']['onsubmit']);
      }



      if(strtolower($form['form']['method'])!='get') {$form['form']['method']='POST';}


      return (new form_object())->generate($form, $form_state, $form_id, $object);
    }
}





class form_object
{

    /**
    * form data
    */
    public $form=array();

    public $fields=array();

    public function __construct()
    {
    }

    public function generate($form, &$form_state, &$form_id, &$object)
    {
      //reset all form messages and errors
      form_set_error(null,null,null,true);
      form_push_message(null,null,null,true);

      $json_response=null;
      $form_onsubmit=null;


        $frm1=isset($form['form']) ? $form['form'] : array();
        if (!empty($frm1)) {
            unset($form['form']);
        }

    //generate form array
    $form=array('form'=>array_merge($frm1, $form));

    //get form fields
    $this->file_field_detected=false;
    $fields=$this->form_fields($form);

    //add enctype if file field is detected
    if($this->file_field_detected) {
      $form['form']['enctype']="multipart/form-data";
      //stdout($form);
    }

    //stdout($fields);

    //detect form method
    $method=strtoupper($form['form']['method']);
    //stdout($form_method);

    //validation and submission
    if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==$method) {

      $rawdata= $method=='POST' ? $_POST : $_GET;

      //does form_id match?
      if(isset($rawdata['form_id']) && $form_id==$rawdata['form_id']) {

      $formdata=array();

      foreach($fields as $field) {
        if($field=='form_id' || $field=='form_token') continue;
        $formdata[$field]= isset($rawdata[$field]) ? xss_clean($rawdata[$field]) : '';
      }

      $_form_id= isset($rawdata['form_id']) ? $rawdata['form_id'] : '';
      $_form_token= isset($rawdata['form_token']) ? $rawdata['form_token'] : '';

      $form_state['values']=$formdata;

      $form_state['files']= isset($_FILES) ? $_FILES : array();

      $form_state['submitted']=true;

      //flag to detect if form is submitted via ajax or not
      $form_state['ajax_submitted']=ajax_submission;



      //check for token at this point
      if(!isValidFormHash($_form_token,$form_id)) {
        $valid_token=false;
        form_set_error('token',t('base+form_invalid_token'));
      } else {

        $evtdata=array(&$form,&$form_state);
        //token is valid, so validate and submit
      //call form validation method/function
      if (is_object($object) && method_exists($object,$form_id.'_validate')) {
          call_user_func_array(array($object, $form_id.'_validate'), $evtdata);
      } else if(function_exists($form_id.'_validate')) {
          call_user_func($form_id.'_validate', $form, $form_state);
      }

      //trigger form validate event
      get_instance()->events->trigger('form_validate', array(&$form,&$form_state,$form_id,$object));

      //get errors
      $errors=form_set_error();

      if(empty($errors)) {
        $evtdata=array(&$form,&$form_state);

      //call form submission since validation was passed
      if (is_object($object) && method_exists($object,$form_id.'_submit')) {
          call_user_func_array(array($object, $form_id.'_submit'), $evtdata);
      } else if(function_exists($form_id.'_submit')) {
          call_user_func($form_id.'_submit', $form, $form_state);
      }

      //trigger form submit event
      get_instance()->events->trigger('form_submit', array(&$form,&$form_state,$form_id,$object));
      }

      }

      //at this point, all validation and submission is complete
      if($form_state['ajax']) {


        $state=array();
        $state['values']=$form_state['values'];
        $state['callback']=$form_state['callback'];

        $json_response=array();
        $json_response['errors']=form_set_error();
        $json_response['messages']=form_push_message();
        $json_response['form_state']=$state;
        //$json_response['form_token']=generateFormHash($form_id, $form_id);

        if(isset($_SESSION['messages'])) {unset($_SESSION['messages']);}
      }
    }
      //stdout($_form_id);
      //stdout($_form_token);

      //stdout($formdata);
    }



    //get html response
    $this->form_state=$form_state;

    //add form token and form id
    $form['form']['form_id'] = array(
    'type' => 'hidden',
    'name' => 'form_id',
    'value' => $form_id,
    );

    $form['form']['form_token'] = array(
    'type' => 'hidden',
    'name' => 'form_token',
    'value' => generateFormHash($form_id, $form_id),
    );


    //sort by weight
    //stdout($form);
    $form=array_recursive_order($form, true);

    //fix a bug to prevent form attributes from getting scattered
    $items=array();
    foreach($form['form'] as $key=>$item) {
    if(is_array($item)) {$items[$key]=$item;unset($form['form'][$key]);}
    }
    $form['form']+=$items;

    //conditions to include form api
    if((isset($form_state['onsubmit']) && $form_state['onsubmit'] != null) || !ajax_submission && $form_state['ajax'] ) {
      addScript("js/jquery/jquery.min.js",null,'asset');
      addScript("features/base/js/core.js",null,'asset');
      addScript("features/form/js/formapi.js",null,'asset');
    }

    //add frontend onsubmit
    if(isset($form_state['onsubmit']) && $form_state['onsubmit'] != null) {
      $fx=$form_state['onsubmit'];
      unset($form_state['onsubmit']);

      if(strpos($fx,'function')===false) {$fx="'$fx'";}

      addScript("form_api_onsubmit($fx,'$form_id');",null,'inline');
    }

    //add frontend ajax error handler
    if(isset($form_state['onerror']) && $form_state['onerror'] != null) {
      $fx=$form_state['onerror'];
      unset($form_state['onerror']);

      if(strpos($fx,'function')===false) {$fx="'$fx'";}

      addScript("form_api_onerror($fx,'$form_id');",null,'inline');
    }



    //add ajax callback
    if(!ajax_submission && $form_state['ajax']) {
    //call form preload here
    if(is_callable($form_state['preload'])) {$form_state['preload'](); unset($form_state['preload']);}

    //trigger form preload event (ajax only)
    get_instance()->events->trigger('form_preload', array(&$form,&$form_state,$form_id,$object));

    //get parameters to pass to ajax
    $action=$form['form']['action'];
    $method=strtoupper($form['form']['method']);

    addScript("form_api_ajax('$action','$method','$form_id');",null,'inline');
    }

    $response=$this->process($form);

    //render json output
    if($json_response!=null) {
      $json_response['html']=$response;
      die('x');
      echo(json_encode($json_response));
      exit();
    }



    //stdout($form_state);

    return $response;
    }

  /**
  * get form fields
  */
  public function form_fields($form, $result=array())
  {
      foreach ($form as $name=>$attributes) {
          if (isset($attributes['type'])) {

            $attributes['type']=strtolower($attributes['type']);

            if($attributes['type']=='file') {$this->file_field_detected=true;}



              $fname= isset($attributes['name']) ? $attributes['name'] : (!$this->is_form_button($attributes['type']) ? $name : null);

              $ftype= isset($attributes['type']) ? strtolower($attributes['type']) : null;
              if ($ftype!=null && $fname!=null) {
                  $fname= isset($attributes['name']) ? $attributes['name'] : $name;
                    //writeln("$ftype => $fname");
                    if($this->is_valid_form_type($ftype)) {
                      $result[]=$fname;
                    }
              }
          }

          if (is_array($attributes)) {
              $result=$this->form_fields($attributes, $result);
          }
      }

      return $result;
  }

/**
* checks if a name matches a valid form type
*
*/
public function is_valid_form_type($ftype) {
  switch (strtolower($ftype)) {
  case 'button':
  case 'submit':
  case 'reset':
  case 'checkbox':
  case 'radio':
  case 'textarea':
  case 'select':
  case 'email':
  case 'hidden':
  case 'number':
  case 'text':
  case 'checkbox':
  case 'radio':
  case 'password':
  case 'file':
  return true;
  break;
  }
return false;
}

public function is_form_button($ftype)
{
  switch (strtolower($ftype)) {
  case 'button':
  case 'submit':
  return true;
  break;
  }
  return false;
}

    /**
    * Process form directives from array
    */
    public function process($form)
    {

        $response="";
        $_data=array();

        $count = 0;

        //loop though each element
        foreach ($form as $name=>$attributes) {
            $type= isset($attributes['type']) ? $attributes['type'] : null;

      //remove weight attribute so it does not render in html
      if (isset($attributes['weight'])) {
          unset($attributes['weight']);
      }

      //set name attribute for valid form types
      if(!$this->is_form_button($attributes['type']) && !isset($attributes['name']) && ($this->is_valid_form_type($attributes['type']))) {
        $attributes['name']=$name;
      }


      //set the default value from the form_state
      if (isset($attributes['name']) && isset($this->form_state['values'][$attributes['name']])) {

        //retrieve value from form-state
        $val=$this->form_state['values'][$attributes['name']];


          if (isset($attributes['type'])) {
              switch (strtolower($attributes['type'])) {
            case 'checkbox':
            case 'radio':
            if (isset($attributes['value']) && $attributes['value']==$val) {
                $attributes['checked']=true;
            }
            break;
            case 'textarea':
            $attributes['text']=$val;
            break;
            case 'select':
            $val=(array) $val;
            $attributes['values']=$val;
            break;
            default:
            //input etc
            $attributes['value']=$val;
          }
          }
      }


            $description="";
            $text="";
            $help="";

            if (isset($attributes['description'])) {
                $description=$attributes['description']."\n";
                unset($attributes['description']);
            }

            if (isset($attributes['text'])) {
                $text=$attributes['text'];
                unset($attributes['text']);
            }


            if (isset($attributes['help'])) {
                $description="<p class=\"help-block\">".$attributes['help']."</p>";
                unset($attributes['help']);
            }

            switch ($type) {
            /*buttons here e.g. button, submit, reset*/
            case 'button':
            case 'submit':
            case 'reset':

            $response.=$this->process_element($name, $attributes, "<button ");
            $response.="$text\n</button>\n";
            break;

            case 'select':
            unset($attributes['type']);

            $options=array();
            $values=array();
            $value2=array();

            if (isset($attributes['options'])) {
                $options=(array) $attributes['options'];
                unset($attributes['options']);
            }
            if (isset($attributes['values'])) {
                $values=(array) $attributes['values'];
                unset($attributes['values']);
            }
            if (isset($attributes['value'])) {
                $value2=(array) $attributes['value'];
                unset($attributes['value']);
            }

            $response.=$this->process_element($name, $attributes, "<{$type} ");

            $assoc=isAssoc($options);
            foreach ($options as $value=>$text) {
                if (!$assoc) {
                    $value=$text;
                }
                $selected= (in_array($value, $values) || in_array($value, $value2)) ? ' selected="selected" ' : '';
                $response.="<option $selected value=\"$value\">$text</option>\n";
            }


            $response=trim($response)."$text\n</{$type}>\n";
            break;

            //non-containers
            case 'email':
            case 'hidden':
            case 'number':
            case 'text':
            case 'checkbox':
            case 'radio':
            case 'password':
            case 'file':

            case 'br':
            case 'hr':
            case 'img':
            $response.=$this->process_element($name, $attributes, '<input ');
             break;

            //container tags e.g. form, div, span, fieldset, label etc
            default:
            unset($attributes['type']);

            $response.=$this->process_element($name, $attributes, "<{$type} ");

            $response=trim($response)."$text\n</{$type}>\n";
            break;
            }

            //add help and description here
            $response= trim($response) . " $description" . $help;
        }


        return $response;
    }

    /**
    * process individual elements and coverts them to html
    *
    */
    public function process_element($name, $attributes, $prefix, $close=true)
    {
        $response="$prefix";
        $count=0;
        foreach ($attributes as $name=>$value) {
            if (is_array($value)) {
                if ($close && $count==0) {
                    $response= trim($response) . ">\n";
                }
                $count++;
                $item=array("$name"=>$value);
                $response.=$this->process($item);
                continue;
            }
            $response.="$name=\"$value\" ";
        }
        $response=trim($response);

        if ($close && $count==0) {
            $response= trim($response) . ">";
        }

        return $response;
    }
}
