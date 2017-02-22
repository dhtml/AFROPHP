<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Core_Module_Ctrl extends Controller
{
    public function create()
    {
        echo 'Module Create';
    }

    public function enable()
    {
        echo 'Module Enable';
    }

    public function disable()
    {
        echo 'Module Disable';
    }
}
