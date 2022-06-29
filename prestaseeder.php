<?php

class PrestaSeeder extends Module
{

    const CONTROLLER_INFO = 'AdminPrestaSeederInformation';
    const CONTROLLER_SETTINGS = 'AdminPrestaSeederSettings';

    private $hooks = array(
        'backOfficeHeader',
        'header',
        'actionObjectProductAddAfter',
        'actionObjectCategoryAddAfter',
        'actionObjectProductDeleteAfter',
        'actionObjectCategoryDeleteAfter',
        'backOfficeHeader'
    );


    public function __construct()
    {
        $this->name = 'prestaseeder';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Aironas Stunžėnas';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Prestashop Seeder');
        $this->description = $this->l('Module for prestashop that will generate dummy products, so you could develop modules with ease.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->loadFiles();
    }

    public function install()
    {
        Configuration::updateValue('SEEDER_IMG_URL', 'https://random.imagecdn.app/500/500');

        if (!parent::install()) {
            $this->_errors[] = $this->l('Could not install module');

            return false;
        }

        if (!$this->registerModuleHooks()) {
            $this->_errors[] = $this->l('Could not register module hooks');

            return false;
        }

        if (!$this->registerModuleTabs()) {
            $this->_errors[] = $this->l('Could not register module admin controllers');

            return false;
        }

        if (!$this->createModuleDatabaseTables()) {
            $this->_errors[] = $this->l('Could not create module database tables');

            return false;
        }

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('SEEDER_IMG_URL');

        if (!$this->deleteModuleTabs()) {
            $this->_errors[] = $this->l('Could not delete module admin controllers');

            return false;
        }

        if (!$this->deleteModuleDatabaseTables()) {
            $this->_errors[] = $this->l('Could not delete module database tables');

            return false;
        }

        if (!parent::uninstall()) {
            $this->_errors[] = $this->l('Could not uninstall module');

            return false;
        }

        return true;
    }

    public function processCron($action = '', $amount)
    {
        switch ($action) {
            case 'createProducts':
                $productSeederObj = new PrestaSeederProduct();
                $productSeederObj->createProduct($amount);
                break;
            case 'createCategories':
                $categorySeederObj = new PrestaSeederCategory();
                $categorySeederObj->createCategory($amount);
                break;
            case 'assignToCategories':
                $this->assignToCategories();
        }
    }

    public function getContent()
    {
        $url = $this->context->link->getAdminLink(self::CONTROLLER_INFO);

        Tools::redirectAdmin($url);
    }

    public function getMenu()
    {
        $currentController = Tools::getValue('controller');

        $menu = array(
            array(
                'url' => $this->context->link->getAdminLink(self::CONTROLLER_INFO),
                'title' => $this->l('Information'),
                'current' => self::CONTROLLER_INFO == $currentController,
                'icon' => 'icon icon-cogs'
            ),
            array(
                'url' => $this->context->link->getAdminLink(self::CONTROLLER_SETTINGS),
                'title' => $this->l('Settings'),
                'current' => self::CONTROLLER_SETTINGS == $currentController,
                'icon' => 'icon icon-cogs'
            ),
        );

        $this->context->smarty->assign('menu', $menu);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/menu.tpl');
    }

    public function hookActionObjectCategoryAddAfter($params)
    {
        $categoryObj = $params['object'];
        $linkRewrites = array();
        foreach($categoryObj->name as $key => $name) {
            $categoryObj->link_rewrite[$key] = Tools::str2url($name.' '.(int) $categoryObj->id);
            $categoryObj->name[$key] = $name.' '.(int) $categoryObj->id;
        }

        if (!$categoryObj->update()) {
            return false;
        }
    }

    public function hookActionObjectProductAddAfter($params)
    {
        $productObj = $params['object'];
        $linkRewrites = array();
        foreach($productObj->name as $key => $name) {
            $productObj->link_rewrite[$key] = Tools::str2url($name.' '.(int) $productObj->id);
            $productObj->name[$key] = $name.' '.(int) $productObj->id;
        }

        if (!$productObj->update()) {
            return false;
        }
    }

    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $categoryObj = $params['object'];

