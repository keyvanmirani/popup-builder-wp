<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/keyvanmirani
 * @since      1.0.0
 *
 * @package    Kivano_Block_Popup
 * @subpackage Kivano_Block_Popup/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Kivano_Block_Popup
 * @subpackage Kivano_Block_Popup/includes
 * @author     k1mirani <keyvan.mirani4419@gmail.com>
 */
class Kivano_Block_Popup_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'kivano-block-popup',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
