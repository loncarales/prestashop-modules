<?php
if (!defined('_CAN_LOAD_FILES_'))
	exit;

/** 
 * ShopTab Products Data Feed
 *
 * Export Products in a data sheet as a Comma Separated .csv file
 *
 * @ (C) Copyright 2010 by (internet-solutions.si | celavi.org) Ales Loncar
 * @ Version 0.1
 *
 */
class ShopTab extends Module
{
    private $_html = '';
	private $_postErrors = array();
	private $_cookie;
	
    function __construct()
    {
        
		global $cookie;
		$this->_cookie = $cookie;
        $this->name = 'shoptab';
        $this->tab  = 'internet-solutions.si | celavi.org';
        $this->version = 0.1;

        parent::__construct();
        $this->page = basename(__FILE__, '.php');
        
        // Create the feed in our default base directory
		if (!Configuration::get('shoptab_filepath'))
			Configuration::updateValue('shoptab_filepath', addslashes($this->_defaultOutputFile()));
	    if (!Configuration::get('shoptab_domain'))
			Configuration::updateValue('shoptab_domain', $_SERVER['HTTP_HOST']);
	    if (!Configuration::get('shoptab_psdir'))
			Configuration::updateValue('shoptab_psdir', __PS_BASE_URI__);
        
        $this->displayName = $this->l('ShopTab Products Data Feed');
        $this->description = $this->l('Export Products in a data sheet as a Comma Separated .csv file for Automatic Weekly Data Feed Updates');
    }
    
	public function uninstall()
	{
		// Should cleanup the config variables to play nice
		Configuration::deleteByName('shoptab_filepath');
		Configuration::deleteByName('shoptab_domain');
		Configuration::deleteByName('shoptab_psdir');
		
		parent::uninstall();
	}
	
    function getContent()
	{
		$this->_html .= '<h2>'.$this->l('ShopTab Products Data Feed').'</h2>';
		
		if (Tools::isSubmit('btnSubmit'))
		{			
			$this->_postValidation();
			
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('shoptab_filepath', addslashes($_POST['filepath'])); // the Tools class kills the windows file name separators :(
				Configuration::updateValue('shoptab_psdir', Tools::getValue('psdir'));	// may have been "fixed" by the validation function
				Configuration::updateValue('shoptab_domain', Tools::getValue('domain')); // may have been "fixed" by the validation function
				// Go try and generate the feed
				$this->_postProcess();
			}
			else
			{
				foreach ($this->_postErrors AS $err)
				{
					$this->_html .= '<div class="alert error">'.$err.'</div>';
				}
			}
		}
			
		//$this->_displayFeed();
		$this->_displayForm();
		
