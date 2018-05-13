<?php

namespace shcherbanich\core\components\base;

interface GroupExpandInterface
{

    public function setChildExpands(array $child_expands = []);

    public function getChildExpands();

    public function setModels(array $models);

    public function getModels();

    public function process();

    public function setExpandKey($expand_key);

    public function getExpandKey();
}