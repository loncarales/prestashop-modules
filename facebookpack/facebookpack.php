<?php
if (! defined ( '_CAN_LOAD_FILES_' ))
	exit ();

/**
 * Facebook Pack
 *
 * Facebook Pack contains Facebook Social Plugins which let you see what your friends have liked, commented on or shared on sites across the web.
 *
 * @ (C) Copyright 2011 by (internet-solutions.si | celavi.org) Ales Loncar
 * @ Version 1.0
 *
 */
class FacebookPack extends Module {
	const INSTALL_SQL_FILE = 'install.sql';
	const UNINSTALL_SQL_FILE = 'uninstall.sql';
	
	private $_html = '';
	private $_postErrors = array ();
	
	private $_fbPack_app_id = '';
	private $_fbPack_app_secret = '';
	private $_fbPack_app_locale = 'en_US';
	
	private $_fbPack_like_button = 'no';
	private $_fbPack_like_url = '';
	private $_fbPack_like_send = 'yes';
	private $_fbPack_like_layout = 'button_count';
	private $_fbPack_like_width = 280;
	private $_fbPack_like_faces = 0;
	private $_fbPack_like_action = 'recommend';
	private $_fbPack_like_color = 'light';
	private $_fbPack_like_font = 'arial';
	
	private $_fbPack_like_box = 'no';
	private $_fbPack_facebook_page_url = 'http://www.facebook.com/FitBodyShop';
	private $_fbPack_box_width = 190;
	private $_fbPack_box_height = 390;
	private $_fbPack_box_color = 'light';
	private $_fbPack_box_faces = 1;
	private $_fbPack_box_border_color = '#000000';
	private $_fbPack_box_stream = 0;
	private $_fbPack_box_header = 1;
	
	private $_fbPack_comments = 'no';
	private $_fbPack_comments_posts = 4;
	private $_fbPack_comments_width = 515;
	private $_fbPack_comments_color = 'light';
	private $_fbPack_comments_moderators = '';
    
    private $_fbPack_login_button = 'no';
    private $_fbPack_login_button_label = 'Login with Facebook';
    
    private $_ps_version = 0;
    
    public      $_errors            = array();
	
	public function __construct() {
		
    	$this->_ps_version = floatval(substr(_PS_VERSION_,0,3));
    	
		$this->name = 'facebookpack';
		// ps version 1.3.x
		if ($this->_ps_version < 1.4)
			$this->tab = 'internet-solutions.si | celavi.org';
		// ps version 1.4.x
		else {
			$this->tab 		= 'social_networks';
			$this->author 	= 'internet-solutions.si | celavi.org';
		}
		$this->version = 1.0;
		
		$this->_refreshProperties ();
		parent::__construct ();
		
		$this->displayName = $this->l( 'Facebook Pack' );
		$this->description = $this->l( 'Facebook Pack contains Facebook Social Plugins: Like Button, Like Box, Login Button, Facebook Comments' );
	}
	
	public function install() {
		if (! file_exists ( dirname ( __FILE__ ) . '/' . self::INSTALL_SQL_FILE ))
			return (false);
		else if (! $sql = file_get_contents ( dirname ( __FILE__ ) . '/' . self::INSTALL_SQL_FILE ))
			return (false);
		$sql = str_replace ( 'PREFIX_', _DB_PREFIX_, $sql );
		$sql = preg_split ( "/;\s*[\r\n]+/", $sql );
		foreach ( $sql as $query ) {
			if (trim ( $query )) {
				if (! Db::getInstance ()->Execute ( trim ( $query ) ))
					return (false);
			}
		}
		
		//if (! $this->_overrideFiles()) {
		//	$this->displayErrors();
		//	return false;
		//}
		
		if (! parent::install () or ! $this->registerHook ( 'xmlNamespace' ) or ! $this->registerHook ( 'header' ) or 
            ! $this->registerHook('top') or ! $this->registerHook ( 'footer' ) or ! $this->registerHook ( 'leftColumn' ) or 
            ! $this->registerHook ( 'extraLeft' ) or ! $this->registerHook ( 'productTab' ) or 
            ! $this->registerHook ( 'productTabContent' ))
			return false;
		
		return true;
	}
	
