<?php

class PrestaSeeder extends Module
{

    const CONTROLLER_INFO = 'AdminPrestaSeederInformation';
    const CONTROLLER_SETTINGS = 'AdminPrestaSeederSettings';

    const LOREM_IPSUM = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
    Ut fringilla nunc eu libero finibus interdum. Phasellus commodo vehicula mauris in hendrerit.
    Cras placerat eu justo ac mollis. Donec nec ipsum sagittis, condimentum dolor vel, luctus ante.
    Sed ac interdum nisl. Nulla faucibus tortor quis tellus sollicitudin, ac efficitur nibh
    facilisis. Nulla semper ligula placerat ipsum dictum eleifend. In hac habitasse platea
    dictumst. Morbi rutrum rutrum neque, ac ullamcorper nisi malesuada id. Sed porttitor arcu
    sed interdum ultricies. Nullam luctus facilisis felis at condimentum.
    Vestibulum hendrerit malesuada pulvinar.';
    const DEFAULT_IMG_IMPORT_URL = 'https://random.imagecdn.app/500/500';

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
    }

    public function install()
    {
        Configuration::updateValue('SEEDER_IMG_URL', 'https://random.imagecdn.app/500/500');

        if (!parent::install()) {
            $this->_errors[] = $this->l('Could not install module');

            return false;
        }

        if (!$this->registerModuleTabs()) {
            $this->_errors[] = $this->l('Could not register module admin controllers');

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
                $this->createProduct($amount);
                break;
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
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');
        $idTab = $tabRepository->findOneIdByClassName($controller);
        $tab = new Tab((int)$idTab);

        if (!Validate::isLoadedObject($tab)) {
            return true;
        }

        if (!$tab->delete()) {
            return false;
        }

        return true;
    }

    private function getRandomEan()
    {
        return substr(str_shuffle("0123456789123"), 0, 13);
    }

    private function getRandomPrice()
    {
        return number_format(((float) rand(47, 630) / 10), 6, '.', '');
    }

    private function getRandomQty()
    {
        return (int) rand(1, 971);
    }

    private function getRandomReference()
    {
        $start_letter = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $number = str_shuffle('0123456789');
        $letter = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        return substr(($start_letter), 0, 1).substr(($number), 0, 8).
            '-'.substr(($letter), 0, 1);
    }

    private function createProduct($amount)
    {
        $idLang = $this->context->language->id;
        $shops = Shop::getShops(true, null, true);
        $rootCategory = Configuration::get('PS_ROOT_CATEGORY');

        $imageLink = (Configuration::get('SEEDER_IMG_URL')) ? Configuration::get('SEEDER_IMG_URL') : self::DEFAULT_IMG_IMPORT_URL;

        for($counter = 1; $counter <= (int) $amount; $counter++) {
            $productObj = new Product(null, false, $idLang);
            $productObj->ean13 = $this->getRandomEan();
            $productObj->reference = $this->getRandomReference();
            $productObj->name = 'Test product '.(int) $counter;
            $productObj->description = self::LOREM_IPSUM;
            $productObj->id_category_default = $rootCategory;
            $productObj->redirect_type = '301';
            $productObj->price = number_format($this->getRandomPrice(), 6, '.', '');
            $productObj->minimal_quantity = 1;
            $productObj->show_price = 1;
            $productObj->on_sale = 0;
            $productObj->online_only = 0;
            $productObj->meta_description = '';
            if (!$productObj->add()) {
                continue;
            }

            StockAvailable::setQuantity($productObj->id, null, $this->getRandomQty());

            $image = new Image();
            $image->id_product = $productObj->id;
            $image->position = Image::getHighestPosition($productObj->id) + 1;
            $image->cover = true;
            if (($image->validateFields(false, true)) === true && ($image->validateFieldsLang(false, true)) === true && $image->add()) {
                $image->associateTo($shops);
                if (!$this->addPicture($productObj->id, $image->id, $imageLink)) {
                    $image->delete();
                }
            }
        }
    }
    
    private function addPicture($idProduct, $idImage = null, $imgPath)
    {
            $tmpFile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
            $watermarkTypes = explode(',', Configuration::get('WATERMARK_TYPES'));
            $imageObj = new Image((int)$idImage);
            $path = $imageObj->getPathForCreation();
            $imgPath = str_replace(' ', '%20', trim($imgPath));
            // Evaluate the memory required to resize the image: if it's too big we can't resize it.
            if (!ImageManager::checkImageMemoryLimit($imgPath)) {
                return false;
            }
            if (@copy($imgPath, $tmpFile)) {
                ImageManager::resize($tmpFile, $path . '.jpg');
                $imagesTypes = ImageType::getImagesTypes('products');
                foreach ($imagesTypes as $imageType) {
                    ImageManager::resize($tmpFile, $path . '-' . stripslashes($imageType['name']) . '.jpg', $imageType['width'], $imageType['height']);
                    if (in_array($imageType['id_image_type'], $watermarkTypes)) {
                        Hook::exec('actionWatermark', array('id_image' => $idImage, 'id_product' => $idProduct));
                    }
                }
            } else {
                unlink($tmpFile);
                return false;
            }
            unlink($tmpFile);
            return true;
    }
}
