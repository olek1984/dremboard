<?php
if ( !defined( 'BPB_VERSION' ) ) exit;

/**
 * Follow Check
 * Removed the Add Follow button if the user is blocking us.
 * @since 1.0
 * @version 1.0
 */
function bpb_follow_check( $follow_id, $follow ) {
	$leader_id = $follow->leader_id; //bp_displayed_user_id();
	$follower_id = $follow->follower_id; //bp_loggedin_user_id();
	
	$list = bpb_get_blocked_users_list_by_type( $leader_id, BPB_FOLLOW );
	if ( in_array( $follower_id, (array) $list ) ) return -1;
	return $follow_id;
}

/**
 * Add Follow Action
 * Replicate the follows_action_add_follow() with a custom check
 * to prevent users from constructing new followship requests to users
 * who block them. Used since even though the button is not shown, you can still
 * construct a request though the URL.
 * @since 1.0
 * @version 1.0
 */
function bpb_follow_action_start() {
	global $bp;

	if ( !bp_is_current_component( $bp->follow->followers->slug ) || !bp_is_current_action( 'start' ) )
		return false;

	if ( bp_displayed_user_id() == bp_loggedin_user_id() )
		return false;

$list = bpb_get_blocked_users_list_by_type( bp_displayed_user_id(), BPB_FOLLOW );
	if ( in_array( bp_loggedin_user_id(), $list ) ) return false;

	check_admin_referer( 'start_following' );

	if ( bp_follow_is_following( array( 'leader_id' => bp_displayed_user_id(), 'follower_id' => bp_loggedin_user_id() ) ) )
		bp_core_add_message( sprintf( __( 'You are already following %s.', 'bp-follow' ), bp_get_displayed_user_fullname() ), 'error' );
	else {
		if ( !bp_follow_start_following( array( 'leader_id' => bp_displayed_user_id(), 'follower_id' => bp_loggedin_user_id() ) ) )
			bp_core_add_message( sprintf( __( 'There was a problem when trying to follow %s, please try again.', 'bp-follow' ), bp_get_displayed_user_fullname() ), 'error' );
		else
			bp_core_add_message( sprintf( __( 'You are now following %s.', 'bp-follow' ), bp_get_displayed_user_fullname() ) );
	}

	// it's possible that wp_get_referer() returns false, so let's fallback to the displayed user's page
	$redirect = wp_get_referer() ? wp_get_referer() : bp_displayed_user_domain();
	bp_core_redirect( $redirect );

	return false;
}
?>