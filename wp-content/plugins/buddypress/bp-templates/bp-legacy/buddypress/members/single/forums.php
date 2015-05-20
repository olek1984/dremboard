<?php

/**
 * BuddyPress - Users Forums
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */
$is_mobile = wp_is_mobile();
?>

<div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="subnav"  role="navigation">
    <?php if( $is_mobile ): ?>
	<ul class="has_sub">
		<li class="has_sub"><a onclick="javascript:;">
			<?php bp_get_selected_options_nav_name(); ?>
		</a></li>
	</ul>
	<?php endif; ?>
	<ul class="sub">
		<?php bp_get_options_nav(); ?>

		<li id="forums-order-select" class="last filter">

			<label for="forums-order-by"><?php _e( 'Order By:', 'buddypress' ); ?></label>
			<select id="forums-order-by">
				<option value="active"><?php _e( 'Last Active', 'buddypress' ); ?></option>
				<option value="popular"><?php _e( 'Most Posts', 'buddypress' ); ?></option>
				<option value="unreplied"><?php _e( 'Unreplied', 'buddypress' ); ?></option>

				<?php do_action( 'bp_forums_directory_order_options' ); ?>

			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php

if ( bp_is_current_action( 'favorites' ) ) :
	bp_get_template_part( 'members/single/forums/topics' );

else :
	do_action( 'bp_before_member_forums_content' ); ?>

	<div class="forums myforums">

		<?php bp_get_template_part( 'forums/forums-loop' ) ?>

	</div>

	<?php do_action( 'bp_after_member_forums_content' ); ?>

<?php endif; ?>
