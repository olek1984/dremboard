<?php

/**
 * BuddyPress Friends Activity Functions
 *
 * These functions handle the recording, deleting and formatting of activity
 * for the user and for this specific component.
 *
 * @package BuddyPress
 * @subpackage FriendsActivity
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Emails ********************************************************************/

/**
 * Send notifications related to a new friendship request.
 *
 * When a friendship is requested, an email and a BP notification are sent to
 * the user of whom friendship has been requested ($friend_id).
 *
 * @param int $friendship_id ID of the friendship object.
 * @param int $initiator_id ID of the user who initiated the request.
 * @param int $friend_id ID of the request recipient.
 */
function friends_notification_new_request( $friendship_id, $initiator_id, $friend_id ) {

	$initiator_name = bp_core_get_user_displayname( $initiator_id );

	if ( 'no' == bp_get_user_meta( (int) $friend_id, 'notification_friends_friendship_request', true ) )
		return false;

	$ud                = get_userdata( $friend_id );
	$all_requests_link = bp_core_get_user_domain( $friend_id ) . bp_get_friends_slug() . '/requests/';
	$settings_slug     = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
	$settings_link     = trailingslashit( bp_core_get_user_domain( $friend_id ) .  $settings_slug . '/notifications' );
	$initiator_link    = bp_core_get_user_domain( $initiator_id );

	// Set up and send the message
	$to       = $ud->user_email;
	$subject  = bp_get_email_subject( array( 'text' => sprintf( __( 'New friendship request from %s', 'buddypress' ), $initiator_name ) ) );
	$message  = sprintf( __(
'%1$s wants to add you as a friend.

To view all of your pending friendship requests: %2$s

To view %3$s\'s profile: %4$s

---------------------
', 'buddypress' ), $initiator_name, $all_requests_link, $initiator_name, $initiator_link );

	// Only show the disable notifications line if the settings component is enabled
	if ( bp_is_active( 'settings' ) ) {
		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );
	}

	// Send the message
	$to      = apply_filters( 'friends_notification_new_request_to', $to );
	$subject = apply_filters( 'friends_notification_new_request_subject', $subject, $initiator_name );
	$message = apply_filters( 'friends_notification_new_request_message', $message, $initiator_name, $initiator_link, $all_requests_link, $settings_link );

	wp_mail( $to, $subject, $message );

	do_action( 'bp_friends_sent_request_email', $friend_id, $subject, $message, $friendship_id, $initiator_id );
}
add_action( 'friends_friendship_requested', 'friends_notification_new_request', 10, 3 );

/**
 * Send notifications related to the acceptance of a friendship request.
 *
 * When a friendship request is accepted, an email and a BP notification are
 * sent to the user who requested the friendship ($initiator_id).
 *
 * @param int $friendship_id ID of the friendship object.
 * @param int $initiator_id ID of the user who initiated the request.
 * @param int $friend_id ID of the request recipient.
 */
function friends_notification_accepted_request( $friendship_id, $initiator_id, $friend_id ) {

	$friend_name = bp_core_get_user_displayname( $friend_id );

	if ( 'no' == bp_get_user_meta( (int) $initiator_id, 'notification_friends_friendship_accepted', true ) )
		return false;

	$ud            = get_userdata( $initiator_id );
	$friend_link   = bp_core_get_user_domain( $friend_id );
	$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
	$settings_link = trailingslashit( bp_core_get_user_domain( $initiator_id ) . $settings_slug . '/notifications' );

	// Set up and send the message
	$to       = $ud->user_email;
	$subject  = bp_get_email_subject( array( 'text' => sprintf( __( '%s accepted your friendship request', 'buddypress' ), $friend_name ) ) );
	$message  = sprintf( __(
'%1$s accepted your friend request.

To view %2$s\'s profile: %3$s

---------------------
', 'buddypress' ), $friend_name, $friend_name, $friend_link );

	// Only show the disable notifications line if the settings component is enabled
	if ( bp_is_active( 'settings' ) ) {
		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );
	}

	// Send the message
	$to      = apply_filters( 'friends_notification_accepted_request_to', $to );
	$subject = apply_filters( 'friends_notification_accepted_request_subject', $subject, $friend_name );
	$message = apply_filters( 'friends_notification_accepted_request_message', $message, $friend_name, $friend_link, $settings_link );

	wp_mail( $to, $subject, $message );

	do_action( 'bp_friends_sent_accepted_email', $initiator_id, $subject, $message, $friendship_id, $friend_id );
}
add_action( 'friends_friendship_accepted', 'friends_notification_accepted_request', 10, 3 );

/** Notifications *************************************************************/

/**
 * Notification formatting callback for bp-friends notifications.
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
function friends_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

    $bp_friendship_text = '';
	switch ( $action ) {
		case 'friendship_accepted':
                        $friendship_accepted_avatar_html = '';
			$link = trailingslashit( bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends' );

                        $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                        $empty_avatar_html = '<div class="notification avatar empty">'
                                                    .'<a href="'
                                                    .$link
                                                    .'">'
                                                    .$avatar
                                                    .'</a></div>';
			// Set up the string and the filter
			if ( (int) $total_items > 1 ) {
				$user_display_name = bp_core_get_user_displayname( $item_id );
				$text = sprintf( __('%s and %d friends accepted your friendship requests', 'buddypress' ),  $user_display_name, (int) $total_items - 1);
				$filter = 'bp_friends_multiple_friendship_accepted_notification';
                                $user_link = bp_core_get_user_domain($item_id);
                                $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                                $friendship_accepted_avatar_html = '<div class="notification avatar">'
                                                            .'<a href="'
                                                            .$user_link
                                                            .'">'
                                                            .$avatar
                                                            .'</a></div>';
			} else {
                                $user_display_name = bp_core_get_user_displayname( $item_id );
                                $text = sprintf( __( '%s accepted your friendship request', 'buddypress' ),  $user_display_name );
				$filter = 'bp_friends_single_friendship_accepted_notification';
                                $user_link = bp_core_get_user_domain($item_id);
                                $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                                $friendship_accepted_avatar_html = '<div class="notification avatar">'
                                                            .'<a href="'
                                                            .$user_link
                                                            .'">'
                                                            .$avatar
                                                            .'</a></div>';
			}
                        
                        $friendship_accepted_message_html = '<div class="notification message">'.'<a class="" href="'.$link.'">'.$text.'</a></div>';
                        
                        $bp_friendship_accepted_html = '<div class="ab-notification-item">';
			$bp_friendship_accepted_html .= $friendship_accepted_avatar_html.$friendship_accepted_message_html.$empty_avatar_html.'</div>';
                        $bp_friendship_text = $bp_friendship_accepted_html;
                        break;

		case 'friendship_request':
                        $friendship_request_avatar_html = '';
                    
			$link = bp_loggedin_user_domain() . bp_get_friends_slug() . '/requests/?new';

                        $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                        $empty_avatar_html = '<div class="notification avatar empty">'
                                                    .'<a href="'
                                                    .$link
                                                    .'">'
                                                    .$avatar
                                                    .'</a></div>';

			// Set up the string and the filter
			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( 'You have %d pending friendship requests', 'buddypress' ), (int) $total_items );
				$filter = 'bp_friends_multiple_friendship_request_notification';
                                $friendship_request_avatar_html = $empty_avatar_html;
			} else {
                                $user_display_name = bp_core_get_user_displayname( $item_id );
                            
				$text = sprintf( __( 'You have a friendship request from %s', 'buddypress' ),  $user_display_name );
				$filter = 'bp_friends_single_friendship_request_notification';

                                $user_link = bp_core_get_user_domain($item_id);
                                $avatar = bp_core_fetch_avatar( array( 'item_id' => $item_id, 'width' => 40, 'height' => 40 ) );
                                $friendship_request_avatar_html = '<div class="notification avatar">'
                                                            .'<a href="'
                                                            .$user_link
                                                            .'">'
                                                            .$avatar
                                                            .'</a></div>';
			}
                        
                        $friendship_request_message_html = '<div class="notification message">'.'<a class="" href="'.$link.'">'.$text.'</a></div>';
                        $bp_friendship_request_html = '<div class="ab-notification-item">';

                        $my_link = bp_core_get_user_domain(bp_loggedin_user_id());
                        $my_avatar = bp_core_fetch_avatar( array( 'item_id' => bp_loggedin_user_id(), 'width' => 40, 'height' => 40 ) );
                        $my_avatar_html = '<div class="notification avatar">'
                                                .'<a href="'
                                                .$my_link
                                                .'">'
                                                .$my_avatar
                                                .'</a></div>';
			
                        $bp_friendship_request_html .= $my_avatar_html.$friendship_request_message_html.$friendship_request_avatar_html.'</div>';
                        $bp_friendship_text = $bp_friendship_request_html;
                        break;
	}
	// Return either an HTML link or an array, depending on the requested format
	if ( 'string' == $format ) {
		//$return = apply_filters( $filter, '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>', (int) $total_items );
                $return = apply_filters( $filter, $bp_friendship_text, (int) $total_items );
	} else {
		$return = apply_filters( $filter, array(
			//'link' => $link,
			//'text' => $text
                        'text'=> $bp_friendship_text
		), (int) $total_items );
	}

	do_action( 'friends_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $return );

	return $return;
}

/**
 * Clear friend-related notifications when ?new=1
 */
function friends_clear_friend_notifications() {
	if ( isset( $_GET['new'] ) && bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->friends->id, 'friendship_accepted' );
	}
}
add_action( 'bp_activity_screen_my_activity', 'friends_clear_friend_notifications' );

/**
 * Delete any friendship request notifications for the logged in user.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_friends_mark_friendship_request_notifications_by_type() {
	if ( isset( $_GET['new'] ) && bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->friends->id, 'friendship_request' );
	}
}
add_action( 'friends_screen_requests', 'bp_friends_mark_friendship_request_notifications_by_type' );

/**
 * Delete any friendship acceptance notifications for the logged in user.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_friends_mark_friendship_accepted_notifications_by_type() {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->friends->id, 'friendship_accepted' );
	}
}
add_action( 'friends_screen_my_friends', 'bp_friends_mark_friendship_accepted_notifications_by_type' );

/**
 * Notify one use that another user has requested their virtual friendship.
 *
 * @since BuddyPress (1.9.0)
 * @param int $friendship_id The unique ID of the friendship
 * @param int $initiator_user_id The friendship initiator user ID
 * @param int $friend_user_id The friendship request reciever user ID
 */
function bp_friends_friendship_requested_notification( $friendship_id, $initiator_user_id, $friend_user_id ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_add_notification( array(
			'user_id'           => $friend_user_id,
			'item_id'           => $initiator_user_id,
			'secondary_item_id' => $friendship_id,
			'component_name'    => buddypress()->friends->id,
			'component_action'  => 'friendship_request',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );
	}
}
add_action( 'friends_friendship_requested', 'bp_friends_friendship_requested_notification', 10, 3 );

/**
 * Remove friend request notice when a member rejects another members
 *
 * @since BuddyPress (1.9.0)
 *
 * @param int $friendship_id (not used)
 * @param object $friendship
 */
function bp_friends_mark_friendship_rejected_notifications_by_item_id( $friendship_id, $friendship ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_item_id( $friendship->friend_user_id, $friendship->initiator_user_id, buddypress()->friends->id, 'friendship_request' );
	}
}
add_action( 'friends_friendship_rejected', 'bp_friends_mark_friendship_rejected_notifications_by_item_id', 10, 2 );

