<?php
/**
 * File used to layout plugin settings.
 *
 *
 */

namespace RahulAryan\Vsr;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$post_types = get_post_types( array( 'public' => true ), '' );
$taxonomies = get_taxonomies(
	array(
		'public'  => true,
		'show_ui' => true,
	),
	'objects'
);
?>
<form class="vsr-settings" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<div class="vsr-settings-left">
		<table class="form-table">
			<tr>
				<th>
					<label><?php esc_attr_e( 'Post types', 'vote-smiley-reaction' ); ?></label>
				</th>
				<td>
					<?php foreach ( $post_types as $post_type ) : ?>
						<div>
							<label>
								<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, rahularyan_vsr()->opt( 'post_types', array() ) ), true ); ?> />
								<?php echo esc_attr( $post_type->label ); ?>
							</label>
						</div>
					<?php endforeach; ?>
					<p class="description"><?php esc_attr_e( 'Select post types for which you would like to enable reactions.', 'vote-smiley-reaction' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label><?php esc_attr_e( 'Taxonomies', 'vote-smiley-reaction' ); ?></label>
				</th>
				<td>
					<?php foreach ( $taxonomies as $tax ): ?>
						<div>
							<label>
								<input type="checkbox" name="taxonomies[]" value="<?php echo esc_attr( $tax->name ); ?>" <?php checked( in_array( $tax->name, rahularyan_vsr()->opt( 'taxonomies', array() ) ), true ); ?> />
								<?php echo esc_attr( $tax->labels->name ); ?>
							</label>
						</div>
					<?php endforeach; ?>
					<p class="description"><?php esc_attr_e( 'Select taxonomies for which you would like to enable reactions.', 'vote-smiley-reaction' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label><?php esc_attr_e( 'Reaction settings', 'vote-smiley-reaction' ); ?></label>
				</th>
				<td>
					<label><input name="one_reaction" type="checkbox" value="1" <?php checked( rahularyan_vsr()->opt( 'one_reaction', false ) ); ?> /> <?php esc_attr_e( 'Allow user to add only one type of reaction in an object.', 'vote-smiley-reaction' ); ?></label>
				</td>
			</tr>
		</table>

		<input type="submit" value="<?php esc_attr_e( 'Save options', 'vote-smiley-reaction' ); ?>" class="button button-primary" />
	</div>

	<div class="vsr-about-me">
		<div><b>About me</b></div>
		<br>
		<div>
			<img src="https://secure.gravatar.com/avatar/0c8cfd3bc56d97fe6bebc035fe9b8c80" />
		</div>
		<br />
		<div>
			Hello admin, my name is Rahul Aryan and I am full stack developer and want to thank you for using this plugin. I will be happy to know your experience with my plugins.
			<br />
			<br/>
			<a href="mailto:rah12@live.com" class="button">Custom Development</a>
			<br />
			<a href="https://github.com/rahularyan/vote-smiley-reaction/issues" target="_blank" class="button vsr-support-button button-primary">Plugin Support</a>
		</div>
	</div>

	<input type="hidden" name="action" value="rahularyan_vsr_save_settings" />
	<input type="hidden" name="__nonce" value="<?php echo esc_attr( wp_create_nonce( 'rahularyan_vsr_save_settings' ) ); ?>" />
</form>
