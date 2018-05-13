<?php

namespace shcherbanich\core\components\base;

class GroupExpand
{

    private $child_expands = [];

    private $models = [];

    private $expand_key;

    public function setChildExpands(array $child_expands = []){

        $this->child_expands = $child_expands;
    }

    public function getChildExpands(){

        return $this->child_expands;
    }

    public function setModels(array $models){

        $this->models = $models;
    }

    public function getModels(){

        return $this->models;
    }

    public function setExpandKey($expand_key){

        $this->expand_key = $expand_key;
    }

    public function getExpandKey(){

        return $this->expand_key;
    }
}