/**
 * Notify a member when another member accepts their virtual friendship request.
 *
 * @since BuddyPress (1.9.0)
 * @param int $friendship_id The unique ID of the friendship
 * @param int $initiator_user_id The friendship initiator user ID
 * @param int $friend_user_id The friendship request reciever user ID
 */
function bp_friends_add_friendship_accepted_notification( $friendship_id, $initiator_user_id, $friend_user_id ) {

	// Bail if notifications is not active
	if ( ! bp_is_active( 'notifications' ) ) {
		return;
	}

	// Remove the friend request notice
	bp_notifications_mark_notifications_by_item_id( $friend_user_id, $initiator_user_id, buddypress()->friends->id, 'friendship_request' );

	// Add a friend accepted notice for the initiating user
	bp_notifications_add_notification(  array(
		'user_id'           => $initiator_user_id,
		'item_id'           => $friend_user_id,
		'secondary_item_id' => $friendship_id,
		'component_name'    => buddypress()->friends->id,
		'component_action'  => 'friendship_accepted',
		'date_notified'     => bp_core_current_time(),
		'is_new'            => 1,
	) );
}
add_action( 'friends_friendship_accepted', 'bp_friends_add_friendship_accepted_notification', 10, 3 );

/**
 * Remove friend request notice when a member withdraws their friend request
 *
 * @since BuddyPress (1.9.0)
 *
 * @param int $friendship_id (not used)
 * @param object $friendship
 */
function bp_friends_mark_friendship_withdrawn_notifications_by_item_id( $friendship_id, $friendship ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_delete_notifications_by_item_id( $friendship->friend_user_id, $friendship->initiator_user_id, buddypress()->friends->id, 'friendship_request' );
	}
}
add_action( 'friends_friendship_withdrawn', 'bp_friends_mark_friendship_withdrawn_notifications_by_item_id', 10, 2 );

/**
 * Remove friendship requests FROM user, used primarily when a user is deleted
 *
 * @since BuddyPress (1.9.0)
 * @param int $user_id
 */
function bp_friends_remove_notifications_data( $user_id = 0 ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_delete_notifications_from_user( $user_id, buddypress()->friends->id, 'friendship_request' );
	}
}
add_action( 'friends_remove_data', 'bp_friends_remove_notifications_data', 10, 1 );
