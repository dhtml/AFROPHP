<?php
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');

class Input extends \System\Base\Prototype
{

	/**
	 * Fetch from array
	 *
	 * Internal method used to retrieve values from global arrays.
	 *
	 * @param	array	&$array		$_GET, $_POST, $_COOKIE, $_SERVER, etc.
	 * @param	mixed	$index		Index for item to be fetched from $array
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	protected function _fetch_from_array(&$array, $index = NULL, $xss_clean = FALSE)
	{

		if (isset($array[$index]))
		{
			$value = $array[$index];
		}
		else
		{
			return NULL;
		}


		return ($xss_clean === TRUE)
			? $this->security->xss_clean($value)
			: $value;
	}

/**
 * Fetch an item from the GET array
 *
 * @param	mixed	$index		Index for item to be fetched from $_GET
 * @param	bool	$xss_clean	Whether to apply XSS filtering
 * @return	mixed
 */
public function get($index = NULL, $xss_clean = FALSE)
{
  return $this->_fetch_from_array($_GET, $index, $xss_clean);
}


/**
 * Fetch an item from the SESSION array
 *
 * @param	mixed	$index		Index for item to be fetched from $_SESSION
 * @param	bool	$xss_clean	Whether to apply XSS filtering
 * @return	mixed
 */
public function session($index = NULL, $xss_clean = FALSE)
{
  return $this->_fetch_from_array($_SESSION, $index, $xss_clean);
}


/**
 * Fetch an item from the POST array
 *
 * @param	mixed	$index		Index for item to be fetched from $_POST
 * @param	bool	$xss_clean	Whether to apply XSS filtering
 * @return	mixed
 */
public function post($index = NULL, $xss_clean = FALSE)
{
  return $this->_fetch_from_array($_POST, $index, $xss_clean);
}



	/**
	 * Fetch an item from POST data with fallback to GET
	 *
	 * @param	string	$index		Index for item to be fetched from $_POST or $_GET
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function post_get($index, $xss_clean = FALSE)
	{
		return isset($_POST[$index])
			? $this->post($index, $xss_clean)
			: $this->get($index, $xss_clean);
	}

  /**
	 * Fetch an item from GET data with fallback to POST
	 *
	 * @param	string	$index		Index for item to be fetched from $_GET or $_POST
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function get_post($index, $xss_clean = FALSE)
	{
		return isset($_GET[$index])
			? $this->get($index, $xss_clean)
			: $this->post($index, $xss_clean);
	}


  	/**
  	 * Fetch an item from the COOKIE array
  	 *
  	 * @param	mixed	$index		Index for item to be fetched from $_COOKIE
  	 * @param	bool	$xss_clean	Whether to apply XSS filtering
  	 * @return	mixed
  	 */
  	public function cookie($index = NULL, $xss_clean = FALSE)
  	{
  		return $this->_fetch_from_array($_COOKIE, $index, $xss_clean);
  	}


    	/**
    	 * Fetch an item from the SERVER array
    	 *
    	 * @param	mixed	$index		Index for item to be fetched from $_SERVER
    	 * @param	bool	$xss_clean	Whether to apply XSS filtering
    	 * @return	mixed
    	 */
    	public function server($index, $xss_clean = FALSE)
    	{
    		return $this->_fetch_from_array($_SERVER, $index, $xss_clean);
    	}


      /**
      * Fetch User Agent string
      *
      * @return	string|null	User Agent string or NULL if it doesn't exist
      */
      public function user_agent($xss_clean = FALSE)
      {
      		return $this->_fetch_from_array($_SERVER, 'HTTP_USER_AGENT', $xss_clean);
      }


      	 /**
         * cookie
         *
      	 * Set cookie
      	 *
      	 * Accepts an arbitrary number of parameters (up to 7) or an associative
      	 * array in the first parameter containing all the values.
      	 *
      	 * @param	string|mixed[]	$name		Cookie name or an array containing parameters
         *
      	 * @param	string		$value		Cookie value
         *
      	 * @param	int		$expire		Cookie expiration time in seconds
         *
      	 * @param	string		$domain		Cookie domain (e.g.: '.yourdomain.com')
         *
      	 * @param	string		$path		Cookie path (default: '/')
         *
      	 * @param	string		$prefix		Cookie name prefix
         *
      	 * @param	bool		$secure		Whether to only transfer cookies via SSL
         *
      	 * @param	bool		$httponly	Whether to only makes the cookie accessible via HTTP (no javascript)
         *
      	 * @return	void
      	 */
      	public function set_cookie($name, $value = '', $expire = 0, $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httponly = FALSE)
      	{

      		if (is_array($name))
      		{
      			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
      			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
      			{
      				if (isset($name[$item]))
      				{
      					$$item = $name[$item];
      				}
      			}
      		}

      		if ($prefix == '' && config_item('cookie_prefix','') != '')
      		{
      			$prefix = config_item('cookie_prefix','');
      		}

      		if ($domain == '' && config_item('cookie_domain') != '')
      		{
      			$domain = config_item('cookie_domain');
      		}

      		if ($path === '/' && config_item('cookie_path') !== '/')
      		{
      			$path = config_item('cookie_path');
      		}

      		if ($secure === FALSE && config_item('cookie_secure',false,true) === TRUE)
      		{
      			$secure = config_item('cookie_secure',false,true);
      		}

      		if ($httponly === FALSE && config_item('cookie_httponly',false,true) !== FALSE)
      		{
      			$httponly = config_item('cookie_httponly',false,true);
      		}

      		if ( ! is_numeric($expire) OR $expire < 0)
      		{
      			$expire = 1;
      		}
      		else
      		{
      			$expire = ($expire > 0) ? time() + $expire : 0;
      		}


      		setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
      	}

        /**
      	 * Is AJAX request?
      	 *
      	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
      	 *
      	 * @return 	bool
      	 */
      	public function is_ajax_request()
      	{
      		return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
      	}

        /**
         * Get Request Method
         *
         * Return the request method
         *
         * @param	bool	$upper	should the result be returned in uppercase?
         *
         * @return 	string
         */
        public function method($upper = FALSE)
        {
          return ($upper)
            ? strtoupper($this->server('REQUEST_METHOD'))
            : strtolower($this->server('REQUEST_METHOD'));
        }
}