	public function uninstall() { 
        if (! file_exists ( dirname ( __FILE__ ) . '/' . self::UNINSTALL_SQL_FILE ))
			return (false);
		else if (! $sql = file_get_contents ( dirname ( __FILE__ ) . '/' . self::UNINSTALL_SQL_FILE ))
			return (false);
        $sql = str_replace ( 'PREFIX_', _DB_PREFIX_, $sql );
		$sql = preg_split ( "/;\s*[\r\n]+/", $sql );
		foreach ( $sql as $query ) {
			if (trim ( $query )) {
				if (! Db::getInstance ()->Execute ( trim ( $query ) ))
					return (false);
			}
		}
        
        // general
		if (! Configuration::deleteByName ( 'FBPACK_APP_ID' ) or ! Configuration::deleteByName ( 'FBPACK_APP_SECRET' ) or ! Configuration::deleteByName ( 'FBPACK_APP_LOCALE' ) or // like button
		! Configuration::deleteByName ( 'FBPACK_LIKE_BUTTON' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_URL' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_SEND' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_LAYOUT' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_WIDTH' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_FACES' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_ACTION' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_COLOR' ) or ! Configuration::deleteByName ( 'FBPACK_LIKE_FONT' ) or // like box
		! Configuration::deleteByName ( 'FBPACK_LIKE_BOX' ) or ! Configuration::deleteByName ( 'FBPACK_FACEBOOK_PAGE_URL' ) or ! Configuration::deleteByName ( 'FBPACK_BOX_WIDTH' ) or ! Configuration::deleteByName ( 'FBPACK_BOX_HEIGHT' ) or ! Configuration::deleteByName ( 'FBPACK_BOX_COLOR' ) or ! Configuration::deleteByName ( 'FBPACK_BOX_FACES' ) or ! Configuration::deleteByName ( 'FBPACK_BOX_BORDER_COLOR' ) or ! Configuration::deleteByName ( 'FBPACK_BOX_STREAM' ) or ! Configuration::deleteByName ( 'FBPACK_BOX_HEADER' ) or // comments
		! Configuration::deleteByName ( 'FBPACK_COMMENTS' ) or ! Configuration::deleteByName ( 'FBPACK_COMMENTS_POSTS' ) or ! Configuration::deleteByName ( 'FBPACK_COMMENTS_WIDTH' ) or ! Configuration::deleteByName ( 'FBPACK_COMMENTS_COLOR' ) or ! Configuration::deleteByName ( 'FBPACK_COMMENTS_MODERATORS' ) or 
        ! Configuration::deleteByName ( 'FBPACK_LOGIN_BUTTON' ) or
                
		! parent::uninstall ())
			return false;
		return true;
	}
	
	public function getContent() {
		$this->_html = '<h2>' . $this->displayName . '</h2>';
		if (! empty ( $_POST )) {
			$this->_postValidation ();
			if (! sizeof ( $this->_postErrors ))
				$this->_postProcess ();
			else
				foreach ( $this->_postErrors as $err )
					$this->_html .= '<div class="alert error">' . $err . '</div>';
		} else
			$this->_html .= '<br />';
		
		$this->_displaySocialPlugins();
		$this->_displayDonation ();
		$this->_displayForm ();
		
		return $this->_html;
	}
	
	/**
	 * Hook Xml Namespace for HTML tag
	 * 
	 * @param mixed $params
	 */
	public function hookXmlNamespace($params) {
		global $smarty;
		
		return $this->display ( __FILE__, 'xml_namespace.tpl' );
	}
	
	/**
	 * Hook Header
	 * 
	 * @param mixed $params
	 */
	public function hookHeader($params) {
		global $smarty;
		
		if ($this->_fbPack_comments == 'yes') {
			$smarty->assign ( 'fbPack_comments', true );
			$smarty->assign ( 'fbPack_app_id', $this->_fbPack_app_id );
			$smarty->assign ( 'fbPack_app_locale', $this->_fbPack_app_locale );
			$smarty->assign ( 'fbPack_comments_moderators', $this->_fbPack_comments_moderators );
		}
		
		if ($this->_fbPack_login_button == 'yes') {
			$smarty->assign ( 'fbPack_login_button', true );
		}
		return $this->display ( __FILE__, 'header.tpl' );
	}
	
    /**
     * Returns module content for Top
     *
     * @param array $params Parameters
     * @return string Content
     */
    public function hookTop($params)
    {
        global $smarty, $cookie;
        
        if ($this->_fbPack_login_button == 'yes') {
        	// User is not logged in
        	
        	if (!$cookie->isLogged()) {
        		$smarty->assign ( 'isLogged', false );
        	} else {
        		$smarty->assign ( 'isLogged', true );
        		$smarty->assign ('fbUser', isset($cookie->fb_user_id) ? true : false);
        		$smarty->assign ('fb_user_id', isset($cookie->fb_user_id) ? $cookie->fb_user_id : null);
        	}
        	/**
        	
        	
            if(!empty($fb_user)){   
                $smarty->assign ( 'fb_img', 'profile' );
                $smarty->assign ( 'fb_user_id', $fb_user_id);
            } else {
                $url = $facebook->getLoginUrl(array(
                    'display'   => 'popup',
                    'scope'     => 'email,user_birthday',
                    'redirect_uri' => _PS_BASE_URL_ . '/my-account.php',
                    'next'      => _PS_BASE_URL_ . '/my-account.php'
                ));
                $smarty->assign ( 'fb_img', 'fb_connect.gif' );
                $smarty->assign ( 'isLogged', !$cookie->isLogged() );
                $smarty->assign ( 'fb_url', $url );
            }
            */
            return $this->display(__FILE__, 'top.tpl');
        }
        
        
    }
    
	/**
	 * Hook Footer
	 * 
	 * @param mixed $params
	 */
	public function hookFooter($params) {
		global $smarty, $cookie;
		
		$smarty->assign( 'fbPack_app_id', $this->_fbPack_app_id );
		$smarty->assign( 'fbPack_app_locale', $this->_fbPack_app_locale );
		
		if ($this->_fbPack_comments == 'yes') {
			$smarty->assign( 'fbPack_comments', true );
		}
		
		if ($this->_fbPack_login_button == 'yes') {
			$smarty->assign( 'fbPack_login_button', true );
			$smarty->assign( 'isLogged', ($cookie->isLogged()) ? 'true' : 'false');
		}
		
		return $this->display ( __FILE__, 'footer.tpl' );
	}
	
	/**
	 * Hook Left Column
	 * 
	 * @param mixed $params
	 */
	public function hookLeftColumn($params) {
		global $smarty;
		
		// Only show if like box is enabled
		if ($this->_fbPack_like_box == 'yes') {
			
			$smarty->assign ( 'fbPack_facebook_page_url', $this->_fbPack_facebook_page_url );
			$smarty->assign ( 'fbPack_box_width', $this->_fbPack_box_width );
			$smarty->assign ( 'fbPack_box_height', $this->_fbPack_box_height );
			$smarty->assign ( 'fbPack_box_color', $this->_fbPack_box_color );
			$smarty->assign ( 'fbPack_box_faces', ($this->_fbPack_box_faces) ? 'true' : 'false' );
			$smarty->assign ( 'fbPack_box_border_color', $this->_fbPack_box_border_color );
			$smarty->assign ( 'fbPack_box_stream', ($this->_fbPack_box_stream) ? 'true' : 'false' );
			$smarty->assign ( 'fbPack_box_header', ($this->_fbPack_box_header) ? 'true' : 'false' );
			
			return $this->display ( __FILE__, 'like_box.tpl' );
		}
	}
	
	/**
	 * Hook Extra Left
	 * 
	 * @param mixed $params
	 */
	public function hookExtraLeft($params) {
		global $smarty;
		
		// Only show if like box is enabled
		if ($this->_fbPack_like_button == 'yes') {
			
			$smarty->assign ( 'fbPack_like_url', $this->_fbPack_like_url );
			$smarty->assign ( 'fbPack_like_send', ($this->_fbPack_like_send == 'yes') ? 'true' : 'false' );
			$smarty->assign ( 'fbPack_like_layout', $this->_fbPack_like_layout );
			$smarty->assign ( 'fbPack_like_width', $this->_fbPack_like_width );
			$smarty->assign ( 'fbPack_like_faces', ($this->_fbPack_like_faces) ? 'true' : 'false' );
			$smarty->assign ( 'fbPack_like_action', $this->_fbPack_like_action );
			$smarty->assign ( 'fbPack_like_color', $this->_fbPack_like_color );
			$smarty->assign ( 'fbPack_like_font', $this->_fbPack_like_font );
			
			return $this->display ( __FILE__, 'like_button.tpl' );
		}
	}
	
	/**
	 * Hook Tab on product page
	 * 
	 * @param mixed $params
	 */
	public function hookProductTab($params) {
		global $smarty;
		
		if ($this->_fbPack_comments == 'yes') {
			
			return $this->display ( __FILE__, 'tab_comments.tpl' );
		}
	}
	
	/**
	 * Hook Content of tab on product page
	 * 
	 * @param mixed $params
	 */
	public function hookProductTabContent($params) {
		global $smarty;
		
		if ($this->_fbPack_comments == 'yes') {
			
			$smarty->assign ( 'fbPack_comments_posts', $this->_fbPack_comments_posts );
			$smarty->assign ( 'fbPack_comments_width', $this->_fbPack_comments_width );
			$smarty->assign ( 'fbPack_comments_color', $this->_fbPack_comments_color );
			
			return $this->display ( __FILE__, 'tab_content_comments.tpl' );
		}
	}
	
	public function displayErrors()
	{
		if ($nbErrors = sizeof($this->_errors)) {
			echo '<div class="alert error"><h3>'.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors', __CLASS__) : $this->l('error', __CLASS__)).'</h3>
	            <ol>';
			foreach ($this->_errors AS $error) {
				echo '<li>'.$error.'</li>';
			}
			echo '
	            </ol></div>';
		}
	}
	
	/**
	 * Override files for total support of a plugin
	 */
	private function _overrideFiles() {
		$templateDir = dirname(dirname(dirname(__FILE__))).'/themes/'._THEME_NAME_;
		$headerTemplate 	= 	$templateDir .'/header.tpl';
		$headerTemplateBack =   $templateDir . '/header.tpl.bak'; 
		//if (!is_file($headerTemplate)) {
		//	$this->_errors[] = Tools::displayError('Header template file is missing: ' . $headerTemplate);
		//	return false;
		//}		
	}
	
	private function _postValidation() {
		if (isset ( $_POST ['submitLikeButton'] )) {
			if (empty ( $_POST ['fbPack_like_width'] ))
				$this->_postErrors [] = $this->l ( 'The width of the Like Button is required.' );
		}
		
		if (isset ( $_POST ['submitLikeBox'] )) {
			if (empty ( $_POST ['fbPack_facebook_page_url'] ))
				$this->_postErrors [] = $this->l ( 'The URL of the Facebook Page is required.' );
			if (empty ( $_POST ['fbPack_box_width'] ))
				$this->_postErrors [] = $this->l ( 'The width of the Like Box is required.' );
			if (empty ( $_POST ['fbPack_box_height'] ))
				$this->_postErrors [] = $this->l ( 'The height of the Like Box is required.' );
		}
		
		if (isset ( $_POST ['submitComments'] )) {
			if ($_POST ['fbPack_comments'] == 'yes') {
				$config = Configuration::getMultiple ( array ('FBPACK_APP_ID' ) );
				if (! isset ( $config ['FBPACK_APP_ID'] ))
					$this->_postErrors [] = $this->l ( 'APP ID is required for Facebook Comments.' );
				if (empty ( $_POST ['fbPack_comments_posts'] ))
					$this->_postErrors [] = $this->l ( 'Number of posts is required.' );
				if (empty ( $_POST ['fbPack_comments_width'] ))
					$this->_postErrors [] = $this->l ( 'The width of the Comments Box is required.' );
				if (empty ( $_POST ['fbPack_comments_moderators'] ))
					$this->_postErrors [] = $this->l ( 'One Comments moderator is required.' );
			}
		}
        
        if (isset ( $_POST ['submitLogin'] )) {
            if ($_POST ['fbPack_login_button'] == 'yes') {
                $config = Configuration::getMultiple ( array ('FBPACK_APP_ID', 'FBPACK_APP_SECRET' ) );
				if (! isset ( $config ['FBPACK_APP_ID'] ))
					$this->_postErrors [] = $this->l ( 'APP ID is required for Facebook Login.' );
				if (! isset ( $config ['FBPACK_APP_SECRET'] ))
					$this->_postErrors [] = $this->l ( 'APP Secret is required for Facebook Login.' );
            }
        }
               
	}
	
	private function _postProcess() {
		if (isset ( $_POST ['submitBasicSettings'] )) {
			Configuration::updateValue ( 'FBPACK_APP_ID', $_POST ['fbPack_app_id'] );
			Configuration::updateValue ( 'FBPACK_APP_SECRET', $_POST ['fbPack_app_secret'] );
			Configuration::updateValue ( 'FBPACK_APP_LOCALE', $_POST ['fbPack_app_locale'] );
		}
		
		if (isset ( $_POST ['submitLikeButton'] )) {
			Configuration::updateValue ( 'FBPACK_LIKE_BUTTON', $_POST ['fbPack_like_button'] );
			Configuration::updateValue ( 'FBPACK_LIKE_URL', $_POST ['fbPack_like_url'] );
			Configuration::updateValue ( 'FBPACK_LIKE_SEND', $_POST ['fbPack_like_send'] );
			Configuration::updateValue ( 'FBPACK_LIKE_LAYOUT', $_POST ['fbPack_like_layout'] );
			Configuration::updateValue ( 'FBPACK_LIKE_WIDTH', $_POST ['fbPack_like_width'] );
			Configuration::updateValue ( 'FBPACK_LIKE_FACES', Tools::getValue ( 'fbPack_like_faces' ) );
			Configuration::updateValue ( 'FBPACK_LIKE_ACTION', $_POST ['fbPack_like_action'] );
			Configuration::updateValue ( 'FBPACK_LIKE_COLOR', $_POST ['fbPack_like_color'] );
			Configuration::updateValue ( 'FBPACK_LIKE_FONT', $_POST ['fbPack_like_font'] );
		}
		
		if (isset ( $_POST ['submitLikeBox'] )) {
			Configuration::updateValue ( 'FBPACK_LIKE_BOX', $_POST ['fbPack_like_box'] );
			Configuration::updateValue ( 'FBPACK_FACEBOOK_PAGE_URL', $_POST ['fbPack_facebook_page_url'] );
			Configuration::updateValue ( 'FBPACK_BOX_WIDTH', $_POST ['fbPack_box_width'] );
			Configuration::updateValue ( 'FBPACK_BOX_HEIGHT', $_POST ['fbPack_box_height'] );
			Configuration::updateValue ( 'FBPACK_BOX_COLOR', $_POST ['fbPack_box_color'] );
			Configuration::updateValue ( 'FBPACK_BOX_FACES', Tools::getValue ( 'fbPack_box_faces' ) );
			Configuration::updateValue ( 'FBPACK_BOX_BORDER_COLOR', $_POST ['fbPack_box_border_color'] );
			Configuration::updateValue ( 'FBPACK_BOX_STREAM', Tools::getValue ( 'fbPack_box_stream' ) );
			Configuration::updateValue ( 'FBPACK_BOX_HEADER', Tools::getValue ( 'fbPack_box_header' ) );
		}
		
		if (isset ( $_POST ['submitComments'] )) {
			Configuration::updateValue ( 'FBPACK_COMMENTS', $_POST ['fbPack_comments'] );
			Configuration::updateValue ( 'FBPACK_COMMENTS_POSTS', $_POST ['fbPack_comments_posts'] );
			Configuration::updateValue ( 'FBPACK_COMMENTS_WIDTH', $_POST ['fbPack_comments_width'] );
			Configuration::updateValue ( 'FBPACK_COMMENTS_COLOR', $_POST ['fbPack_comments_color'] );
			Configuration::updateValue ( 'FBPACK_COMMENTS_MODERATORS', $_POST ['fbPack_comments_moderators'] );
		}
        
        if (isset ( $_POST ['submitLogin'] )) {
            Configuration::updateValue ( 'FBPACK_LOGIN_BUTTON', $_POST ['fbPack_login_button'] );
        }
		
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l ( 'ok' ) . '" /> ' . $this->l ( 'Settings updated' ) . '</div>';
		
		$this->_refreshProperties ();
	}
	
	private function _refreshProperties() {
		// general
		$config = Configuration::getMultiple ( array ('FBPACK_APP_ID', 'FBPACK_APP_SECRET', 'FBPACK_APP_LOCALE' ) );
		if (isset ( $config ['FBPACK_APP_ID'] ))
			$this->_fbPack_app_id = $config ['FBPACK_APP_ID'];
		if (isset ( $config ['FBPACK_APP_SECRET'] ))
			$this->_fbPack_app_secret = $config ['FBPACK_APP_SECRET'];
		if (isset ( $config ['FBPACK_APP_LOCALE'] ))
			$this->_fbPack_app_locale = $config ['FBPACK_APP_LOCALE'];
		
		// like button
		$config = Configuration::getMultiple ( array ('FBPACK_LIKE_BUTTON', 'FBPACK_LIKE_URL', 'FBPACK_LIKE_SEND', 'FBPACK_LIKE_LAYOUT', 'FBPACK_LIKE_WIDTH', 'FBPACK_LIKE_FACES', 'FBPACK_LIKE_ACTION', 'FBPACK_LIKE_COLOR', 'FBPACK_LIKE_FONT' ) );
		if (isset ( $config ['FBPACK_LIKE_BUTTON'] ))
			$this->_fbPack_like_button = $config ['FBPACK_LIKE_BUTTON'];
		if (isset ( $config ['FBPACK_LIKE_URL'] ))
			$this->_fbPack_like_url = $config ['FBPACK_LIKE_URL'];
		if (isset ( $config ['FBPACK_LIKE_SEND'] ))
			$this->_fbPack_like_send = $config ['FBPACK_LIKE_SEND'];
		if (isset ( $config ['FBPACK_LIKE_LAYOUT'] ))
			$this->_fbPack_like_layout = $config ['FBPACK_LIKE_LAYOUT'];
		if (isset ( $config ['FBPACK_LIKE_WIDTH'] ))
			$this->_fbPack_like_width = $config ['FBPACK_LIKE_WIDTH'];
		if (isset ( $config ['FBPACK_LIKE_FACES'] ))
			$this->_fbPack_like_faces = $config ['FBPACK_LIKE_FACES'];
		if (isset ( $config ['FBPACK_LIKE_ACTION'] ))
			$this->_fbPack_like_action = $config ['FBPACK_LIKE_ACTION'];
		if (isset ( $config ['FBPACK_LIKE_COLOR'] ))
			$this->_fbPack_like_color = $config ['FBPACK_LIKE_COLOR'];
		if (isset ( $config ['FBPACK_LIKE_FONT'] ))
			$this->_fbPack_like_font = $config ['FBPACK_LIKE_FONT'];
		
		// like button
		$config = Configuration::getMultiple ( array ('FBPACK_LIKE_BOX', 'FBPACK_FACEBOOK_PAGE_URL', 'FBPACK_BOX_WIDTH', 'FBPACK_BOX_HEIGHT', 'FBPACK_BOX_COLOR', 'FBPACK_BOX_FACES', 'FBPACK_BOX_BORDER_COLOR', 'FBPACK_BOX_STREAM', 'FBPACK_BOX_HEADER' ) );
		if (isset ( $config ['FBPACK_LIKE_BOX'] ))
			$this->_fbPack_like_box = $config ['FBPACK_LIKE_BOX'];
		if (isset ( $config ['FBPACK_FACEBOOK_PAGE_URL'] ))
			$this->_fbPack_facebook_page_url = $config ['FBPACK_FACEBOOK_PAGE_URL'];
		if (isset ( $config ['FBPACK_BOX_WIDTH'] ))
			$this->_fbPack_box_width = $config ['FBPACK_BOX_WIDTH'];
		if (isset ( $config ['FBPACK_BOX_HEIGHT'] ))
			$this->_fbPack_box_height = $config ['FBPACK_BOX_HEIGHT'];
		if (isset ( $config ['FBPACK_BOX_COLOR'] ))
			$this->_fbPack_box_color = $config ['FBPACK_BOX_COLOR'];
		if (isset ( $config ['FBPACK_BOX_FACES'] ))
			$this->_fbPack_box_faces = $config ['FBPACK_BOX_FACES'];
		if (isset ( $config ['FBPACK_BOX_BORDER_COLOR'] ))
			$this->_fbPack_box_border_color = $config ['FBPACK_BOX_BORDER_COLOR'];
		if (isset ( $config ['FBPACK_BOX_STREAM'] ))
			$this->_fbPack_box_stream = $config ['FBPACK_BOX_STREAM'];
		if (isset ( $config ['FBPACK_BOX_HEADER'] ))
			$this->_fbPack_box_header = $config ['FBPACK_BOX_HEADER'];
		
		// comments
		$config = Configuration::getMultiple ( array ('FBPACK_COMMENTS', 'FBPACK_COMMENTS_POSTS', 'FBPACK_COMMENTS_WIDTH', 'FBPACK_COMMENTS_COLOR', 'FBPACK_COMMENTS_MODERATORS' ) );
		if (isset ( $config ['FBPACK_COMMENTS'] ))
			$this->_fbPack_comments = $config ['FBPACK_COMMENTS'];
		if (isset ( $config ['FBPACK_COMMENTS_POSTS'] ))
			$this->_fbPack_comments_posts = $config ['FBPACK_COMMENTS_POSTS'];
		if (isset ( $config ['FBPACK_COMMENTS_WIDTH'] ))
			$this->_fbPack_comments_width = $config ['FBPACK_COMMENTS_WIDTH'];
		if (isset ( $config ['FBPACK_COMMENTS_COLOR'] ))
			$this->_fbPack_comments_color = $config ['FBPACK_COMMENTS_COLOR'];
		if (isset ( $config ['FBPACK_COMMENTS_MODERATORS'] ))
			$this->_fbPack_comments_moderators = $config ['FBPACK_COMMENTS_MODERATORS'];
        
        
        // login
		$config = Configuration::getMultiple ( array ('FBPACK_LOGIN_BUTTON' ) );
        if (isset ( $config ['FBPACK_LOGIN_BUTTON'] ))
			$this->_fbPack_login_button = $config ['FBPACK_LOGIN_BUTTON'];
	}
	
	private function _displayDonation() {
		$this->_html .= '<form>
                    <fieldset class="width3" style="width:850px">
                        <legend><img src="' . $this->_path . 'donate.png" />' . $this->l ( 'Donate' ) . '</legend>
                        <p class="clear">' . $this->l ( 'If you like this plugin and find it useful, help keep this plugin free and actively developed by clicking the donate button. Also, don\'t forget to follow me on Twitter.' ) . '</p>
                        <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E4H8QSP3NPYU2" title="' . $this->l ( 'Donate with Paypal' ) . '" target="_blank"><img alt="' . $this->l ( 'Donate with Paypal' ) . '" src="' . $this->_path . 'donate.jpg"></a>
                        <a href="http://twitter.com/alesl/" title="' . $this->l ( 'Follow me on Twitter' ) . '" target="_blank"><img alt="' . $this->l ( 'Follow me on Twitter' ) . '" src="' . $this->_path . 'twitter.jpg"></a>
                    </fieldset>
		</form><br />';
	}
	
