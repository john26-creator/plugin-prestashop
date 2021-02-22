<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Hs_Module extends Module
{
    public function __construct()
    {
        $this->name = 'hs_module';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Jonathan AMSELEM';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Premier module Happy cats');
        $this->description = $this->l('Mon premier module super cool');

        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ?');

        if (!Configuration::get('HS_MODULE_PAGENAME')) {
            $this->warning = $this->l('Aucun nom fourni');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||
            !$this->registerHook('leftColumn') ||
            !$this->registerHook('header') ||
            !Configuration::updateValue('HS_MODULE_PAGENAME', 'Happy Cats')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('HS_MODULE_PAGENAME')
        ) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('btnSubmit')) {
            $pageName = strval(Tools::getValue('HS_MODULE_PAGENAME'));

            if (
                !$pageName||
                empty($pageName)
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('HS_MODULE_PAGENAME', $pageName);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Récupère la langue par défaut
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Initialise les champs du formulaire dans un tableau
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Configuration value'),
                        'name' => 'HS_MODULE_PAGENAME',
                        'size' => 20,
                        'required' => true
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name'  => 'btnSubmit'
                )
            ),
        );

        $helper = new HelperForm();

        // Module, token et currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&amp;configure='.$this->name;

        // Langue
        $helper->default_form_language = $defaultLang;

        // Charge la valeur de HS_MODULE_PAGENAME depuis la base
        $helper->fields_value['HS_MODULE_PAGENAME'] = Configuration::get('HS_MODULE_PAGENAME');

        return $helper->generateForm(array($form));
    }

    public function hookDisplayLeftColumn($params)
    {
        $this->context->smarty->assign([
            'hs_page_name' => Configuration::get('HS_MODULE_PAGENAME'),
            'hs_page_link' => $this->context->link->getModuleLink('hs_module', 'display')
        ]);

        return $this->display(__FILE__, 'hs_module.tpl');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->registerStylesheet(
            'hs_module',
            $this->_path.'views/css/hs_module.css',
            ['server' => 'remote', 'position' => 'head', 'priority' => 150]
        );
    }
}