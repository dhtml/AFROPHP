<?php
/**
* The email class
*/

defined('BASEPATH') or exit('No direct script access allowed');

include_once __DIR__."/vendor/autoload.php";

class Email extends PHPMailer
{

/**
* class constructor extending PHPMailer library
*/
public function __construct()
    {

      //if sending of email is disabled
      if(!config_item('send_enabled',true,true)) {
        return;
      }

      parent::__construct();

      //check of smtp configuration
      if(config_item('smtp_enabled',false,true)) {
        $this->isSMTP();
        $this->Host = config_item('smtp_host');
        $this->SMTPAuth = true;
        $this->Username = config_item('smtp_username');
        $this->Password = config_item('smtp_password');
        $this->SMTPSecure = config_item('smtp_secure');
        $this->Port = config_item('smtp_port');;
      }
    }


    /**
    * sender of the mail
    *
    * @param string   $email    The email address of the sender
    * @param string   $name     The name of the sender
    *
    * @return object
    */
    public function from($email,$name='')
    {
      $this->setFrom($email,$name);
      return $this;
    }

    /**
    * recipient of the mail
    *
    * @param string   $email    The email address of the recipient
    * @param string   $name     The name of the recipient
    *
    * @return object
    */
    public function to($email,$name='')
    {
      $this->addAddress($email,$name);
      return $this;
    }


         /**
         * cc
         *
         * carbon copy of the mail
         *
         * @param string   $email    The email address of the recipient
         * @param string   $name     The name of the recipient
         *
         * @return object
         */
          public function cc($email,$name='')
          {
            $this->addCC($email,$name);
            return $this;
          }


            /**
            * bcc
            *
            * Blind carbon copy of the mail
            *
            * @param string   $email    The email address of the recipient
            * @param string   $name     The name of the recipient
            *
            * @return object
            */
            public function bcc($email,$name='')
            {
              $this->addBCC($email,$name);
              return $this;
            }


            /**
            * reply_to
            *
            * Specify the reply_to field
            *
            * @param string   $email    The email address of the sender
            * @param string   $name     The name of the sender
            *
            * @return object
            */
            public function reply_to($email,$name='')
            {
              $this->addReplyTo($email,$name);
              return $this;
            }

            /**
            * subject
            *
            * Subject of the mail
            *
            * @param string   $subject    The subject of the email
            *
            * @return object
            */
            public function subject($subject='')
            {
              $this->Subject=$subject;
              return $this;
            }

            /**
            * message
            *
            * Message of the mail
            *
            * @param string   $message    The message of the email
            *
            * @return object
            */
            public function message($message='')
            {
              $this->Body    = $message;
              $this->AltBody = strip_tags($message);

              if($this->Body!=$this->AltBody) {
                $this->isHTML(true);
              }

              return $this;
            }



            /**
             * send
             *
             * Create a message and send it.
             * Uses the sending method specified by $Mailer.
             *
             * @param boolean   clear   Set to true to clear the addresses after sending the mail
             *
             * @throws phpmailerException
             * @return boolean false on error - See the ErrorInfo property for details of the error.
             */
            public function send($clear=true)
            {
              //if sending of email is disabled
              if(!config_item('send_enabled',true,true)) {
                return false;
              }

              $response=parent::send();
              if($clear) {$this->clear();}
              return $response;
            }

            /**
            * clear
            *
            * Clears all addresses and attachments
            *
            * @return object
            */
            public function clear()
            {
                $this->clearAddresses();
                $this->clearAttachments();
                return $this;
            }


}
