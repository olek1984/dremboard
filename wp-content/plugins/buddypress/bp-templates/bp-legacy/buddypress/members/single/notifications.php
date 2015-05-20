<?php

/**
 * BuddyPress - Users Notifications
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

		<li id="members-order-select" class="last filter">
			<?php bp_notifications_sort_order_form(); ?>
		</li>
	</ul>
</div>

<?php
switch ( bp_current_action() ) :

	// Unread
	case 'unread' :
		bp_get_template_part( 'members/single/notifications/unread' );
		break;

	// Read
	case 'read' :
		bp_get_template_part( 'members/single/notifications/read' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
