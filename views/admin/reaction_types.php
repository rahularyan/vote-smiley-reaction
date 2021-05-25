<?php
/**
 * Reaction types admin view.
 *
 * @author Rahul Aryan<rah12@live.com>
 */

namespace RahulAryan\Vsr;

defined( 'ABSPATH' ) || exit;

// $reaction_types = array(
// 	'like' => array(
// 		'slug' => 'like',
// 		'name'    => __( 'Like', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// 	'dislike' => array(
// 		'slug'    => 'dislike',
// 		'name'    => __( 'Dislike', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// 	'laugh' => array(
// 		'slug'    => 'laugh',
// 		'name'    => __( 'Laugh', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// 	'confused' => array(
// 		'slug'    => 'confused',
// 		'name'    => __( 'Confused', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// 	'heart' => array(
// 		'slug'    => 'heart',
// 		'name'    => __( 'Heart', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// 	'hooray' => array(
// 		'slug'    => 'hooray',
// 		'name'    => __( 'Hooray', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// 	'rocket' => array(
// 		'slug'    => 'rocket',
// 		'name'    => __( 'Rocket', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// 	'eyes' => array(
// 		'slug'    => 'eyes',
// 		'name'    => __( 'Eyes', 'vote-smiley-reaction' ),
// 		'icon'    => '',
// 		'show_on' => array(),
// 	),
// );

$counter         = 1;
$add_button_args = implode(
	',',
	array(
		wp_create_nonce( 'add_reaction_row' )
	)
);

$reaction_types = rahularyan_vsr()->get_reaction_types();

?>
<br />
<div class="rahularyan-vsr vsr-reaction-types">

	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<button class="button button-primary"><?php esc_attr_e( 'Save reaction types', 'vote-smiley-reaction' ); ?></button>
		<button class="button rahularyan-vsr-rest-to-defaults" data-vsr="<?php echo esc_attr( wp_create_nonce( 'vsr_rest_reactions' ) ) ; ?>"><?php esc_attr_e( 'Reset to default', 'vote-smiley-reaction' ); ?></button>

		<br />
		<br />
		<table class="vsr-reactions-table">
			<tbody>
				<tr class="vsr-reaction-type">
					<th>#</th>
					<th><?php esc_attr_e( 'Icon', 'vote-smiley-reaction' ); ?></th>
					<th><?php esc_attr_e( 'Slug', 'vote-smiley-reaction' ); ?></th>
					<th><?php esc_attr_e( 'Name', 'vote-smiley-reaction' ); ?></th>
					<th><?php esc_attr_e( 'Where to show', 'vote-smiley-reaction' ); ?></th>
				</tr>

				<?php if ( $reaction_types ) : ?>
					<?php foreach ( $reaction_types as $slug => $reaction ) : ?>
						<?php include rahularyan_vsr()->get_path( '/views/admin/reaction_type.php' ); ?>
						<?php $counter++; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="vsr-reaction-type vsr-reaction-type-no-row">
						<td colspan="5"><?php esc_attr_e( 'No reaction type registered, click below button to add one.', 'vote-smiley-reaction' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<br>
		<button class="button rahularyan-vsr-add-reaction-type" data-vsr="<?php echo esc_js( $add_button_args ); ?>"><?php esc_attr_e( 'Add reaction type', 'vote-smiley-reaction' ); ?></button>
		<br>
		<br>
		<br>
		<button class="button button-primary" type="submit"><?php esc_attr_e( 'Save reaction types', 'vote-smiley-reaction' ); ?></button>
		<button class="button rahularyan-vsr-rest-to-defaults" data-vsr="<?php echo esc_attr( wp_create_nonce( 'vsr_rest_reactions' ) ) ; ?>"><?php esc_attr_e( 'Reset to default', 'vote-smiley-reaction' ); ?></button>

		<?php wp_nonce_field( 'vsr_save_reaction_type' ); ?>
		<input type="hidden" name="action" value="rahularyan_vsr_save_reaction_types" />
	</form>

</div>
