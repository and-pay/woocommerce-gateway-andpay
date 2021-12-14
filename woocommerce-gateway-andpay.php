<?php
/**
 *
 * @link              https://andpay.io/about/
 * @since             1.0.0
 * @package           Woocommerce Gateway Andpay
 *
 * @wordpress-plugin
 * Plugin Name:       Andpay - Algo Payments
 * Plugin URI:        https://andpay.io/product-integrations/
 * Description:       The gateway for supporting Algo payments within your store, using Andpay and Algorand Blockchain. Algo, USDt, USDc, EURe, YLDY, Planets, Smile Coin.
 * Version:           1.0.3
 * Author:            Andpay
 * Author URI:        https://andpay.io/about/
 * Text Domain:       andpay
 * Domain Path:       /languages
 * License: 		  GPL-3.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

function activate_andpay() {
  require_once plugin_dir_path(__FILE__) . 'includes/class-andpay-activator.php';
  Andpay_Activator::activate();
}

register_activation_hook(__FILE__, 'activate_andpay');

function deactive_andpay() {
  require_once plugin_dir_path(__FILE__) . 'includes/class-andpay-deactivator.php';
  Andpay_Deactivator::deactivate();
}

register_deactivation_hook(__FILE__, 'deactive_andpay');

require plugin_dir_path(__FILE__) . 'includes/class-andpay.php';

$autoloader = __DIR__ . '/vendor/autoload.php';
$andpaySdkAutoload = __DIR__ . '/vendor/andpay/andpay-api-php/vendor/autoload.php';
if (file_exists($autoloader)) {
	require $autoloader;
}

if (file_exists($andpaySdkAutoload)) {
	require $andpaySdkAutoload;
}

function run_andpay() {
	if ( class_exists( 'woocommerce' ) ) {
		$andpay = new Andpay();
		$andpay->run();
	} else {
		add_action( 'admin_notices', 'ap_wc_not_active' );
	}
}

add_action( 'plugins_loaded', 'run_andpay' );

function ap_wc_not_active() {
    ?>
    <div class="error notice">
        <p><?php _e( 'Andpay - Algo Payments: woocommerce is not activated/installed. Please activate or deactivate Andpay.', 'andpay' ); ?></p>
    </div>
    <?php
}
