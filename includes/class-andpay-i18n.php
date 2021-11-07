<?php

class Andpay_i18n {

  public function load_plugin_textdomain() {
    load_plugin_textdomain(
			'andpay',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
  }
  
}

?>
