<?php
/**
 * Files defines global functions.
 *
 * @package RahulAryan\Vsr
 * @since 0.1.0
 * @copyright Rahul Aryan <rah12@live.com>
 */

// Prevent direct access to file.
defined( 'ABSPATH' ) || exit;

/**
 * Get the singleton instance of plugin.
 *
 * @return RahulAryan\Vsr
 */
function rahularyan_vsr() {
	return RahulAryan\Vsr::get_instance();
}
