<?php
/**
 * File defines hooks class.
 *
 * @package RahulAryan\Vsr\DB
 * @since 0.1.0
 * @author Rahul Aryan <rah12@live.com>
 */

namespace RahulAryan\Vsr;

// Prevent direct access to file.
defined( 'ABSPATH' ) || exit( 'No direct script access allowed' );

/**
 * Class defines frontend hooks.
 *
 * @package RahulAryan\Vsr
 * @since 0.1.0
 */
final class Hooks {
	/**
	 * Register all hooks callbacks.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'the_content', array( __CLASS__, 'the_content' ), 99999999 );
		add_action( 'wp_footer', array( __CLASS__, '__footer_script' ) );
	}

	/**
	 * Register styles and scripts.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		?>
			<script type="text/javascript">
				window.rahularyanVsr = {
					ajaxurl: "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
				}
			</script>
		<?php
		wp_enqueue_script( 'rahularyan_vsr-frontend-script', rahularyan_vsr()->get_url( 'assets/js/frontend.js' ), [ 'jquery' ], \RahulAryan\Vsr::VERSION, true );
		wp_enqueue_style( 'rahularyan_vsr-frontend-style', rahularyan_vsr()->get_url( 'assets/styles/frontend.css' ), [], \RahulAryan\Vsr::VERSION );
	}

	/**
	 * Append reactions button to the bottom of the content.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public static function the_content( $content ) {
		if ( is_singular() ) {
			$content = $content . rahularyan_vsr()->display_reactions();
		}

		return $content;
	}

	public function __footer_script() {
		rahularyan_vsr()->shall_load_scripts();
	}
}
