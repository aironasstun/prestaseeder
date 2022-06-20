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

    public function createProduct($amount)
    {
        $idLang = Context::getContext()->language->id;
        $shops = Shop::getShops(true, null, true);
        $defaultTaxRuleGroup = Configuration::get('SEEDER_DEFAULT_TAX');
        $rootCategory = Configuration::get('PS_ROOT_CATEGORY');
        $homeCategory = Configuration::get('PS_HOME_CATEGORY');
        $imageLink = (Configuration::get('SEEDER_IMG_URL')) ? Configuration::get('SEEDER_IMG_URL') : self::DEFAULT_IMG_IMPORT_URL;

        for($counter = 1; $counter <= (int) $amount; $counter++) {

            $name = 'Test product '.(int) $counter;

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
            $productObj->on_sale = 0;
            $productObj->online_only = 0;
            $productObj->meta_description = '';
            $productObj->id_tax_rules_group = (int) $defaultTaxRuleGroup;
            $productObj->link_rewrite = $this->getMultiLang(Tools::str2url($name));
            if (!$productObj->add()) {
                continue;
            }

            $seederProduct = new PrestaSeederProduct();
            $seederProduct->id_product = $productObj->id;
            $seederProduct->add();

            $productObj->addToCategories(array($homeCategory));
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