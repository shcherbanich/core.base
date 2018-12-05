<?php

namespace shcherbanich\core\components\data\rest;

use shcherbanich\core\components\base\GroupExpandInterface;
use shcherbanich\core\components\Base\Translatable;
use yii\base\Arrayable;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\db\ActiveRecordInterface;
use yii\helpers\ArrayHelper;

class Serializer extends \yii\rest\Serializer
{

    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array the array representation of the model
     */
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {

            return null;
        }

        list($fields, $expand) = $this->getRequestedFields();

        $extraFields = $model->extraFields();

        $callbackExpands = [];

        $groupExpandsClasses = [];

        foreach ($expand as $k => $extraField) {

            if (isset($extraFields[$extraField])) {

                if (is_object($extraFields[$extraField]) && $extraFields[$extraField] instanceof GroupExpandInterface) {

                    $groupExpandsClasses[$extraField] = new $extraFields[$extraField];

                } elseif (is_callable($extraFields[$extraField])) {

                    $callbackExpands[$extraField] = $extraFields[$extraField];
                }

                unset($expand[$k]);

            } elseif (!in_array($extraField, $extraFields)) {

                unset($expand[$k]);
            }
        }

        $serialized_model = $model->toArray($fields, $expand);

        if ($model instanceof Translatable) {

            $serialized_model = $model::translate($serialized_model, \Yii::$app->language);
        }

        foreach ($callbackExpands as $key => $callbackExpand) {

            $serialized_model[$key] = $callbackExpand($serialized_model);
        }

        foreach ($groupExpandsClasses as $expand_key => $groupExpandsClass) {

            $groupExpandsClass->setModels([$serialized_model]);

            $groupExpandsClass->setExpandKey($expand_key);

            $groupExpandsClass->process();

            $serialized_model = $groupExpandsClass->getModels()[0];
        }

        $headers = $this->request->getHeaders();

        $x_linkable = $headers->get('X-Linkable');

        if ('enabled' !== $x_linkable) {

            unset($serialized_model['_links']);
        }

        return $serialized_model;
    }

    /**
     * Serializes a set of models.
     * @param array $models
     * @return array the array representation of the models
     */
    protected function serializeModels(array $models)
    {
        list ($fields, $expand) = $this->getRequestedFields();

        $returnModels = [];

        $headers = $this->request->getHeaders();

        $x_linkable = $headers->get('X-Linkable');

        if ($expand) {

            $primaryKeys = [];

            if ($models) {

                $model = current($models);

                if($model instanceof ActiveRecordInterface) {

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

                    $groupExpandsClasses = [];

                    foreach ($expand as $k => $extraField) {

                        if (isset($extraFields[$extraField])) {

                            if (is_object($extraFields[$extraField]) && $extraFields[$extraField] instanceof GroupExpandInterface) {

                                $groupExpandsClasses[$extraField] = new $extraFields[$extraField];
                            } elseif (is_callable($extraFields[$extraField])) {

                                $callbackExpands[$extraField] = $extraFields[$extraField];
                            }

                            unset($expand[$k]);
                        } elseif (!in_array($extraField, $extraFields)) {

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

                        foreach ($pk as $k => $v) {

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

                        foreach ($callbackExpands as $key => $callbackExpand) {

                            $models[$i][$key] = $callbackExpand($model);
                        }
                    }

                    foreach ($groupExpandsClasses as $expand_key => $groupExpandsClass) {

                        $groupExpandsClass->setModels($models);

                        $groupExpandsClass->setExpandKey($expand_key);

                        $groupExpandsClass->process();

                        $models = $groupExpandsClass->getModels();
                    }

                    foreach ($sortNeed as $k => $hash) {

                        if (isset($sortCurrentData[$hash]) && isset($models[$sortCurrentData[$hash]])) {

                            $returnModels[$k] = $models[$sortCurrentData[$hash]];

                            if ($x_linkable !== 'enabled') {

                                unset($returnModels[$k]['_links']);
                            }
                        }
                    }
                }
                else{

                    $returnModels = $models;
                }
            }
        } else {

            foreach ($models as $i => $model) {

                if ($model instanceof Arrayable) {

                    $returnModels[$i] = $model->toArray($fields, []);

                    if ($model instanceof Translatable) {

                        $returnModels[$i] = $model::translate($returnModels[$i], \Yii::$app->language);
                    }

                } elseif (is_array($model)) {

                    $returnModels[$i] = ArrayHelper::toArray($model);
                }

                if($x_linkable !== 'enabled'){

                    unset($returnModels[$i]['_links']);
                }
            }
        }

        return $returnModels;
    }

    /**
     * @inheritdoc
     */
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } elseif (is_array($data) && isset($data[0]) && $data[0] instanceof Arrayable) {
            return $this->serializeModels($data);
        }

        return $data;
    }
}