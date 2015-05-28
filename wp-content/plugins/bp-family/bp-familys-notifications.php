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

/** Emails ********************************************************************/

/**
 * Send notifications related to a new familyship request.
 *
 * When a familyship is requested, an email and a BP notification are sent to
 * the user of whom familyship has been requested ($family_id).
 *
 * @param int $familyship_id ID of the familyship object.
 * @param int $initiator_id ID of the user who initiated the request.
 * @param int $family_id ID of the request recipient.
 */
function familys_notification_new_request( $familyship_id, $initiator_id, $family_id ) {

	$initiator_name = bp_core_get_user_displayname( $initiator_id );

	if ( 'no' == bp_get_user_meta( (int) $family_id, 'notification_familys_familyship_request', true ) )
		return false;

	$ud                = get_userdata( $family_id );
	$all_requests_link = bp_core_get_user_domain( $family_id ) . bp_get_familys_slug() . '/requests/';
	$settings_slug     = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
	$settings_link     = trailingslashit( bp_core_get_user_domain( $family_id ) .  $settings_slug . '/notifications' );
	$initiator_link    = bp_core_get_user_domain( $initiator_id );

	// Set up and send the message
	$to       = $ud->user_email;
	$subject  = bp_get_email_subject( array( 'text' => sprintf( __( 'New familyship request from %s', 'buddypress' ), $initiator_name ) ) );
	$message  = sprintf( __(
'%1$s wants to add you as a family.

To view all of your pending familyship requests: %2$s

To view %3$s\'s profile: %4$s

---------------------
', 'buddypress' ), $initiator_name, $all_requests_link, $initiator_name, $initiator_link );

	// Only show the disable notifications line if the settings component is enabled
	if ( bp_is_active( 'settings' ) ) {
		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );
	}

	// Send the message
	$to      = apply_filters( 'familys_notification_new_request_to', $to );
	$subject = apply_filters( 'familys_notification_new_request_subject', $subject, $initiator_name );
	$message = apply_filters( 'familys_notification_new_request_message', $message, $initiator_name, $initiator_link, $all_requests_link, $settings_link );

	wp_mail( $to, $subject, $message );

	do_action( 'bp_familys_sent_request_email', $family_id, $subject, $message, $familyship_id, $initiator_id );
}
add_action( 'familys_familyship_requested', 'familys_notification_new_request', 10, 3 );

/**
 * Send notifications related to the acceptance of a familyship request.
 *
 * When a familyship request is accepted, an email and a BP notification are
 * sent to the user who requested the familyship ($initiator_id).
 *
 * @param int $familyship_id ID of the familyship object.
 * @param int $initiator_id ID of the user who initiated the request.
 * @param int $family_id ID of the request recipient.
 */
function familys_notification_accepted_request( $familyship_id, $initiator_id, $family_id ) {

	$family_name = bp_core_get_user_displayname( $family_id );

	if ( 'no' == bp_get_user_meta( (int) $initiator_id, 'notification_familys_familyship_accepted', true ) )
		return false;

	$ud            = get_userdata( $initiator_id );
	$family_link   = bp_core_get_user_domain( $family_id );
	$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
	$settings_link = trailingslashit( bp_core_get_user_domain( $initiator_id ) . $settings_slug . '/notifications' );

	// Set up and send the message
	$to       = $ud->user_email;
	$subject  = bp_get_email_subject( array( 'text' => sprintf( __( '%s accepted your familyship request', 'buddypress' ), $family_name ) ) );
	$message  = sprintf( __(
'%1$s accepted your family request.

To view %2$s\'s profile: %3$s

---------------------
', 'buddypress' ), $family_name, $family_name, $family_link );

	// Only show the disable notifications line if the settings component is enabled
	if ( bp_is_active( 'settings' ) ) {
		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );
	}

	// Send the message
	$to      = apply_filters( 'familys_notification_accepted_request_to', $to );
	$subject = apply_filters( 'familys_notification_accepted_request_subject', $subject, $family_name );
	$message = apply_filters( 'familys_notification_accepted_request_message', $message, $family_name, $family_link, $settings_link );

	wp_mail( $to, $subject, $message );

	do_action( 'bp_familys_sent_accepted_email', $initiator_id, $subject, $message, $familyship_id, $family_id );
}
add_action( 'familys_familyship_accepted', 'familys_notification_accepted_request', 10, 3 );

/** Notifications *************************************************************/

/**
 * Notification formatting callback for bp-familys notifications.
 *
 * @param string $action The kind of notification being rendered.
 * @param int $item_id The primary item ID.
 * @param int $secondary_item_id The secondary item ID.
 * @param int $total_items The total number of messaging-related notifications
 *        waiting for the user.
 * @param string $format 'string' for BuddyBar-compatible notifications;
 *        'array' for WP Toolbar. Default: 'string'.
 * @return array|string
 */
/*
function familys_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	switch ( $action ) {
		case 'familyship_accepted':
			$link = trailingslashit( bp_loggedin_user_domain() . bp_get_familys_slug() . '/my-familys' );

			// Set up the string and the filter
			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( '%d familys accepted your familyship requests', 'buddypress' ), (int) $total_items );
				$filter = 'bp_familys_multiple_familyship_accepted_notification';
			} else {
				$text = sprintf( __( '%s accepted your familyship request', 'buddypress' ),  bp_core_get_user_displayname( $item_id ) );
				$filter = 'bp_familys_single_familyship_accepted_notification';
			}

			break;

		case 'familyship_request':
			$link = bp_loggedin_user_domain() . bp_get_familys_slug() . '/requests/?new';

			// Set up the string and the filter
			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( 'You have %d pending familyship requests', 'buddypress' ), (int) $total_items );
				$filter = 'bp_familys_multiple_familyship_request_notification';
			} else {
				$text = sprintf( __( 'You have a familyship request from %s', 'buddypress' ),  bp_core_get_user_displayname( $item_id ) );
				$filter = 'bp_familys_single_familyship_request_notification';
			}

			break;
	}

	// Return either an HTML link or an array, depending on the requested format
	if ( 'string' == $format ) {
		$return = apply_filters( $filter, '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>', (int) $total_items );
	} else {
		$return = apply_filters( $filter, array(
			'link' => $link,
			'text' => $text
		), (int) $total_items );
	}

	do_action( 'familys_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $return );

	return $return;
}
*/
function familys_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	switch ( $action ) {
		case 'familyship_accepted':
                        $avatar_html = '';
                    
                        $link = trailingslashit( bp_loggedin_user_domain() . bp_get_familys_slug() . '/my-familys' );
                        $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                        $empty_avatar_html = '<div class="notification avatar empty">'
                                                    .'<a href="'
                                                    .$link
                                                    .'">'
                                                    .$avatar
                                                    .'</a></div>';

			// Set up the string and the filter
			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( '%s and %d familys accepted your familyship requests', 'buddypress' ), bp_core_get_user_displayname( $item_id ), (int) $total_items - 1);
				$filter = 'bp_familys_multiple_familyship_accepted_notification';
                                $user_link = bp_core_get_user_domain($item_id);
                                $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                                $avatar_html = '<div class="notification avatar">'
                                                            .'<a href="'
                                                            .$user_link
                                                            .'">'
                                                            .$avatar
                                                            .'</a></div>';

			} else {
				$text = sprintf( __( '%s accepted your familyship request', 'buddypress' ),  bp_core_get_user_displayname( $item_id ) );
				$filter = 'bp_familys_single_familyship_accepted_notification';
                                $user_link = bp_core_get_user_domain($item_id);
                                $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                                $avatar_html = '<div class="notification avatar">'
                                                            .'<a href="'
                                                            .$user_link
                                                            .'">'
                                                            .$avatar
                                                            .'</a></div>';
			}
                        $message_html = '<div class="notification message" >'.'<a class="" href="'.$link.'">'.$text.'</a></div>';
                        $bp_familyship_accepted_html = '<div class="ab-notification-item">';
			$bp_familyship_accepted_html .= $avatar_html.$message_html.$empty_avatar_html.'</div>';
                        $bp_friendship_text = $bp_familyship_accepted_html;


			break;

		case 'familyship_request':
                        $avatar_html = '';
			$link = bp_loggedin_user_domain() . bp_get_familys_slug() . '/requests/?new';
                        $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                        $empty_avatar_html = '<div class="notification avatar empty">'
                                                    .'<a href="'
                                                    .$link
                                                    .'">'
                                                    .$avatar
                                                    .'</a></div>';

			// Set up the string and the filter
			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( 'You have %d pending familyship requests', 'buddypress' ), (int) $total_items );
				$filter = 'bp_familys_multiple_familyship_request_notification';
                                $avatar_html = $empty_avatar_html;
			} else {
				$text = sprintf( __( 'You have a familyship request from %s', 'buddypress' ),  bp_core_get_user_displayname( $item_id ) );
				$filter = 'bp_familys_single_familyship_request_notification';
                                $user_link = bp_core_get_user_domain($item_id);
                                $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                                $avatar_html = '<div class="notification avatar">'
                                                            .'<a href="'
                                                            .$user_link
                                                            .'">'
                                                            .$avatar
                                                            .'</a></div>';
                                
			}
                        $message_html = '<div class="notification message" >'.'<a class="" href="'.$link.'">'.$text.'</a></div>';
                        $bp_familyship_request_html = '<div class="ab-notification-item">';
                        
                        $my_link = bp_core_get_user_domain(bp_loggedin_user_id());
                        $my_avatar = bp_core_fetch_avatar( array( 'item_id' => bp_loggedin_user_id(), 'width' => 40, 'height' => 40 ) );
                        $my_avatar_html = '<div class="notification avatar">'
                                                .'<a href="'
                                                .$my_link
                                                .'">'
                                                .$my_avatar
                                                .'</a></div>';
                        $bp_familyship_request_html .= $my_avatar_html.$message_html.$avatar_html.'</div>';
                        $bp_friendship_text = $bp_familyship_request_html;

			break;
	}

	// Return either an HTML link or an array, depending on the requested format
	if ( 'string' == $format ) {
		$return = apply_filters( $filter, $bp_friendship_text, (int) $total_items );
	} else {
		$return = apply_filters( $filter, array(
			//'link' => $link,
			'text' => $bp_friendship_text
		), (int) $total_items );
	}

	do_action( 'familys_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $return );

	return $return;
}
/**
 * Clear family-related notifications when ?new=1
 */
