<?php

namespace shcherbanich\core\components\base;

interface Translatable
{

    public static function translatableAttributes();

    public static function translate(array $attributes, $lang);
}