<?php

class PrestaSeeder extends Module
{

    const CONTROLLER_INFO = 'AdminPrestaSeederInformation';
    const CONTROLLER_SETTINGS = 'AdminPrestaSeederSettings';

    private $hooks = array(
        'backOfficeHeader',
        'header',
        'actionObjectProductDeleteAfter',
        'actionObjectCategoryDeleteAfter',
        'actionObjectAttributeDeleteAfter',
        'actionObjectAttributeGroupDeleteAfter',
        'actionObjectFeatureDeleteAfter',
        'actionObjectFeatureValueDeleteAfter',
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

    public function processCron($action = '', $amount = 0)
    {
        switch ($action) {
            case 'createProducts':
                $productSeederObj = new PrestaSeederProduct();
                $productSeederObj->createProduct($amount);
                break;
            case 'createAttributeGroups':
                $attributeGroupSeederObj = new PrestaSeederAttributeGroup();
                $attributeGroupSeederObj->createAttributeGroup($amount);
                break;
            case 'createAttributes':
                $attributeSeederObj = new PrestaSeederAttribute();
                $attributeSeederObj->createAttribute($amount);
                break;
            case 'createCategories':
                $categorySeederObj = new PrestaSeederCategory();
                $categorySeederObj->createCategory($amount);
                break;
            case 'createFeatures':
                $featureSeederObj = new PrestaSeederFeature();
                $featureSeederObj->createFeature($amount);
                break;
            case 'createFeatureValues':
                $featureValueSeederObj = new PrestaSeederFeatureValue();
                $featureValueSeederObj->createFeatureValue($amount);
                break;
            case 'assignToCategories':
                $this->assignToCategories();
                break;
            case 'createCombinations':
                $productCombinationSeederObj = new PrestaSeederProductCombination();
                $productCombinationSeederObj->createProductCombination();
                break;
            case 'assignFeaturesToProducts':
                $this->assignFeaturesToProducts();
                break;
            case 'full':
                $start = microtime(true);
                $this->processCron($action = 'createAttributeGroups', $amount);
                $this->processCron($action = 'createAttributes', $amount);
                $this->processCron($action = 'createFeatures', $amount);
                $this->processCron($action = 'createFeatureValues', $amount);
                $this->processCron($action = 'createProducts', $amount*2);
                $this->processCron($action = 'createCategories', $amount);
                $this->processCron($action = 'assignToCategories');
                $this->processCron($action = 'assignFeaturesToProducts');
                $this->processCron($action = 'createCombinations');
                dump('Finally done.');
                dump('Execution time: ' . number_format(microtime(true) - $start, 5, ',', '') . ' s');
                break;
        }
    }

    public function getContent()
    {
        $url = $this->context->link->getAdminLink(self::CONTROLLER_SETTINGS);

        Tools::redirectAdmin($url);
    }

    public function getMenu()
    {
        $currentController = Tools::getValue('controller');

        $menu = array(
            array(
                'url' => $this->context->link->getAdminLink(self::CONTROLLER_SETTINGS),
                'title' => $this->l('Settings'),
                'current' => self::CONTROLLER_SETTINGS == $currentController,
                'icon' => 'icon icon-cogs'
            ),
            array(
                'url' => $this->context->link->getAdminLink(self::CONTROLLER_INFO),
                'title' => $this->l('Information'),
                'current' => self::CONTROLLER_INFO == $currentController,
                'icon' => 'icon icon-cogs'
            ),
        );

        $this->context->smarty->assign('menu', $menu);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/menu.tpl');
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

    public function hookActionObjectAttributeDeleteAfter($params)
    {
        $attributeObj = $params['object'];

        $primaryId = PrestaSeederAttribute::getPrimaryById($attributeObj->id);
        $seederAttributeObj = new PrestaSeederAttribute($primaryId);

        if (!Validate::isLoadedObject($seederAttributeObj)) {
            return;
        }

        $seederAttributeObj->delete();
    }

    public function hookActionObjectAttributeGroupDeleteAfter($params)
    {
        $attributeGroupObj = $params['object'];

        $primaryId = PrestaSeederAttributeGroup::getPrimaryById($attributeGroupObj->id);
        $seederAttributeGroupObj = new PrestaSeederAttributeGroup($primaryId);

        if (!Validate::isLoadedObject($seederAttributeGroupObj)) {
            return;
        }

        $seederAttributeGroupObj->delete();
    }

    public function hookActionObjectFeatureDeleteAfter($params)
    {
        $featureObj = $params['object'];

        $primaryId = PrestaSeederFeature::getPrimaryById($featureObj->id);
        $seederFeatureObj = new PrestaSeederFeature($primaryId);

        if (!Validate::isLoadedObject($seederFeatureObj)) {
            return;
        }

        $seederFeatureObj->delete();
    }

    public function hookActionObjectFeatureValueDeleteAfter($params)
    {
        $featureValueObj = $params['object'];

        $primaryId = PrestaSeederFeatureValue::getPrimaryById($featureValueObj->id);
        $seederFeatureValueObj = new PrestaSeederFeatureValue($primaryId);

        if (!Validate::isLoadedObject($seederFeatureValueObj)) {
            return;
        }

        $seederFeatureValueObj->delete();
    }


    private function assignToCategories()
    {
        $productIds = PrestaSeederProduct::getGeneratedProductIds();
        $categoryIds = PrestaSeederCategory::getGeneratedCategoryIds();

        dump($productIds, $categoryIds);

        $categoryCounter = 0;

        foreach ($productIds as $productId) {
            // Check if currently counter is not bigger than total array length. We use -1 because array starts from 0
            if ($categoryCounter > count($categoryIds)-1) {
                $categoryCounter = 0;
            }

            $productObj = new Product((int) $productId);
            if(!Validate::isLoadedObject($productObj)) {
                return;
            }

            $productObj->id_category_default = $categoryIds[$categoryCounter];
            if (!$productObj->update()) {
                continue;
            }

            $categoryCounter++;
        }
    }

    private function assignFeaturesToProducts()
    {
        $productIds = PrestaSeederProduct::getGeneratedProductIds();
        $featureIds = PrestaSeederFeature::getGeneratedFeatureIds();

        foreach ($productIds as $productId) {
            foreach($featureIds as $featureId) {
                $featureValue = PrestaSeederFeatureValue::getRandomFeatureValue((int) $featureId['id_feature']);
                Product::addFeatureProductImport($productId, $featureId['id_feature'], $featureValue);
            }
        }
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
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_product_combination` (
                `id_seeder_product_combination` INT(11) NOT NULL AUTO_INCREMENT,
                `id_product` INT(11) NOT NULL,
                `id_product_attribute` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_product_combination`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_category` (
                `id_seeder_category` INT(11) NOT NULL AUTO_INCREMENT,
                `id_category` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_category`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_attribute_group` (
                `id_seeder_attribute_group` INT(11) NOT NULL AUTO_INCREMENT,
                `id_attribute_group` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_attribute_group`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_attribute` (
                `id_seeder_attribute` INT(11) NOT NULL AUTO_INCREMENT,
                `id_attribute` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_attribute`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_feature` (
                `id_seeder_feature` INT(11) NOT NULL AUTO_INCREMENT,
                `id_feature` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_feature`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seeder_feature_value` (
                `id_seeder_feature_value` INT(11) NOT NULL AUTO_INCREMENT,
                `id_feature_value` INT(11) NOT NULL,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_seeder_feature_value`)
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
                `'._DB_PREFIX_.'seeder_product_combination`,
                `'._DB_PREFIX_.'seeder_category`,
                `'._DB_PREFIX_.'seeder_attribute_group`,
                `'._DB_PREFIX_.'seeder_attribute`,
                `'._DB_PREFIX_.'seeder_feature`,
                `'._DB_PREFIX_.'seeder_feature_value`
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
            self::CONTROLLER_SETTINGS => $this->l('Settings'),
            self::CONTROLLER_INFO => $this->l('Information'),
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
