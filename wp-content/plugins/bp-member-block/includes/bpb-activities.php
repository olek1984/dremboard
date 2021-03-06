<?php
if ( !defined( 'BPB_VERSION' ) ) exit;

/**
 * Block Activity View
 * If the user listed is blocking us, we will not be able to see
 * their activities.
 * @since 1.0
 * @version 1.0
 */
function bpb_remove_activity_if_blocked( $update_content ) {
	global $members_template;
	
	if ( !empty( $members_template->member->id ) )
		$user_id = $members_template->member->id;
	elseif ( bp_displayed_user_id() )
		$user_id = bp_displayed_user_id();

	$users_list = bpb_get_blocked_users_list_by_type( $user_id );
	if ( in_array( get_current_user_id(), $users_list ) && ! current_user_can( BPB_ADMIN_CAP ) )
		return '';

	return $update_content;
}

/**
 * Filter Activities
 * Runs though the activities and removes activities that we are block and those we are blocked by.
 * @since 1.0
 * @version 1.0
 */
function bpb_filter_activities( &$activities, &$args )
{
	if ( ! is_user_logged_in() ) return $activities;
	
	$user_id = get_current_user_id();
	$my_list = bpb_get_blocked_users_list_by_type( $user_id );
	$args['exclude'] = implode( ',', $my_list );

	$removed = 0;

	// Enforce those I block
	foreach ( $activities['activities'] as $num => $activity ) {
		if ( in_array( $activity->user_id, $my_list ) && ! user_can( $activity->user_id, BPB_ADMIN_CAP ) ) {
			unset( $activities['activities'][ $num ] );
			$removed = $removed+1;
		}
	}

	// Re-organize the array
	$activities['activities'] = array_values( $activities['activities'] );

	// Enforce those who block me
	foreach ( $activities['activities'] as $num => $activity ) {
		$their_list = bpb_get_blocked_users_list_by_type( $activity->user_id );
		if ( in_array( $user_id, $their_list ) && ! user_can( $activity->user_id, BPB_ADMIN_CAP ) ) {
			unset( $activities['activities'][ $num ] );
			$removed = $removed+1;
		}
	}

	// Re-organize the array
	$activities['activities'] = array_values( $activities['activities'] );

	// Update counter
	if ( $removed > 0 )
		$activities['total'] = $activities['total']-$removed;

	// Return the good news
	return $activities;
}
?>