<?php

namespace shcherbanich\core\helpers;

class Text
{
    /**
     * Сжатие текста
     *
     * @param string $text
     *
     * @return string $compressedText
     */
    public static function compress($text)
    {

        return base64_encode(gzcompress($text, 9));
    }

    /**
     * Развертывание текста
     *
     * @param string $compressedText
     *
     * @return string $text
     */
    public static function uncompress($compressedText)
    {

        try {

            return gzuncompress(base64_decode($compressedText, 9));

        } catch (\Exception $e) {}

        return '';
    }

    /**
     * Транслитерация текста
     *
     * @param string $str
     *
     * @return string $transliterate_str
     */
    public static function transliterate($str)
    {
        $rus = ['А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'];
        $lat = ['A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya'];
        return str_replace($rus, $lat, $str);
    }

    /**
     * Получить нужную форму
     *
     * @param string $number
     * @param string $before
     * @param string $after
     *
     * @return string $transliterate_str
     */
    public static function get_plural_form($number, $before, $after) {

        $cases = array(2,0,1,1,1,2);

        return $before[($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)]].' '.$number.' '.$after[($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)]];
    }
}