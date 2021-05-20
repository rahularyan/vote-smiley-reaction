<?php
/**
 * File defines main plugin class.
 *
 * @package RahulAryan\Vsr
 * @since 0.1.0
 * @author Rahul Aryan <rah12@live.com>
 */

namespace RahulAryan;

use RahulAryan\Vsr\DB;

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
	const DB_VERSION = 1;

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
	 * To check if widget is loaded.
	 *
	 * @var false
	 */
	private $widget_loaded = false;

	/**
	 * Return the instance of this class.
	 *
	 * @return Vsr
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();

			global $wpdb;

			// Set reaction table in $wpdb for easier access.
			$wpdb->rahularyan_reactions = $wpdb->base_prefix . self::REACTIONS_TABLE;

			self::$instance->dir = dirname( dirname( __FILE__ ) );
			self::$instance->url = plugin_dir_url( dirname( __FILE__ ) );

			self::$instance->include_files();
			self::$instance->actions();
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
	}

	/**
	 * Require critical files.
	 *
	 * @return void
	 */
	private function include_files() {
		require_once $this->get_path( '/includes/functions.php' );
		require_once $this->get_path( '/includes/hooks.php' );
		require_once $this->get_path( '/includes/ajax.php' );
		require_once $this->get_path( '/includes/db.php' );

		if ( is_admin() ) {
			require_once $this->get_path( '/includes/admin.php' );
			Vsr\Admin::get_instance();
		} else {
			Vsr\Hooks::register();
		}
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
		return user_trailingslashit( $this->url ) . $path;
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
				'DB_VERSION'     => 0,
				'post_types'     => array( 'post', 'page' ),
				'reaction_types' => array(
					'like'        => '',
					'dislike'     => '',
					'100'         => '',
					'eyes'        => '',
					'heart'       => '',
					'closed-eyes' => '',
					'heart-eyes'  => '',
					'star'        => '',
				),
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

	/**
	 * Get all reaction types name and icons.
	 *
	 * @return array
	 */
	public function get_reaction_types() {
		$reactions = $this->opt( 'reaction_types', array() );

		if ( empty( $reactions ) ) {
			return array();
		}

		$reaction_types = array();

		foreach ( $reactions as $type => $icon ) {
			$default_icon = rahularyan_vsr()->get_url( 'assets/images/icons/' . $type . '.svg' );
			$reaction_types[ $type ] = empty( $icon ) ? $default_icon : $icon;
		}

		return $reaction_types;
	}

	/**
	 * Display reaction widget.
	 *
	 * @return string
	 */
	public function display_reactions( $id = null, $type = null, $user_id = false ) {
		$this->widget_loaded = true;

		if ( null === $id ) {
			$id = get_queried_object_id();
		}

		if ( null === $type ) {
			$object = get_queried_object();

			if ( isset( $object->taxonomy ) ) {
				$type = $object->taxonomy;
			} elseif ( isset( $object->post_type ) ) {
				$type = $object->post_type;
			} elseif ( isset( $object->roles ) ) {
				$type = 'user';
			} elseif ( is_a( $object, 'WP_Post_Type' ) ) {
				$type = 'cpt_' . $object->name;
			}
		}

		$id   = apply_filters( 'rahularyan_vsr_display_reaction_id', $id );
		$type = apply_filters( 'rahularyan_vsr_display_reaction_type', $type );

		if ( empty( $id ) && empty( $type ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				$html = __( 'Unable to guess id and type of current page.' );
			}
			return $html;
		}

		$html = '<div class="rahularyan-vsr" id="rahularyan-vsr">';

		foreach ( $this->get_reaction_types() as $reaction_type => $icon ) {
			$args = array( wp_create_nonce( 'vs-reaction' ), $id, $type, $reaction_type );
			$args = implode( ',', $args );

			$count        = (int) DB::count_by_object_id( $id, $type, $reaction_type );
			$user_reacted = DB::has_user_reacted( $id, $type, $reaction_type, $user_id );
			$css_classes  = 'rahularyan-vsr-type-' . $type . ' ' . ( $user_reacted ? ' rahularyan-vsr-active' : '' );

			$html .= '<a href="#" class="rahularyan-vsr-reaction ' . esc_attr( $css_classes ) . '" data-vsr="' . esc_attr( $args ) . '">
				<img src="' . esc_url( $icon ) . '" /> <span data-vsr-count="' . $count . '">' . $count . '</span>
			</a>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Enqueue scripts when needed.
	 *
	 * @return void
	 */
	public function shall_load_scripts() {
		if ( $this->widget_loaded ) {
			wp_enqueue_script( 'rahularyan_vsr-frontend-script' );
		}
	}

}
