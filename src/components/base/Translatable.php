<?php

namespace shcherbanich\core\components\Base;

interface Translatable
{

    public static function translate(array $attributes, $lang);
}