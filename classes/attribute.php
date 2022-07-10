<?php

require_once(_PS_MODULE_DIR_.$this->name.'/traits/generate.php'); // IS THIS EVEN NEEDED ?

class PrestaSeederAttribute extends ObjectModel
{
    use Generate;

    public $id_seeder_attribute;

    public $id_attribute;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'seeder_attribute',
        'primary' => 'id_seeder_attribute',
        'fields' => array(
            'id_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function add($auto_date = true, $null_values = false)
    {
        if (parent::add($auto_date, $null_values)) {
            $attributeObj = new Attribute($this->id_attribute);
            if (!Validate::isLoadedObject($attributeObj)) {
                return false;
            }

            foreach($attributeObj->name as $key => $name) {
                if ($attributeObj->color) {
                    $attributeObj->name[$key] = 'Color ' . (int) $attributeObj->id;
                } else {
                    $attributeObj->name[$key] = $name.' '.(int) $attributeObj->id;
                }
            }

            if (!$attributeObj->update()) {
                return false;
            }
            return true;
        }
    }

    public function createAttribute($amount)
    {
        $attributeGroups = PrestaSeederAttributeGroup::getGeneratedAttributeGroupIds();
        if(!$attributeGroups) {
            dump('Create attribute groups first!');
            return;
        }

        $name = 'Test attribute';

        foreach($attributeGroups as $attributeGroup) {
            for($counter = 1; $counter <= (int) $amount; $counter++) {
                $attributeObj = new Attribute();
                $attributeObj->name = $this->getMultiLang($name);
                $attributeObj->id_attribute_group = (int) $attributeGroup['id_attribute_group'];
                if ($attributeGroup['is_color_group'] == 1) {
                    $attributeObj->color = $this->getRandomColorHex();
                }

                if (!$attributeObj->add()) {
                    continue;
                }

                $seederAttribute = new PrestaSeederAttribute();
                $seederAttribute->id_attribute = $attributeObj->id;
                $seederAttribute->add();

            }
        }
    }

    public static function getPrimaryById($id_attribute)
    {
        return (int) Db::getInstance()->getValue('
        SELECT `id_seeder_attribute`
        FROM `'._DB_PREFIX_.'seeder_attribute`
        WHERE `id_attribute` = '.(int) $id_attribute
        );
    }

}
