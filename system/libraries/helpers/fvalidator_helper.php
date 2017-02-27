<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class fvalidator
{
    const PASSWORD_MIN_LENGTH = 4;

    const PASSWORD_MAX_LENGTH = 15;

    const USER_NAME_PATTERN = '/^[\w]{1,32}$/';

    const EMAIL_PATTERN = '/^([\w\-\.\+\%]*[\w])@((?:[A-Za-z0-9\-]+\.)+[A-Za-z]{2,})$/';
    

    const URL_PATTERN = '/^(http(s)?:\/\/)?((\d+\.\d+\.\d+\.\d+)|(([\w-]+\.)+([a-z,A-Z][\w-]*)))(:[1-9][0-9]*)?(\/?([\w-.\,\/:%+@&*=]+[\w- \,.\/?:%+@&=*|]*)?)?(#(.*))?$/';

    const INT_PATTERN = '/^[-+]?[0-9]+$/';

    const FLOAT_PATTERN = '/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/';

    const ALPHA_NUMERIC_PATTERN = '/^[A-Za-z0-9]+$/';

    public static function isEmailValid( $value )
    {
		return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }
	
	public static function isPhoneValid($value) {
		$isPhoneNum=false;
		//eliminate every char except 0-9
		$justNums = preg_replace("/[^0-9]/", '', $value);
		
		if (strlen($justNums) > 5) {return true;}
		
		return false;
	}
	
	
	public static function isCaptchaValid($value) {
	   if( $_SESSION['security_code'] == $value && !empty($_SESSION['security_code'] ) ) {
		unset($_SESSION['security_code']);
		return true;
		} else {
		return false;
		}
	}

	public static function isVideoUrl($value) {
	if(!self::isUrlValid($value)) {return false;}
	
	$bsearch=array(
	'https://www.',
	'http://www.',
	'https://',
	'http://',
	'www.',
	);
	$value=str_replace($bsearch,'//',$value);
	
	
	$valid=array(
	'//player.vimeo.com/video/',
	'//youtube.com/embed/',
	'//dailymotion.com/embed/',
	'//dailymotion.com/embed/',
	'//hulu.com/',
	'//netflix.com/',
	'//screen.yahoo.com/',
	'//yahoo.com/',
	'//vube.com/embed/',
	'//twitch.tv/',
	'//liveleak.com/',
	'//vine.co/',
	'//ustream.tv/',
	'//break.com/',
	'//tv.com/',
	'//metacafe.com/',
	'//viewster.com/',
	'//viewster.com/',
	);
	
	foreach($valid as $vid) {
	 if(strpos($value,$vid)!==false) {return true;}
	}
	
	return false;
	}

    public static function isUrlValid( $value )
    {
	if(substr($value,0,2)=='//') {$value="http:{$value}";}
        $pattern = self::URL_PATTERN;

        $trimValue = trim($value);

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isIntValid( $value )
    {
        $intValue = (int) $value;

        if ( !preg_match(self::INT_PATTERN, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isFloatValid( $value )
    {
        $floatValue = (float) $value;

        if ( !preg_match(self::FLOAT_PATTERN, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isAlphaNumericValid( $value )
    {
        $pattern = self::ALPHA_NUMERIC_PATTERN;

        $trimValue = trim($value);

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isUserNameValid( $value )
    {
		return self::sanitize_username($value)==$value ? true : false;
    }
	
	/***
	* Removes all illegal characters from a username
	*/
	public static function sanitize_username($s)
	{
				$s=preg_replace(array
					('/[^\w\-\. ]+/u','/\s\s+/','/\.\.+/','/--+/','/__+/'),
				array(' ',' ','.','-','_'),$s
				);
				
				$s=str_replace(array('_','.','-'),'',$s);
				$s=filter_var($s, FILTER_SANITIZE_EMAIL);
				return $s;
	}

	/**
	* Create a string of random characters the desired length.
	*
	* @param int the length of the string
	* @param bool $only_letters if true
	* @return array
	*/
	public static function random_characters($length, $only_letters = FALSE)
	{
		$s='';for($i=0;$i<$length; $i++)$s.=($only_letters?chr(mt_rand(33,126)):chr(mt_rand(65,90)));return$s;
	}

	/**
	* Filter a valid UTF-8 string so that it contains only words, numbers,
	* dashes, underscores, periods, and spaces - all of which are safe
	* characters to use in file names, URI, XML, JSON, and (X)HTML.
	*
	* @param string $string to clean
	* @param bool $spaces TRUE to allow spaces
	* @return string
	*/
	public static function sanitize($s,$spaces=TRUE)
	{
		$s=preg_replace(array('/[^\w\-\. ]+/u','/\s\s+/','/\.\.+/','/--+/','/__+/'),array(' ',' ','.','-','_'),$s);if(!$spaces)$s=preg_replace('/--+/','-',str_replace(' ','-',$s));return trim($s,'-._ ');
	}

	/**
	* Create a SEO friendly URL string from a valid UTF-8 string
	*
	* @param string $string to filter
	* @return string
	*/
	public static function sanitize_url($string)
	{
		return urlencode(strtolower(self::sanitize($string,FALSE)));
	}	
	public static function isRealNameValid($value) {
	$value=explode(' ',$value);
	if(count($value)!=2) {return false;} 
	$value=implode('',$value);
	if(!self::isAlphaNumericValid($value)) {return false;}
	return true;
	}
	
	public static function isNameValid($value) {
	if(!self::isAlphaNumericValid($value)) {return false;}
	return true;
	}

    public static function isDateValid( $month, $day, $year )
    {
	if(!is_numeric($month)||!is_numeric($day)||!is_numeric($year)) {return false;}
        if ( !checkdate($month, $day, $year) )
        {
            return false;
        }
        return true;
    }
}
