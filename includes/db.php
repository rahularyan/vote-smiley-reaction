<?php
/**
 * File defines DB class.
 *
 * @package RahulAryan\Vsr\DB
 * @since 0.1.0
 * @author Rahul Aryan <rah12@live.com>
 */

namespace RahulAryan\Vsr;

// Prevent direct access to file.
defined( 'ABSPATH' ) || exit( 'No direct script access allowed' );

/**
 * Class handles database related operations.
 *
 * @since 0.1.0
 */
final class DB {

	/**
	 * Check if database version matches with the version stored in table.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public static function table_require_update() {
		return rahularyan_vsr()->opt( 'DB_VERSION' ) !== \RahulAryan\Vsr::DB_VERSION;
	}

	/**
	 * Create reactions table if needed.
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public static function install() {
		if ( ! self::table_require_update() ) {
			return;
		}

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $wpdb->rahularyan_reactions (
			reaction_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			object_id bigint(20) NOT NULL,
			object_type varchar(64) NOT NULL,
			reaction_type varchar(64) NOT NULL,
			date_reacted datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (reaction_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		rahularyan_vsr()->update_opt( 'DB_VERSION', \RahulAryan\Vsr::DB_VERSION );
	}

	/**
	 * Insert reaction to table.
	 *
	 * @param string $reaction_type Type of reaction.
	 * @param int    $object_id     Id of object/post id.
	 * @param string $object_type   Type of object, like custom post type slug or taxonomy slug.
	 * @param int    $user_id       Id of user. Default is current user.
	 * @param string $date_created  Date created, default is current time.
	 * @return int|false Return id of reaction if successful else false.
	 * @since 0.1.0
	 */
	public static function insert_reaction( $reaction_type, $object_id, $object_type = 'post', $user_id = false, $date_created = false ) {
		global $wpdb;

		if ( false === $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( false === $date_created ) {
			$date_created = current_time( 'mysql' );
		}

		$data = array(
			'reaction_type' => (string) $reaction_type,
			'object_id'     => (int) $object_id,
			'object_type'   => (string) $object_type,
			'user_id'       => (int) $user_id,
			'date_created'  => (string) $date_created,
		);

		/**
		 * Filter to modify data before reaction is inserted to database.
		 *
		 * @param array $data Data to modify.
		 * @return array Modified data.
		 */
		$data = apply_filters( 'rahularyan_vsr_pre_insert_reaction', $data );

		/**
		 * Check if data is empty.
		 */
		if ( empty( $data ) ) {
			return false;
		}

		$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB
			$wpdb->rahularyan_reactions,
			$data,
			array(
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
			)
		);

		if ( false === $inserted ) {
			return false;
		}

		$id = $wpdb->insert_id;

		/**
		 * Action called after a reaction is inserted in table.
		 *
		 * @since 0.1.0
		 */
		do_action( 'rahularyan_vsr_after_insert_reaction', $id );

		return $id;
	}

	/**
	 * Delete a reaction by reaction id.
	 *
	 * @param int $id Id of reaction to delete.
	 * @return bool
	 * @since 0.1.0
	 */
	public static function delete_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->delete( // phpcs:ignore WordPress.DB
			$wpdb->rahularyan_reactions,
			array( 'reaction_id' => $id ),
			array( '%d' )
		);

		if ( false === $row ) {
			return false;
		}

		/**
		 * Action called after a reaction is deleted.
		 *
		 * @param int $id ID of reaction.
		 * @since 0.1.0
		 */
		do_action( 'rahularyan_vsr_deleted_reaction', $id );

		return true;
	}

	/**
	 * Bulk delete reactions by object id.
	 *
	 * @param int    $object_id   Id of object.
	 * @param string $object_type Type of object.
	 * @return false|int Return false on failure and count of rows deleted.
	 */
	public static function delete_by_object_id( $object_id, $object_type ) {
		global $wpdb;

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT reaction_id FROM $wpdb->rahularyan_reactions WHERE object_id = %d AND object_type = %s", $object_id, $object_type )
		);

		if ( empty( $ids ) ) {
			return false;
		}

		// Delete reaction one by one.
		foreach ( $ids as $id ) {
			self::delete_by_id( $id );
		}

		return count( $ids );
	}

	/**
	 * Bulk delete reactions by user id.
	 *
	 * @param int $user_id Id of user.
	 * @return false|int Return false on failure and count of rows deleted.
	 */
	public static function delete_by_user_id( $user_id ) {
		global $wpdb;

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT reaction_id FROM {$wpdb->rahularyan_reactions} WHERE user_id = %d", $user_id )
		);

		if ( empty( $ids ) ) {
			return false;
		}

		// Delete reaction one by one.
		foreach ( $ids as $id ) {
			self::delete_by_id( $id );
		}

		return count( $ids );
	}

	/**
	 * Get a reaction by reaction id.
	 *
	 * @param int $id Id of reaction.
	 * @return array|null
	 */
	public static function get_by_id( $id ) {
		global $wpdb;

		return $wpdb->get_row( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT * FROM {$wpdb->rahularyan_reactions} WHERE reaction_id = %d", $id )
		);
	}

	/**
	 * Get reactions by object id and type.
	 *
	 * @param int    $object_id   Id of object.
	 * @param string $object_type Type of object.
	 * @return array|null
	 */
	public static function get_by_object_id( $object_id, $object_type ) {
		global $wpdb;

		return $wpdb->get_results( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT * FROM {$wpdb->rahularyan_reactions} WHERE object_id = %d AND object_type = %s", $object_id, $object_type )
		);
	}

	/**
	 * Get total count of reactions by reaction type.
	 *
	 * @param string    $reaction_type Type of reaction.
	 * @param int|false $user_id User ID, default is current user.
	 * @return int
	 * @since 0.1.0
	 */
	public static function count_by_type( $reaction_type, $user_id = false ) {
		global $wpdb;

		$user_id    = false === $user_id ? get_current_user_id() : $user_id;
		$user_query = '';

		// Check if user id is empty.
		if ( ! empty( $user_id ) ) {
			$user_query = $wpdb->prepare( 'AND user_id = %d', $user_id );
		}

		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT COUNT(1) FROM {$wpdb->rahularyan_reactions} WHERE reaction_type = %s $user_query", $reaction_type ) // phpcs:ignore WordPress.DB
		);
	}

	/**
	 * Get total count of reactions by object id and type.
	 *
	 * @param int          $object_id   Object id.
	 * @param string|false $object_type Object type.
	 * @return int
	 * @since 0.1.0
	 */
	public static function count_by_object_id( $object_id, $object_type = false ) {
		global $wpdb;

		$object_type_query = '';

		if ( false !== $object_type ) {
			$object_type_query = $wpdb->prepare( 'AND object_type = %s', $object_type );
		}

		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT COUNT(1) FROM {$wpdb->rahularyan_reactions} WHERE object_id = %d $object_type_query", $object_id ) // phpcs:ignore WordPress.DB
		);
	}
}
