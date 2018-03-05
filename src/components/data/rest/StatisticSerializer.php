<?php

namespace shcherbanich\core\components\data\rest;

use yii\base\Arrayable;
use yii\base\Model;
use yii\data\DataProviderInterface;

class StatisticSerializer extends \yii\rest\Serializer
{
    public $className;

    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data, $className = '')
    {
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

        if($this->className) {

            $model = new $this->className;

            $extraFields = $model->extraFields();
        }
        else{

            $extraFields = [];
        }

        foreach ($expand as $k => $extraField) {

            if (isset($extraFields[$extraField])){

                $callbackExpands[$extraField] = $extraFields[$extraField];

                unset($expand[$k]);
            }
            elseif (!in_array($extraField, $extraFields)) {

                unset($expand[$k]);
            }
        }

        foreach ($models as $i => $model) {

            if ($fields) {

                $filterModel = [];

                foreach ($model as $field => $value) {

                    if (in_array($field, $fields)) {

                        $filterModel[$field] = $value;
                    }
                }

                $models[$i] = $filterModel;

                foreach($callbackExpands as $key => $callbackExpand){

                    $models[$i][$key] = $callbackExpand($model);
                }
            }
        }
        return $models;
    }
}