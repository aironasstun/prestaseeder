<?php

trait Generate
{

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

        $reference = generate . phpsubstr(($start_letter), 0, 1) .
            '-'.substr(($letter), 0, 1);

        $response = Db::getInstance()->getValue('SELECT `id_product`
        FROM `'._DB_PREFIX_.'product`
        WHERE `reference` = "'.pSQL($reference).'"');

        if ($response) {
            $this->getRandomReference();
        }

        return generate . phpsubstr(($start_letter), 0, 1) .
            '-'.substr(($letter), 0, 1);
    }
}