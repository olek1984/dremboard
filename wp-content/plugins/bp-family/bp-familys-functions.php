<?php

/**
 * BuddyPress familys Functions
 *
 * Functions are where all the magic happens in BuddyPress. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 *
 * @package BuddyPress
 * @subpackage familysFunctions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Create a new familyship.
 *
 * @param int $initiator_userid ID of the "initiator" user (the user who is
 *        sending the familyship request).
 * @param int $family_userid ID of the "family" user (the user whose familyship
 *        is being requested).
 * @param bool $force_accept Optional. Whether to force acceptance. When false,
 *        running familys_add_family() will result in a familyship request.
 *        When true, running familys_add_family() will result in an accepted
 *        familyship, with no notifications being sent. Default: false.
 * @return bool True on success, false on failure.
 */
function familys_add_family( $initiator_userid, $family_userid, $force_accept = false ) {

	// Check if already familys, and bail if so
	$familyship = new BP_familys_familyship;
	if ( (int) $familyship->is_confirmed ) {
		return true;
	}
				//!!!!!
	update_option("test004",'yes');
	// Setup the familyship data
	$familyship->initiator_user_id = $initiator_userid;
	$familyship->family_user_id    = $family_userid;
	$familyship->is_confirmed      = 0;
	$familyship->is_limited        = 0;
	$familyship->date_created      = bp_core_current_time();
update_option("test004",'yes111');
	if ( !empty( $force_accept ) ) {
		$familyship->is_confirmed = 1;
	}
				//!!!!!
	update_option("test004",'no');
	// Bail if familyship could not be saved (how sad!)
	if ( ! $familyship->save() ) {
		return false;
	}
				//!!!!!
	update_option("test004",'004');
	// Send notifications
	if ( empty( $force_accept ) ) {
		$action = 'familys_familyship_requested';

	// Update family totals
	} else {
		$action = 'familys_familyship_accepted';
		familys_update_family_totals( $familyship->initiator_user_id, $familyship->family_user_id, 'add' );
	}

	// Call the above titled action and pass familyship data into it
	do_action( $action, $familyship->id, $familyship->initiator_user_id, $familyship->family_user_id, $familyship );

	return true;
}

/**
 * Remove a familyship.
 *
 * Will also delete the related "familyship_accepted" activity item.
 *
 * @param int $initiator_userid ID of the familyship initiator.
 * @param int $family_userid ID of the family user.
 * @return bool True on success, false on failure.
 */
function familys_remove_family( $initiator_userid, $family_userid ) {

	$familyship_id = BP_familys_familyship::get_familyship_id( $initiator_userid, $family_userid );
	$familyship    = new BP_familys_familyship( $familyship_id );

	do_action( 'familys_before_familyship_delete', $familyship_id, $initiator_userid, $family_userid );

	// Remove the activity stream item for the user who canceled the familyship
	familys_delete_activity( array( 'item_id' => $familyship_id, 'type' => 'familyship_accepted', 'user_id' => bp_displayed_user_id() ) );

	// This hook is misleadingly named - the familyship is not yet deleted.
	// This is your last chance to do something while the familyship exists
	do_action( 'familys_familyship_deleted', $familyship_id, $initiator_userid, $family_userid );

	if ( $familyship->delete() ) {
		familys_update_family_totals( $initiator_userid, $family_userid, 'remove' );

		do_action( 'familys_familyship_post_delete', $initiator_userid, $family_userid );

		return true;
	}

	return false;
}

/**
 * Mark a familyship request as accepted.
 *
 * Also initiates a "familyship_accepted" activity item.
 *
 * @param int $familyship_id ID of the pending familyship object.
 * @return bool True on success, false on failure.
 */
function familys_accept_familyship( $familyship_id ) {

	// Get the friesdhip data
	$familyship = new BP_familys_familyship( $familyship_id, true, false );

	// Accepting familyship
	if ( empty( $familyship->is_confirmed ) && BP_familys_familyship::accept( $familyship_id ) ) {

		// Bump the familyship counts
		familys_update_family_totals( $familyship->initiator_user_id, $familyship->family_user_id );

		do_action( 'familys_familyship_accepted', $familyship->id, $familyship->initiator_user_id, $familyship->family_user_id, $familyship );

		return true;
	}

	return false;
}

/**
 * Mark a familyship request as rejected.
 *
 * @param int $familyship_id ID of the pending familyship object.
 * @return bool True on success, false on failure.
 */
function familys_reject_familyship( $familyship_id ) {
	$familyship = new BP_familys_familyship( $familyship_id, true, false );

	if ( empty( $familyship->is_confirmed ) && BP_familys_familyship::reject( $familyship_id ) ) {
		do_action_ref_array( 'familys_familyship_rejected', array( $familyship_id, &$familyship ) );
		return true;
	}

	return false;
}

