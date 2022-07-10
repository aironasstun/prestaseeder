<?php

require_once(_PS_MODULE_DIR_.$this->name.'/traits/generate.php'); // IS THIS EVEN NEEDED ?

class PrestaSeederAttributeGroup extends ObjectModel
{
    use Generate;

    public $id_seeder_attribute_group;

    public $id_attribute_group;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'seeder_attribute_group',
        'primary' => 'id_seeder_attribute_group',
        'fields' => array(
            'id_attribute_group' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function add($auto_date = true, $null_values = false)
    {
        if (parent::add($auto_date, $null_values)) {
            $attributeGroupObj = new AttributeGroup($this->id_attribute_group);
            if (!Validate::isLoadedObject($attributeGroupObj)) {
                return false;
            }

            foreach($attributeGroupObj->name as $key => $name) {
                $attributeGroupObj->public_name[$key] = $name.' '.(int) $attributeGroupObj->id;
                $attributeGroupObj->name[$key] = $name.' '.(int) $attributeGroupObj->id;
            }

            if (!$attributeGroupObj->update()) {
                return false;
            }
            return true;
        }
    }

    public function createAttributeGroup($amount)
    {
        for($counter = 1; $counter <= (int) $amount; $counter++) {

            $group_type = 'select';
            $name = 'Test attribute group';

            if ($counter <= 1) {
                $group_type = 'color';
            }

            $attributeGroupObj = new AttributeGroup();
            $attributeGroupObj->name = $this->getMultiLang($name);
            $attributeGroupObj->public_name = $this->getMultiLang($name);
            $attributeGroupObj->group_type = $group_type;
            if (!$attributeGroupObj->add()) {
                continue;
            }

            $seederAttributeGroup = new PrestaSeederAttributeGroup();
            $seederAttributeGroup->id_attribute_group = $attributeGroupObj->id;
            $seederAttributeGroup->add();

        }
    }

    public static function getPrimaryById($id_attribute_group)
    {
        return (int) Db::getInstance()->getValue('
        SELECT `id_seeder_attribute_group`
        FROM `'._DB_PREFIX_.'seeder_attribute_group`
        WHERE `id_attribute_group` = '.(int) $id_attribute_group
        );
    }

    public static function getGeneratedAttributeGroupIds()
    {
        return (array) Db::getInstance()->executeS('
        SELECT `sag`.`id_attribute_group`, ag.`is_color_group`
        FROM `'._DB_PREFIX_.'seeder_attribute_group` sag
            LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag
                ON (`ag`.`id_attribute_group` = `sag`.`id_attribute_group`)
        ');
    }
}
