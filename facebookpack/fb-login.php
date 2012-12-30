<?php
require(dirname(dirname(dirname(__FILE__))).'/config/config.inc.php');

function parse_date($date) {
	$timestamp = strtotime($date);
	if (false === $timestamp) {
		return '';
	} else {
		$post_date = date('Y-m-d', $timestamp);
		return $post_date;
	}
}

function generate_password($length=9, $strength=0) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}

	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}

$config = Configuration::getMultiple ( array ('FBPACK_APP_ID', 'FBPACK_APP_SECRET' ) );
if (isset ( $config ['FBPACK_APP_ID'] ) && isset ( $config ['FBPACK_APP_SECRET'] )) {
	require_once 'src/facebook.php';
	
	/* Server Params */
	$server_host_ssl = Tools::getHttpHost(false, true);
	$server_host     = str_replace(':'._PS_SSL_PORT_, '',$server_host_ssl);
	$protocol = 'http://';
	
	global $cookie;
	
	// Init Cookie
	$cookie = new Cookie('ps');
	
	$facebook = new Facebook(array(
	      'appId'  => $config ['FBPACK_APP_ID'],
	      'secret' => $config ['FBPACK_APP_SECRET'],
	));
	
	// See if there is a user from a cookie
	$fb_user = $facebook->getUser();
	
	if ($fb_user) {
        try {
            // Proceed knowing you have a logged in user who's authenticated.
            $fb_user_profile = $facebook->api('/me');
            $fb_user_id = $fb_user_profile['id'];
        } catch (FacebookApiException $e) {
            //echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
            $fb_user = null;
        }
    }
    
    // User is not Logged and FB user is authorized
    if (!$cookie->isLogged() && isset($fb_user_id)) {
    	$customer = new Customer();
        
        $authentication = $customer->getByEmail(trim($fb_user_profile['email']));
        if (!$authentication OR !$customer->id) {
        	// new User
        	$customer = new Customer();
        	$customer->firstname = $fb_user_profile['first_name'];
        	$customer->lastname  = $fb_user_profile['last_name'];
        	$customer->email	 = $fb_user_profile['email'];
        	$passwd = generate_password(10,8);
        	$customer->passwd	 = md5(_COOKIE_KEY_ . $passwd);
        	/** Only php 5.3 */
            if (!defined('PHP_VERSION_ID')) {
			    $version = explode('.', PHP_VERSION);
			    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
			}
			if (array_key_exists('birthday', $fb_user_profile)) {
				if (PHP_VERSION_ID < 50300) {
					$birthday = parse_date($fb_user_profile['birthday']);
				} else {
					$date = DateTime::createFromFormat('m/d/Y', $fb_user_profile['birthday']);
					if ($date)
	                	$birthday = $date->format('Y-m-d');
					else 
						$birthday = '';
				}
	        	if ($birthday)
	        		$customer->birthday = $birthday;
			}
        	$customer->active = 1;
        	if (!$customer->add())
        		$errors[] = Tools::displayError('an error occurred while creating your account');
        	else {
        		if (!Mail::Send(intval($cookie->id_lang), 'account', 'Welcome!',
        		array('{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{email}' => $customer->email, '{passwd}' => $passwd), $customer->email, $customer->firstname.' '.$customer->lastname))
        		$errors[] = Tools::displayError('cannot send email');
        	}
        	if (isset($errors)) {
        		die(Tools::displayError('an error occurred while creating your account via facebook'));
        	}
        	$cookie->id_customer = intval($customer->id);
        	$cookie->customer_lastname = $customer->lastname;
        	$cookie->customer_firstname = $customer->firstname;
        	$cookie->passwd = $customer->passwd;
        	$cookie->logged = 1;
        	$cookie->email = $customer->email;
        	$cookie->fb_user_id = $fb_user_id;
        	Module::hookExec('authentication');
        	$url = $protocol.$server_host . '/my-account.php';
        	header("Location: $url");
        	exit;
        }
        else
        { // existing User
            $cookie->id_customer = intval($customer->id);
            $cookie->customer_lastname = $customer->lastname;
            $cookie->customer_firstname = $customer->firstname;
            $cookie->logged = 1;
            $cookie->passwd = $customer->passwd;
            $cookie->email = $customer->email;
            $cookie->fb_user_id = $fb_user_id;
            if (Configuration::get('PS_CART_FOLLOWING') AND (empty($cookie->id_cart) OR Cart::getNbProducts($cookie->id_cart) == 0))
                $cookie->id_cart = intval(Cart::lastNoneOrderedCart(intval($customer->id)));
            Module::hookExec('authentication');
            $url = $protocol.$server_host . '/my-account.php';
        	header("Location: $url");
        	exit;
        }
    }
}

