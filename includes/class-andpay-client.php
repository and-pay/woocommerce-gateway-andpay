<?php

class Andpay_Client {

    private static $instance;
    private static $client;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function client($api_key = null) {
        if(empty($api_key)) {
            $api_key = self::get_api_key();
        }

        if(!empty(self::$client)) {
            return self::$client;
        }

        self::$client = new \Andpay\Client($api_key);

        return self::$client;
    }

    public static function authenticate($api_key) {
        $custom_client = new \Andpay\Client($api_key);
        $currencies = $custom_client->currencies()->all();

        if(!empty($currencies) && isset($currencies[0]['name'])) {
            return true;
        }

        return false;
    }

    public static function get_api_key() {
        $options = get_option('woocommerce_woocommerce-gateway-andpay_settings');

        if(isset($options['api_key']) && !empty($options['api_key'])) {
            return $options['api_key'];
        }

        return false;
    }
}

?>
