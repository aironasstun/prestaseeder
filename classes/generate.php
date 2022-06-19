<?php

trait Generate
{
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

        return substr(($start_letter), 0, 1).substr(($number), 0, 8).
            '-'.substr(($letter), 0, 1);
    }
}