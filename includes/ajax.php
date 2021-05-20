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

		if ( 'add_reaction' === $vsr_action ) {
			self::__action_add_reaction();
		}

		exit;
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
}
