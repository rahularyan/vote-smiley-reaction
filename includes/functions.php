<?php
/**
 * Files defines global functions.
 *
 * @package RahulAryan\Vsr
 * @since 0.1.0
 * @copyright Rahul Aryan <rah12@live.com>
 */

namespace RahulAryan\Vsr;

// Prevent direct access to file.
defined( 'ABSPATH' ) || exit;

/**
 * Sanitize an array.
 *
 * @param array|string $value Value to be sanitized.
 * @return array|string
 * @since 0.0.1
 */
function sanitize_array( $value ) {
	if ( ! is_array( $value ) ) {
		return sanitize_text_field( wp_unslash( $value ) );
	}

	return array_map( 'RahulAryan\Vsr\sanitize_array', $value );
}

/**
 * Get sanitized value from request.
 *
 * @param string $name    Name of variable.
 * @param mixed  $default Default value to return.
 * @return mixed
 * @since 0.0.1
 */
function get_var( $name, $default = null ) {
	if ( isset( $_REQUEST[ $name ] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification
		return sanitize_array( $_REQUEST[ $name ] ); // phpcs:ignore
	}

	return $default;
}

/**
 * Check if user has permission to cast a reaction on a object.
 *
 * @param integer $object_id      Id of object.
 * @param string  $object_type    Type of object.
 * @param int     $user_id        Id of user, default is current user.
 * @return boolean
 * @since 0.1.0
 */
function can_user_react( $object_id, $object_type, $user_id = false ) {
	$user_id = false === $user_id ? get_current_user_id() : $user_id;

	if ( empty( $user_id ) || empty( $object_id ) || empty( $object_type ) ) {
		return false;
	}

	$check = apply_filters( 'rahularyan_vsr_can_user_react', null );

	if ( true === $check ) {
		return true;
	}

	// $user_reacted = DB::has_user_reacted( $object_id, $object_type, false, $user_id );

	// // If only one reaction is allowed in setting then return.
	// if ( $user_reacted && rahularyan_vsr()->opt( 'one_reaction', false ) ) {
	// 	return false;
	// }

	return true;
}
