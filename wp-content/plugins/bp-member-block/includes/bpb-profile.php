<?php
if ( !defined( 'BPB_VERSION' ) ) exit;

/**
 * Setup BP Navigation
 * @since 1.0
 * @version 1.0
 */
function bpb_setup_navigation() {
	global $bp;
	
	if ( !is_user_logged_in() || ( !current_user_can( BPB_ADMIN_CAP ) && get_current_user_id() != bp_displayed_user_id() ) ) return;

	bp_core_new_subnav_item( array(
		'name'                    => __( 'Blocked Members', 'bpblock' ),
		'slug'                    => 'blocked',
		'parent_url'              => $bp->displayed_user->domain . 'settings/',
		'parent_slug'             => 'settings',
		'screen_function'         => 'bpb_my_blocked_members',
		'show_for_displayed_user' => false
	) );
}

/**
 * Load Blocking Navigation Items
 * @since 1.0
 * @version 1.0
 */
function bpb_my_blocked_members() {
	add_action( 'bp_template_title',   'bpb_my_blocked_title' );
	add_action( 'bp_template_content', 'bpb_my_blocked_members_screen' );
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

/**
 * Menu Title
 * @since 1.0
 * @version 1.0
 */
function bpb_my_blocked_title() {
	if ( current_user_can( BPB_ADMIN_CAP ) && get_current_user_id() != bp_displayed_user_id() )
		echo "<div class='block_list_title'>".__( 'Members this user blocks', 'bpblock' )."</div>";
	else
		echo "<div class='block_list_title'>".__( 'Members you currently block', 'bpblock' )."</div>";
}

/**
 * My Blocked Members Screen
 * @since 1.0
 * @version 1.0
 */
function bpb_my_blocked_members_screen() {
	$user_id = bp_displayed_user_id();
	$token = wp_create_nonce( 'unblock-' . $user_id );
	$list = bpb_get_blocked_users( $user_id );

	if ( empty( $list ) )
		$list[] = 0; ?>

<table class="members-blocked list">
	<thead>
		<th class="member" style="width:35%;"><?php _e( 'User', 'bpblock' ); ?></th>
		<th class="type" style="width:15%;"><?php _e( 'Friend', 'bpblock' ); ?></th>
		<th class="type" style="width:15%;"><?php _e( 'Following', 'bpblock' ); ?></th>
		<th class="type" style="width:15%;"><?php _e( 'Message', 'bpblock' ); ?></th>
		<th class="actions" style="width:20%;"><?php _e( 'Actions', 'bpblock' ); ?></th>
	</thead>
	<tbody>
<?php

	if ( $list[0] == 0 ) { ?>
	<tr>
		<td colspan="5"><?php _e( 'No users found', 'bpblock' ); ?></td>
	</tr>
<?php
	}

	foreach ( (array) $list as $member_id => $block_type ) {
			$member = get_user_by( 'id', $member_id );
			// If user has been removed, remove it from our list as well
			if ( $member === false ) {
				bpb_remove_user_from_list( $user_id, $member_id );
				continue;
			}else{
			$block_token = wp_create_nonce( 'unblock-' . $user_id );
			$params = $user_id.", ".$member_id.", '".$token."'";
			?>
		<tr>
			<td class="member"><?php echo $member->display_name; ?></td>
			<td class="type_friend <?php echo (check_blocked_type($block_type, BPB_FRIEND))? "blocked":"" ?>"><?php echo (check_blocked_type($block_type, BPB_FRIEND))? "blocked":"" ?></td>
			<td class="type_follow <?php echo (check_blocked_type($block_type, BPB_FOLLOW))? "blocked":"" ?>"><?php echo (check_blocked_type($block_type, BPB_FOLLOW))? "blocked":"" ?></td>
			<td class="type_message <?php echo (check_blocked_type($block_type, BPB_MESSAGE))? "blocked":"" ?>"><?php echo (check_blocked_type($block_type, BPB_MESSAGE))? "blocked":"" ?></td>
			<td class="actions">
			<div id="block-<?php echo $member_id;?>" class="generic-button block-this-user blocked" onclick="member_unblock_action(<?php echo $params;?>)"><a href="" class="activity-button" onclick="javascript:return false;"><?php _e( 'Unblock', 'bpblock' ); ?></a></div>
			</td>
		</tr>
<?php
		}
	}
?>

	</tbody>
</table>
<?php
}

function bpb_setup_tool_bar() {
	// Bail if this is an ajax request
	if ( !bp_use_wp_admin_bar() || defined( 'DOING_AJAX' ) )
		return;

	// Only add menu for logged in user
	if ( is_user_logged_in() ) {
		global $bp, $wp_admin_bar;

		// Add secondary parent item for all BuddyPress components
		$wp_admin_bar->add_menu( array(
			'parent' => 'my-account-settings',
			'id'     => 'my-block-list',
			'title'  => __( 'Blocked Members', 'bpblock' ),
			'href'   => bp_loggedin_user_domain() . 'settings/blocked/'
		) );
	}
}
?>