		return $this->_html;
	}
	
    private function _directory()
	{
		return dirname(__FILE__).'/../../'; // move up to the __PS_BASE_URI__ directory
	}
	
    private function _winFixFilename($file)
	{
		return str_replace('\\\\','\\',$file);
	}
	
    private function _can_write($filename)
	{
		// Test if we can write the file specified in the config screen
		@unlink($filename);
		$fp = @fopen($filename, 'wb');
		@fclose($fp);
		return file_exists($filename); 
	}
	
    private function _defaultOutputFile()
	{
		// PHP on windows seems to return a trailing '\' where as on unix it doesn't
		$output_dir = realpath($this->_directory());
		$dir_separator = '/';
		
		// If there's a windows directory separator on the end, 
		// then don't add the unix one too when building the final output file
		if (substr($output_dir, -1, 1)=='\\')
			$dir_separator = '';
		
		$output_file = $output_dir.$dir_separator.'shoptab.csv';
		return $output_file;
	}
	
    private function _getPath($id_category, $path = '')
	{		
		$category = new Category(intval($id_category), intval($this->_cookie->id_lang));
		
		if (!Validate::isLoadedObject($category))
			die (Tools::displayError());
		
		if ($category->id == 1)
			return htmlentities($path);
		
		$pipe = ' > ';

		$category_name = Category::hideCategoryPosition($category->name);
		
		if ($path != $category_name)
			$path = $category_name.($path!='' ? $pipe.$path : '');
		
		return $this->_getPath(intval($category->id_parent), $path);
	}
	
	private function _clearUTF($input)
	{
	    $replaceThem = array(
		    'è' => 'c',
		    'È' => 'C',
		    'Æ' => 'C',
		    'æ' => 'c',
		    'ð' => 'd',
		    '&scaron;' => 's',
		    '&Scaron;' => 'S',
	        'č' => 'c',
	        'š'	=> 's',
	        'ž'	=> 'z',
	        'Ž'	=> 'Z',
	        '&Aring;' => 's',
	        '&iexcl;' => 'c'
		);
		/**
		 * Esencialne ma&Aring;&iexcl;&Auml;çobe
		 */
		
	    return strtr($input, $replaceThem);   
	}
	
    private function _displayForm()
	{
		$this->_html .=
			'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<label>'.$this->l('Output Location: ').'</label>
					<div class="margin-form">
						<input name="filepath" type="text" style="width: 600px;" value="'.(isset($_POST['filepath']) ? $_POST['filepath'] : $this->_winFixFilename(Configuration::get('shoptab_filepath'))).'"/>
						<p class="clear">'.$this->l('Example (default):').' '.$this->_defaultOutputFile().'</p>
					</div>
					<label>'.$this->l('Domain: ').'</label>
					<div class="margin-form">
						<input name="domain" type="text" style="width: 600px;" value="'.Tools::getValue('domain', Configuration::get('shoptab_domain')).'"/>
						<p class="clear">'.$this->l('Example (default):').' '.$_SERVER['HTTP_HOST'].'</p>
					</div>
					<label>'.$this->l('Shop Base: ').'</label>
					<div class="margin-form">
						<input name="psdir" type="text" style="width: 600px;" value="'.Tools::getValue('psdir', Configuration::get('shoptab_psdir')).'"/>
						<p class="clear">'.$this->l('Example (default):').' '.__PS_BASE_URI__.'</p>
					</div>
				</fieldset>
				<br />
				<input name="btnSubmit" class="button" value="'.((!file_exists($this->_winFixFilename(Configuration::get('shoptab_filepath')))) ? $this->l('Generate feed file') : $this->l('Update feed file')).'" type="submit" />
			</form>';
	}
	
    private function _postValidation()
	{
	    // Used $_POST here to allow us to modify them directly - naughty I know :)
		if (empty($_POST['domain']) OR strlen($_POST['domain']) < 3)
		{
			$this->_postErrors[] = $this->l('Domain is required/invalid.');
		} else {
			// Clean the domain name, just in case someone puts more than just a plain domain name in there
			$domain_split = explode('/',str_replace('http://','', $_POST['domain']));
			$_POST['domain']=$domain_split[0];
		}
		
        if (empty($_POST['psdir']))
		{
			$this->_postErrors[] = $this->l('Shop base is required.');
		} else {
			// Need to be absolutely sure that $psdir starts and ends with a '/'
			if (substr($_POST['psdir'], -1, 1)!='/')
				$_POST['psdir'] = $_POST['psdir'].'/';
			if (substr($_POST['psdir'], 0, 1)!='/')
				$_POST['psdir'] = '/'.$_POST['psdir'];
		}
		
		// could check that this is a valid path, but the next test will
		// do that for us anyway
		// But first we need to get rid of the escape characters
		$_POST['filepath'] = $this->_winFixFilename($_POST['filepath']);
		if (empty($_POST['filepath']) OR (strlen($_POST['filepath']) > 255))
			$this->_postErrors[] = $this->l('The target location is invalid');
		
		if (!$this->_can_write($_POST['filepath']))
			$this->_postErrors[] = $this->l('The output location is invalid.<br />Cannot write to').' '.$_POST['filepath'];
	}
	
    private function _addToFeed(Array $items)
	{
		$filename = $this->_winFixFilename(Configuration::get('shoptab_filepath'));
		if(file_exists($filename))
		{
			$fp = fopen($filename, 'w');
		    foreach ($items as $fields) {
                fputcsv($fp, $fields);
            }
			fclose($fp);
		}
	}
	
	private function _postProcess()
	{
	    $domain = Configuration::get('shoptab_domain');
		$psdir = Configuration::get('shoptab_psdir');
		$items_added = 0;
		
		$link = new Link();
		/**
		 * Get Only Home Products
		 */
		$Products = Product::getProducts(intval($this->_cookie->id_lang), 0, NULL, 'id_product', 'ASC', 1, true);
				
		if($Products)
		{
		    $items = array(
		        array('title', 'description', 'category', 'link', 'image_link', 'price', 'condition')
		    );
		    foreach ($Products AS $Product)
		    {
			    $images = Image::getImages(intval($this->_cookie->id_lang), $Product['id_product']);
			    $product_link = $link->getProductLink($Product['id_product'], $Product['link_rewrite']);
			    // Make 1.1 result look like 1.2
				if (strpos( $product_link, 'http://' ) === false )        
					$product_link = 'http://'.$_SERVER['HTTP_HOST'].$product_link;
				// remove the start to get a URI relative to __PS_BASE_URI__
				$product_link = str_replace('http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__,'',$product_link);                 				
				// Then build a full url according to the settings
				$product_link = 'http://'.$domain.$psdir.$product_link;
				$title = $Product['name'];
				$description = $Product['description_short'];
				$category = $this->_getPath($Product['id_category_default']);
				$image = '';
				if (isset($images[0]))
					$image = 'http://'.$domain.$psdir.'img/p/'.$images[0]['id_product'].'-'.$images[0]['id_image'].'-large.jpg';
				$price = Product::getPriceStatic(intval($Product['id_product']));
				$items[] = array(
				    $this->_clearUTF($title), $this->_clearUTF($description), $this->_clearUTF($category), $product_link, $image, $price, 'new'
				);
			}
		    $this->_addToFeed($items);
		}
	}
}