<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 * Query builder helper
 */
class httpquery {

    /**
     * Array to hold query
     */
    private $query = Array();

    /**
     * Array to hold parts of the query
     */
    private $parts = Array();

    /**
     * pass a full or partial url into this e.g. http://google.com/?q=search
     */
    public function __construct($url) {
        $this -> parts = parse_url($url);

        if (isset($this -> parts['query'])) {
            parse_str($this -> parts['query'], $this -> query);
        }
		
		return $this;
    }

    function unparse_url($parsed_url) {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) && $parsed_url['query']!='' ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * adds a new parameter to the query
     *
     * @return object
     */
    public function set($name, $value) {
        $this -> query["$name"] = $value;
        return $this;
    }
	
    /**
     * removes a query item
     *
     * @return object
     */
	public function remove($name) {
        if(isset($this -> query["$name"])) {unset($this -> query["$name"]);}
        return $this;
	}

    /**
     * rebuilds query to path
     *
     * @return string
     */
    public function rebuild() {
        $this -> parts['query'] = http_build_query($this -> query);
        return $this -> unparse_url($this -> parts);
    }

	//returns query string
	public function query_string() {
        $parsed_url = http_build_query($this -> query);
		return $parsed_url;
	}
	
	public function __toString()
    {
        return $this->query_string();
    }
}