/**
 * Withdraw a familyship request.
 *
 * @param int $initiator_userid ID of the familyship initiator - this is the
 *            user who requested the familyship, and is doing the withdrawing.
 * @param int $family_userid ID of the requested family.
 * @return bool True on success, false on failure.
 */
function familys_withdraw_familyship( $initiator_userid, $family_userid ) {
	$familyship_id = BP_familys_familyship::get_familyship_id( $initiator_userid, $family_userid );
	$familyship    = new BP_familys_familyship( $familyship_id, true, false );

	if ( empty( $familyship->is_confirmed ) && BP_familys_familyship::withdraw( $familyship_id ) ) {

		// @deprecated Since 1.9
		do_action_ref_array( 'familys_familyship_whithdrawn', array( $familyship_id, &$familyship ) );

		// @since 1.9
		do_action_ref_array( 'familys_familyship_withdrawn',  array( $familyship_id, &$familyship ) );

		return true;
	}

	return false;
}

/**
 * Check whether two users are familys.
 *
 * @param int $user_id ID of the first user.
 * @param int $possible_family_id ID of the other user.
 * @return bool Returns true if the two users are familys, otherwise false.
 */
function familys_check_familyship( $user_id, $possible_family_id ) {

	if ( 'is_family' == BP_familys_familyship::check_is_family( $user_id, $possible_family_id ) )
		return true;

	return false;
}

/**
 * Get the familyship status of two familys.
 *
 * Will return 'is_familys', 'not_familys', or 'pending'.
 *
 * @param int $user_id ID of the first user.
 * @param int $possible_family_id ID of the other user.
 * @return string family status of the two users.
 */
function familys_check_familyship_status( $user_id, $possible_family_id ) {
	return BP_familys_familyship::check_is_family( $user_id, $possible_family_id );
}

/**
 * Get the family count of a given user.
 *
 * @param int $user_id ID of the user whose familys are being counted.
 * @return int family count of the user.
 */
function familys_get_total_family_count( $user_id = 0 ) {
	if ( empty( $user_id ) )
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();

	$count = bp_get_user_meta( $user_id, 'total_family_count', true );
	if ( empty( $count ) )
		$count = 0;

	return apply_filters( 'familys_get_total_family_count', $count );
}

/**
 * Check whether a given user has any familys.
 *
 * @param int $user_id ID of the user whose familys are being checked.
 * @return bool True if the user has familys, otherwise false.
 */
function familys_check_user_has_familys( $user_id ) {
	$family_count = familys_get_total_family_count( $user_id );

	if ( empty( $family_count ) )
		return false;

	if ( !(int) $family_count )
		return false;

	return true;
}

/**
 * Get the ID of two users' familyship, if it exists.
 *
 * @param int $initiator_user_id ID of the first user.
 * @param int $family_user_id ID of the second user.
 * @return int|bool ID of the familyship if found, otherwise false.
 */
function familys_get_familyship_id( $initiator_user_id, $family_user_id ) {
	return BP_familys_familyship::get_familyship_id( $initiator_user_id, $family_user_id );
}

/**
 * Get the IDs of a given user's familys.
 *
 * @param int $user_id ID of the user whose familys are being retreived.
 * @param bool $family_requests_only Optional. Whether to fetch unaccepted
 *        requests only. Default: false.
 * @param bool $assoc_arr Optional. True to receive an array of arrays keyed as
 *        'user_id' => $user_id; false to get a one-dimensional array of user
 *        IDs. Default: false.
 */
function familys_get_family_user_ids( $user_id, $family_requests_only = false, $assoc_arr = false ) {
	return BP_familys_familyship::get_family_user_ids( $user_id, $family_requests_only, $assoc_arr );
}

/**
 * Search the familys of a user by a search string.
 *
 * @param string $filter The search string, matched against xprofile fields (if
 *        available), or usermeta 'nickname' field.
 * @param int $user_id ID of the user whose familys are being searched.
 * @param int $limit Optional. Max number of familys to return.
 * @param int $page Optional. The page of results to return. Default: null (no
 *        pagination - return all results).
 * @return array|bool On success, an array: {
 *     @type array $familys IDs of familys returned by the query.
 *     @type int $count Total number of familys (disregarding
 *           pagination) who match the search.
 * }. Returns false on failure.
 */
function familys_search_familys( $search_terms, $user_id, $pag_num = 10, $pag_page = 1 ) {
	return BP_familys_familyship::search_familys( $search_terms, $user_id, $pag_num, $pag_page );
}

