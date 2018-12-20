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

        return gzuncompress(base64_decode($compressedText, 9));
    }

    /**
     * Изменение формы слова
     *
     * @param string $number
     * @param string $before
     * @param string $after
     *
     * @return string $transliterate_str
     */
    public static function getPluralForm($number, $before, $after) {

        $cases = array(2,0,1,1,1,2);

        return $before[($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)]].' '.$number.' '.$after[($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)]];
    }
}