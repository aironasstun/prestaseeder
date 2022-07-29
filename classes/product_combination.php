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

    public function createProductCombination($random = false, $limit = false, $offset = false)
    {
        $colorCombinations = PrestaSeederAttribute::getColorAttributes();
        $regularCombinations = PrestaSeederAttribute::getRegularAttributes();
        $products = PrestaSeederProduct::getGeneratedProductIds($random, $limit, $offset);

        if(!$colorCombinations) {
            dump('Create attributes with colors first!');
            return;
        }

        if(!$regularCombinations) {
            dump('Create attributes with first!');
            return;
        }

        if(!$products) {
            dump('Create products first!');
            return;
        }

        foreach ($products as $product) {
            $productObj = new Product($product);
            if (!Validate::isLoadedObject($productObj)) {
                continue;
            }
            foreach ($colorCombinations as $color) {
                foreach ($regularCombinations as $attribute) {
                    $combinationObj = new Combination();
                    $combinationObj->id_product = $productObj->id;
                    $combinationObj->reference = $productObj->reference;
                    $combinationObj->price = $this->getRandomPrice();


                    if (!$combinationObj->add()) {
                        continue;
                    }

                    StockAvailable::setQuantity($combinationObj->id_product, $combinationObj->id, $this->getRandomQty());
                    $combinationObj->setAttributes(array($color['id_attribute'], $attribute['id_attribute']));

                    $seederProductCombination = new PrestaSeederProductCombination();
                    $seederProductCombination->id_product = $combinationObj->id_product;
                    $seederProductCombination->id_product_attribute = $combinationObj->id;
                    $seederProductCombination->add();
                }
            }
        }
    }
}
