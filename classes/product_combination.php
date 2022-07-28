<?php

require_once(_PS_MODULE_DIR_.$this->name.'/traits/generate.php'); // IS THIS EVEN NEEDED ?

class PrestaSeederProductCombination extends ObjectModel
{
    use Generate;

    public $id_seeder_product_combination;

    public $id_product;

    public $id_product_attribute;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'seeder_product_combination',
        'primary' => 'id_seeder_product_combination',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function createProductCombination()
    {
            $combinationObj = new Combination();
            $combinationObj->id_product = 102;
            $combinationObj->reference = 'Q90367845-T';

            if (!$combinationObj->add()) {
//                continue;
            }

            $combinationObj->setAttributes(array(181, 184));

            $seederProductCombination = new PrestaSeederProductCombination();
            $seederProductCombination->id_product = $combinationObj->id_product;
            $seederProductCombination->id_product_attribute = $combinationObj->id;
            $seederProductCombination->add();
    }

    public static function getPrimaryById($id_product)
    {
        return (int) Db::getInstance()->getValue('
        SELECT `id_seeder_product`
        FROM `'._DB_PREFIX_.'seeder_product`
        WHERE `id_product` = '.(int) $id_product
        );
    }

    public static function getGeneratedProductIds()
    {
        $products = (array) Db::getInstance()->executeS('
        SELECT `id_product`
        FROM `'._DB_PREFIX_.'seeder_product`
        ');

        $product_ids = array();

        foreach($products as $product) {
            $product_ids[] = (int) $product['id_product'];
        }

        return $product_ids;
    }
}
