<?php
/**
 * File used to layout plugin settings.
 *
 *
 */

namespace RahulAryan\Vsr;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$tabs = array(
	'settings'       => __( 'Settings', 'vote-smiley-reaction' ),
	'reaction_types' => __( 'Reaction Types', 'vote-smiley-reaction' ),
);

$active_tab = get_var( 'tab', 'settings' );

if ( ! isset( $tabs[ $active_tab ] ) ) {
    esc_attr_e( 'Invalid tab.', 'vote-smiley-reaction' );
    return;
}
?>
<div class="wrap">
	<h2>Vote Smiley Reaction</h2>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $slug => $title ) : ?>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=vote-smiley-reaction&tab=' . $slug ) ); ?>" class="nav-tab <?php echo $active_tab == $slug ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $title ); ?></a>
		<?php endforeach; ?>
	</h2>

	<?php include rahularyan_vsr()->get_path( '/views/admin/' . $active_tab . '.php' ); ?>
</div>
