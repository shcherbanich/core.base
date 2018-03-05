<?php

namespace shcherbanich\core\components\rest;

use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class ProxyRestServiceController extends \yii\web\Controller
{

    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'shcherbanich\core\components\data\rest\Serializer';

    public $controllerName = '';

    public $serviceName = '';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

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

        $headers->add('Access-Control-Allow-Headers', 'Authorization');
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

    public function actions()
    {
        $actions = parent::actions();

        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction'
        ];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function runAction($id, $params = []){

        $serviceRequest = new \shcherbanich\core\helpers\microService\Request;

        $serviceRequest->setCommand(($id ? "{$this->controllerName}/{$id}" : "{$this->controllerName}" ).'?'.Yii::$app->request->getQueryString());

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

        $response = Yii::$app->{$this->serviceName}->sendRequest($serviceRequest, ['method' => Yii::$app->request->getMethod()]);

        $responseData = $response->getResponseData();

        $result = $response->getData();

        Yii::$app->response->setStatusCode($responseData['status_code']);

        Yii::$app->response->format = $responseData['format'];

        foreach($responseData['headers'] as $name => $headers){

            foreach($headers as $header){

                if(!in_array($name, ['connection', 'http-code', 'server', 'set-cookie', 'access-control-allow-origin'])) {

                    Yii::$app->response->headers->add($name, $header);
                }
            }
        }

        return $this->afterAction((new Action($id, $this)), $result);
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);

        return $this->serializeData($result);
    }

    /**
     * Serializes the specified data.
     * The default implementation will create a serializer based on the configuration given by [[serializer]].
     * It then uses the serializer to serialize the given data.
     * @param mixed $data the data to be serialized
     * @return mixed the serialized data.
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}