/**
 * Get a list of IDs of users who have requested familyship of a given user.
 *
 * @param int $user_id The ID of the user who has received the familyship
 *        requests.
 * @return array|bool An array of user IDs, or false if none are found.
 */
function familys_get_familyship_request_user_ids( $user_id ) {
	return BP_familys_familyship::get_familyship_request_user_ids( $user_id );
}

/**
 * Get a user's most recently active familys.
 *
 * @see BP_Core_User::get_users() for a description of return value.
 *
 * @param int $user_id ID of the user whose familys are being retreived.
 * @param int $per_page Optional. Number of results to return per page.
 *        Default: 0 (no pagination; show all results).
 * @param int $page Optional. Number of the page of results to return.
 *        Default: 0 (no pagination; show all results).
 * @param string $filter Optional. Limit results to those matching a search
 *        string.
 * @return array See {@link BP_Core_User::get_users()}.
 */
function familys_get_recently_active( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	return apply_filters( 'familys_get_recently_active', BP_Core_User::get_users( 'active', $per_page, $page, $user_id, $filter ) );
}

/**
 * Get a user's familys, in alphabetical order.
 *
 * @see BP_Core_User::get_users() for a description of return value.
 *
 * @param int $user_id ID of the user whose familys are being retreived.
 * @param int $per_page Optional. Number of results to return per page.
 *        Default: 0 (no pagination; show all results).
 * @param int $page Optional. Number of the page of results to return.
 *        Default: 0 (no pagination; show all results).
 * @param string $filter Optional. Limit results to those matching a search
 *        string.
 * @return array See {@link BP_Core_User::get_users()}.
 */
function familys_get_alphabetically( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	return apply_filters( 'familys_get_alphabetically', BP_Core_User::get_users( 'alphabetical', $per_page, $page, $user_id, $filter ) );
}

/**
 * Get a user's familys, in the order in which they joined the site.
 *
 * @see BP_Core_User::get_users() for a description of return value.
 *
 * @param int $user_id ID of the user whose familys are being retreived.
 * @param int $per_page Optional. Number of results to return per page.
 *        Default: 0 (no pagination; show all results).
 * @param int $page Optional. Number of the page of results to return.
 *        Default: 0 (no pagination; show all results).
 * @param string $filter Optional. Limit results to those matching a search
 *        string.
 * @return array See {@link BP_Core_User::get_users()}.
 */
function familys_get_newest( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	return apply_filters( 'familys_get_newest', BP_Core_User::get_users( 'newest', $per_page, $page, $user_id, $filter ) );
}

/**
 * Get the last active date of many users at once.
 *
 * @see BP_familys_familyship::get_bulk_last_active() for a description of
 *      arguments and return value.
 *
 * @param array $user_ids See BP_familys_familyship::get_bulk_last_active().
 * @return array $user_ids See BP_familys_familyship::get_bulk_last_active().
 */
function familys_get_bulk_last_active( $family_ids ) {
	return BP_familys_familyship::get_bulk_last_active( $family_ids );
}

/**
 * Get a list of familys that a user can invite into this group.
 *
 * Excludes familys that are already in the group, and banned familys if the
 * user is not a group admin.
 *
 * @since BuddyPress (1.0.0)
 *
 * @param int $user_id User ID whose familys to see can be invited. Default:
 *        ID of the logged-in user.
 * @param int $group_id Group to check possible invitations against.
 * @return mixed False if no familys, array of users if familys.
 */
function familys_get_familys_invite_list( $user_id = 0, $group_id = 0 ) {

	// Default to logged in user id
	if ( empty( $user_id ) )
		$user_id = bp_loggedin_user_id();

	// Only group admins can invited previously banned users
	$user_is_admin = (bool) groups_is_user_admin( $user_id, $group_id );

	// Assume no familys
	$familys = array();

	// Default args
	$args = apply_filters( 'bp_familys_pre_get_invite_list', array(
		'user_id'  => $user_id,
		'type'     => 'alphabetical',
		'per_page' => 0
	) );

	// User has familys
	if ( bp_has_members( $args ) ) {

		/**
		 * Loop through all familys and try to add them to the invitation list.
		 *
		 * Exclude familys that:
		 *     1. are already members of the group
		 *     2. are banned from this group if the current user is also not a
		 *        group admin.
		 */
		while ( bp_members() ) :

			// Load the member
			bp_the_member();

			// Get the user ID of the family
			$family_user_id = bp_get_member_user_id();

			// Skip family if already in the group
			if ( groups_is_user_member( $family_user_id, $group_id ) )
				continue;

			// Skip family if not group admin and user banned from group
			if ( ( false === $user_is_admin ) && groups_is_user_banned( $family_user_id, $group_id ) )
				continue;

			// family is safe, so add it to the array of possible familys
			$familys[] = array(
				'id'        => $family_user_id,
				'full_name' => bp_get_member_name()
			);

		endwhile;
	}

	// If no familys, explicitly set to false
	if ( empty( $familys ) )
		$familys = false;

	// Allow familys to be filtered
	return apply_filters( 'bp_familys_get_invite_list', $familys, $user_id, $group_id );
}

