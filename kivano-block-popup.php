<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/keyvanmirani
 * @since             1.0.0
 * @package           Kivano_Block_Popup
 *
 * @wordpress-plugin
 * Plugin Name:       Kivano Block Popup
 * Plugin URI:        https://github.com/keyvanmirani/kivano-block-popup-wp
 * Description:       Kivano Block Popup for wordpress with wp-blocks
 * Version:           1.0.2
 * Author:            k1mirani
 * Author URI:        https://github.com/keyvanmirani/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kivano-block-popup
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'KIVANO_BLOCK_POPUP_VERSION', '1.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-kivano-block-popup-activator.php
 */
function activate_kivano_block_popup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kivano-block-popup-activator.php';
	Kivano_Block_Popup_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-kivano-block-popup-deactivator.php
 */
function deactivate_kivano_block_popup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kivano-block-popup-deactivator.php';
	Kivano_Block_Popup_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_kivano_block_popup' );
register_deactivation_hook( __FILE__, 'deactivate_kivano_block_popup' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-kivano-block-popup.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_kivano_block_popup() {

	$plugin = new Kivano_Block_Popup();
	$plugin->run();

}
run_kivano_block_popup();
