<?php

namespace shcherbanich\core\components\Base;

interface Translatable
{

    public static function translatableAttributes();

    public static function translate(array $attributes, $lang);
}