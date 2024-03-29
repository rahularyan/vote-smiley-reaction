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

$show_on_options = RahulAryan\Vsr\Admin::get_where_to_show_options();
$active_show_on  = rahularyan_vsr()->get_reaction_type_show_on( $reaction['slug'] );
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

	<td class="vsr-reaction-type-field<?php echo is_array( $active_show_on ) ? ' active' : ''; ?>">
		<div class="vsr-reaction-type-show_on-msg">
			<?php esc_attr_e( 'By default it will show everywhere. You can ', 'vote-smiley-reaction' ); ?>
			<a href="#"><?php esc_attr_e( 'select', 'vote-smiley-reaction' ); ?></a>
			<br>
			<br>
		</div>
		<?php if ( $show_on_options ) : ?>
			<select name="reactions[<?php echo (int) $counter; ?>][show_on][]" multiple="multiple" class="vsr-reaction-type-show_on"<?php echo ! is_array( $active_show_on ) ? ' disabled' : ''; ?>>
				<?php foreach ( $show_on_options as $label => $optgroup ) : ?>
					<?php if ( is_array( $optgroup ) ) : ?>
						<optgroup label="<?php echo esc_attr( $label ); ?>">
							<?php foreach ( $optgroup as $key => $opt ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( true, in_array( $key, $active_show_on ) ); ?>><?php echo esc_html( $opt ); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php else: ?>
						<option value="<?php echo esc_attr( $optgroup ); ?>" <?php selected( true, in_array( $label, $active_show_on ) ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
	</td>
</tr>
