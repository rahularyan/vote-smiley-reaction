<?php
/**
 * File used to layout plugin settings.
 *
 *
 */

$post_types = get_post_types( array( 'show_ui' => true ), '' );
?>
<div class="wrap">
	<h2>Vote Smiley Reaction</h2>

	<form class="vsr-settings">
		<div class="vsr-settings-left">
			<table class="form-table">
				<tr>
					<th>
						<label><?php esc_attr_e( 'Post types', 'vote-smiley-reaction' ); ?></label>
					</th>
					<td>
						<?php foreach ( $post_types as $post_type ): ?>
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
						<label><?php esc_attr_e( 'Reaction Types', 'vote-smiley-reaction' ); ?></label>
					</th>
					<td>
						<div class="vsr-icons-list">
							<?php foreach ( rahularyan_vsr()->get_reaction_types() as $reaction_type => $icon ): ?>
								<div>
									<?php $default_icon = rahularyan_vsr()->get_url( 'assets/images/icons/' . $reaction_type . '.svg' ); ?>
									<img src="<?php echo esc_url( empty( $icon ) ? $default_icon : $icon ); ?>" />
								</div>
							<?php endforeach; ?>
						</div>
						<p class="description"><?php esc_attr_e( 'Types of reaction.', 'vote-smiley-reaction' ); ?></p>
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
	</form>
</div>
