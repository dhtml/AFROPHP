<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->registerPlugin("block","translate", "theme_translate_block");

/**
* theme_translate_block
*
* translate a text from inside the template file using language files found in a plugin
*
* <code>
* {translate from="en" to="fr" }I am single{/translate}
* </code>
* if from is not specified, auto will be assumed
* if to is not specified, default is the current language being used
*
* @param  array     $params     array of parameters
* @param  string    $content    the content of the block (between the tags)
* @param  object    $smarty     Smarty template object
* @param  integer   $repeat     The current repeat value. A block function is called twice.
*
* @return   the translated string
*/
function theme_translate_block($params, $content, $smarty, &$repeat) {
  if (isset($content)) {
    $to = isset($params["to"]) ? $params["to"] : null;
    $from = isset($params["from"]) ? $params["from"] : null;
    // do some translation with $content
    $translation=get_instance()->lang->translate($content,$to,$from);
    return $translation;
  }
}