function familys_clear_family_notifications() {
	if ( isset( $_GET['new'] ) && bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->familys->id, 'familyship_accepted' );
	}
}
add_action( 'bp_activity_screen_my_activity', 'familys_clear_family_notifications' );

/**
 * Delete any familyship request notifications for the logged in user.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_familys_mark_familyship_request_notifications_by_type() {
	if ( isset( $_GET['new'] ) && bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->familys->id, 'familyship_request' );
	}
}
add_action( 'familys_screen_requests', 'bp_familys_mark_familyship_request_notifications_by_type' );

/**
 * Delete any familyship acceptance notifications for the logged in user.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_familys_mark_familyship_accepted_notifications_by_type() {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->familys->id, 'familyship_accepted' );
	}
}
add_action( 'familys_screen_my_familys', 'bp_familys_mark_familyship_accepted_notifications_by_type' );

/**
 * Notify one use that another user has requested their virtual familyship.
 *
 * @since BuddyPress (1.9.0)
 * @param int $familyship_id The unique ID of the familyship
 * @param int $initiator_user_id The familyship initiator user ID
 * @param int $family_user_id The familyship request reciever user ID
 */
function bp_familys_familyship_requested_notification( $familyship_id, $initiator_user_id, $family_user_id ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_add_notification( array(
			'user_id'           => $family_user_id,
			'item_id'           => $initiator_user_id,
			'secondary_item_id' => $familyship_id,
			'component_name'    => buddypress()->familys->id,
			'component_action'  => 'familyship_request',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );
	}
}
add_action( 'familys_familyship_requested', 'bp_familys_familyship_requested_notification', 10, 3 );

