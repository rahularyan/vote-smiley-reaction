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

use function RahulAryan\Vsr\can_user_react;

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
				'one_reaction'   => false,
				'reaction_types' => $this->get_default_reactions(),
				'taxonomies'     => array( 'category', 'post_tag' ),
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

	public function get_default_reactions() {
		$defaults = array(
			'vote_up' => array(
				'slug'     => 'vote_up',
				'name'     => __( 'Vote Up', 'vote-smiley-reaction' ),
				'icon'     => $this->get_url( '/assets/images/icons/vote_up.svg' ),
				'icon_id'  => 0,                                                      // This is important for setting default icon.
				'required' => true,
			),
			'vote_down' => array(
				'slug'     => 'vote_down',
				'name'     => __( 'Vote Down', 'vote-smiley-reaction' ),
				'icon'     => $this->get_url( '/assets/images/icons/vote_down.svg' ),
				'icon_id'  => 0,                                                        // This is important for setting default icon.
				'required' => true,
			),
			'100' => array(
				'slug'    => '100',
				'name'    => __( '100', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/100.svg' ),
				'icon_id' => 0,
			),
			'angry' => array(
				'slug'    => 'angry',
				'name'    => __( 'Angry', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/angry.svg' ),
				'icon_id' => 0,
			),
			'clap' => array(
				'slug'    => 'clap',
				'name'    => __( 'Clap', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/clap.svg' ),
				'icon_id' => 0,
			),
			'confused' => array(
				'slug'    => 'confused',
				'name'    => __( 'Confused', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/confused.svg' ),
				'icon_id' => 0,
			),
			'eyes' => array(
				'slug'    => 'eyes',
				'name'    => __( 'Eyes', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/eyes.svg' ),
				'icon_id' => 0,
			),
			'happy' => array(
				'slug'    => 'happy',
				'name'    => __( 'Happy', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/happy.svg' ),
				'icon_id' => 0,
			),
			'idea' => array(
				'slug'    => 'idea',
				'name'    => __( 'Idea', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/idea.svg' ),
				'icon_id' => 0,
			),
			'rocket' => array(
				'slug'    => 'rocket',
				'name'    => __( 'Rocket', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/rocket.svg' ),
				'icon_id' => 0,
			),
			'unhappy' => array(
				'slug'    => 'unhappy',
				'name'    => __( 'Unhappy', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/unhappy.svg' ),
				'icon_id' => 0,
			),
			'flag' => array(
				'slug'    => 'flag',
				'name'    => __( 'Flag', 'vote-smiley-reaction' ),
				'icon'    => $this->get_url( '/assets/images/icons/flag.svg' ),
				'icon_id' => 0,
			),
		);

		return apply_filters( 'rahularyan_vsr_default_reactions', $defaults );
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

		foreach ( $reactions as $k => $reaction ) {
			if ( 0 === $reaction['icon_id'] && ! empty( $reaction['icon'] ) && 0 === strpos( $reaction['icon'], 'http' ) ) {
				$reactions[ $k ]['icon'] = $reaction['icon'];
			}
		}

		return $reactions;
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
				$type = 'author';
			} elseif ( is_a( $object, 'WP_Post_Type' ) ) {
				$type = 'cpt_' . $object->name;
			}
		}

		$id   = apply_filters( 'rahularyan_vsr_display_reaction_id', $id );
		$type = apply_filters( 'rahularyan_vsr_display_reaction_type', $type );

		if ( empty( $id ) && empty( $type ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				$html = __( 'Unable to guess id and type of current page.', 'vote-smiley-reaction' );
			}
			return $html;
		}

		$html = '<div class="rahularyan-vsr" id="rahularyan-vsr">';

		foreach ( $this->get_reaction_types() as $slug => $reaction ) {
			$icon = $this->get_reaction_type_icon( $slug );

			if ( empty( $icon ) ) {
				continue;
			}

			$show_on = rahularyan_vsr()->get_reaction_type_show_on( $slug );

			if ( is_array( $show_on ) && ! in_array( $type, $show_on ) ) {
				continue;
			}

			$args = array( wp_create_nonce( 'vs-reaction' ), $id, $type, $slug );
			$args = implode( ',', $args );

			$count        = (int) DB::count_by_object_id( $id, $type, $slug );
			$user_reacted = DB::has_user_reacted( $id, $type, $slug, $user_id );
			$css_classes  = 'rahularyan-vsr-type-' . $type . ' ' . ( $user_reacted ? ' rahularyan-vsr-active' : '' );

			$html .= '<a href="#" class="rahularyan-vsr-reaction ' . esc_attr( $css_classes ) . '" data-vsr="' . esc_attr( $args ) . '" data-vsr-title="' . esc_attr( $reaction['name'] ) . '">
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

	/**
	 * Get a single reaction type.
	 *
	 * @param string $slug Slug of reaction type.
	 * @return false|array
	 */
	public function get_reaction_type( $slug ) {
		$types = $this->get_reaction_types();

		if ( isset( $types[ $slug ] ) ) {
			return $types[ $slug ];
		}

		return false;
	}

	/**
	 * Get show of setting of a reaction type.
	 *
	 * @param string $slug Reaction type.
	 * @return array|boolean
	 */
	public function get_reaction_type_show_on( $slug ) {
		$type = $this->get_reaction_type( $slug );

		if ( ! $type ) {
			return false;
		}

		if ( ! empty( $type['show_on'] ) ) {
			return $type['show_on'];
		}

		return true;
	}

	public function get_reaction_type_icon( $type ) {
		$types = $this->get_reaction_types();

		if ( empty( $types[ $type ] ) ) {
			return false;
		}

		$reaction = $types[ $type ];

		if ( ! empty( $reaction['icon'] ) && 0 === strpos( $reaction['icon'], 'http' ) ) {
			return $reaction['icon'];
		} elseif ( empty( $reaction['icon'] ) && ! empty( $reaction['icon_id'] ) ) {
			return wp_get_attachment_url( $reaction['icon_id'] );
		}

		return false;
	}

}
