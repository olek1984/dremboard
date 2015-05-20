<?php

/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */
$is_mobile = wp_is_mobile();
?>

<?php do_action( 'bp_before_activity_entry' ); ?>
<?php
//$qur = "SELECT * FROM ".$wpdb->prefix."bp_activity WHERE id=".bp_get_activity_id()." AND type IN ('activity_wall', 'activity_own', 'activity_friend', 'activity_group' )";

//$par = $wpdb->get_results($qur, OBJECT);
?>
<li class="<?php bp_activity_css_class(); ?> asdh" id="activity-<?php bp_activity_id(); ?>">
	<div id="hexd-<?php bp_activity_id(); ?>" class="hexd" style="display:none; color:#fff;background:#1E8022;padding:10px;text-align: center;"></div>
	<div class="activity-header-image"></div>
	<div class="activity-header">
		<?php bp_activity_action(); ?>
		<div class="activity-avatar">
			
			<a href="<?php bp_activity_user_link(); ?>">
			<?php bp_activity_avatar(); ?>
			</a>
		</div>
	</div>
	<div class="activity-content <?php echo($is_mobile)?'mobile':''; ?>">
        <?php if ( $is_mobile ) :
            $activity_link = home_url( bp_get_activity_root_slug() .'/'. bp_get_activity_id() );
            ?>
        <input class="mobile_link" type="hidden" value="<?php echo $activity_link; ?>">
        <?php endif;?>
		<?php if ( bp_activity_has_content() ) : ?>
		<div class="activity-inner">
			<?php bp_activity_content_body(); ?>
		</div>

		<?php endif; ?>

		<?php do_action( 'bp_activity_entry_content' ); ?>

		<div class="activity-meta">

			<?php if ( bp_get_activity_type() == 'activity_comment' ) : ?>

				<a href="<?php bp_activity_thread_permalink(); ?>" class="button view bp-secondary-action" title="<?php _e( 'View Conversation', 'buddypress' ); ?>"><?php _e( 'View Conversation', 'buddypress' ); ?></a>

			<?php endif; ?>
			
			<?php if ( is_user_logged_in() ) : ?>

				<?php if ( bp_activity_can_comment() ) : ?>

					<a href="<?php bp_activity_comment_link(); ?>" class="button acomment-reply bp-primary-action" id="acomment-comment-<?php bp_activity_id(); ?>"><?php printf( __( 'Comment <span>%s</span>', 'buddypress' ), bp_activity_get_comment_count() ); ?></a>

				<?php endif; ?>

				<?php if ( bp_activity_can_favorite() ) : ?>

					<?php if ( !bp_get_activity_is_favorite() ) : ?>

						<a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e( 'Mark as Favorite', 'buddypress' ); ?>"><?php _e( 'Favorite', 'buddypress' ); ?></a>

					<?php else : ?>

						<a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e( 'Remove Favorite', 'buddypress' ); ?>"><?php _e( 'Remove Favorite', 'buddypress' ); ?></a>

					<?php endif; ?>

				<?php endif; ?>
                <?php do_action( 'bp_activity_entry_meta' ); ?>
				<?php if ( bp_activity_user_can_delete() ) bp_activity_delete_link(); ?>

				

			<?php endif; ?>

		</div>

	</div>
	

	<?php do_action( 'bp_before_activity_entry_comments' ); ?>

	<?php if ( ( is_user_logged_in() && bp_activity_can_comment() ) || bp_is_single_activity() ) : ?>

		<div class="activity-comments">

			<?php bp_activity_comments(); ?>

			<?php if ( is_user_logged_in() ) : ?>

				<form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
					<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar( 'width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT ); ?></div>
					<div class="ac-reply-content">
						<div class="ac-textarea">
							<textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input helper-input helper-input-box has_mark" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
							<div class="ac-file">
								<input type="file" accept="image/*" name="file" title="Choose a file to upload" aria-label="Choose a file to upload" id="ac-input-file-<?php bp_activity_id(); ?>" class="ac-input-file"/>
							</div>
						</div>
						<div class="ac-file-view" style="display: none;" id="ac-file-view-<?php bp_activity_id(); ?>">
							<img class="ac-file-item" src=""/>
							<div class="close"></div>
						</div>
						<input type="submit" name="ac_form_submit" value="<?php _e( 'Post', 'buddypress' ); ?>" /> &nbsp; <a href="#" class="ac-reply-cancel"><?php _e( 'Cancel', 'buddypress' ); ?></a>
						<input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
					</div>

					<?php do_action( 'bp_activity_entry_comments' ); ?>

					<?php wp_nonce_field( 'new_activity_comment', '_wpnonce_new_activity_comment' ); ?>

				</form>

			<?php endif; ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'bp_after_activity_entry_comments' ); ?>

</li>

<?php do_action( 'bp_after_activity_entry' ); ?>
