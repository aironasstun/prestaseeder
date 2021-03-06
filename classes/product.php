<?php

require_once(_PS_MODULE_DIR_.$this->name.'/traits/generate.php'); // IS THIS EVEN NEEDED ?

class PrestaSeederProduct extends ObjectModel
{
    use Generate;

    const LOREM_IPSUM = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
    Ut fringilla nunc eu libero finibus interdum. Phasellus commodo vehicula mauris in hendrerit.
    Cras placerat eu justo ac mollis.';

    const DEFAULT_IMG_IMPORT_URL = 'https://random.imagecdn.app/500/500';

    public $id_seeder_product;

    public $id_product;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'seeder_product',
        'primary' => 'id_seeder_product',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function add($auto_date = true, $null_values = false)
    {
        if (parent::add($auto_date, $null_values)) {
            $productObj = new Product($this->id_product);
            if (!Validate::isLoadedObject($productObj)) {
                return false;
            }

            foreach($productObj->name as $key => $name) {
                $productObj->link_rewrite[$key] = Tools::str2url($name.' '.(int) $productObj->id);
                $productObj->name[$key] = $name.' '.(int) $productObj->id;
            }

            if (!$productObj->update()) {
                return false;
            }
            return true;
        }
    }

    public function createProduct($amount)
    {
        $idLang = Context::getContext()->language->id;
        $shops = Shop::getShops(true, null, true);
        $defaultTaxRuleGroup = Configuration::get('SEEDER_DEFAULT_TAX');
        $rootCategory = Configuration::get('PS_ROOT_CATEGORY');
        $homeCategory = Configuration::get('PS_HOME_CATEGORY');
        $imageLink = (Configuration::get('SEEDER_IMG_URL')) ? Configuration::get('SEEDER_IMG_URL') : self::DEFAULT_IMG_IMPORT_URL;

        for($counter = 1; $counter <= (int) $amount; $counter++) {

            $name = 'Test product';

            $productObj = new Product();
            $productObj->ean13 = $this->getRandomEan();
            $productObj->reference = $this->getRandomReference();
            $productObj->name = $this->getMultiLang($name);
            $productObj->description = self::LOREM_IPSUM;
            $productObj->id_category_default = $rootCategory;
            $productObj->redirect_type = '301';
            $productObj->price = $this->getRandomPrice();
            $productObj->minimal_quantity = 1;
            $productObj->show_price = 1;
            $productObj->id_tax_rules_group = (int) $defaultTaxRuleGroup;
            $productObj->link_rewrite = $this->getMultiLang(Tools::str2url($name));
            if (!$productObj->add()) {
                continue;
            }

            $seederProduct = new PrestaSeederProduct();
            $seederProduct->id_product = $productObj->id;
            $seederProduct->add();

            StockAvailable::setQuantity($productObj->id, null, $this->getRandomQty());

            $this->addPictures($productObj->id, rand(2,3), $imageLink, $shops);
        }
    }

    public static function getPrimaryById($id_product)
    {
        return (int) Db::getInstance()->getValue('
        SELECT `id_seeder_product`
        FROM `'._DB_PREFIX_.'seeder_product`
        WHERE `id_product` = '.(int) $id_product
        );
    }

    public static function getGeneratedProductIds($random = false, $limit = false, $offset = false)
    {

        $sql = 'SELECT `id_product`
        FROM `'._DB_PREFIX_.'seeder_product`';

        if ($random) {
            $sql .= 'ORDER BY RAND()';
        }

        if ($limit) {
            $sql .= 'LIMIT '.(int) $limit;
        }

        if ($offset) {
            $sql .= 'OFFSET '.(int) $offset;
        }

        $products = (array) Db::getInstance()->executeS($sql);

        $product_ids = array();

        foreach($products as $product) {
            $product_ids[] = (int) $product['id_product'];
        }

        return $product_ids;
    }
}
