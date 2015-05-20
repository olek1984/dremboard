<?php
if ( !defined( 'BPB_VERSION' ) ) exit;


/**
 * get, check the member is blocked about current user
 * Returns true or false
 * @since 1.0
 * @version 1.0
 */

function bpb_get_blocked_type($user_id = NULL, $member_id = NULL){
	if ( $user_id === NULL || $member_id === NULL) return;
	
	$block_list = bpb_get_blocked_users($user_id);
	$block_type = $block_list[$member_id];
	
	return ($block_type == NULL || $block_type == 0) ? NULL : $block_type;
}

function bpb_check_blocked_type($user_id, $member_id, $block_type_check){
	if ($user_id === NULL || $member_id === NULL || $block_type_check === NULL) return;
	
	$block_type = bpb_get_blocked_type($user_id, $member_id);
	if ($block_type == NULL) return false;
	
	return check_blocked_type($block_type, $block_type_check);
}

function bpb_check_blocked_user( $user_id = NULL, $member_id = NULL ) {
	if ( $user_id === NULL || $member_id === NULL) return;
	
	$block_type = bpb_get_blocked_type($user_id, $member_id);
	return ($block_type == NULL) ? false : true;
}

function check_blocked_type ($block_type, $block_type_check){
	$check_val = $block_type % $block_type_check;
	return ($check_val == 0) ? true : false;
}

/**
 * Get Blocked Users
 * Returns a given users or the current users list of blocked users.
 * @filter bpb_get_blocked_users or blocked users by type.
 * @since 1.0
 * @version 1.0
 */
function bpb_get_blocked_users( $user_id = NULL ) {
	if ( $user_id === NULL ) return;
	
	if ( $user_id === get_current_user_id() )
		$list = wp_cache_get( 'bpb', 'bpb_my_block_list' );
	else
		$list = get_user_meta( $user_id, '_block', true );
	
	if ( empty( $list ) )
		$list = array();

	$_list = apply_filters( 'bpb_get_blocked_users', $list, $user_id );
	return array_filter( $_list );
}

function bpb_get_blocked_users_list_by_type( $user_id = NULL, $block_type_check = NULL) {
	if ( $user_id === NULL) return;
	
	$block_list = bpb_get_blocked_users($user_id);
	$block_list_return = array();
	
	foreach($block_list as $member_id => $block_type){
		if($block_type_check == NULL){
			$block_list_return[] = $member_id;
		}else if(check_blocked_type($block_type, $block_type_check)){
			$block_list_return[] = $member_id;
		}
	}
	return $block_list_return;
}

/**
 * Get Block Link
 * Returns the link to add a user to the current users blocked list.
 * @filter bpb_block_link 
 * @since 1.0
 * @version 1.0
 */
function bpb_block_link( $list_id = 0, $user_id = 0 ) {
	return apply_filters( 'bpb_block_link', add_query_arg( array(
		'action' => 'block',
		'list'   => $list_id,
		'num'    => $user_id,
		'token'  => wp_create_nonce( 'block-' . $list_id )
	) ), $list_id, $user_id );
}

/**
 * Get Unblock Link
 * Returns the link to remove a user from the current users block list.
 * @filter bpb_unblock_link
 * @since 1.0
 * @version 1.0
 */
function bpb_unblock_link( $list_id = 0, $user_id = 0 ) {
	return apply_filters( 'bpb_unblock_link', add_query_arg( array(
		'action' => 'unblock',
		'list'   => $list_id,
		'num'    => $user_id,
		'token'  => wp_create_nonce( 'unblock-' . $list_id )
	) ), $list_id, $user_id );
}

/**
 * Remove ID from List
 * Removes a given ID from a users block list.
 * @filter bpb_remove_user_from_list
 * @since 1.0
 * @version 1.0
 */
function bpb_remove_user_from_list( $list_id = NULL, $id_to_remove = NULL ) {
	$current = bpb_get_blocked_users( $list_id );
	$new = array();
	foreach ( (array) $current as $user_id => $block_type) {
		if ( $user_id != $id_to_remove )
			$new[$user_id] = $block_type;
	}
	update_user_meta( $list_id, '_block', apply_filters( 'bpb_remove_user_from_list', $new, $list_id, $id_to_remove ) );
}
?>