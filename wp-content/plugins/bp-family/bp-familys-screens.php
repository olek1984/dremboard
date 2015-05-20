<?php

/**
 * BuddyPress familys Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when
 * their specific URL is caught. They will first save or manipulate data using
 * business functions, then pass on the user to a template file.
 *
 * @package BuddyPress
 * @subpackage familysScreens
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Catch and process the My familys page.
 */
function familys_screen_my_familys() {
//global $bp;
	do_action( 'familys_screen_my_familys' );
//var_dump("1112");
	//bp_core_load_template( apply_filters( 'familys_template_my_familys', 'members/single/home1' ) );
	bp_core_load_template( apply_filters( 'familys_template_my_familys', 'members/single/home' ) );
//var_dump(BP_family_DIR.'/members/single/home');
	//bp_core_load_template( BP_family_DIR.'/members/single/home.php');
	//bp_get_template_part( 'members/single/followers' );
	//bp_core_load_template( 'members/single/followers' );
	/*
				$custom_front = bp_locate_template( array( 'members/single/home.php' ), false, true );
				if     ( ! empty( $custom_front   ) ) { load_template( $custom_front, true );};
				*/
					
}

/**
 * Catch and process the Requests page.
 */
function familys_screen_requests() {
	if ( bp_is_action_variable( 'accept', 0 ) && is_numeric( bp_action_variable( 1 ) ) ) {
		// Check the nonce
		check_admin_referer( 'familys_accept_familyship' );

		if ( familys_accept_familyship( bp_action_variable( 1 ) ) )
			bp_core_add_message( __( 'familyship accepted', 'buddypress' ) );
		else
			bp_core_add_message( __( 'familyship could not be accepted', 'buddypress' ), 'error' );

		bp_core_redirect( trailingslashit( bp_loggedin_user_domain() . bp_current_component() . '/' . bp_current_action() ) );

	} elseif ( bp_is_action_variable( 'reject', 0 ) && is_numeric( bp_action_variable( 1 ) ) ) {
		// Check the nonce
		check_admin_referer( 'familys_reject_familyship' );

		if ( familys_reject_familyship( bp_action_variable( 1 ) ) )
			bp_core_add_message( __( 'familyship rejected', 'buddypress' ) );
		else
			bp_core_add_message( __( 'familyship could not be rejected', 'buddypress' ), 'error' );

		bp_core_redirect( trailingslashit( bp_loggedin_user_domain() . bp_current_component() . '/' . bp_current_action() ) );

	} elseif ( bp_is_action_variable( 'cancel', 0 ) && is_numeric( bp_action_variable( 1 ) ) ) {
		// Check the nonce
		check_admin_referer( 'familys_withdraw_familyship' );

		if ( familys_withdraw_familyship( bp_loggedin_user_id(), bp_action_variable( 1 ) ) )
			bp_core_add_message( __( 'familyship request withdrawn', 'buddypress' ) );
		else
			bp_core_add_message( __( 'familyship request could not be withdrawn', 'buddypress' ), 'error' );

		bp_core_redirect( trailingslashit( bp_loggedin_user_domain() . bp_current_component() . '/' . bp_current_action() ) );
	}

	do_action( 'familys_screen_requests' );

	bp_core_load_template( apply_filters( 'familys_template_requests', 'members/single/home' ) );
}

/**
 * Add familys-related settings to the Settings > Notifications page.
 */
function familys_screen_notification_settings() {

	if ( !$send_requests = bp_get_user_meta( bp_displayed_user_id(), 'notification_familys_familyship_request', true ) )
		$send_requests   = 'yes';

	if ( !$accept_requests = bp_get_user_meta( bp_displayed_user_id(), 'notification_familys_familyship_accepted', true ) )
		$accept_requests = 'yes'; ?>

	<table class="notification-settings" id="familys-notification-settings">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'familys', 'buddypress' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'buddypress' ) ?></th>
				<th class="no"><?php _e( 'No', 'buddypress' )?></th>
			</tr>
		</thead>

		<tbody>
			<tr id="familys-notification-settings-request">
				<td></td>
				<td><?php _e( 'A member sends you a familyship request', 'buddypress' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_familys_familyship_request]" value="yes" <?php checked( $send_requests, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_familys_familyship_request]" value="no" <?php checked( $send_requests, 'no', true ) ?>/></td>
			</tr>
			<tr id="familys-notification-settings-accepted">
				<td></td>
				<td><?php _e( 'A member accepts your familyship request', 'buddypress' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_familys_familyship_accepted]" value="yes" <?php checked( $accept_requests, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_familys_familyship_accepted]" value="no" <?php checked( $accept_requests, 'no', true ) ?>/></td>
			</tr>

			<?php do_action( 'familys_screen_notification_settings' ); ?>

		</tbody>
	</table>

<?php
}
add_action( 'bp_notification_settings', 'familys_screen_notification_settings' );

//???
function bp_family_load_template_filter( $found_template, $templates ) {
	global $bp;
//var_dump($found_template);
//var_dump($templates);
$found_template = BP_family_DIR.'/members/single/home.php';
//var_dump(bp_is_current_component( $bp->familys->slug ));
//return BP_family_DIR.'/members/single/home.php';
//return BP_family_DIR.'/members/single/home.php';
	// Only filter the template location when we're on the follow component pages.
	if ( ! bp_is_current_component( $bp->familys->slug ) && ! bp_is_current_component( $bp->familys->slug ) )
		return $found_template;

	// $found_template is not empty when the older template files are found in the
	// parent and child theme
	//
	//  /wp-content/themes/YOUR-THEME/members/single/following.php
	//  /wp-content/themes/YOUR-THEME/members/single/followers.php
	//
	// The older template files utilize a full template ( get_header() +
	// get_footer() ), which sucks for themes and theme compat.
	//
	// When the older template files are not found, we use our new template method,
	// which will act more like a template part.
	if ( empty( $found_template ) ) {
		// register our theme compat directory
		//
		// this tells BP to look for templates in our plugin directory last
		// when the template isn't found in the parent / child theme
		bp_register_template_stack( 'bp_familys_get_template_directory', 14 );

		// locate_template() will attempt to find the plugins.php template in the
		// child and parent theme and return the located template when found
		//
		// plugins.php is the preferred template to use, since all we'd need to do is
		// inject our content into BP
		//
		// note: this is only really relevant for bp-default themes as theme compat
		// will kick in on its own when this template isn't found
		$found_template = locate_template( 'members/single/plugins.php', false, false );

		// add AJAX support to the members loop
		// can disable with the 'bp_follow_allow_ajax_on_follow_pages' filter
		if ( apply_filters( 'bp_familys_allow_ajax_on_familys_pages', true ) ) {
			// add the "Order by" dropdown filter
			//!!!add_action( 'bp_member_plugin_options_nav',    'bp_follow_add_members_dropdown_filter' );

			// add ability to use AJAX
			//!!!add_action( 'bp_after_member_plugin_template', 'bp_familys_add_ajax_to_members_loop' );
		}

		// add our hook to inject content into BP
		//
		// note the new template name for our template part
		add_action( 'bp_template_content', create_function( '', "
			bp_get_template_part( 'members/single/familys' );
		" ) );
	}
//var_dump("1168");
//var_dump($found_template);
	return apply_filters( 'bp_family_load_template_filter', $found_template );
}
//???
//add_filter( 'bp_located_template', 'bp_family_load_template_filter', 10, 2 );