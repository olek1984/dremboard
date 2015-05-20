<?php

/**
 * BuddyPress - Users Settings
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
		<?php if ( bp_core_can_edit_settings() ) : ?>

			<?php bp_get_options_nav(); ?>

		<?php endif; ?>
	</ul>
</div>

<?php

switch ( bp_current_action() ) :
	case 'notifications'  :
		bp_get_template_part( 'members/single/settings/notifications'  );
		break;
	case 'capabilities'   :
		bp_get_template_part( 'members/single/settings/capabilities'   );
		break;
	case 'delete-account' :
		bp_get_template_part( 'members/single/settings/delete-account' );
		break;
	case 'general'        :
		bp_get_template_part( 'members/single/settings/general'        );
		break;
	default:
		bp_get_template_part( 'members/single/plugins'                 );
		break;
endswitch;