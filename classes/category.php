<?php

require_once(_PS_MODULE_DIR_.$this->name.'/traits/generate.php'); // ONCE AGAIN, DO WE NEED THIS ?

class PrestaSeederCategory extends ObjectModel
{
    use Generate;

    const LOREM_IPSUM = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
    Ut fringilla nunc eu libero finibus interdum. Phasellus commodo vehicula mauris in hendrerit.
    Cras placerat eu justo ac mollis.';

    const DEFAULT_IMG_IMPORT_URL = 'https://random.imagecdn.app/500/500';

    public $id_seeder_category;

    public $id_category;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'seeder_category',
        'primary' => 'id_seeder_category',
        'fields' => array(
            'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function add($auto_date = true, $null_values = false)
    {
        if (parent::add($auto_date, $null_values)) {
            $categoryObj = new Category($this->id_category);
            if (!Validate::isLoadedObject($categoryObj)) {
                return false;
            }

            $linkRewrites = array();
            foreach($categoryObj->name as $key => $name) {
                $categoryObj->link_rewrite[$key] = Tools::str2url($name.' '.(int) $categoryObj->id);
                $categoryObj->name[$key] = $name.' '.(int) $categoryObj->id;
            }

            if (!$categoryObj->update()) {
                return false;
            }
            return true;
        }
    }

    public function createCategory($amount)
    {
        $idLang = Context::getContext()->language->id;
        $shops = Shop::getShops(true, null, true);
        $rootCategory = (int) Configuration::get('PS_ROOT_CATEGORY');
        $homeCategory = (int) Configuration::get('PS_HOME_CATEGORY');
        $imageLink = (Configuration::get('SEEDER_IMG_URL')) ? Configuration::get('SEEDER_IMG_URL') : self::DEFAULT_IMG_IMPORT_URL;

        for($counter = 1; $counter <= (int) $amount; $counter++) {

            $name = 'Test category';

            $categoryObj = new Category();
            $categoryObj->active = 1;
            $categoryObj->id_parent = $homeCategory;
            $categoryObj->name = $this->getMultiLang($name);
            $categoryObj->link_rewrite = $this->getMultiLang(Tools::str2url($name));

            if ($categoryObj->add()) {
                $seederCategory = new PrestaSeederCategory();
                $seederCategory->id_category = $categoryObj->id;
                if ($seederCategory->add()) {
                    $this->addPicture($categoryObj->id, null, $imageLink, 'categories');
                }
            }
        }
    }

    public static function getCategories()
    {
        return Db::getInstance()->executeS('
        SELECT `id_seeder_category`, `id_category`
        FROM `'._DB_PREFIX_.'seeder_category`
        ');
    }

    public static function getPrimaryById($id_category)
    {
        return (int) Db::getInstance()->getValue('
        SELECT `id_seeder_category`
        FROM `'._DB_PREFIX_.'seeder_category`
        WHERE `id_category` = '.(int) $id_category
        );
    }

    public static function getGeneratedCategoryIds()
    {
        $categories = (array) Db::getInstance()->executeS('
        SELECT `id_category`
        FROM `'._DB_PREFIX_.'seeder_category`
        ');

        $category_ids = array();

        foreach($categories as $category) {
            $category_ids[] = (int) $category['id_category'];
        }

        return $category_ids;
    }
}