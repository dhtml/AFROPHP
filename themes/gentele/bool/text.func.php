<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->registerPlugin("function","text", "theme_text_func");

/**
* theme_text_func
*
* translate a text from inside the template file using language files found in a plugin
*
* <code>
* {text key="kinfet+your_thoughts_heading"}
* </code>
*
* @param  array     $params     array of parameters
* @param  object    $smarty     Smarty template object
*
* @return   the translated string
*/
function theme_text_func($params, $smarty) {
        if (empty($params["key"])) {
            return "";
        } else {
            $key = explode('+',$params["key"]);
            return get_instance()->lang->text($key[0], $key[1]);
        }
}
