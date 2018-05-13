<?php

namespace shcherbanich\core\components\data\rest;

use shcherbanich\core\components\base\GroupExpandInterface;
use yii\base\Arrayable;
use yii\base\Model;
use yii\data\DataProviderInterface;

class StatisticSerializer extends \yii\rest\Serializer
{
    /**
     * Model class name
     */
    public $className;

    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @param string $className.
     * @return mixed the converted data.
     */
    public function serialize($data, $className = '')
    {

        $this->className = $className;

        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        }

        return $data;
    }

    /**
     * Serializes a set of models.
     * @param array $models
     * @return array the array representation of the models
     */
    protected function serializeModels(array $models)
    {
        list ($fields, $expand) = $this->getRequestedFields();

        $callbackExpands = [];

        $groupExpandsClasses = [];

        $childCallbackExpands = [];

        if($this->className) {

            $model = new $this->className;

            $extraFields = $model->extraFields();
        }
        else{

            $extraFields = [];
        }

        foreach ($expand as $k => $extraField) {

            $exp_extraField = explode('.', $extraField);

            if(!isset($exp_extraField[1])) {

                if (isset($extraFields[$extraField])) {

                    if(is_object($extraFields[$extraField]) && $extraFields[$extraField] instanceof GroupExpandInterface){

                        $groupExpandsClasses[$extraField] = new $extraFields[$extraField];
                    }
                    elseif(is_callable($extraFields)) {

                        $callbackExpands[$extraField] = $extraFields[$extraField];
                    }

                    unset($expand[$k]);
                } elseif (!in_array($extraField, $extraFields)) {

                    unset($expand[$k]);
                }
            }
            else{

                $childCallbackExpands[$exp_extraField[0]] = isset($childCallbackExpands[$exp_extraField[0]]) ? $childCallbackExpands[$exp_extraField[0]] : [];

                $childCallbackExpands[$exp_extraField[0]][] = $exp_extraField[1];
            }
        }

        foreach ($models as $i => $model) {

            foreach($callbackExpands as $key => $callbackExpand){

                $c_expands = isset($childCallbackExpands[$key]) ? $childCallbackExpands[$key] : [];

                $models[$i][$key] = $callbackExpand(json_decode(json_encode($model)), $c_expands);
            }
        }

        foreach ($groupExpandsClasses as $expand_key => $groupExpandsClass){

            $c_expands = isset($childCallbackExpands[$expand_key]) ? $childCallbackExpands[$expand_key] : [];

            $groupExpandsClass->setModels(json_decode(json_encode($models)));

            $groupExpandsClass->setChildExpands($c_expands);

            $groupExpandsClass->setExpandKey($expand_key);

            $groupExpandsClass->process();

            $models = $groupExpandsClass->getModels();
        }

        return $models;
    }
}