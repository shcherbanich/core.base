<?php

namespace shcherbanich\core\components\data\rest;

use yii\base\Arrayable;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Link;

class Serializer extends \yii\rest\Serializer
{

    /**
     * Serializes a set of models.
     * @param array $models
     * @return array the array representation of the models
     */
    protected function serializeModels(array $models)
    {
        list ($fields, $expand) = $this->getRequestedFields();

        $returnModels = [];

        if ($expand) {

            $primaryKeys = [];

            if ($models) {

                $model = current($models);

                $className = $model->className();


                $sortNeed = [];

                foreach ($models as $i => $model) {

                    $pk = $model->getPrimaryKey(true);

                    foreach ($pk as $key => $value) {

                        $primaryKeys[$model->tableName() . ".$key"][$value] = $value;
                    }

                    $keyHash = md5(json_encode($pk));

                    if (!isset($sortNeed[$keyHash])) {

                        $sortNeed[$i] = $keyHash;
                    } else {

                        $sortNeed[$i] = $keyHash . sha1(json_encode($pk));
                    }
                }

                $model = new $className;

                $extraFields = $model->extraFields();

                $pk = $model->getPrimaryKey(true);

                $callbackExpands = [];

                foreach ($expand as $k => $extraField) {

                    if (isset($extraFields[$extraField])){

                        $callbackExpands[$extraField] = $extraFields[$extraField];

                        unset($expand[$k]);
                    }
                    elseif (!in_array($extraField, $extraFields)) {

                        unset($expand[$k]);
                    }
                }

                $models = $model->find()
                    ->joinWith($expand)
                    ->where($primaryKeys)
                    ->all();

                $sortCurrentData = [];

                foreach ($models as $i => $model) {

                    $c_pk = [];

                    foreach($pk as $k=>$v){

                        $c_pk[$k] = $model[$k];
                    }

                    $keyHash = md5(json_encode($c_pk));

                    if (!isset($sortCurrentData[$keyHash])) {

                        $sortCurrentData[$keyHash] = $i;
                    } else {

                        $sortCurrentData[$keyHash . sha1(json_encode($pk))] = $i;
                    }

                    if ($model instanceof Arrayable) {

                        $models[$i] = $model->toArray($fields, $expand);

                    } elseif (is_array($model)) {

                        $models[$i] = ArrayHelper::toArray($model);
                    }

                    foreach($callbackExpands as $key => $callbackExpand){

                        $models[$i][$key] = $callbackExpand($model);
                    }
                }

                foreach ($sortNeed as $k => $hash) {

                    if (isset($sortCurrentData[$hash]) && isset($models[$sortCurrentData[$hash]])) {

                        $returnModels[$k] = $models[$sortCurrentData[$hash]];
                    }
                }
            }
        } else {

            $headers = $this->request->getHeaders();

            $x_linkable = $headers->get('X-Linkable');

            foreach ($models as $i => $model) {

                if ($model instanceof Arrayable) {

                    $returnModels[$i] = $model->toArray($fields, []);

                    if($x_linkable === 'disabled'){

                        unset($returnModels[$i]['_links']);
                    }

                } elseif (is_array($model)) {

                    $returnModels[$i] = ArrayHelper::toArray($model);
                }
            }
        }

        return $returnModels;
    }
}