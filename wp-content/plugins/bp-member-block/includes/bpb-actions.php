<?php
if ( !defined( 'BPB_VERSION' ) ) exit;

/**
 * Action Handler
 * @since 1.0
 * @version 1.0
 */
function bpb_handle_actions() {
	if ( !is_user_logged_in() ) return;

	if ( !isset( $_REQUEST['action'] ) || !isset( $_REQUEST['user_id'] ) || !isset( $_REQUEST['block_token'] ) || !isset( $_REQUEST['member_id'] ) || !isset( $_REQUEST['block_type'] ) ) return;
	
	switch ( $_REQUEST['action'] ) {
		case 'unblock_member' :
			if ( wp_verify_nonce( $_REQUEST['block_token'], 'unblock-' . $_REQUEST['user_id'] ) ) {
				$current = bpb_get_blocked_users( (int) $_REQUEST['user_id'] );
				if ( isset( $current[ $_REQUEST['member_id'] ] ) ) {
					unset( $current[ $_REQUEST['member_id'] ] );
					update_user_meta( (int) $_REQUEST['user_id'], '_block', $current );
					
					do_action( 'bpb_action_unblock', $current );
					
					bp_core_add_message( __( 'User successfully unblocked', 'bpblock' ) );
					$result = array();
					$result['block_token'] = wp_create_nonce( 'block-' . $_REQUEST['user_id'] );
					echo json_encode($result);
					exit();
				}
			}
		break;
		case 'block_member' :
			if ( wp_verify_nonce( $_REQUEST['block_token'], 'block-' . $_REQUEST['user_id'] ) ) {
				$current = bpb_get_blocked_users( (int) $_REQUEST['user_id'] );
				if ( user_can( (int) $_REQUEST['member_id'], BPB_ADMIN_CAP ) ) {
					bp_core_add_message( __( 'You can not block administrators / moderators', 'bpblock' ), 'error' );
				}
				else {
					$current[$_REQUEST['member_id']] = $_REQUEST['block_type'];
					update_user_meta( (int) $_REQUEST['user_id'], '_block', $current );

					do_action( 'bpb_action_block', $current );
				
					bp_core_add_message( __( 'User successfully blocked', 'bpblock' ) );

					$result = array();
					$result['unblock_token'] = wp_create_nonce( 'unblock-' . $_REQUEST['user_id'] );
					echo json_encode($result);
					exit();
				}
			}
		break;
		default :
			do_action( 'bpb_action' );
		break;
	}
	//wp_safe_redirect( remove_query_arg( array( 'action', 'list', 'num', 'token' ) ) );
	exit();
}



/**
 * Add Block Button in Members List
 * @since 1.0
 * @version 1.0
 */
function bpb_insert_block_button_loop() {
	if ( !is_user_logged_in() ) return;
	
	$user_id = get_current_user_id();
	$member_id = bp_get_member_user_id();
	if ( $user_id == $member_id || user_can( $member_id, BPB_ADMIN_CAP ) ) return;
//	echo '<div class="generic-button block-this-user"><a href="' . bpb_block_link( $user_id, $member_id ) . '" class="activity-button">' . __( 'Block', 'bpblock' ) . '</a></div>';
	
	if (bpb_check_blocked_user($user_id, $member_id)){
		$token = wp_create_nonce( 'unblock-' . $user_id );
		echo '<div id="block-'.$member_id.'" class="generic-button block-this-user blocked" onclick="member_unblock_action('.$user_id.", ".$member_id.", '".$token."'".')"><a href="#" class="activity-button">' . __( 'Unblock', 'bpblock' ) . '</a></div>';
	}else{
		$token = wp_create_nonce( 'block-' . $user_id );
		echo '<div id="block-'.$member_id.'" class="generic-button block-this-user unblocked" onclick="member_block_action('.$user_id.", ".$member_id.", '".$token."'".')"><a href="#" class="activity-button">' . __( 'Block', 'bpblock' ) . '</a></div>';
	}
}

/**
 * Add Block Button in Loop
 * @since 1.0
 * @version 1.0
 */
function bpb_insert_block_button_profile() {
	if ( !is_user_logged_in() ) return;
	$user_id = get_current_user_id();
	$member_id = bp_displayed_user_id();
	if ( $user_id == $member_id || user_can( $member_id, BPB_ADMIN_CAP ) ) return;
	echo '<div class="generic-button block-this-user"><a href="' . bpb_block_link( $user_id, $member_id ) . '" class="activity-button">' . __( 'Block', 'bpblock' ) . '</a></div>';
}

?>