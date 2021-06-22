<?php

namespace app\modules\api\v1\models;

use app\modules\api\v1\api\ExchangeApi;
use Exception;
use yii\base\Model;

/**
 * Form for the adding lead
 *
 * @property string     $method
 * @property string     $currency_from
 * @property string     $currency_to
 * @property string     $value
 * @property string     $converted_value
 * @property array      $rates
 * @property int        $partner_id
 *
 * @property mixed $integratorId
 */
class ExchangeProvider extends Model
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $currency_from;

    /**
     * @var string
     */
    public $currency_to;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $converted_value;

    /**
     * @var array
     */
    public $rates;

    /**
     * @var int
     */
    public $partner_id;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['method', 'partner_id'], 'required'],
            [['currency_from', 'currency_to'], 'string'],
            [['value'], 'float'],
            [['currency_from', 'currency_to', 'value'], 'customRequired'],
        ];
    }

    /**
     * @return bool
     */
    public function customRequired()
    {
        if (isset($this->value)) {
            if (!isset($this->currency_from) || !isset($this->currency_to)) {
                $this->addError(
                    'convert',
                    \Yii::t('app', 'While use param value, params currency_from and currency_to become required')
                );
            } elseif ($this->currency_from == $this->currency_to) {
                $this->addError(
                    'convert',
                    \Yii::t('app', 'Need different currencies for converting')
                );
            }

            if ($this->currency_from == 'BTC' && $this->value < 0.0000000001) {
                $this->addError(
                    'convert',
                    \Yii::t('app', 'Minimal converting value for BTC is 0.0000000001')
                );
            } elseif ($this->value < 0.01) {
                $this->addError(
                    'convert',
                    \Yii::t('app', 'Minimal converting value is 0.01')
                );
            }
        }

        return true;
    }

    /**
     * @param $apiToken
     *
     * @return $this
     */
    public function setPartnerId($apiToken)
    {
        /**
         *  Конкретная реализация зависит от настроек системы, в которую будем интегрироваться.
         *  Для теста принимаем, что данный метод реализован в указанном классе и возвращает экземпляр этого класса
         *  в случае наличия соответствующей записи в бд.
         *  Именно в этом классе необходимо настроить формат токена (64 символа из числа "A-Za-z0-9\-\_")
         */
        $partner = Partners::getByApiKey($apiToken);
        if ($partner) {
            $this->partner_id = $partner->id;
        }

        return $this;
    }

    /**
     * @return array
     */
    public static function getRates()
    {
        return ExchangeApi::getRates();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function convert()
    {
        /**
         * Конечно, ветка else и проверка isset избыточны, поскольку настроена валидация,
         * но всегда лучше перестраховаться
         */
        $rates = ExchangeApi::getRates();
        if (isset($this->currency_from)
            && isset($this->currency_to)
            && isset($this->value))
        {
            return [
                'currency_from' => $this->currency_from,
                'currency_to' => $this->currency_to,
                'value' => $this->value,
                'converted_value' => ExchangeApi::convert(
                    $this->currency_from,
                    $this->currency_to,
                    $this->value
                ),
                'rate' => $rates[$this->currency_to]['last']
            ];
        } else {
            throw new Exception('Missed necessary params!');
        }

    }
}