	private function _displaySocialPlugins() {
		$this->_html .= '<img src="../modules/facebookpack/social_plugins.jpg" style="float:left; margin-right:15px;"><b>' . $this->l ( 'This module contains Facebook Social Plugins' ) . '</b><br /><br />
		' . $this->l ( 'One of the easiest ways to make your online presence more social is by adding Facebook social plugins to your shop.' ) . '<br />
		' . $this->l ( 'Here you can choose to add four different Facebook social plugins: Like Button, Like Box, Comments and Login Button (Facebook Connect).') . '<br clear="all" /><br />';
	}
	
	private function _displayForm() {
		$this->_html .= '<form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
            <fieldset class="width3" style="width:850px">
                <legend><img src="' . $this->_path . 'fb.png" />' . $this->l ( 'Basic Settings' ) . '</legend>
                <label>' . $this->l ( 'App ID' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_app_id" value="' . Tools::getValue ( 'fbPack_app_id', $this->_fbPack_app_id ) . '" />
                    <p class="clear">' . $this->l ( 'This is the APP ID you need to get for Comments and Login Button. This can be retrieved from your Facebook application page: http://www.facebook.com/developers/apps' ) . '</p>
                </div>
                <label>' . $this->l ( 'App Secret' ) . '</label>
                    <div class="margin-form">
                        <input style="width:500px;" type="text" name="fbPack_app_secret" value="' . Tools::getValue ( 'fbPack_app_secret', $this->_fbPack_app_secret ) . '" />
                        <p class="clear">' . $this->l ( 'This is the APP Secret you need to get for Comments and Login Button. This can be retrieved from your Facebook application page: http://www.facebook.com/developers/apps' ) . '</p>
                    </div>
                    <label>' . $this->l ( 'Internationalization' ) . '</label>
                    <div class="margin-form">
                    	<select name="fbPack_app_locale" style="width:150px">
							<option value="af_ZA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "af_ZA" ? 'selected="selected"' : "") . '>Afrikaans</option>
							<option value="sq_AL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sq_AL" ? 'selected="selected"' : "") . '>Albanian</option>
							<option value="ar_AR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ar_AR" ? 'selected="selected"' : "") . '>Arabic</option>
							<option value="hy_AM" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hy_AM" ? 'selected="selected"' : "") . '>Armenian</option>
							<option value="ay_BO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ay_BO" ? 'selected="selected"' : "") . '>Aymara</option>
							<option value="az_AZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "az_AZ" ? 'selected="selected"' : "") . '>Azeri</option>
							<option value="eu_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "eu_ES" ? 'selected="selected"' : "") . '>Basque</option>
							<option value="be_BY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "be_BY" ? 'selected="selected"' : "") . '>Belarusian</option>
							<option value="bn_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "bn_IN" ? 'selected="selected"' : "") . '>Bengali</option>
							<option value="bs_BA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "bs_BA" ? 'selected="selected"' : "") . '>Bosnian</option>
							<option value="bg_BG" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "bg_BG" ? 'selected="selected"' : "") . '>Bulgarian</option>
							<option value="ca_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ca_ES" ? 'selected="selected"' : "") . '>Catalan</option>
							<option value="ck_US" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ck_US" ? 'selected="selected"' : "") . '>Cherokee</option>
							<option value="hr_HR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hr_HR" ? 'selected="selected"' : "") . '>Croatian</option>
							<option value="cs_CZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "cs_CZ" ? 'selected="selected"' : "") . '>Czech</option>
							<option value="da_DK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "da_DK" ? 'selected="selected"' : "") . '>Danish</option>
							<option value="nl_BE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nl_BE" ? 'selected="selected"' : "") . '>Dutch (Belgi&euml;)</option>
							<option value="nl_NL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nl_NL" ? 'selected="selected"' : "") . '>Dutch</option>
							<option value="en_PI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_PI" ? 'selected="selected"' : "") . '>English (Pirate)</option>
							<option value="en_GB" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_GB" ? 'selected="selected"' : "") . '>English (UK)</option>
							<option value="en_US" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_US" ? 'selected="selected"' : "") . '>English (US)</option>
							<option value="en_UD" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_UD" ? 'selected="selected"' : "") . '>English (Upside Down)</option>
							<option value="eo_EO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "eo_EO" ? 'selected="selected"' : "") . '>Esperanto</option>
							<option value="et_EE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "et_EE" ? 'selected="selected"' : "") . '>Estonian</option>
							<option value="fo_FO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fo_FO" ? 'selected="selected"' : "") . '>Faroese</option>
							<option value="tl_PH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tl_PH" ? 'selected="selected"' : "") . '>Filipino</option>
							<option value="fb_FI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fb_FI" ? 'selected="selected"' : "") . '>Finnish (test)</option>
							<option value="fi_FI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fi_FI" ? 'selected="selected"' : "") . '>Finnish</option>
							<option value="fr_CA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fr_CA" ? 'selected="selected"' : "") . '>French (Canada)</option>
							<option value="fr_FR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fr_FR" ? 'selected="selected"' : "") . '>French (France)</option>
							<option value="gl_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "gl_ES" ? 'selected="selected"' : "") . '>Galician</option>
							<option value="ka_GE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ka_GE" ? 'selected="selected"' : "") . '>Georgian</option>
							<option value="de_DE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "de_DE" ? 'selected="selected"' : "") . '>German</option>
							<option value="el_GR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "el_GR" ? 'selected="selected"' : "") . '>Greek</option>
							<option value="gn_PY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "gn_PY" ? 'selected="selected"' : "") . '>Guaran&iacute;</option>
							<option value="gu_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "gu_IN" ? 'selected="selected"' : "") . '>Gujarati</option>
							<option value="he_IL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "he_IL" ? 'selected="selected"' : "") . '>Hebrew</option>
							<option value="hi_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hi_IN" ? 'selected="selected"' : "") . '>Hindi</option>
							<option value="hu_HU" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hu_HU" ? 'selected="selected"' : "") . '>Hungarian</option>
							<option value="is_IS" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "is_IS" ? 'selected="selected"' : "") . '>Icelandic</option>
							<option value="id_ID" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "id_ID" ? 'selected="selected"' : "") . '>Indonesian</option>
							<option value="ga_IE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ga_IE" ? 'selected="selected"' : "") . '>Irish</option>
							<option value="it_IT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "it_IT" ? 'selected="selected"' : "") . '>Italian</option>
							<option value="ja_JP" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ja_JP" ? 'selected="selected"' : "") . '>Japanese</option>
							<option value="jv_ID" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "jv_ID" ? 'selected="selected"' : "") . '>Javanese</option>
							<option value="kn_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "kn_IN" ? 'selected="selected"' : "") . '>Kannada</option>
							<option value="kk_KZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "kk_KZ" ? 'selected="selected"' : "") . '>Kazakh</option>
							<option value="km_KH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "km_KH" ? 'selected="selected"' : "") . '>Khmer</option>
							<option value="tl_ST" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tl_ST" ? 'selected="selected"' : "") . '>Klingon</option>
							<option value="ko_KR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ko_KR" ? 'selected="selected"' : "") . '>Korean</option>
							<option value="ku_TR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ku_TR" ? 'selected="selected"' : "") . '>Kurdish</option>
							<option value="la_VA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "la_VA" ? 'selected="selected"' : "") . '>Latin</option>
							<option value="lv_LV" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "lv_LV" ? 'selected="selected"' : "") . '>Latvian</option>
							<option value="fb_LT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fb_LT" ? 'selected="selected"' : "") . '>Leet Speak</option>
							<option value="li_NL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "li_NL" ? 'selected="selected"' : "") . '>Limburgish</option>
							<option value="lt_LT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "lt_LT" ? 'selected="selected"' : "") . '>Lithuanian</option>
							<option value="mk_MK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mk_MK" ? 'selected="selected"' : "") . '>Macedonian</option>
							<option value="mg_MG" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mg_MG" ? 'selected="selected"' : "") . '>Malagasy</option>
							<option value="ms_MY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ms_MY" ? 'selected="selected"' : "") . '>Malay</option>
							<option value="ml_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ml_IN" ? 'selected="selected"' : "") . '>Malayalam</option>
							<option value="mt_MT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mt_MT" ? 'selected="selected"' : "") . '>Maltese</option>
							<option value="mr_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mr_IN" ? 'selected="selected"' : "") . '>Marathi</option>
							<option value="mn_MN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mn_MN" ? 'selected="selected"' : "") . '>Mongolian</option>
							<option value="ne_NP" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ne_NP" ? 'selected="selected"' : "") . '>Nepali</option>
							<option value="se_NO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "se_NO" ? 'selected="selected"' : "") . '>Northern S&aacute;mi</option>
							<option value="nb_NO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nb_NO" ? 'selected="selected"' : "") . '>Norwegian (bokmal)</option>
							<option value="nn_NO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nn_NO" ? 'selected="selected"' : "") . '>Norwegian (nynorsk)</option>
							<option value="ps_AF" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ps_AF" ? 'selected="selected"' : "") . '>Pashto</option>
							<option value="fa_IR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fa_IR" ? 'selected="selected"' : "") . '>Persian</option>
							<option value="pl_PL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pl_PL" ? 'selected="selected"' : "") . '>Polish</option>
							<option value="pt_BR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pt_BR" ? 'selected="selected"' : "") . '>Portuguese (Brazil)</option>
							<option value="pt_PT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pt_PT" ? 'selected="selected"' : "") . '>Portuguese (Portugal)</option>
							<option value="pa_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pa_IN" ? 'selected="selected"' : "") . '>Punjabi</option>
							<option value="qu_PE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "qu_PE" ? 'selected="selected"' : "") . '>Quechua</option>
							<option value="ro_RO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ro_RO" ? 'selected="selected"' : "") . '>Romanian</option>
							<option value="rm_CH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "rm_CH" ? 'selected="selected"' : "") . '>Romansh</option>
							<option value="ru_RU" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ru_RU" ? 'selected="selected"' : "") . '>Russian</option>
							<option value="sa_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sa_IN" ? 'selected="selected"' : "") . '>Sanskrit</option>
							<option value="sr_RS" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sr_RS" ? 'selected="selected"' : "") . '>Serbian</option>
							<option value="zh_CN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zh_CN" ? 'selected="selected"' : "") . '>Simplified Chinese (China)</option>
							<option value="sk_SK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sk_SK" ? 'selected="selected"' : "") . '>Slovak</option>
							<option value="sl_SI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sl_SI" ? 'selected="selected"' : "") . '>Slovenian</option>
							<option value="so_SO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "so_SO" ? 'selected="selected"' : "") . '>Somali</option>
							<option value="es_CL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_CL" ? 'selected="selected"' : "") . '>Spanish (Chile)</option>
							<option value="es_CO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_CO" ? 'selected="selected"' : "") . '>Spanish (Colombia)</option>
							<option value="es_MX" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_MX" ? 'selected="selected"' : "") . '>Spanish (Mexico)</option>
							<option value="es_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_ES" ? 'selected="selected"' : "") . '>Spanish (Spain)</option>
							<option value="es_VE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_VE" ? 'selected="selected"' : "") . '>Spanish (Venezuela)</option>
							<option value="es_LA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_LA" ? 'selected="selected"' : "") . '>Spanish</option>
							<option value="sw_KE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sw_KE" ? 'selected="selected"' : "") . '>Swahili</option>
							<option value="sv_SE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sv_SE" ? 'selected="selected"' : "") . '>Swedish</option>
							<option value="sy_SY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sy_SY" ? 'selected="selected"' : "") . '>Syriac</option>
							<option value="tg_TJ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tg_TJ" ? 'selected="selected"' : "") . '>Tajik</option>
							<option value="ta_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ta_IN" ? 'selected="selected"' : "") . '>Tamil</option>
							<option value="tt_RU" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tt_RU" ? 'selected="selected"' : "") . '>Tatar</option>
							<option value="te_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "te_IN" ? 'selected="selected"' : "") . '>Telugu</option>
							<option value="th_TH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "th_TH" ? 'selected="selected"' : "") . '>Thai</option>
							<option value="zh_HK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zh_HK" ? 'selected="selected"' : "") . '>Traditional Chinese (Hong Kong)</option>
							<option value="zh_TW" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zh_TW" ? 'selected="selected"' : "") . '>Traditional Chinese (Taiwan)</option>
							<option value="tr_TR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tr_TR" ? 'selected="selected"' : "") . '>Turkish</option>
							<option value="uk_UA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "uk_UA" ? 'selected="selected"' : "") . '>Ukrainian</option>
							<option value="ur_PK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ur_PK" ? 'selected="selected"' : "") . '>Urdu</option>
							<option value="uz_UZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "uz_UZ" ? 'selected="selected"' : "") . '>Uzbek</option>
							<option value="vi_VN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "vi_VN" ? 'selected="selected"' : "") . '>Vietnamese</option>
							<option value="cy_GB" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "cy_GB" ? 'selected="selected"' : "") . '>Welsh</option>
							<option value="xh_ZA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "xh_ZA" ? 'selected="selected"' : "") . '>Xhosa</option>
							<option value="yi_DE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "yi_DE" ? 'selected="selected"' : "") . '>Yiddish</option>
							<option value="zu_ZA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zu_ZA" ? 'selected="selected"' : "") . '>Zulu</option>
						</select>
                    	<p class="clear">' . $this->l ( 'Set appropriate locale for your site. You can read more about supported locales here: http://developers.facebook.com/docs/internationalization/' ) . '</p>
                    </div>
                <input type="submit" name="submitBasicSettings" value="' . $this->l ( 'Update basic settings' ) . '" class="button" />
            </fieldset>    
        </form>
        <br />
    	<form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
         	<fieldset class="width3" style="width:850px">
            	<legend><img src="' . $this->_path . 'like.png" />' . $this->l ( 'Facebook Like Button' ) . '</legend>
            	<label>' . $this->l ( 'Enable Plugin' ) . '</label>
            	<div class="margin-form">
            		<input type="radio" name="fbPack_like_button" value="yes" ' . (Tools::getValue ( 'fbPack_like_button', $this->_fbPack_like_button ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_like_button" value="no" ' . (Tools::getValue ( 'fbPack_like_button', $this->_fbPack_like_button ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Facebook Like Button' ) . '</p>
                </div>
                <label>' . $this->l ( 'URL to Like' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_like_url" value="' . Tools::getValue ( 'fbPack_like_url', $this->_fbPack_like_url ) . '" />
                    <p class="clear">' . $this->l ( 'The URL to like. Defaults to the current page.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Send Button' ) . '</label>
            	<div class="margin-form">
            		<input type="radio" name="fbPack_like_send" value="yes" ' . (Tools::getValue ( 'fbPack_like_send', $this->_fbPack_like_send ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_like_send" value="no" ' . (Tools::getValue ( 'fbPack_like_send', $this->_fbPack_like_send ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Include a Send button.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Layout Style' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_like_layout" style="width:150px">
   						<option value="standard" ' . (Tools::getValue ( 'fbPack_like_layout', $this->_fbPack_like_layout ) == "standard" ? 'selected="selected"' : "") . '>' . $this->l ( 'Standard' ) . '</option>
   						<option value="button_count" ' . (Tools::getValue ( 'fbPack_like_layout', $this->_fbPack_like_layout ) == "button_count" ? 'selected="selected"' : "") . '>' . $this->l ( 'Button (Count)' ) . '</option>
   						<option value="box_count" ' . (Tools::getValue ( 'fbPack_like_layout', $this->_fbPack_like_layout ) == "box_count" ? 'selected="selected"' : "") . '>' . $this->l ( 'Box (Count)' ) . '</option>
   					</select>
                    <p class="clear">' . $this->l ( 'Determines the size and amount of social context next to the button.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Width' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_like_width" value="' . Tools::getValue ( 'fbPack_like_width', $this->_fbPack_like_width ) . '" />
                    <p class="clear">' . $this->l ( 'The width of the plugin, in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Show Faces' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_like_faces" value="1" ' . (Tools::getValue ( 'fbPack_like_faces', $this->_fbPack_like_faces ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show profile pictures below the button. (Standard layout only)' ) . '</p>
                </div>
                <label>' . $this->l ( 'Verb to display' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_like_action" style="width:150px">
   						<option value="like" ' . (Tools::getValue ( 'fbPack_like_action', $this->_fbPack_like_action ) == "like" ? 'selected="selected"' : "") . '>' . $this->l ( 'Like' ) . '</option>
   						<option value="recommend" ' . (Tools::getValue ( 'fbPack_like_action', $this->_fbPack_like_action ) == "recommend" ? 'selected="selected"' : "") . '>' . $this->l ( 'Recommend' ) . '</option>
   					</select>
                    <p class="clear">' . $this->l ( 'The verb to display in the button. Currently only \'like\' and \'recommend\' are supported.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Color Scheme' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_like_color" style="width:150px">
   						<option value="light" ' . (Tools::getValue ( 'fbPack_like_color', $this->_fbPack_like_color ) == "light" ? 'selected="selected"' : "") . '>' . $this->l ( 'Light' ) . '</option>
   						<option value="dark" ' . (Tools::getValue ( 'fbPack_like_color', $this->_fbPack_like_color ) == "dark" ? 'selected="selected"' : "") . '>' . $this->l ( 'Dark' ) . '</option>
   					</select>
                    <p class="clear">' . $this->l ( 'The color scheme of the plugin.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Font' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_like_font" style="width:150px">
   						<option value="arial" ' . (Tools::getValue ( 'fbPack_like_font', $this->_fbPack_like_font ) == "arial" ? 'selected="selected"' : "") . '>' . $this->l ( 'Arial' ) . '</option>
   						<option value="lucida grande" ' . (Tools::getValue ( 'fbPack_like_font', $this->_fbPack_like_font ) == "lucida grande" ? 'selected="selected"' : "") . '>' . $this->l ( 'Lucida Grande' ) . '</option>
   						<option value="segoe ui" ' . (Tools::getValue ( 'fbPack_like_font', $this->_fbPack_like_font ) == "segoe ui" ? 'selected="selected"' : "") . '>' . $this->l ( 'Segoe Ui' ) . '</option>
   						<option value="tahoma" ' . (Tools::getValue ( 'fbPack_like_font', $this->_fbPack_like_font ) == "tahoma" ? 'selected="selected"' : "") . '>' . $this->l ( 'Tahoma' ) . '</option>
   						<option value="trebuchet ms" ' . (Tools::getValue ( 'fbPack_like_font', $this->_fbPack_like_font ) == "trebuchet ms" ? 'selected="selected"' : "") . '>' . $this->l ( 'Trebuchet MS' ) . '</option>
   						<option value="verdana" ' . (Tools::getValue ( 'fbPack_like_font', $this->_fbPack_like_font ) == "verdana" ? 'selected="selected"' : "") . '>' . $this->l ( 'Verdana' ) . '</option>
   					</select>
                    <p class="clear">' . $this->l ( 'The font of the plugin.' ) . '</p>
                </div>
                <input type="submit" name="submitLikeButton" value="' . $this->l ( 'Update settings for Like Button' ) . '" class="button" />
        	</fieldset>
    	</form>
        <br />
        <form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
            <fieldset class="width3" style="width:850px">
            	<legend><img src="' . $this->_path . 'like.png" />' . $this->l ( 'Facebook Like Box' ) . '</legend>
            	<label>' . $this->l ( 'Enable Plugin' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_like_box" value="yes" ' . (Tools::getValue ( 'fbPack_like_box', $this->_fbPack_like_box ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_like_box" value="no" ' . (Tools::getValue ( 'fbPack_like_box', $this->_fbPack_like_box ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Facebook Like Box' ) . '</p>
                </div>
                <label>' . $this->l ( 'Facebook Page URL' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_facebook_page_url" value="' . Tools::getValue ( 'fbPack_facebook_page_url', $this->_fbPack_facebook_page_url ) . '" />
                    <p class="clear">' . $this->l ( 'The URL of the Facebook Page for this Like box.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Width' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_box_width" value="' . Tools::getValue ( 'fbPack_box_width', $this->_fbPack_box_width ) . '" />
                    <p class="clear">' . $this->l ( 'The width of the plugin in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Height' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_box_height" value="' . Tools::getValue ( 'fbPack_box_height', $this->_fbPack_box_height ) . '" />
                    <p class="clear">' . $this->l ( 'The height of the plugin in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Color Scheme' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_box_color" style="width:150px">
   						<option value="light" ' . (Tools::getValue ( 'fbPack_box_color', $this->_fbPack_box_color ) == "light" ? 'selected="selected"' : "") . '>' . $this->l ( 'Light' ) . '</option>
   						<option value="dark" ' . (Tools::getValue ( 'fbPack_box_color', $this->_fbPack_box_color ) == "dark" ? 'selected="selected"' : "") . '>' . $this->l ( 'Dark' ) . '</option>
   					</select>
                    <p class="clear">' . $this->l ( 'The color scheme of the plugin.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Show Faces' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_box_faces" value="1" ' . (Tools::getValue ( 'fbPack_box_faces', $this->_fbPack_box_faces ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show profile photos in the plugin.' ) . '</p>
                </div>
                 <label>' . $this->l ( 'Border Color' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_box_border_color" value="' . Tools::getValue ( 'fbPack_box_border_color', $this->_fbPack_box_border_color ) . '" />
                    <p class="clear">' . $this->l ( 'The border color of the plugin.' ) . '</p>
                </div>
                
                <label>' . $this->l ( 'Show Stream' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_box_stream" value="1" ' . (Tools::getValue ( 'fbPack_box_stream', $this->_fbPack_box_stream ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show the profile stream for the public profile.' ) . '</p>
                </div>
                
                <label>' . $this->l ( 'Show Header' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_box_header" value="1" ' . (Tools::getValue ( 'fbPack_box_header', $this->_fbPack_box_header ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show the \'Find us on Facebook\' bar at top. Only shown when either stream or faces are present.' ) . '</p>
                </div>
                <input type="submit" name="submitLikeBox" value="' . $this->l ( 'Update settings for Like Box' ) . '" class="button" />
            </fieldset>
    	</form>
    	<br />
    	<form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
    		<fieldset class="width3" style="width:850px">
            	<legend><img src="' . $this->_path . 'wall_post.png" />' . $this->l ( 'Comments' ) . '</legend>
            	<label>' . $this->l ( 'Enable Plugin' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_comments" value="yes" ' . (Tools::getValue ( 'fbPack_comments', $this->_fbPack_comments ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_comments" value="no" ' . (Tools::getValue ( 'fbPack_comments', $this->_fbPack_comments ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Comments' ) . '</p>
                </div>
                <label>' . $this->l ( 'Number of posts' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_comments_posts" value="' . Tools::getValue ( 'fbPack_comments_posts', $this->_fbPack_comments_posts ) . '" />
                    <p class="clear">' . $this->l ( 'The number of posts to display by default.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Width' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_comments_width" value="' . Tools::getValue ( 'fbPack_comments_width', $this->_fbPack_comments_width ) . '" />
                    <p class="clear">' . $this->l ( 'The width of the plugin, in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Color Scheme' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_comments_color" style="width:150px">
   						<option value="light" ' . (Tools::getValue ( 'fbPack_comments_color', $this->_fbPack_comments_color ) == "light" ? 'selected="selected"' : "") . '>' . $this->l ( 'Light' ) . '</option>
   						<option value="dark" ' . (Tools::getValue ( 'fbPack_comments_color', $this->_fbPack_comments_color ) == "dark" ? 'selected="selected"' : "") . '>' . $this->l ( 'Dark' ) . '</option>
   					</select>
                    <p class="clear">' . $this->l ( 'The color scheme of the plugin.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Moderators' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_comments_moderators" value="' . Tools::getValue ( 'fbPack_comments_moderators', $this->_fbPack_comments_moderators ) . '" />
                    <p class="clear">' . $this->l ( 'Comments moderators (user ID, see: http://www.facebook.com/note.php?note_id=91532827198). To add multiple moderators, separate the uids by comma without spaces.' ) . '</p>
                </div>
            	<input type="submit" name="submitComments" value="' . $this->l ( 'Update settings for Comments' ) . '" class="button" />
            </fieldset>
    	</form>
        <br />
    	<form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
    		<fieldset class="width3" style="width:850px">
            	<legend><img src="' . $this->_path . 'fb_white.png" />' . $this->l ( 'Login Button' ) . '</legend>
            	<label>' . $this->l ( 'Enable Plugin' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_login_button" value="yes" ' . (Tools::getValue ( 'fbPack_login_button', $this->_fbPack_login_button ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_login_button" value="no" ' . (Tools::getValue ( 'fbPack_login_button', $this->_fbPack_login_button ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Login Button' ) . '</p>
                </div>
                <input type="submit" name="submitLogin" value="' . $this->l ( 'Update settings for Login Button' ) . '" class="button" />
            </fieldset>
    	</form>';
	}
}