        $primaryId = PrestaSeederCategory::getPrimaryById($categoryObj->id);
        $seederCategoryObj = new PrestaSeederCategory($primaryId);

        if (!Validate::isLoadedObject($seederCategoryObj)) {
            return;
        }

        $seederCategoryObj->delete();
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        $productObj = $params['object'];

        $primaryId = PrestaSeederProduct::getPrimaryById($productObj->id);
        $seederProductObj = new PrestaSeederProduct($primaryId);

        if (!Validate::isLoadedObject($seederProductObj)) {
            return;
        }

        $seederProductObj->delete();
    }

//    public function hookBackOfficeHeader()
//    {
//        $this->context->controller->addJS($this->_path.'views/js/back.js');
//    }

    private function assignToCategories()
    {
        $productIds = PrestaSeederProduct::getGeneratedProductIds();
        $categoryIds = PrestaSeederCategory::getGeneratedCategoryIds();

        dump($productIds, $categoryIds);

        $difference = count($productIds) / count($categoryIds);
//        if (count($productIds) % count($categoryIds) != 0) {
//            $difference++;
//        }

        $assigning = array_chunk($productIds, $difference);

        $formattingArray = array();

        for ($i = 0; $i <= count($categoryIds); $i++) {
            $formattingArray[$categoryIds[$i]] = $assigning[$i];
        }

        dump($assigning,$formattingArray); die();



    }

    private function createModuleDatabaseTables()
    {
        $sql = array();

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_product` (
                `id_seeder_product` INT(11) NOT NULL AUTO_INCREMENT,
                `id_product` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_product`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_category` (
                `id_seeder_category` INT(11) NOT NULL AUTO_INCREMENT,
                `id_category` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_category`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function deleteModuleDatabaseTables()
    {
        $sql = array();

        $sql[] = '
            DROP TABLE IF EXISTS
                `'._DB_PREFIX_.'seeder_product`,
                `'._DB_PREFIX_.'seeder_category`
        ';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
        return true;
    }

    private function registerModuleHooks()
    {
        if (empty($this->hooks)) {
            return true;
        }

        foreach ($this->hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    private function registerModuleTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true;
        }

        foreach ($tabs as $controller => $tabName) {
            if (!$this->registerModuleTab($controller, $tabName, -1)) {
                return false;
            }
        }

        return true;
    }

    private function getModuleTabs()
    {
        return array(
            self::CONTROLLER_INFO => $this->l('Information'),
            self::CONTROLLER_SETTINGS => $this->l('Settings'),

        );
    }

    private function registerModuleTab($controller, $tabName, $idParent)
    {
        $idTab = (int)Tab::getIdFromClassName($controller);

        if ($idTab) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $controller;
        $tab->name = array();
        $languages = Language::getLanguages(false);
        $tab->module = $this->name;
        $tab->id_parent = (int)$idParent;

        foreach ($languages as $language) {
            $tab->name[$language['id_lang']] = $tabName;
        }

        $tab->add();

        return (bool)$tab->id;
    }

    private function deleteModuleTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true;
        }

        foreach (array_keys($tabs) as $controller) {
            if (!$this->deleteModuleTab($controller)) {
                return false;
            }
        }

        return true;
    }

    private function deleteModuleTab($controller)
    {
        $idTab = (int) Tab::getIdFromClassName($controller);
        $tab = new Tab((int) $idTab);

        if (!Validate::isLoadedObject($tab)) {
            return true;
        }

        if (!$tab->delete()) {
            return false;
        }

        return true;
    }

    private function loadFiles()
    {
        $classes = glob(_PS_MODULE_DIR_.$this->name.'/classes/*.php');

        foreach ($classes as $class) {
            if ($class != _PS_MODULE_DIR_.$this->name.'/classes/index.php') {
                require_once($class);
            }
        }
    }
}
