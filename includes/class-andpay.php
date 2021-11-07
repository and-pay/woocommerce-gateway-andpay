<?php

class Andpay {

    protected $loader;
    protected $gateway;
    protected $client;

    protected $plugin_name;

    protected $version;

    public function __construct() {
        $this->plugin_name = 'woocommerce-gateway-andpay';

        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
    }

    public function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-andpay-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-andpay-i18n.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-andpay-gateway.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-andpay-client.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/currencies/class-andpay-currencies.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/currencies/class-andpay-currencies-helper.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-andpay-validator.php';

        // Utility
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/utility/class-andpay-logger.php';

        $this->loader = new Andpay_Loader();

        $this->init_gateway();
        $this->init_currencies();
    }

    private function set_locale() {
        $plugin_i18n = new Andpay_i18n();
        $plugin_i18n->load_plugin_textdomain();
    }

    public function init_gateway() {
        $this->gateway = new Andpay_Gateway();

        $this->loader->add_action('woocommerce_payment_gateways', $this->gateway, 'add_gateway_class');
        $this->loader->add_action('woocommerce_update_options_payment_gateways_' . $this->plugin_name, $this->gateway, 'process_admin_options', 10, 1);
        $this->loader->add_action('woocommerce_api_wc-andpay', $this->gateway, 'process_psp_result', 10, 1);
        $this->loader->add_action('woocommerce_api_wc-andpay-webhook', $this->gateway, 'process_psp_result_webhook', 10, 1);
    }

    public function init_currencies() {
        $currencies = new Andpay_Currencies();

        $this->loader->add_action('woocommerce_currencies', $currencies, 'add_currencies', 10, 1);
        $this->loader->add_action('woocommerce_currency_symbol', $currencies, 'add_currency_symbol', 10, 2);

        $this->loader->add_filter('woocommerce_price_format', $currencies, 'change_currency_position', 10, 2);
        $this->loader->add_filter('wc_get_price_decimals', $currencies, 'adjust_currency_decimals', 10, 1);
        $this->loader->add_filter('woocommerce_price_trim_zeros', $currencies, 'trim_zeros_for_currency', 999, 1);
        $this->loader->add_filter('formatted_woocommerce_price', $currencies, 'formatted_woocommerce_price', 10, 2);

    }

    public function run() {
    	$this->loader->run();
    }

    public function get_plugin_name() {
    	return $this->plugin_name;
    }

    public function get_loader() {
    	return $this->loader;
    }

    public function get_version() {
    	return $this->version;
    }

    public function get_client() {
        return $this->client;
    }
}


?>
