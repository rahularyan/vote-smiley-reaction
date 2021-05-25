<?php
/**
 * File defines admin class.
 *
 * @package RahulAryan\Vsr\Admin
 * @since 0.1.0
 * @author Rahul Aryan <rah12@live.com>
 */

namespace RahulAryan\Vsr;

// Prevent direct access to file.
defined( 'ABSPATH' ) || exit( 'No direct script access allowed' );

/**
 * Admin class.
 *
 * @since 0.1.0
 */
class Admin {
	/**
	 * Store instance of of this class.
	 *
	 * @var Admin
	 */
	private static $instance;

	/**
	 * Return singleton instance of this class.
	 *
	 * @return Admin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	private function __construct() {
		$this->actions();
	}

	/**
	 * Get url to settings page of plugin.
	 *
	 * @param string|null $tab Tab slug.
	 * @return string
	 */
	public static function settings_urls( $tab = null) {
		$ret = admin_url( 'options-general.php?page=vote-smiley-reaction' );

		if ( ! empty( $tab ) ) {
			$ret = $ret . '&tab=' .$tab;
		}

		return $ret;
	}

	/**
	 * Register action hooks for admin.
	 *
	 * @return void
	 */
	private function actions() {
		DB::install();

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		add_action( 'wp_ajax_rahularyan_vsr', array( 'RahulAryan\\Vsr\\Ajax', 'ajax_handler' ) );
		add_action( 'wp_ajax_nopriv_rahularyan_vsr', array( 'RahulAryan\\Vsr\\Ajax', 'ajax_handler' ) );

		add_action( 'admin_post_rahularyan_vsr_save_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_rahularyan_vsr_save_reaction_types', array( __CLASS__, 'save_reaction_types' ) );
	}

	/**
	 * Enqueue scripts and styles in wp-admin.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		wp_enqueue_media();

		wp_enqueue_style( 'rahularyan-vsr-admin-style', rahularyan_vsr()->get_url( 'assets/styles/admin.css' ), [], \RahulAryan\Vsr::VERSION );
		wp_enqueue_script( 'rahularyan-vsr-admin-script', rahularyan_vsr()->get_url( 'assets/js/admin.js' ), [ 'jquery' ], \RahulAryan\Vsr::VERSION );
	}

	/**
	 * Admin menu hooks.
	 *
	 * @return void
	 */
	public static function admin_menu() {
		add_options_page(
			'Vote Smiley Reaction',
			'Vote Smiley Reaction',
			'manage_options',
			'vote-smiley-reaction',
			array( __CLASS__, 'settings_page' )
		);
	}

	/**
	 * Callback for loading admin view.
	 *
	 * @return void
	 */
	public static function settings_page() {
		include rahularyan_vsr()->get_path( '/views/admin/index.php' );
	}

	public static function save_settings() {
		$nonce = get_var( '__nonce', '' );

		if ( ! wp_verify_nonce( $nonce, 'rahularyan_vsr_save_settings' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You are not allowed to access this page.', 'vote-smiley-reaction' ) );
		}

		$post_types   = get_var( 'post_types', array() );
		$taxonomies   = get_var( 'taxonomies', array() );
		$one_reaction = (bool) get_var( 'one_reaction', false );

		if ( is_array( $post_types ) ) {
			rahularyan_vsr()->update_opt( 'post_types', $post_types );
		}

		if ( is_array( $taxonomies ) ) {
			rahularyan_vsr()->update_opt( 'taxonomies', $taxonomies );
		}

		rahularyan_vsr()->update_opt( 'one_reaction', $one_reaction );

		wp_redirect( self::settings_urls() );
		exit;
	}

	/**
	 * Sanitize reaction type.
	 *
	 * This is used while saving reaction types.
	 *
	 * @param array $reaction Reaction array.
	 * @return array Sanitized reaction.
	 */
	private static function sanitize_reaction_type( $reaction ) {
		if ( empty( $reaction['slug'] ) || empty( $reaction['name'] ) ) {
			return array();
		}

		$sanitized = array();

		$sanitized['slug'] = sanitize_key( $reaction['slug'] );
		$sanitized['name'] = sanitize_text_field( $reaction['name'] );
		$sanitized['icon'] = absint( $reaction['icon'] );

		$previous = rahularyan_vsr()->get_reaction_types();

		if ( empty( $sanitized['icon'] ) && isset( $previous[ $sanitized['slug'] ] ) ) {
			$sanitized ['icon']   = $previous[ $sanitized['slug'] ]['icon'];
			$sanitized['icon_id'] = 0;

		} else {
			$attachment = wp_get_attachment_url( $reaction['icon'] );

			if ( empty( $attachment ) ) {
				return array();
			}

			$sanitized['icon']    = $attachment;
			$sanitized['icon_id'] = (int) $reaction['icon'];
		}

		return $sanitized;
	}

	/**
	 * Callback called by admin_post_ hook while saving reaction types.
	 *
	 * @return void
	 */
	public static function save_reaction_types() {
		check_admin_referer( 'vsr_save_reaction_type' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You are not allowed to access this action', 'vote-smiley-reaction' ) );
		}

		$reactions = get_var( 'reactions', [] );

		if ( empty( $reactions ) ) {
			rahularyan_vsr()->update_opt( 'reaction_types', [] );
		}

		$defaults = rahularyan_vsr()->get_default_reactions();
		$required = wp_list_filter( $defaults, array( 'required' => true ) );

		$sanitized = array();

		foreach ( $reactions as $reaction ) {
			$reaction = self::sanitize_reaction_type( $reaction );

			if ( ! empty( $reaction ) ) {
				$sanitized[ $reaction['slug'] ] = $reaction;
			}
		}

		if ( ! empty( $required ) ) {
			foreach ( $required as $reaction ) {
				if ( ! isset( $sanitized[ $reaction['slug'] ] ) ) {
					$sanitized[ $reaction['slug'] ] = $reaction;
					$reaction ['icon']              = empty( $reactions[ $reaction['slug'] ]['icon'] ) ? $reaction['icon'] : $reactions[ $reaction['slug'] ]['icon'];
					$reactions[ $reaction['slug'] ] = $reaction;
				} else {
					$sanitized[ $reaction['slug'] ]['icon']     = ! empty( $sanitized[ $reaction['slug'] ]['icon_id'] ) ? $sanitized[ $reaction['slug'] ]['icon'] : $reaction['icon'];
					$sanitized[ $reaction['slug'] ]['required'] = true;
				}
			}
		}

		rahularyan_vsr()->update_opt( 'reaction_types', $sanitized );

		wp_redirect( self::settings_urls( 'reaction_types' ) );
		exit;
	}
}
