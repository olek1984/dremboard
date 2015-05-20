<?php

/**
 * BuddyPress familys Caching.
 *
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout BuddyPress.
 *
 * @package BuddyPress
 * @subpackage familysCaching
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Clear familys-related cache for members of a specific familyship.
 *
 * @param int $familyship_id ID of the familyship whose two members should
 *        have their familys cache busted.
 */
function familys_clear_family_object_cache( $familyship_id ) {
	if ( !$familyship = new BP_familys_familyship( $familyship_id ) )
		return false;

	wp_cache_delete( 'familys_family_ids_' .    $familyship->initiator_user_id, 'bp' );
	wp_cache_delete( 'familys_family_ids_' .    $familyship->family_user_id,    'bp' );
}

// List actions to clear object caches on
add_action( 'familys_familyship_accepted', 'familys_clear_family_object_cache' );
add_action( 'familys_familyship_deleted',  'familys_clear_family_object_cache' );

// List actions to clear super cached pages on, if super cache is installed
add_action( 'familys_familyship_rejected',  'bp_core_clear_cache' );
add_action( 'familys_familyship_accepted',  'bp_core_clear_cache' );
add_action( 'familys_familyship_deleted',   'bp_core_clear_cache' );
add_action( 'familys_familyship_requested', 'bp_core_clear_cache' );
