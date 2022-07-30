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
        $colorConfiguration = Configuration::get('SEEDER_COLOR_IN_COMBINATIONS');
        $attributeGroupsConfiguration = Configuration::get('SEEDER_ATTRIBUTE_GROUP_IN_COMBINATIONS');
        $attributesConfiguration = Configuration::get('SEEDER_ATTRIBUTES_IN_COMBINATIONS');

        $products = PrestaSeederProduct::getGeneratedProductIds($random, $limit, $offset);
        $colorCombinations = PrestaSeederAttribute::getColorAttributes(true, $colorConfiguration);

        if(!$colorCombinations) {
            dump('Create attributes with colors first!');
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

            $attributeGroups = PrestaSeederAttributeGroup::getRegularAttributeGroups(true, $attributeGroupsConfiguration);

            $attributeGroupCollection = array();
            foreach ($attributeGroups as $attributeGroup) {
                $attributeGroupCollection[$attributeGroup['id_attribute_group']] = PrestaSeederAttribute::getRegularAttributesFromGroup((int) $attributeGroup['id_attribute_group'], true, $attributesConfiguration);
            }

            if(!$attributeGroupCollection) {
                dump('Unable to get attributes from attribute groups');
                return;
            }

            foreach ($colorCombinations as $color) {
                foreach ($attributeGroupCollection as $attributeGroup) {
                    foreach($attributeGroup as $attribute) {


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
}
