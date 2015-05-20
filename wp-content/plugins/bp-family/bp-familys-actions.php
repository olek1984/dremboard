<?php

/**
 * BuddyPress familys Actions
 *
 * Action functions are exactly the same as screen functions, however they do
 * not have a template screen associated with them. Usually they will send the
 * user back to the default screen after execution.
 *
 * @package BuddyPress
 * @subpackage familysActions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Catch and process familyship requests.
 */
function familys_action_add_family() {
	if ( !bp_is_familys_component() || !bp_is_current_action( 'add-family' ) )
		return false;

	if ( !$potential_family_id = (int)bp_action_variable( 0 ) )
		return false;

	if ( $potential_family_id == bp_loggedin_user_id() )
		return false;

	$familyship_status = BP_familys_familyship::check_is_family( bp_loggedin_user_id(), $potential_family_id );
//!!!!!
	//update_option("test001",bp_loggedin_user_id());
	//update_option("test002",$potential_family_id);

	if ( 'not_familys' == $familyship_status ) {

		if ( !check_admin_referer( 'familys_add_family' ) )
			//!!!return false;
			{
				//!!!!!
	
	update_option("test003",'yes');
	return false;
			}
		if ( !familys_add_family( bp_loggedin_user_id(), $potential_family_id ) ) {
			bp_core_add_message( __( 'familyship could not be requested.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( 'familyship requested', 'buddypress' ) );
		}

	} else if ( 'is_family' == $familyship_status ) {
		bp_core_add_message( __( 'You are already familys with this user', 'buddypress' ), 'error' );
	} else {
		bp_core_add_message( __( 'You already have a pending familyship request with this user', 'buddypress' ), 'error' );
	}

	bp_core_redirect( wp_get_referer() );

	return false;
}
add_action( 'bp_init', 'familys_action_add_family' );

/**
 * Catch and process Remove familyship requests.
 */
function familys_action_remove_family() {
	if ( !bp_is_familys_component() || !bp_is_current_action( 'remove-family' ) )
		return false;

	if ( !$potential_family_id = (int)bp_action_variable( 0 ) )
		return false;

	if ( $potential_family_id == bp_loggedin_user_id() )
		return false;

	$familyship_status = BP_familys_familyship::check_is_family( bp_loggedin_user_id(), $potential_family_id );

	if ( 'is_family' == $familyship_status ) {

		if ( !check_admin_referer( 'familys_remove_family' ) )
			return false;

		if ( !familys_remove_family( bp_loggedin_user_id(), $potential_family_id ) ) {
			bp_core_add_message( __( 'familyship could not be canceled.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( 'familyship canceled', 'buddypress' ) );
		}

	} else if ( 'is_familys' == $familyship_status ) {
		bp_core_add_message( __( 'You are not yet familys with this user', 'buddypress' ), 'error' );
	} else {
		bp_core_add_message( __( 'You have a pending familyship request with this user', 'buddypress' ), 'error' );
	}

	bp_core_redirect( wp_get_referer() );

	return false;
}
add_action( 'bp_init', 'familys_action_remove_family' );
