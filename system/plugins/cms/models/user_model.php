<?php
defined('BASEPATH') or exit('No direct script access allowed');
class User_model extends model
{
    public $table = '{users}';

    public function __construct()
    {
      //$this->db->drop("$this->table");
      parent::__construct();
    }


    public function create_schema()
    {
      parent::create_schema("
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(128) NOT NULL DEFAULT '',
      `username` varchar(32) NOT NULL DEFAULT '',
      `password` varchar(64) NOT NULL DEFAULT '',
      `joinStamp` int(11) NOT NULL DEFAULT '0',
      `activityStamp` int(11) NOT NULL DEFAULT '0',
      `accountType` varchar(32) NOT NULL DEFAULT '',
      `emailVerify` tinyint(2) NOT NULL DEFAULT '0',
      `joinIp` int(11) UNSIGNED NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      UNIQUE KEY `username` (`username`),
      KEY `accountType` (`accountType`),
      KEY `joinStamp` (`joinStamp`),
      KEY `activityStamp` (`activityStamp`)
      ");

      $json='
      [
          {
              "id": "1",
              "username": "admin",
              "email": "admin@website.com"
          },
          {
              "id": "2",
              "username": "Guest",
              "email": "anonymous@website.com"
          }
      ]';
      $this->insert_json_string($json);
    }
}
