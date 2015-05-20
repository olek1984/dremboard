<?php

/**
 * BuddyPress familys Activity Functions
 *
 * These functions handle the recording, deleting and formatting of activity
 * for the user and for this specific component.
 *
 * @package BuddyPress
 * @subpackage familysActivity
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Record an activity item related to the familys component.
 *
 * A wrapper for {@link bp_activity_add()} that provides some familys-specific
 * defaults.
 *
 * @see bp_activity_add() for more detailed description of parameters and
 *      return values.
 *
 * @param array $args {
 *     An array of arguments for the new activity item. Accepts all parameters
 *     of {@link bp_activity_add()}. The one difference is the following
 *     argument, which has a different default here:
 *     @type string $component Default: the id of your familys component
 *           (usually 'familys').
 * }
 * @return bool See {@link bp_activity_add()}.
 */
function familys_record_activity( $args = '' ) {

	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	$r = wp_parse_args( $args, array(
		'user_id'           => bp_loggedin_user_id(),
		'action'            => '',
		'content'           => '',
		'primary_link'      => '',
		'component'         => buddypress()->familys->id,
		'type'              => false,
		'item_id'           => false,
		'secondary_item_id' => false,
		'recorded_time'     => bp_core_current_time(),
		'hide_sitewide'     => false
	) );

	return bp_activity_add( $r );
}

/**
 * Delete an activity item related to the familys component.
 *
 * @param array $args {
 *     An array of arguments for the item to delete.
 *     @type int $item_id ID of the 'item' associated with the activity item.
 *           For familys activity items, this is usually the user ID of one
 *           of the familys.
 *     @type string $type The 'type' of the activity item (eg
 *           'familyship_accepted').
 *     @type int $user_id ID of the user associated with the activity item.
 * }
 * @return bool True on success, false on failure.
 */
function familys_delete_activity( $args ) {
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	bp_activity_delete_by_item_id( array(
		'component' => buddypress()->familys->id,
		'item_id'   => $args['item_id'],
		'type'      => $args['type'],
		'user_id'   => $args['user_id']
	) );
}

/**
 * Register the activity actions for bp-familys.
 */
function familys_register_activity_actions() {

	if ( !bp_is_active( 'activity' ) ) {
		return false;
	}

	$bp = buddypress();

	// These two added in BP 1.6
	bp_activity_set_action( $bp->familys->id, 'familyship_accepted', __( 'familyships accepted', 'buddypress' ) );
	bp_activity_set_action( $bp->familys->id, 'familyship_created',  __( 'New familyships',      'buddypress' ) );

	// < BP 1.6 backpat
	bp_activity_set_action( $bp->familys->id, 'familys_register_activity_action', __( 'New familyship created', 'buddypress' ) );

	do_action( 'familys_register_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'familys_register_activity_actions' );

/**
 * Add activity stream items when one members accepts another members request
 * for virtual familyship.
 *
 * @since BuddyPress (1.9.0)
 *
 * @param int $familyship_id
 * @param int $initiator_user_id
 * @param int $family_user_id
 * @param object $familyship Optional
 */
function bp_familys_familyship_accepted_activity( $familyship_id, $initiator_user_id, $family_user_id, $familyship = false ) {

	// Bail if Activity component is not active
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	// Get links to both members profiles
	$initiator_link = bp_core_get_userlink( $initiator_user_id );
	$family_link    = bp_core_get_userlink( $family_user_id    );

	// Record in activity streams for the initiator
	familys_record_activity( array(
		'user_id'           => $initiator_user_id,
		'type'              => 'familyship_created',
		'action'            => apply_filters( 'familys_activity_familyship_accepted_action', sprintf( __( '%1$s and %2$s are now Family', 'buddypress' ), $initiator_link, $family_link ), $familyship ),
		'item_id'           => $familyship_id,
		'secondary_item_id' => $family_user_id
	) );

	// Record in activity streams for the family
	familys_record_activity( array(
		'user_id'           => $family_user_id,
		'type'              => 'familyship_created',
		'action'            => apply_filters( 'familys_activity_familyship_accepted_action', sprintf( __( '%1$s and %2$s are now Family', 'buddypress' ), $family_link, $initiator_link ), $familyship ),
		'item_id'           => $familyship_id,
		'secondary_item_id' => $initiator_user_id,
		'hide_sitewide'     => true // We've already got the first entry site wide
	) );
}
add_action( 'familys_familyship_accepted', 'bp_familys_familyship_accepted_activity', 10, 4 );
