<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Profiles_model extends model
{
    public $table = '{profiles}';

    public function __construct()
    {
        parent::__construct();
    }


    public function create_schema()
    {
      $create="
      CREATE TABLE IF NOT EXISTS `{$this->table}` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` int(11) UNSIGNED NOT NULL,
        `first_name` varchar(100) NOT NULL,
        `last_name` varchar(100) NOT NULL,
        `address` varchar(100) NOT NULL,
        `sex` varchar(1) NOT NULL,
        `picture` varchar(255) NOT NULL,
        `location` varchar(255) NOT NULL,
        `url` varchar(255) NOT NULL,
        `day_birthdate` int(11) UNSIGNED NOT NULL,
        `month_birthdate` int(11) UNSIGNED NOT NULL,
        `year_birthdate` int(11) UNSIGNED NOT NULL,
        `birthday` datetime NOT NULL,
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
              "user_id": "1",
              "first_name": "Admin",
              "last_name": "User",
              "sex": "1",
              "day_birthdate": "0",
              "month_birthdate": "0",
              "year_birthdate": "0"
          },
          {
              "user_id": "2",
              "first_name": "Guest",
              "last_name": "Guest",
              "sex": "1",
              "day_birthdate": "0",
              "month_birthdate": "0",
              "year_birthdate": "0"
          }
      ]';

      $this->insert_json_string($json);
    }
}
