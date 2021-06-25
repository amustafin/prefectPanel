<?php

namespace app\modules\api\v1\api;

/**
 * Class ExchangeApi
 * @package app\modules\api\v1\api
 */
class ExchangeApi
{
    private const URL = 'https://blockchain.info/ticker ';
    private const PERCENTAGE = 0.02;

    /**
     * @return array
     */
    private static function getOriginRates(): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL             => self::URL,
        ));
        $rates  		    = json_decode(curl_exec($ch), true);
        $header  			= curl_getinfo($ch);
        $header['errno']  	= curl_errno($ch);
        $header['errmsg'] 	= curl_error($ch);
        curl_close($ch);

        return $rates;
    }

    /**
     * @param float $value
     *
     * @return float
     */
    private static function percentage(float $value): float
    {
        return $value * self::PERCENTAGE;
    }

    /**
     * @return array
     */
    public static function getRates(): array
    {
        $rates = self::getOriginRates();

        foreach ($rates as $key => &$singleRate) {
            $singleRate['15m'] = $singleRate['15m'] + self::percentage($singleRate['15m']);
            $singleRate['last'] = $singleRate['last'] + self::percentage($singleRate['last']);
            $singleRate['buy'] = $singleRate['buy'] + self::percentage($singleRate['buy']);
            $singleRate['sell'] = $singleRate['sell'] + self::percentage($singleRate['sell']);
        }

        return $rates;
    }

    /**
     * @param string    $from
     * @param string    $to
     * @param float     $value
     *
     * @return array
     */
    public static function convert(string $from, string $to, float $value): array
    {
        $rates = self::getRates();

        /**
         * не уверен, что для расчетов нужно именно значение last, но в любом случае, эта реализация может работать,
         * даже после подстановки другого поля
         */
        if ($from == 'BTC') {
            $rate = $rates[$to]['last'];
            $convertedValue = round($value * $rate, 2);
        } elseif ($to == 'BTC') {
            $rate = 1 / $rates[$from]['last'];
            $convertedValue = round($value * $rate, 10);
        } else {
            $rate = $rates[$to]['last'] / $rates[$from]['last'];
            $convertedValue = round($value * $rate, 2);
        }

        return [
            'currency_from' => $from,
            'currency_to' => $to,
            'value' => $value,
            'converted_value' => $convertedValue,
            'rate' => $rate
        ];
    }
}
