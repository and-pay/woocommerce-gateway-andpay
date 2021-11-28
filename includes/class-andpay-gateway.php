<?php

/**
 * Andpay Gateway
 *
 * @class Andpay_Gateway
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @package WooCommerce/Classes/Payment
 */
class Andpay_Gateway extends WC_Payment_Gateway {

    public $app;

    public function __construct() {
        $this->id = "woocommerce-gateway-andpay";
        $this->has_fields = false;

        $this->configure_method();

        $this->init_settings();
        $this->init_form_fields();

        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
    }

    public function configure_method() {
        $currency = get_woocommerce_currency();
        $supported_currency = Andpay_Currencies_Helper::get_supported_currency($currency);

        if($supported_currency) {
            $this->method_title = $supported_currency['name'] . " Payments by Andpay";
            $this->method_description = "Pay with " . $supported_currency['name'];

            $this->icon = plugins_url('/public/images/' . $supported_currency['icon'], __DIR__ );
            return;
        }

        $this->method_title = "Algo Payments by Andpay";
        $this->method_description = "Pay with Algo";
        $this->icon = plugins_url('/public/images/icon.png', __DIR__ );
        return;
    }

    public function authenticate_api($api_key) {
        $authentication = Andpay_Client::get_instance()->authenticate($api_key);

        if(!$authentication) {
            WC_Admin_Settings::add_error(__("It seems that the Andpay Payment Method is configured with an incorrect API Key. Please check this.", "andpay"));
        }
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'andpay' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Algo Payments', 'andpay' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Title', 'andpay' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'andpay' ),
                'default' => __( 'Pay with Algo', 'andpay' ),
                'desc_tip'      => true,
            ),
            'description' => array(
                'title' => __( 'Customer Message', 'andpay' ),
                'type' => 'textarea',
                'default' => ''
            ),
            'api_key' => array(
                'title' => __("API Key", "andpay"),
                'type' => 'password',
                'default' => ''
            ),
            'wallet' => array(
                'title' => __("Wallet Address", "andpay"),
                'type' => 'text',
                'default' => ''
            )
        );
    }

    public function admin_options() {
        ?>
         <h2><?php echo esc_attr($this->method_title); ?></h2>
         <hr />
         <h3>Webhook support</h3>
         <p>You must add the following webhook endpoint <code><?php echo esc_url(get_site_url(null, "?wc-api=wc-andpay-webhook")); ?></code> to your <a href="https://andpay.io/user/api-tokens/" target="_blank">Andpay account settings</a>.
             This will enable you to receive notifications on the payment statusses.</p>
         <table class="form-table">
         <?php $this->generate_settings_html(); ?>
         </table>
         <?php
    }

    public function process_payment( $order_id ) {
        $client = Andpay_Client::get_instance()->client();

        $order = new WC_Order($order_id);

        $amount = Andpay_Currencies_Helper::toMicroValue($order->get_total(), $order->get_currency());

        $paymentData = [
            "amount" => $amount,
            "address" => $this->settings['wallet'],
            "currency" => $order->get_currency(),
            "name" => "#" . $order_id
        ];
        $response = $client->payments()->create($paymentData);

        if(isset($response['payment_link'])) {
            $psp_reference = $response['id'];

            $order->update_meta_data('andpay_psp_reference', $psp_reference);
            $order->save();

            $payment_link = $response['payment_link'];

            return array(
                'result' => 'success',
                'redirect' => $payment_link . "?redirect_url=" . urlencode(get_site_url(null, "?wc-api=wc-andpay") . "&psp_ref=". $psp_reference ."&key=" . $order->get_order_key())
            );
        }

        return 0;
    }

    function process_psp_result_webhook() {
        global $woocommerce;

        $json = file_get_contents('php://input');

        Andpay_Logger::debug('Received webhook: ' . $json);

        if(empty($json)) {
            Andpay_Logger::debug('Json is empty');
            return false;
        }

        $json_data = json_decode($json, true);

        if(empty($json_data) || (isset($json_data['event']) && $json_data['event'] != 'payment.received')) {
            Andpay_Logger::debug('Callback is not payment.received.');
            return false;
        }

        $payment = $json_data['data'];

        $psp_ref = sanitize_text_field($payment['payment_id']);

        $orders = wc_get_orders(array(
        	'meta_query'	=>	array(
        		array(
                    'key'   => 'andpay_psp_reference',
        			'value'	=>	$psp_ref,
                    'compare' => '='
        		)
        	)
        ));

        if(empty($orders) || !isset($orders[0])) {
            Andpay_Logger::debug('Orders is empty');
            return false;
        }

        $order = $orders[0];

        if($order->is_paid()) {
            Andpay_Logger::debug('Order already paid');
            return false;
        }

        return $this->finalise_payment($order, true);
    }

    function process_psp_result() {
        global $woocommerce;

        if(!$_GET['psp_ref'] || !$_GET['key']) {
            $error_message = "Could not find psp reference";
            throw new Exception ( $error_message );
        }

        $key = sanitize_key(wp_unslash($_GET['key']));
        $psp_ref = sanitize_text_field(wp_unslash($_GET['psp_ref']));

        $order_id = wc_get_order_id_by_order_key($key);

        if(empty($order_id)) {
            // Order not found
            $error_message = "Order not found";
            throw new Exception ( $error_message );
        }

        $order = wc_get_order($order_id);

        if($order->is_paid()) {
            return wp_redirect( $this->get_return_url( $order ) );
        }

        return $this->finalise_payment($order);
    }

    function finalise_payment($order, $webhook = false) {
        global $woocommerce;

        $actual_psp_reference = $order->get_meta('andpay_psp_reference');

        $transactions = Andpay_Client::get_instance()->client()->payments()->transactions($actual_psp_reference);

        $valid_payment = Andpay_Validator::validate_payment($transactions, $order);

        if($valid_payment == true) {
            // Complete payment
            $order->payment_complete();

            // Remove cart
            $woocommerce->cart->empty_cart();

            // Add note
            $order->add_order_note( __('Andpay payment successfully completed: ' . $actual_psp_reference, 'andpay') );

            Andpay_Logger::debug('Payment ' . $actual_psp_reference . ' for order '. $order->get_id() .' successfully completed');

            if($webhook == true) {
                return true;
            }
            return wp_redirect( $this->get_return_url( $order ) );
        } else {
            // No valid payment, let's retry.
            $order->update_status( 'failed', __( 'Payment error:', 'woocommerce' ));

            wc_add_notice( "Something went wrong with your payment. Please try again." , 'error' );

            Andpay_Logger::debug('Something went wrong with payment ' . $actual_psp_reference . ' for order '. $order->get_id());

            if($webhook == true) {
                return false;
            }
            return wp_redirect(WC()->cart->get_checkout_url());
        }
    }

    function process_admin_options() {
        $post_data = $this->get_post_data();
        if(!empty($post_data) && isset($post_data['woocommerce_'. $this->id .'_enabled']) && $post_data['woocommerce_'. $this->id .'_enabled'] == true) {
            $errors = false;

            if(empty($post_data['woocommerce_'. $this->id .'_api_key'])) {
                WC_Admin_Settings::add_error(__("API Key is necessary before activating Algo Payments by Andpay.", "andpay"));
                $errors = true;
            }

            if(empty($post_data['woocommerce_'. $this->id .'_wallet'])) {
                WC_Admin_Settings::add_error(__("Wallet Address is necessary before activating Algo Payments by Andpay", "andpay"));
                $errors = true;
            }

            if($errors == true) {
                unset($post_data['woocommerce_'. $this->id .'_enabled']);
                $this->set_post_data($post_data);
            }
        }

        $this->authenticate_api($post_data['woocommerce_'. $this->id .'_api_key']);

        return parent::process_admin_options();
    }

    function add_gateway_class( $methods ) {
        $currency = get_woocommerce_currency();
        $supported_currency = Andpay_Currencies_Helper::get_supported_currency($currency);

        if($supported_currency || is_admin()) {
            $methods[] = 'Andpay_Gateway';
        }

        if(!$supported_currency) {
            WC_Admin_Settings::add_error(__("It seems that the currency configured for your store isn't supported by Algo Payments. Head over to Woocommerce > Settings and adjust the currency to one of the supported currencies.", "andpay"));
        }

        return $methods;
    }

}


?>