/**
 * Get a count of a user's familys who can be invited to a given group.
 *
 * Users can invite any of their familys except:
 *
 * - users who are already in the group
 * - users who have a pending invite to the group
 * - users who have been banned from the group
 *
 * @param int $user_id ID of the user whose familys are being counted.
 * @param int $group_id ID of the group familys are being invited to.
 * @return int $invitable_count Eligible family count.
 */
function familys_count_invitable_familys( $user_id, $group_id ) {
	return BP_familys_familyship::get_invitable_family_count( $user_id, $group_id );
}

/**
 * Get a total family count for a given user.
 *
 * @param int $user_id Optional. ID of the user whose familyships you are
 *        counting. Default: displayed user (if any), otherwise logged-in user.
 * @return int family count for the user.
 */
function familys_get_family_count_for_user( $user_id ) {
	return BP_familys_familyship::total_family_count( $user_id );
}

/**
 * Return a list of a user's familys, filtered by a search term.
 *
 * @param string $search_terms Search term to filter on.
 * @param int $user_id ID of the user whose familys are being searched.
 * @param int $pag_num Number of results to return per page. Default: 0 (no
 *        pagination - show all results).
 * @param int $pag_num Number of the page being requested. Default: 0 (no
 *        pagination - show all results).
 * @return array Array of BP_Core_User objects corresponding to familys.
 */
function familys_search_users( $search_terms, $user_id, $pag_num = 0, $pag_page = 0 ) {

	$user_ids = BP_familys_familyship::search_users( $search_terms, $user_id, $pag_num, $pag_page );

	if ( empty( $user_ids ) )
		return false;

	$users = array();
	for ( $i = 0, $count = count( $user_ids ); $i < $count; ++$i )
		$users[] = new BP_Core_User( $user_ids[$i] );

	return array( 'users' => $users, 'count' => BP_familys_familyship::search_users_count( $search_terms ) );
}

/**
 * Has a familyship been confirmed (accepted)?
 *
 * @param int $familyship_id The ID of the familyship being checked.
 * @return bool True if the familyship is confirmed, otherwise false.
 */
function familys_is_familyship_confirmed( $familyship_id ) {
	$familyship = new BP_familys_familyship( $familyship_id );
	return $familyship->is_confirmed;
}

/**
 * Update user family counts.
 *
 * family counts are cached in usermeta for performance reasons. After a
 * familyship event (acceptance, deletion), call this function to regenerate
 * the cached values.
 *
 * @param int $initiator_user_id ID of the first user.
 * @param int $family_user_id ID of the second user.
 * @param string $status Optional. The familyship event that's been triggered.
 *        'add' will ++ each user's family counts, while any other string
 *        will --.
 */
function familys_update_family_totals( $initiator_user_id, $family_user_id, $status = 'add' ) {

	if ( 'add' == $status ) {
		bp_update_user_meta( $initiator_user_id, 'total_family_count', (int)bp_get_user_meta( $initiator_user_id, 'total_family_count', true ) + 1 );
		bp_update_user_meta( $family_user_id, 'total_family_count', (int)bp_get_user_meta( $family_user_id, 'total_family_count', true ) + 1 );
	} else {
		bp_update_user_meta( $initiator_user_id, 'total_family_count', (int)bp_get_user_meta( $initiator_user_id, 'total_family_count', true ) - 1 );
		bp_update_user_meta( $family_user_id, 'total_family_count', (int)bp_get_user_meta( $family_user_id, 'total_family_count', true ) - 1 );
	}
}

/**
 * Remove all familys-related data concerning a given user.
 *
 * Removes the following:
 *
 * - familyships of which the user is a member
 * - Cached family count for the user
 * - Notifications of familyship requests sent by the user
 *
 * @param int $user_id ID of the user whose family data is being removed.
 */
function familys_remove_data( $user_id ) {

	do_action( 'familys_before_remove_data', $user_id );

	BP_familys_familyship::delete_all_for_user( $user_id );

	// Remove usermeta
	bp_delete_user_meta( $user_id, 'total_family_count' );

	do_action( 'familys_remove_data', $user_id );
}
add_action( 'wpmu_delete_user',  'familys_remove_data' );
add_action( 'delete_user',       'familys_remove_data' );
add_action( 'bp_make_spam_user', 'familys_remove_data' );
