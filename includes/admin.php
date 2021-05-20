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
	}

	/**
	 * Enqueue scripts and styles in wp-admin.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		wp_enqueue_style( 'rahularyan-vsr-admin-style', rahularyan_vsr()->get_url( 'assets/styles/admin.css' ), [], \RahulAryan\Vsr::VERSION );
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
		include rahularyan_vsr()->get_path( '/views/admin/settings.php' );
	}
}
