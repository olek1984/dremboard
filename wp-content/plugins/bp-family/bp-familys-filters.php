<?php

/**
 * BuddyPress family Filters
 *
 * @package BuddyPress
 * @subpackage familysFilters
 */

/**
 * Filter BP_User_Query::populate_extras to override each queried users fullname.
 *
 * @since BuddyPress (1.7.0)
 *
 * @global BuddyPress $bp Global BuddyPress settings.
 * @global WPDB $wpdb WordPress database access object.
 *
 * @param BP_User_Query $user_query The BP_User_Query object.
 * @param string $user_ids_sql Comma-separated list of user IDs to fetch extra
 *        data for, as determined by BP_User_Query.
 */
function bp_familys_filter_user_query_populate_extras( BP_User_Query $user_query, $user_ids_sql ) {
	global $bp, $wpdb;

	// Fetch whether or not the user is a family of the current user
	$family_status = $wpdb->get_results( $wpdb->prepare( "SELECT initiator_user_id, family_user_id, is_confirmed FROM {$bp->familys->table_name} WHERE (initiator_user_id = %d AND family_user_id IN ( {$user_ids_sql} ) ) OR (initiator_user_id IN ( {$user_ids_sql} ) AND family_user_id = %d )", bp_loggedin_user_id(), bp_loggedin_user_id() ) );

	// The "family" is the user ID in the pair who is *not* the logged in user
	foreach ( (array) $family_status as $fs ) {
		$family_id = bp_loggedin_user_id() == $fs->initiator_user_id ? $fs->family_user_id : $fs->initiator_user_id;

		if ( isset( $user_query->results[ $family_id ] ) ) {
			$user_query->results[ $family_id ]->is_family = $fs->is_confirmed;
		}
	}
}
add_filter( 'bp_user_query_populate_extras', 'bp_familys_filter_user_query_populate_extras', 4, 2 );
