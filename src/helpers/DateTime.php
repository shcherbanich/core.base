<?php

namespace shcherbanich\core\helpers;

class DateTime
{

    const FORMAT_RU_DATE = '%frd%';

    const FORMAT_TIME = '%ft%';

    public static $months = [ 1 => 'января' , 'февраля' , 'марта' , 'апреля' , 'мая' , 'июня' , 'июля' , 'августа' , 'сентября' , 'октября' , 'ноября' , 'декабря' ];

    public static function unix_to_text($unix, $date_format = '') {

        $unix *= 1;

        $date_format = preg_replace_callback('/('.self::FORMAT_RU_DATE.')/', function($matches) use($unix){

            return date( 'd ' . self::$months[date( 'n', $unix )] . ' Y', $unix );

        }, $date_format);


        $date_format = preg_replace_callback('/('.self::FORMAT_TIME.')/', function($matches) use($unix){

            return date('H:i:s', $unix);

        }, $date_format);

        return date($date_format, $unix);
    }
}