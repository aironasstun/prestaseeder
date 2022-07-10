<?php

require_once(_PS_MODULE_DIR_.$this->name.'/traits/generate.php'); // IS THIS EVEN NEEDED ?

class PrestaSeederFeatureValue extends ObjectModel
{
    use Generate;

    public $id_seeder_feature_value;

    public $id_feature_value;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'seeder_feature_value',
        'primary' => 'id_seeder_feature_value',
        'fields' => array(
            'id_feature_value' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function add($auto_date = true, $null_values = false)
    {
        if (parent::add($auto_date, $null_values)) {
            $featureValueObj = new FeatureValue($this->id_feature_value);
            if (!Validate::isLoadedObject($featureValueObj)) {
                return false;
            }

            foreach($featureValueObj->value as $key => $name) {
                $featureValueObj->value[$key] = $name.' '.(int) $featureValueObj->id;
            }

            if (!$featureValueObj->update()) {
                return false;
            }
            return true;
        }
    }

    public function createFeatureValue($amount)
    {
        $features = PrestaSeederFeature::getGeneratedFeatureIds();
        if(!$features) {
            dump('Create features first!');
            return;
        }

        foreach ($features as $feature) {
            for($counter = 1; $counter <= (int) $amount; $counter++) {

                $name = 'Test feature value';
                $featureValueObj = new FeatureValue();
                $featureValueObj->id_feature = (int) $feature['id_feature'];
                $featureValueObj->value = $this->getMultiLang($name);
                if (!$featureValueObj->add()) {
                    continue;
                }

                $seederFeatureValue = new PrestaSeederFeatureValue();
                $seederFeatureValue->id_feature_value = $featureValueObj->id;
                $seederFeatureValue->add();

            }
        }
    }

    public static function getPrimaryById($id_feature_value)
    {
        return (int) Db::getInstance()->getValue('
        SELECT `id_seeder_feature_value`
        FROM `'._DB_PREFIX_.'seeder_feature_value`
        WHERE `id_feature_value` = '.(int) $id_feature_value
        );
    }
}
