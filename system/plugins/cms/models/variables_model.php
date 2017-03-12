<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Variables_model extends model
{
    public $table = '{variables}';

    public function __construct()
    {
        parent::__construct();
    }


    public function create_schema()
    {
      $create="
      CREATE TABLE IF NOT EXISTS `{$this->table}` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` tinytext NOT NULL,
        `scope` tinytext NOT NULL,
        `value` mediumtext NOT NULL,
        `vid` tinytext NOT NULL,
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
    }
}
