<?php

namespace shcherbanich\core\components\rest;

use Yii;
use yii\base\InvalidParamException;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class ProxyRestServiceController extends \yii\base\Controller
{

    public $controllerName = '';

    public $serviceName = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if(!$this->controllerName){

            throw new InvalidParamException("controllerName must be set");
        }

        if(!$this->serviceName){

            throw new InvalidParamException("serviceName must be set");
        }

        Yii::$app->user->enableSession = false;

        $headers = Yii::$app->response->headers;

        $headers->add('Access-Control-Allow-Origin', '*');

        $headers->add('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function runAction($id, $params = []){

        $serviceRequest = new \shcherbanich\core\helpers\microService\Request;

        $sendParams = [];

        foreach ($params as $key => $param){

            $sendParams[] = "{$key}={$param}";
        }

        $serviceRequest->setCommand(($id ? "{$this->controllerName}/{$id}" : "{$this->controllerName}" ).'?'.implode('&', $sendParams));

        Yii::$app->{$this->serviceName}->addRequestHandler('auth', function($request){

            foreach(\Yii::$app->request->headers as $name => $headers){

                if(in_array($name, ['authorization', 'content-Type'])) {

                    foreach($headers as $header){

                        $request->addHeaders([$name => $header]);
                    }
                }
            }
        });

        $sendParams = Yii::$app->getRequest()->getBodyParams();

        $sendParams = is_array($sendParams) ? $sendParams : [];

        $serviceRequest->setParams($sendParams);

        return Yii::$app->{$this->serviceName}->sendRequest($serviceRequest, ['method' => Yii::$app->request->getMethod()])->getContent();
    }
}