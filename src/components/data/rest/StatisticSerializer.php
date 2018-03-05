<?php

namespace shcherbanich\core\components\data\rest;

class StatisticSerializer extends \yii\rest\Serializer
{

    /**
     * Serializes a set of models.
     * @param array $models
     * @return array the array representation of the models
     */
    protected function serializeModels(array $models)
    {
        list ($fields, $expand) = $this->getRequestedFields();

        $callbackExpands = [];

        $model = current($models);

        $extraFields = $model->extraFields();

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