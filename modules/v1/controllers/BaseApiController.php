<?php

namespace app\modules\api\v1\controllers;

use app\modules\api\v1\models\ExchangeProvider;
use Exception;
use Throwable;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\rest\ActiveController;

/**
 * Class BaseApiController
 *
 * @package app\modules\v1\controllers
 */
class BaseApiController extends ActiveController
{
    private const ACTION_RATES      = 'rates';
    private const ACTION_CONVERT    = 'convert';

    /**
     * @var array
     */
    public $currencies = [];

    /**
     * @return array
     */
    public function behaviors(): array
    {
        /**
         * Чтобы быть максимально честным и объективным, я не до конца понимаю, как этот код работает "под капотом",
         * однако, на моем проекте я увидел именно такую реализацию авторизации по Bearer Token, и она работает.
         * Это соответствует моему заявлению о собственном уровне: я могу поддерживать существующее приложение,
         * и повторить используемые решения там, где это необходимо, но, возможно, самостоятельно не смогу создать
         * архитектуру с нуля.
         */
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => 'json',
                ],
            ],
            'authenticator' => [
                'class' => CompositeAuth::class,
                'authMethods' => [
                    HttpBearerAuth::class,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actionIndex(): array
    {
        $code = 200;

        try {
            $exchangeProvider = new ExchangeProvider();
            $params = Yii::$app->request->getBodyParams();

            $exchangeProvider->setPartnerId(Yii::$app->user->getIdentity()->getAuthKey())
                ->load($params);

            if (!$exchangeProvider->validate()) {
                $code = 403;
                return $this->onError($code, $exchangeProvider->getErrors());
            }

            switch ($params['method']) {
                case self::ACTION_RATES:
                    $result = $exchangeProvider->getRates();
                    break;
                case self::ACTION_CONVERT:
                    $result = $exchangeProvider->convert();
                    break;
                default:
                    throw new Exception('Unknown method');
            }

            return $this->onSuccess($code, $result);
        } catch (Throwable $throwable) {
            $code = 500;
            Yii::error($throwable->getTraceAsString());
            return $this->onError($code, $throwable->getMessage());
        }
    }

    /**
     * @param $code
     * @param $data
     *
     * @return array
     */
    public function onSuccess($code, $data): array
    {
        return $this->respond('success', $code, $data);
    }

    /**
     * @param $code
     * @param $message
     *
     * @return array
     */
    public function onError($code, $message): array
    {
        Yii::error(var_export($message, 1));
        return $this->respond('error', $code, $message);
    }

    /**
     * @param $status
     * @param $code
     * @param $data
     *
     * @return array
     */
    private function respond($status, $code, $data): array
    {
        return [
            'status' => $status,
            'code' => (int)$code,
            'data' => $data
        ];
    }
}
