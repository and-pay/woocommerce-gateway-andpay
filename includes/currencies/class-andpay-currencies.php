<?php

class Andpay_Currencies {

    public function add_currencies($currencies) {
        $supported_currencies = Andpay_Currencies_Helper::get_supported_currencies();

        foreach($supported_currencies as $key => $currency) {
            $currencies[$key] = $currency['name'];
        }

        return $currencies;
    }

    public function add_currency_symbol($currency_symbol, $currency) {
        $supported_currencies = Andpay_Currencies_Helper::get_supported_currencies();

        foreach($supported_currencies as $key => $s_currency) {
            if($key == $currency) {
                $currency_symbol = $s_currency['name'];
            }
        }

        return $currency_symbol;
    }

    public function change_currency_position($format, $currency_pos) {
        if($this->is_andpay_supported_currency()) {
            return '%2$s&nbsp;%1$s'; // Right space
        }

        return $format;
    }

    public function adjust_currency_decimals($decimals) {
        if($supported_currency = $this->is_andpay_supported_currency()) {
            return strlen((string)$supported_currency['multiple'])-1;
        }

        return $decimals;
    }

    public function formatted_woocommerce_price( $price, $product ) {
        if($supported_currency = $this->is_andpay_supported_currency()) {
            return (float)preg_replace('/[^\d.]/', '', $price);
        }
    }

    public function trim_zeros_for_currency() {
        if($this->is_andpay_supported_currency()) {
            return true;
        }
    }

    public function is_andpay_supported_currency() {
        $current_currency = strtoupper(get_woocommerce_currency_symbol());
        $supported_currencies = Andpay_Currencies_Helper::get_supported_currencies();

        if(isset($supported_currencies[$current_currency])) {
            return $supported_currencies[$current_currency];
        }

        return false;
    }

}

?>
