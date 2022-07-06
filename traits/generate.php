<?php

trait Generate
{

    public function getRandomColorHex()
    {
        $chars = 'ABCDEF0123456789';
        $color = '#';
        for ($i = 0; $i < 6; $i++) {
            $color .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $color;
    }

    public function getMultiLang($field)
    {
        $response = array();
        foreach (Language::getIDs(false) as $id_lang) {
            $response[$id_lang] = $field;
        }
        return $response;
    }

    public function getRandomEan()
    {
        return substr(str_shuffle("0123456789123"), 0, 13);
    }

    public function getRandomPrice()
    {
        return number_format(((float) rand(47, 630) / 10), 6, '.', '');
    }

    public function getRandomQty()
    {
        return (int) rand(1, 971);
    }

    public function getRandomReference()
    {
        $start_letter = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $number = str_shuffle('0123456789');
        $letter = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        $reference = substr(($start_letter), 0, 1).substr(($number), 0, 8).
            '-'.substr(($letter), 0, 1);

        $response = Db::getInstance()->getValue('SELECT `id_product`
        FROM `'._DB_PREFIX_.'product`
        WHERE `reference` = "'.pSQL($reference).'"');

        if ($response) {
            $this->getRandomReference();
        }

        return $reference;
    }

    public function addPicture($id_entity, $id_image = null, $imgPath, $type = 'products')
    {
        $tmpFile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermarkTypes = explode(',', Configuration::get('WATERMARK_TYPES'));
        switch ($type) {
            default:
            case 'products':
                $image_obj = new Image($id_image);
                $path = $image_obj->getPathForCreation();
                break;
            case 'categories':
                $path = _PS_CAT_IMG_DIR_ . (int) $id_entity;
                break;
        }
        $imgPath = str_replace(' ', '%20', trim($imgPath));
        // Evaluate the memory required to resize the image: if it's too big we can't resize it.
        if (!ImageManager::checkImageMemoryLimit($imgPath)) {
            return false;
        }
        if (@copy($imgPath, $tmpFile)) {
            ImageManager::resize($tmpFile, $path . '.jpg');
            $imagesTypes = ImageType::getImagesTypes($type);
            foreach ($imagesTypes as $imageType) {
                ImageManager::resize($tmpFile, $path . '-' . stripslashes($imageType['name']) . '.jpg', $imageType['width'], $imageType['height']);
                if (in_array($imageType['id_image_type'], $watermarkTypes)) {
                    Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
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