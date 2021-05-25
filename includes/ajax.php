<?php
/**
 * File defines ajax callbacks.
 *
 * @package RahulAryan\Vsr\DB
 * @since 0.1.0
 * @author Rahul Aryan <rah12@live.com>
 */

namespace RahulAryan\Vsr;

// Prevent direct access to file.
defined( 'ABSPATH' ) || exit( 'No direct script access allowed' );

/**
 * Class defines ajax callbacks.
 *
 * @package RahulAryan\Vsr
 * @since 0.1.0
 */
final class Ajax {
	/**
	 * Ajax handler.
	 *
	 * @since 0.1.0
	 */
	public static function ajax_handler() {
		$vsr_action = get_var( 'vsr_action', '' );

		if ( empty( $vsr_action ) && ! method_exists( __CLASS__, '__action_add_reaction' ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Invalid action.', 'vote-smiley-reaction' ) ), 403 );
		}

		$vsr_action = '__action_' . $vsr_action;

		if ( method_exists( __CLASS__, $vsr_action ) ) {
			self::$vsr_action();
		}

		wp_send_json_error( array( 'msg' => __( 'Invalid action.', 'vote-smiley-reaction' ) ) );
	}

	/**
	 * Ajax callbacks for adding a vote.
	 *
	 * @return void
	 */
	private static function __action_add_reaction() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'You must be logged in to react.', 'vote-smiley-reaction' ) ), 403 );
		}

		$args          = explode( ',', get_var( 'args', '' ) );
		$nonce         = ! empty( $args[0] ) ? $args[0] : '';
		$object_id     = ! empty( $args[1] ) ? $args[1] : '';
		$object_type   = ! empty( $args[2] ) ? $args[2] : '';
		$reaction_type = ! empty( $args[3] ) ? $args[3] : '';
		$user_action   = 'added_reaction';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'vs-reaction' ) || ! can_user_react( $object_id, $object_type ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'You are not allowed to react.', 'vote-smiley-reaction' ) ), 403 );
		}

		$reacted = DB::get( $object_id, $object_type, $reaction_type, get_current_user_id() );

		// if user has already reacted then undo the reaction.
		if ( $reacted ) {
			$user_action = 'undo_reaction';
			$ret = DB::delete_by_id( $reacted->reaction_id );
		} else {
			$ret = DB::insert_reaction( $reaction_type, $object_id, $object_type );
		}

		if ( false === $ret ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Failed to react.', 'vote-smiley-reaction' ) ), 400 );
		}

		// Send success response.
		wp_send_json_success(
			array(
				'msg'         => 'undo_reaction' === $user_action ? esc_attr__( 'Successfully removed reaction.', 'vote-smiley-reaction' ) : esc_attr__( 'Successfully reacted.', 'vote-smiley-reaction' ),
				'user_action' => $user_action,
				'count'       => DB::count_by_object_id( $object_id, $object_type, $reaction_type ),
				'success'     => true,
			)
		);
	}

	private static function __action_get_reaction_type_row() {
		$args    = get_var( 'args', '' );
		$counter = get_var( 'counter', 1 );

		if ( empty( $args ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Bad request', 'vote-smiley-reaction' ) ) );
		}

		$args = explode( ',', $args );

		if ( ! wp_verify_nonce( $args[0], 'add_reaction_row' ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Bad request', 'vote-smiley-reaction' ) ) );
		}

		$reaction = array(
			'slug'    => '',
			'name'    => '',
			'icon'    => '',
			'show_on' => [],
		);

		$counter++;

		include rahularyan_vsr()->get_path( '/views/admin/reaction_type.php' );
		exit;
	}

	private static function __action_delete_reaction_type_row() {
		$args = get_var( 'args', '' );
		$slug = sanitize_key( get_var( 'slug', '' ) );

		if ( empty( $args ) || ! current_user_can( 'manage_options' ) || empty( $slug ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Bad request', 'vote-smiley-reaction' ) ) );
		}

		$reaction = rahularyan_vsr()->get_reaction_type( $slug );

		if ( empty( $reaction ) ) {
			// Send success even if reaction does not exits.
			wp_send_json_success( array( 'msg' => esc_attr__( 'Success', 'vote-smiley-reaction' ) ) );
		}

		$args = explode( ',', $args );

		if ( ! wp_verify_nonce( $args[0], 'delete_reaction_row' ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Bad request', 'vote-smiley-reaction' ) ) );
		}
	}

	private static function __action_reset_reaction_types() {
		$args = get_var( 'args', '' );

		if ( empty( $args ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Bad request', 'vote-smiley-reaction' ) ) );
		}

		$args = explode( ',', $args );

		if ( ! wp_verify_nonce( $args[0], 'vsr_rest_reactions' ) ) {
			wp_send_json_error( array( 'msg' => esc_attr__( 'Bad request', 'vote-smiley-reaction' ) ) );
		}

		rahularyan_vsr()->update_opt( 'reaction_types', rahularyan_vsr()->get_default_reactions() );

		wp_send_json_success( array( 'msg' => __( 'Success', 'vote-smiley-reaction' ) ) );
	}
}
