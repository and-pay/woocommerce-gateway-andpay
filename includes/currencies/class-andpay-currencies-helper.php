<?php

class Andpay_Currencies_Helper {

    public $CURRENCY_ALGO = "ALGO";

    public static function toMicroValue($amount, $currency = self::CURRENCY_ALGO) {
        $multiple = self::get_supported_currency($currency)['multiple'];

        if(empty($multiple)) {
            return $amount;
        }

        return $amount * $multiple;
    }

    public static function fromMicroValue($amount, $currency = self::CURRENCY_ALGO) {
        $multiple = self::get_supported_currency($currency)['multiple'];

        if(empty($multiple)) {
            return $amount;
        }

        return $amount / $multiple;
    }

    public static function get_supported_currency($currency = self::CURRENCY_ALGO) {
        $supported_currencies = self::get_supported_currencies();

        if(isset($supported_currencies[$currency])) {
            return $supported_currencies[$currency];
        }

        return false;
    }

    public static function get_supported_currencies() {
        return [
            "ALGO" => [
                "name" => "ALGO",
                "multiple" => 1000000,
                "icon" => "currency-algo.svg",
                "asset" => null,
                "asset_test" => null
            ],
            "USDC" => [
                "name" => "USDC",
                "multiple" => 1000000,
                "icon" => "currency-usdc.svg",
                "asset" => 31566704,
                "asset_test" => 10458941
            ],
            "USDT" => [
                "name" => "USDt",
                "multiple" => 1000000,
                "icon" => "currency-usdt.svg",
                "asset" => 312769,
                "asset_test" => null
            ],
            "EURE" => [
                "name" => "EURe",
                "multiple" => 100000000,
                "icon" => "currency-eure.png",
                "asset" => 83209012,
                "asset_test" => 12400859
            ],
            "YLDY" => [
                "name" => "YLDY",
                "multiple" => 1000000,
                "icon" => "currency-yldy.png",
                "asset" => 226701642,
                "asset_test" => null
            ],
            "PLANETS" => [
                "name" => "Planets",
                "multiple" => 1000000,
                "icon" => "currency-planet.png",
                "asset" => 27165954,
                "asset_test" => null
            ],
            "SMILE" => [
                "name" => "SMILE",
                "multiple" => 1000000,
                "icon" => "currency-smile.png",
                "asset" => 300208676,
                "asset_test" => null
            ]
        ];
    }

}

?>