/**
 * Remove family request notice when a member rejects another members
 *
 * @since BuddyPress (1.9.0)
 *
 * @param int $familyship_id (not used)
 * @param object $familyship
 */
function bp_familys_mark_familyship_rejected_notifications_by_item_id( $familyship_id, $familyship ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_item_id( $familyship->family_user_id, $familyship->initiator_user_id, buddypress()->familys->id, 'familyship_request' );
	}
}
add_action( 'familys_familyship_rejected', 'bp_familys_mark_familyship_rejected_notifications_by_item_id', 10, 2 );

/**
 * Notify a member when another member accepts their virtual familyship request.
 *
 * @since BuddyPress (1.9.0)
 * @param int $familyship_id The unique ID of the familyship
 * @param int $initiator_user_id The familyship initiator user ID
 * @param int $family_user_id The familyship request reciever user ID
 */
function bp_familys_add_familyship_accepted_notification( $familyship_id, $initiator_user_id, $family_user_id ) {

	// Bail if notifications is not active
	if ( ! bp_is_active( 'notifications' ) ) {
		return;
	}

	// Remove the family request notice
	bp_notifications_mark_notifications_by_item_id( $family_user_id, $initiator_user_id, buddypress()->familys->id, 'familyship_request' );

	// Add a family accepted notice for the initiating user
	bp_notifications_add_notification(  array(
		'user_id'           => $initiator_user_id,
		'item_id'           => $family_user_id,
		'secondary_item_id' => $familyship_id,
		'component_name'    => buddypress()->familys->id,
		'component_action'  => 'familyship_accepted',
		'date_notified'     => bp_core_current_time(),
		'is_new'            => 1,
	) );
}
add_action( 'familys_familyship_accepted', 'bp_familys_add_familyship_accepted_notification', 10, 3 );

/**
 * Remove family request notice when a member withdraws their family request
 *
 * @since BuddyPress (1.9.0)
 *
 * @param int $familyship_id (not used)
 * @param object $familyship
 */
function bp_familys_mark_familyship_withdrawn_notifications_by_item_id( $familyship_id, $familyship ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_delete_notifications_by_item_id( $familyship->family_user_id, $familyship->initiator_user_id, buddypress()->familys->id, 'familyship_request' );
	}
}
add_action( 'familys_familyship_withdrawn', 'bp_familys_mark_familyship_withdrawn_notifications_by_item_id', 10, 2 );

/**
 * Remove familyship requests FROM user, used primarily when a user is deleted
 *
 * @since BuddyPress (1.9.0)
 * @param int $user_id
 */
function bp_familys_remove_notifications_data( $user_id = 0 ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_delete_notifications_from_user( $user_id, buddypress()->familys->id, 'familyship_request' );
	}
}
add_action( 'familys_remove_data', 'bp_familys_remove_notifications_data', 10, 1 );
