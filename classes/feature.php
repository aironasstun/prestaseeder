<?php

require_once(_PS_MODULE_DIR_.$this->name.'/traits/generate.php'); // IS THIS EVEN NEEDED ?

class PrestaSeederFeature extends ObjectModel
{
    use Generate;

    public $id_seeder_feature;

    public $id_feature;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'seeder_feature',
        'primary' => 'id_seeder_feature',
        'fields' => array(
            'id_feature' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function add($auto_date = true, $null_values = false)
    {
        if (parent::add($auto_date, $null_values)) {
            $featureObj = new Feature($this->id_feature);
            if (!Validate::isLoadedObject($featureObj)) {
                return false;
            }

            foreach($featureObj->name as $key => $name) {
                $featureObj->name[$key] = $name.' '.(int) $featureObj->id;
            }

            if (!$featureObj->update()) {
                return false;
            }
            return true;
        }
    }

    public function createFeature($amount)
    {
        $name = 'Test feature';

        for($counter = 1; $counter <= (int) $amount; $counter++) {
            $featureObj = new Feature();
            $featureObj->name = $this->getMultiLang($name);

            if (!$featureObj->add()) {
                continue;
            }

            $seederFeature = new PrestaSeederFeature();
            $seederFeature->id_feature = $featureObj->id;
            $seederFeature->add();
        }
    }

    public static function getPrimaryById($id_feature)
    {
        return (int) Db::getInstance()->getValue('
        SELECT `id_seeder_feature`
        FROM `'._DB_PREFIX_.'seeder_feature`
        WHERE `id_feature` = '.(int) $id_feature
        );
    }

    public static function getGeneratedFeatureIds()
    {
        return (array) Db::getInstance()->executeS('
        SELECT `id_feature`
        FROM `'._DB_PREFIX_.'seeder_feature`
        ');
    }
}
