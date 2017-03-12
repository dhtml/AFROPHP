<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Feed_model extends model
{
    public $table = '{feeds}';

    public function __construct()
    {
        parent::__construct();
    }


    public function create_schema()
    {

        parent::create_schema("
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` int(11) UNSIGNED NOT NULL,
        `hash` varchar(255) NOT NULL,
        `time` int(11) UNSIGNED NOT NULL,
        `scope` varchar(255) NOT NULL,
        `parent` varchar(255) NOT NULL,
        `text` varchar(255) NOT NULL,
        `otext` varchar(255) NOT NULL,
        `image` varchar(255) NOT NULL,
        `status` int(11) UNSIGNED NOT NULL,
        `created_at` datetime DEFAULT NULL,
        `created_by` int(10) UNSIGNED DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        `updated_by` int(10) UNSIGNED DEFAULT NULL,
        `deleted_at` datetime DEFAULT NULL,
        `deleted_by` int(10) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
        ");


        $this->insert_data();
    }


    public function insert_data()
    {
      $json='[
    {
        "id": "1",
        "user_id": "1",
        "hash": "21000000222110112010",
        "time": "1413868471",
        "scope": "newsfeed",
        "parent": "",
        "text": "I am still wondering about this Titanium Mobile Development Environment. Guess I shall be giving it a trial very soon.",
        "otext": "I am still wondering about this Titanium Mobile Development Environment. Guess I shall be giving it a trial very soon.",
        "image": "",
        "status": "0"
    },
    {
        "id": "2",
        "user_id": "1255",
        "hash": "20120102220111112122",
        "time": "1413873286",
        "scope": "comment",
        "parent": "21000000222110112010",
        "text": "really :D",
        "otext": "really :D",
        "image": "",
        "status": "0"
    },
    {
        "id": "3",
        "user_id": "1",
        "hash": "11221100110202102012",
        "time": "1413876244",
        "scope": "comment",
        "parent": "21000000222110112010",
        "text": "Smileys are not yet supported, we shall get to that one sooner or later",
        "otext": "Smileys are not yet supported, we shall get to that one sooner or later",
        "image": "",
        "status": "0"
    }
]';

    //$this->insert_json_string($json);

    $this->insert_json_url(__DIR__."/datasource/feeds2.json");
    }
}
