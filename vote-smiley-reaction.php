<?php
/**
 * Plugin Name:     Vote & Smiley Reaction
 * Plugin URI:      https://github.com/rahularyan/vote-smiley-reaction
 * Description:     Add voting and smiley reaction in WordPress.
 * Author:          Rahul Aryan
 * Author URI:      https://wp.cafe
 * Text Domain:     vote-smiley-reaction
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         RahulAryan\Vsr
 */

// Do not allow direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Include main plugin class.
 */
require_once dirname( __FILE__ ) . '/includes/main.php';

/**
 * Get the singleton instance of plugin.
 *
 * @return RahulAryan\Vsr
 */
function rahularyan_vsr() {
	return \RahulAryan\Vsr::get_instance();
}

// Load plugin.
add_action( 'plugins_loaded', 'rahularyan_vsr' );
