<?php
/**
 * Plugin Name:     Vote Smiley Reaction
 * Plugin URI:      https://github.com/rahularyan/vote-smiley-reaction
 * Description:     Add voting and smiley reaction in WordPress.
 * Author:          Rahul Aryan
 * Author URI:      https://rahularyan.com
 * Text Domain:     vote-smiley-reaction
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         RahulAryan\Vsr
 */

namespace RahulAryan;

// Do not allow direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @package RahulAryan
 * @since 0.1.0
 */
final class Vsr {
	/**
	 * Version of the plugin.
	 *
	 * @var string
	 */
	const VERSION = '0.1.0';

	/**
	 * Version of the database.
	 *
	 * @var string
	 */
	const DB_VERSION = '1';

	/**
	 * Option key.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'rahularyan_vsr_opts';

	/**
	 * Table name of reactions table.
	 *
	 * @var string
	 */
	const REACTIONS_TABLE = 'rahularyan_reactions';

	/**
	 * Store singleton instance of this class.
	 *
	 * @var null|Vsr
	 */
	private static $instance;

	/**
	 * Plugin base dir.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	private $dir = '';

	/**
	 * Plugin base url.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	private $url = '';

	/**
	 * Initial check done.
	 *
	 * @var bool
	 */
	private $checked = false;

	/**
	 * Return the instance of this class.
	 *
	 * @return Vsr
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'vote-smiley-reaction' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'vote-smiley-reaction' ), '1.0' );
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		global $wpdb;

		// Set reaction table in $wpdb for easier access.
		$wpdb->rahularyan_reactions = $wpdb->prefix . self::REACTIONS_TABLE;

		$this->dir = dirname( plugin_basename( __FILE__ ) );
		$this->url = plugin_dir_url( __FILE__ );

		$this->include_files();
	}

	/**
	 * Require critical files.
	 *
	 * @return void
	 */
	private function include_files() {
		require_once $this->get_path( 'includes/global-functions.php' );
	}

	/**
	 * Add actions hooks.
	 *
	 * @return Vsr
	 */
	private function actions() {
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
		return $this;
	}

	/**
	 * Load plugin localization.
	 *
	 * @return Vsr
	 */
	public function load_text_domain() {
		$languages_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		load_plugin_textdomain( 'vote-smiley-reaction', false, $languages_dir );

		return $this;
	}

	/**
	 * Initial check of plugin requirements.
	 *
	 * @return Vsr
	 */
	public function initial_check() {
		if ( $this->checked ) {
			return $this;
		}

		// Perform initial check.

		return $this;
	}

	/**
	 * Get absolute path to a directory or file.
	 *
	 * @param string $path Path.
	 * @return string Absolute path.
	 */
	public function get_path( $path ) {
		return wp_normalize_path( $this->dir . $path );
	}

	/**
	 * Get absolute url to a directory or file.
	 *
	 * @param string $path Path.
	 * @return string Absolute path.
	 */
	public function get_url( $path ) {
		return $this->url . user_trailingslashit( $path );
	}

	/**
	 * Return all options of the plugin.
	 *
	 * @return array
	 * @since 0.0.1
	 */
	private function all_opts() {
		$options = get_option( self::OPTION_KEY );
		return wp_parse_args(
			$options,
			array(
				'DB_VERSION' => 0,
			)
		);
	}

	/**
	 * Update plugin option.
	 *
	 * @param string $key   Name of option.
	 * @param mixed  $value Value of options.
	 * @return void
	 * @since 0.0.1
	 */
	public function update_opt( $key, $value ) {
		$options         = $this->all_opts();
		$options[ $key ] = $value;

		update_option( self::OPTION_KEY, $options );
	}

	/**
	 * Get plugin options.
	 *
	 * @since 0.0.1
	 * @param string $key     Name of option.
	 * @param string $default Default value to return.
	 * @return mixed|null     Returns null if option does not exists.
	 */
	public function opt( $key, $default = null ) {
		$options = $this->all_opts();

		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}

		return $default;
	}
}
