<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Roles_model extends My_Model
{
    public $table = '{roles}';

    public function __construct()
    {
        parent::__construct();
    }


    public function create_schema()
    {
      $create="
      CREATE TABLE IF NOT EXISTS `{$this->table}` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `perms` text NOT NULL,
        `locked` int(11) UNSIGNED NOT NULL,
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
      $json='[
    {
        "id": "1",
        "name": "anonymous user",
        "description": "Website visitors that are not logged in.",
        "locked": "1"
    },
    {
        "id": "2",
        "name": "authenticated user",
        "description": "A registered user who presently signed in on the website.",
        "locked": "1"
    },
    {
        "id": "3",
        "name": "moderator",
        "description": "A person moderating the website",
        "locked": "1"
    },
    {
        "id": "4",
        "name": "superadmin",
        "description": "A super administrative user",
        "locked": "1"
    }
]';

      $this->insert_json_string($json);
    }
}
