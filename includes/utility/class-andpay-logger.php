<?php

class Andpay_Logger {

    private static $instance;
    private static $logger;
    private static $context = array('source' => 'woocommerce-gateway-andpay');

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function get_logger() {
        if (null === self::$logger) {
            self::$logger = wc_get_logger();
        }

        return self::$logger;
    }

    public static function debug($message) {
        $logger = self::get_logger();

        return $logger->debug($message, self::$context);
    }

}

?>
