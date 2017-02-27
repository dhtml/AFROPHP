<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Users_model extends My_Model
{
    public $table = '{users}';

    public function __construct()
    {
        parent::__construct();
    }


    public function create_schema()
    {
      $create="
      CREATE TABLE IF NOT EXISTS `{$this->table}` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `auth_id` int(11) UNSIGNED NOT NULL,
        `token_id` int(11) UNSIGNED NOT NULL,
        `role_id` int(11) UNSIGNED NOT NULL,
        `username` varchar(100) NOT NULL,
        `email` varchar(255) NOT NULL,
        `phone` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `verification` varchar(255) NOT NULL,
        `source` varchar(50) NOT NULL,
        `status` int(11) UNSIGNED NOT NULL DEFAULT '1',
        `last_login` datetime DEFAULT NULL,
        `ip_address` varchar(50) NOT NULL,
        `last_activity` datetime DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        `created_by` int(10) UNSIGNED DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        `updated_by` int(10) UNSIGNED DEFAULT NULL,
        `deleted_at` datetime DEFAULT NULL,
        `deleted_by` int(10) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      ";
      $this->db->query($create);

      $this->insert_data();
    }


    public function insert_data()
    {
      $json='
      [
          {
              "id": "1",
              "role_id": "4",
              "username": "admin",
              "email": "admin@website.com",
              "phone": "",
              "password": ""
          },
          {
              "id": "2",
              "role_id": "1",
              "username": "Guest",
              "email": "anonymous@website.com",
              "phone": "",
              "password": ""
          }
      ]';

      $this->insert_json_string($json);
    }
}
