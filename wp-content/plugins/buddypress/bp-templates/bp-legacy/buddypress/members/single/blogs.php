<?php

/**
 * BuddyPress - Users Blogs
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */
$is_mobile = wp_is_mobile();
?>

<div class="item-list-tabs <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="subnav"  role="navigation">
    <?php if( $is_mobile ): ?>
	<ul class="has_sub">
		<li class="has_sub"><a onclick="javascript:;">
			<?php bp_get_selected_options_nav_name(); ?>
		</a></li>
	</ul>
	<?php endif; ?>
	<ul class="sub">

		<?php bp_get_options_nav(); ?>

		<li id="blogs-order-select" class="last filter">

			<label for="blogs-all"><?php _e( 'Order By:', 'buddypress' ); ?></label>
			<select id="blogs-all">
				<option value="active"><?php _e( 'Last Active', 'buddypress' ); ?></option>
				<option value="newest"><?php _e( 'Newest', 'buddypress' ); ?></option>
				<option value="alphabetical"><?php _e( 'Alphabetical', 'buddypress' ); ?></option>

				<?php do_action( 'bp_member_blog_order_options' ); ?>

			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php
switch ( bp_current_action() ) :

	// Home/My Blogs
	case 'my-sites' :
		do_action( 'bp_before_member_blogs_content' ); ?>

		<div class="blogs myblogs" role="main">

			<?php bp_get_template_part( 'blogs/blogs-loop' ) ?>

		</div><!-- .blogs.myblogs -->

		<?php do_action( 'bp_after_member_blogs_content' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;