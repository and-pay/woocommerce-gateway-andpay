<?php

class Andpay_Validator {

    public static function validate_payment($transactions, $order) {
        if(empty($transactions) || empty($order)) {
            return false;
        }

        $transaction = $transactions[0];

        // Validate psp reference
        $psp_reference = $order->get_meta('andpay_psp_reference');
        if($psp_reference != $transaction['payment_id']) {
            return false;
        }

        // Validate transaction
        $amount = Andpay_Currencies_Helper::toMicroValue($order->get_total(), $order->get_currency());
        if($transaction['amount'] != $amount) {
            return false;
        }

        // Validate currency
        $currency = $order->get_currency();
        if($transaction['currency'] != $currency) {
            return false;
        }

        // Validate wallet receiver
        $wallet_address = self::get_wallet_address();
        if($transaction['receiver'] != $wallet_address) {
            return false;
        }

        return true;
    }

    public static function get_wallet_address() {
        $options = get_option('woocommerce_woocommerce-gateway-andpay_settings');

        if(isset($options['wallet']) && !empty($options['wallet'])) {
            return $options['wallet'];
        }

        return false;
    }
}


?>
