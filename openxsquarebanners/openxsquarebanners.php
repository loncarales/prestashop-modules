<?php
if (!defined('_CAN_LOAD_FILES_'))
    exit;

class OpenXSquareBanners extends Module
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'openxsquarebanners';
        $this->tab  = 'internet-solutions.si | celavi.org';
        $this->version = 0.1;

        parent::__construct();

        $this->displayName = $this->l('Home Page Square Banners');
        $this->description = $this->l('Two IAB Square Banners (250 x 250) for your homepage via OpenX');
    }

    function install()
    {
        if (!parent::install() OR !$this->registerHook('home') OR !$this->registerHook('header'))
           return false;
        return true;
    }

    public function getContent()
    {
        $this->_html = '<h2>'.$this->displayName.'</h2>';
        $this->_postProcess();
        $this->_displayForm();
        return $this->_html;
    }

    public function _displayForm()
    {
        $xml = false;

            $fd = @fopen(dirname(__FILE__) . '/openxsquarebanners.txt', 'r');
            $contents = @fread($fd, filesize(dirname(__FILE__) . '/openxsquarebanners.txt'));
            @fclose($fd);

            if ($contents) {
                $banners = unserialize($contents);
                $left_banner = $banners['left_banner'];
                $right_banner = $banners['right_banner'];
            }

        $this->_html .= '
            <form method="post" action="' . $_SERVER['REQUEST_URI'] . '" enctype="multipart/form-data">
                <fieldset class="width3" style="width:850px">
                    <legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Home Page Square Banners Settings').'</legend>
                    <h3>Left IAB Square Banner (250 x 250)</h3>
                    <label>Invocation Code</label>
                    <div class="margin-form">
                        <textarea cols="64" rows="6" name="left_openx_code" id="left_openx_code">' . (isset($left_banner) ? $left_banner : '') . '</textarea>
                        <p class="clear">'.$this->l('Copy OpenX javascript code and paste it here').'</p>
                    </div>
                    <h3>Right IAB Square Banner (250 x 250)</h3>
                    <label>Invocation Code</label>
                    <div class="margin-form">
                        <textarea cols="64" rows="6" name="right_openx_code" id="right_openx_code">' . (isset($right_banner) ? $right_banner : '') . '</textarea>
                        <p class="clear">'.$this->l('Copy OpenX javascript code and paste it here').'</p>
                    </div>
                    <input type="submit" name="submitChanges" value="'.$this->l('Update').'" class="button" />
                </fieldset>
            </form>';
    }

    function hookHeader($params)
    {
        return $this->display(__FILE__, 'openxsquarebannersheader.tpl');
    }

    function hookHome($params)
    {
        if (file_exists(dirname(__FILE__) . '/openxsquarebanners.txt'))
            if ($fd = @fopen(dirname(__FILE__) . '/openxsquarebanners.txt', 'r')) {
                $contents = @fread($fd, filesize(dirname(__FILE__) . '/openxsquarebanners.txt'));
                @fclose($fd);

                if ($contents) {
                    $banners = unserialize($contents);
                    $left_banner = $banners['left_banner'];
                    $right_banner = $banners['right_banner'];
                }

                global $smarty;
                $smarty->assign(array(
                        'left_openx_code' => (isset($left_banner) ? $left_banner : ''),
                        'right_openx_code' => (isset($right_banner) ? $right_banner : '')
                        ));
                return $this->display(__FILE__, 'openxsquarebanners.tpl');
            }
        return false;
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('submitChanges')) {
            if (! Tools::getValue('left_openx_code')) {
                $this->_postErrors[] = $this->l('Invocation Code for Left IAB Square Banner is empty!');
            }
            if (! Tools::getValue('right_openx_code')) {
                $this->_postErrors[] = $this->l('Invocation Code for Right IAB Square Banner is empty!');
            }

            if (!sizeof($this->_postErrors)) {
                $banners = array(
                   'left_banner' => Tools::getValue('left_openx_code'),
                   'right_banner' => Tools::getValue('right_openx_code')
                );

                if ($fd = @fopen(dirname(__FILE__) . '/openxsquarebanners.txt', 'w')) {
                    if (!@fwrite($fd, serialize($banners)))
                        $this->_html .= $this->displayError($this->l('Unable to write to the banners file.'));
                    if (!@fclose($fd))
                        $this->_html .= $this->displayError($this->l('Can\'t close the banners file.'));
                }   else
                        $this->_html .= $this->displayError($this->l('Unable to update the banners file.<br />Please check the editor file\'s writing permissions.'));

                $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
            } else {
                foreach ($this->_postErrors AS $err) {
                    $this->_html .= '<div class="alert error">'.$err.'</div>';
                }
            }
        }
    }
}