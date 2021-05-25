<?php
/**
 * Template used to render single reaction type row in settings page.
 *
 * @author Rahul Aryan <rah12@live.com>
 *
 * @global int $counter Increment counter.
 * @global array $reaction Reaction array.
 */

defined( 'ABSPATH' ) || exit;

$delete_button_args = wp_create_nonce( 'add_reaction_row' );
$icon               = rahularyan_vsr()->get_reaction_type_icon( $reaction['slug'] );
?>

<?php if ( $reaction['required'] ) : ?>
	<tr class="vsr-reaction-info">
		<td colspan="5"><?php esc_attr_e( 'This is a system default reaction type', 'vote-smiley-reaction' ); ?> &#8628;</td>
	</tr>
<?php endif; ?>

<tr class="vsr-reaction-type" data-vsr-row>
	<td class="vsr-reaction-type-counter">
		<div></div>
		<?php if ( ! $reaction['required'] ) : ?>
			<a href="#" class="rahularyan-vsr-delete-reaction-type" data-vsr="<?php echo esc_js( $delete_button_args ); ?>"><?php esc_attr_e( 'Delete', 'vote-smiley-reaction' ); ?></a>
		<?php endif; ?>
	</td>
	<td class="vsr-reaction-type-field vsr-reaction-type-icon<?php echo ! empty( $icon ) ? ' has-image' : ''; ?>">
		<?php if ( ! empty( $icon ) ) : ?>
			<img src="<?php echo esc_url( $icon ); ?>" />
		<?php endif; ?>

		<input type="hidden" name="reactions[<?php echo (int) $counter; ?>][icon]" value="<?php echo (int) $reaction['icon_id']; ?>" />
		<a href="#" class="rahularyan-vsr-upload-btn rahularyan-vsr-open-media"><?php esc_attr_e( 'Set icon', 'vote-smiley-reaction' ); ?></a>
		<a href="#" class="rahularyan-vsr-remove-icon-btn rahularyan-vsr-open-media"><?php esc_attr_e( 'Replace icon', 'vote-smiley-reaction' ); ?></a>
	</td>
	<td class="vsr-reaction-type-field">
		<input type="text" class="vsr-reaction-type-field-slug" value="<?php echo esc_attr( $reaction['slug'] ); ?>" <?php echo $reaction['required'] ? ' disabled="disabled" ' : ''; ?> />
		<input type="hidden" name="reactions[<?php echo (int) $counter; ?>][slug]" value="<?php echo esc_attr( $reaction['slug'] ); ?>" />
	</td>
	<td class="vsr-reaction-type-field">
		<input type="text" name="reactions[<?php echo (int) $counter; ?>][name]" value="<?php echo esc_attr( $reaction['name'] ); ?>" />
	</td>

	<td class="vsr-reaction-type-field">
		<!-- <select name="show_on" multiple="multiple">
			<optgroup label="<?php esc_attr_e( 'Post Types', 'vote-smiley-reaction' ); ?>">
				<option>Tyrannosaurus</option>
				<option>Velociraptor</option>
				<option>Deinonychus</option>
			</optgroup>
		</select> -->
	</td>
</tr>
