<?php

/**
 * BuddyPress Activity Notifications.
 *
 * @package BuddyPress
 * @subpackage ActivityNotifications
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Emails *********************************************************************/

/**
 * Send email and BP notifications when a user is mentioned in an update.
 *
 * @since BuddyPress (1.2)
 *
 * @uses bp_notifications_add_notification()
 * @uses bp_get_user_meta()
 * @uses bp_core_get_user_displayname()
 * @uses bp_activity_get_permalink()
 * @uses bp_core_get_user_domain()
 * @uses bp_get_settings_slug()
 * @uses bp_activity_filter_kses()
 * @uses bp_core_get_core_userdata()
 * @uses wp_specialchars_decode()
 * @uses get_blog_option()
 * @uses bp_is_active()
 * @uses bp_is_group()
 * @uses bp_get_current_group_name()
 * @uses apply_filters() To call the 'bp_activity_at_message_notification_to' hook.
 * @uses apply_filters() To call the 'bp_activity_at_message_notification_subject' hook.
 * @uses apply_filters() To call the 'bp_activity_at_message_notification_message' hook.
 * @uses wp_mail()
 * @uses do_action() To call the 'bp_activity_sent_mention_email' hook
 *
 * @param int $activity_id The ID of the activity update.
 * @param int $receiver_user_id The ID of the user who is receiving the update.
 */
function bp_activity_at_message_notification( $activity_id, $receiver_user_id ) {

	// Don't leave multiple notifications for the same activity item
	$notifications = BP_Core_Notification::get_all_for_user( $receiver_user_id, 'all' );

	foreach( $notifications as $notification ) {
		if ( $activity_id == $notification->item_id ) {
			return;
		}
	}

	$activity = new BP_Activity_Activity( $activity_id );

	$subject = '';
	$message = '';
	$content = '';

	// Now email the user with the contents of the message (if they have enabled email notifications)
	if ( 'no' != bp_get_user_meta( $receiver_user_id, 'notification_activity_new_mention', true ) ) {
		$poster_name = bp_core_get_user_displayname( $activity->user_id );

		$message_link  = bp_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
		$settings_link = bp_core_get_user_domain( $receiver_user_id ) . $settings_slug . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = bp_activity_filter_kses( strip_tags( stripslashes( $activity->content ) ) );

		// Set up and send the message
		$ud       = bp_core_get_core_userdata( $receiver_user_id );
		$to       = $ud->user_email;
		$subject  = bp_get_email_subject( array( 'text' => sprintf( __( '%s mentioned you in an update', 'buddypress' ), $poster_name ) ) );

		if ( bp_is_active( 'groups' ) && bp_is_group() ) {
			$message = sprintf( __(
'<p>%1$s mentioned you in the group "%2$s"</p>:

<div style="margin: 20px; padding: 10px; border-left: 1px solid #e4e4e4;">%3$s</div>

<p>To view and respond to the message, log in and visit: </p><p>%4$s</p>

<p>---------------------</p>
', 'buddypress' ), $poster_name, bp_get_current_group_name(), $content, $message_link );
		} else {
			$message = sprintf( __(
'<p>%1$s mentioned you in an update:</p>

<div style="margin: 20px; padding: 10px; border-left: 1px solid #e4e4e4;">%2$s</div>

<p>To view and respond to the message, log in and visit: </p><p>%3$s</p>

<p>---------------------</p>
', 'buddypress' ), $poster_name, $content, $message_link );
		}

		// Only show the disable notifications line if the settings component is enabled
		if ( bp_is_active( 'settings' ) ) {
			$message .= sprintf( __( '<p>To disable these notifications please log in and go to: </p><p>%s</p>', 'buddypress' ), $settings_link );
		}

		// Send the message
		$to 	 = apply_filters( 'bp_activity_at_message_notification_to', $to );
		$subject = apply_filters( 'bp_activity_at_message_notification_subject', $subject, $poster_name );
		$message = apply_filters( 'bp_activity_at_message_notification_message', $message, $poster_name, $content, $message_link, $settings_link );

		wp_mail( $to, $subject, $message );
	}

	do_action( 'bp_activity_sent_mention_email', $activity, $subject, $message, $content, $receiver_user_id );
}

function bp_activity_at_friend_share_notification( $activity_id, $receiver_user_id ) {

	// Don't leave multiple notifications for the same activity item
	$notifications = BP_Core_Notification::get_all_for_user( $receiver_user_id, 'all' );

	foreach( $notifications as $notification ) {
		if ( $activity_id == $notification->item_id ) {
			return;
		}
	}

	$activity = new BP_Activity_Activity( $activity_id );

	$subject = '';
	$message = '';
	$content = '';

	// Now email the user with the contents of the message (if they have enabled email notifications)
	if ( 'no' != bp_get_user_meta( $receiver_user_id, 'notification_activity_new_share', true ) ) {
		$poster_name = bp_core_get_user_displayname( $activity->user_id );

		$message_link  = bp_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
		$settings_link = bp_core_get_user_domain( $receiver_user_id ) . $settings_slug . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = bp_activity_filter_kses( strip_tags( stripslashes( $activity->content ) ) );

		// Set up and send the message
		$ud       = bp_core_get_core_userdata( $receiver_user_id );
		$to       = $ud->user_email;
		$subject  = bp_get_email_subject( array( 'text' => sprintf( __( '%s shared for you in an update', 'buddypress' ), $poster_name ) ) );

		if ( bp_is_active( 'groups' ) && bp_is_group() ) {
			$message = sprintf( __(
'<p>%1$s shared for you in the group "%2$s"</p>:

<div style="margin: 20px; padding: 10px; border-left: 1px solid #e4e4e4;">%3$s</div>

<p>To view and respond to the message, log in and visit: </p><p>%4$s</p>

<p>---------------------</p>
', 'buddypress' ), $poster_name, bp_get_current_group_name(), $content, $message_link );
		} else {
			$message = sprintf( __(
'<p>%1$s shared for you in an update:</p>

<div style="margin: 20px; padding: 10px; border-left: 1px solid #e4e4e4;">%2$s</div>

<p>To view and respond to the message, log in and visit: </p><p>%3$s</p>

<p>---------------------</p>
', 'buddypress' ), $poster_name, $content, $message_link );
		}

		// Only show the disable notifications line if the settings component is enabled
		if ( bp_is_active( 'settings' ) ) {
			$message .= sprintf( __( '<p>To disable these notifications please log in and go to: </p><p>%s</p>', 'buddypress' ), $settings_link );
		}

		// Send the message
		$to 	 = apply_filters( 'bp_activity_share_for_friend_to', $to );
		$subject = apply_filters( 'bp_activity_share_for_friend_subject', $subject, $poster_name );
		$message = apply_filters( 'bp_activity_share_for_friend_message', $message, $poster_name, $content, $message_link, $settings_link );

		wp_mail( $to, $subject, $message );
	}

	do_action( 'bp_activity_share_for_friend_email', $activity, $subject, $message, $content, $receiver_user_id );
}

/**
 * Send email and BP notifications when an activity item receives a comment.
 *
 * @since BuddyPress (1.2)
 *
 * @uses bp_get_user_meta()
 * @uses bp_core_get_user_displayname()
 * @uses bp_activity_get_permalink()
 * @uses bp_core_get_user_domain()
 * @uses bp_get_settings_slug()
 * @uses bp_activity_filter_kses()
 * @uses bp_core_get_core_userdata()
 * @uses wp_specialchars_decode()
 * @uses get_blog_option()
 * @uses bp_get_root_blog_id()
 * @uses apply_filters() To call the 'bp_activity_new_comment_notification_to' hook
 * @uses apply_filters() To call the 'bp_activity_new_comment_notification_subject' hook
 * @uses apply_filters() To call the 'bp_activity_new_comment_notification_message' hook
 * @uses wp_mail()
 * @uses do_action() To call the 'bp_activity_sent_reply_to_update_email' hook
 * @uses apply_filters() To call the 'bp_activity_new_comment_notification_comment_author_to' hook
 * @uses apply_filters() To call the 'bp_activity_new_comment_notification_comment_author_subject' hook
 * @uses apply_filters() To call the 'bp_activity_new_comment_notification_comment_author_message' hook
 * @uses do_action() To call the 'bp_activity_sent_reply_to_reply_email' hook
 *
 * @param int $comment_id The comment id.
 * @param int $commenter_id The ID of the user who posted the comment.
 * @param array $params {@link bp_activity_new_comment()}
 */
function bp_activity_new_comment_notification( $comment_id = 0, $commenter_id = 0, $params = array() ) {

	// Set some default parameters
	$activity_id = 0;
	$parent_id   = 0;

	extract( $params );

	$original_activity = new BP_Activity_Activity( $activity_id );

	if ( $original_activity->user_id != $commenter_id && 'no' != bp_get_user_meta( $original_activity->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name   = bp_core_get_user_displayname( $commenter_id );
		$thread_link   = bp_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
		$settings_link = bp_core_get_user_domain( $original_activity->user_id ) . $settings_slug . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = bp_activity_filter_kses( stripslashes($content) );

		// Set up and send the message
		$ud      = bp_core_get_core_userdata( $original_activity->user_id );
		$to      = $ud->user_email;
		$subject = bp_get_email_subject( array( 'text' => sprintf( __( '%s replied to one of your updates', 'buddypress' ), $poster_name ) ) );
		$message = sprintf( __(
'<p>%1$s replied to one of your updates:</p>

<div style="margin: 20px; padding: 10px; border-left: 1px solid #e4e4e4;">%2$s</div>

<p>To view your original update and all comments, log in and visit: </p><p>%3$s</p>

<p>---------------------</p>
', 'buddypress' ), $poster_name, $content, $thread_link );

		// Only show the disable notifications line if the settings component is enabled
		if ( bp_is_active( 'settings' ) ) {
			$message .= sprintf( __( '<p>To disable these notifications please log in and go to: </p><p>%s</p>', 'buddypress' ), $settings_link );
		}

		/* Send the message */
		$to = apply_filters( 'bp_activity_new_comment_notification_to', $to );
		$subject = apply_filters( 'bp_activity_new_comment_notification_subject', $subject, $poster_name );
		$message = apply_filters( 'bp_activity_new_comment_notification_message', $message, $poster_name, $content, $thread_link, $settings_link );

		wp_mail( $to, $subject, $message );

		do_action( 'bp_activity_sent_reply_to_update_email', $original_activity->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}

	/***
	 * If this is a reply to another comment, send an email notification to the
	 * author of the immediate parent comment.
	 */
	if ( empty( $parent_id ) || ( $activity_id == $parent_id ) ) {
		return false;
	}

	$parent_comment = new BP_Activity_Activity( $parent_id );

	if ( $parent_comment->user_id != $commenter_id && $original_activity->user_id != $parent_comment->user_id && 'no' != bp_get_user_meta( $parent_comment->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name   = bp_core_get_user_displayname( $commenter_id );
		$thread_link   = bp_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
		$settings_link = bp_core_get_user_domain( $parent_comment->user_id ) . $settings_slug . '/notifications/';

		// Set up and send the message
		$ud       = bp_core_get_core_userdata( $parent_comment->user_id );
		$to       = $ud->user_email;
		$subject = bp_get_email_subject( array( 'text' => sprintf( __( '%s replied to one of your comments', 'buddypress' ), $poster_name ) ) );

		$poster_name = stripslashes( $poster_name );
		$content = bp_activity_filter_kses( stripslashes( $content ) );

$message = sprintf( __(
'<p>%1$s replied to one of your comments:</p>

<div style="margin: 20px; padding: 10px; border-left: 1px solid #e4e4e4;">%2$s</div>

<p>To view the original activity, your comment and all replies, log in and visit: </p><p>%3$s</p>

<p>---------------------</p>
', 'buddypress' ), $poster_name, $content, $thread_link );

		// Only show the disable notifications line if the settings component is enabled
		if ( bp_is_active( 'settings' ) ) {
			$message .= sprintf( __( '<p>To disable these notifications please log in and go to: </p><p>%s</p>', 'buddypress' ), $settings_link );
		}

		/* Send the message */
		$to = apply_filters( 'bp_activity_new_comment_notification_comment_author_to', $to );
		$subject = apply_filters( 'bp_activity_new_comment_notification_comment_author_subject', $subject, $poster_name );
		$message = apply_filters( 'bp_activity_new_comment_notification_comment_author_message', $message, $poster_name, $content, $settings_link, $thread_link );

		wp_mail( $to, $subject, $message );

		do_action( 'bp_activity_sent_reply_to_reply_email', $original_activity->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}
}

/**
 * Helper method to map action arguments to function parameters
 *
 * @since BuddyPress (1.9.0)
 * @param int $comment_id
 * @param array $params
 */
function bp_activity_new_comment_notification_helper( $comment_id, $params ) {
	bp_activity_new_comment_notification( $comment_id, $params['user_id'], $params );
}
add_action( 'bp_activity_comment_posted', 'bp_activity_new_comment_notification_helper', 10, 2 );

/** Notifications *************************************************************/

/**
 * Format notifications related to activity.
 *
 * @since BuddyPress (1.5)
 *
 * @uses bp_loggedin_user_domain()
 * @uses bp_get_activity_slug()
 * @uses bp_core_get_user_displayname()
 * @uses apply_filters() To call the 'bp_activity_multiple_at_mentions_notification' hook.
 * @uses apply_filters() To call the 'bp_activity_single_at_mentions_notification' hook.
 * @uses do_action() To call 'activity_format_notifications' hook.
 *
 * @param string $action The type of activity item. Just 'new_at_mention' for now.
 * @param int $item_id The activity ID.
 * @param int $secondary_item_id In the case of at-mentions, this is the mentioner's ID.
 * @param int $total_items The total number of notifications to format.
 * @param string $format 'string' to get a BuddyBar-compatible notification, 'array' otherwise.
 * @return string $return Formatted @mention notification.
 */
function bp_activity_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	switch ( $action ) {
		case 'new_at_mention':
			$activity_id      = $item_id;
			$poster_user_id   = $secondary_item_id;
			$at_mention_link  = bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/';
			$at_mention_title = sprintf( __( '@%s Mentions', 'buddypress' ), bp_get_loggedin_user_username() );

			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( 'You have %1$d new mentions', 'buddypress' ), (int) $total_items );
				$filter = 'bp_activity_multiple_at_mentions_notification';
			} else {
				$user_fullname = bp_core_get_user_displayname( $poster_user_id );
				$text =  sprintf( __( '%1$s mentioned you', 'buddypress' ), $user_fullname );
				$filter = 'bp_activity_single_at_mentions_notification';
			}
			if ( 'string' == $format ) {
				$return = apply_filters( $filter, '<a href="' . esc_url( $at_mention_link ) . '" title="' . esc_attr( $at_mention_title ) . '">' . esc_html( $text ) . '</a>', $at_mention_link, (int) $total_items, $activity_id, $poster_user_id );
				} else {
				$return = apply_filters( $filter, array(
				'text' => $text,
				'link' => $at_mention_link
				), $at_mention_link, (int) $total_items, $activity_id, $poster_user_id );
				}
		break;
		case 'share_for_friend':
			$activity_id      = $item_id;
			$poster_user_id   = $secondary_item_id;
			$at_friend_share_link  = bp_loggedin_user_domain() . bp_get_activity_slug() . '/friends/';
			$at_friend_share_title = sprintf( __( '@%s Have Shared', 'buddypress' ), bp_get_loggedin_user_username() );

			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( 'You have %1$d new shared for you', 'buddypress' ), (int) $total_items );
				$filter = 'bp_activity_multiple_share_for_you_notification';
			} else {
				$user_fullname = bp_core_get_user_displayname( $poster_user_id );
				$text =  sprintf( __( '%1$s shared for you', 'buddypress' ), $user_fullname );
				$filter = 'bp_activity_single_share_for_you_notification';
			}
			if ( 'string' == $format ) {
				$return = apply_filters( $filter, '<a href="' . esc_url( $at_friend_share_link ) . '" title="' . esc_attr( $at_friend_share_title ) . '">' . esc_html( $text ) . '</a>', $at_friend_share_link, (int) $total_items, $activity_id, $poster_user_id );
			} else {
				$return = apply_filters( $filter, array(
				'text' => $text,
				'link' => $at_friend_share_link
				), $at_friend_share_link, (int) $total_items, $activity_id, $poster_user_id );
			}
		break;
	}

	do_action( 'activity_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return $return;
}

/**
 * Notify a member when their nicename is mentioned in an activity stream item.
 *
 * Hooked to the 'bp_activity_sent_mention_email' action, we piggy back off the
 * existing email code for now, since it does the heavy lifting for us. In the
 * future when we separate emails from Notifications, this will need its own
 * 'bp_activity_at_name_send_emails' equivalent helper function.
 *
 * @since BuddyPress (1.9.0)
 *
 * @param obj $activity
 * @param string $subject (not used)
 * @param string $message (not used)
 * @param string $content (not used)
 * @param int $receiver_user_id
 */
function bp_activity_at_mention_add_notification( $activity, $subject, $message, $content, $receiver_user_id ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_add_notification( array(
			'user_id'           => $receiver_user_id,
			'item_id'           => $activity->id,
			'secondary_item_id' => $activity->user_id,
			'component_name'    => buddypress()->activity->id,
			'component_action'  => 'new_at_mention',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );
	}
}
add_action( 'bp_activity_sent_mention_email', 'bp_activity_at_mention_add_notification', 10, 5 );

function bp_activity_at_share_add_notification( $activity, $subject, $message, $content, $receiver_user_id ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_add_notification( array(
			'user_id'           => $receiver_user_id,
			'item_id'           => $activity->id,
			'secondary_item_id' => $activity->user_id,
			'component_name'    => buddypress()->activity->id,
			'component_action'  => 'share_for_friend',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );
	}
}
add_action( 'bp_activity_share_for_friend_email', 'bp_activity_at_share_add_notification', 10, 5 );

/**
 * Remove activity notifications when a user clicks on them.
 *
 * @since BuddyPress (1.5)
 *
 * @uses bp_notifications_mark_all_notifications_by_type()
 */
function bp_activity_remove_screen_notifications() {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->activity->id, 'new_at_mention' );
	}
}
add_action( 'bp_activity_screen_my_activity',               'bp_activity_remove_screen_notifications' );
//add_action( 'bp_activity_screen_single_activity_permalink', 'bp_activity_remove_screen_notifications' );
add_action( 'bp_activity_screen_mentions',                  'bp_activity_remove_screen_notifications' );
add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));

//////////////////  send mail for activity flag and copy right.  //////////////////
/*
 *  send to flag@Dremborad.com with flag .
 */

function bp_activity_flag_notification($activity_id, $send_user_id, $flag_content) {
    $activity = new BP_Activity_Activity($activity_id);
    $receiver_user_id = username_exists('flag');
    if (true || $receiver_user_id) {
        $subject = '';
        $message = '';
        $content = '';

        $poster_name = bp_core_get_user_displayname($activity->user_id);
        $poster_name = stripslashes($poster_name);
        
        $flager_name = bp_core_get_user_displayname($send_user_id);
        $flager_name = stripslashes($flager_name);
        
        $message_link = bp_activity_get_permalink($activity_id);
        
        $content = bp_activity_filter_kses(strip_tags(stripslashes($activity->content)));

        // Set up and send the message
        //$ud = bp_core_get_core_userdata($receiver_user_id);
        $flag_mail_address = get_option( 'bp-flag-mail-address', '' );
        $to       = $flag_mail_address;
        $subject  = bp_get_email_subject( array( 'text' => sprintf( __( '%1$s flaged for this drēm as "%2$s"', 'buddypress' ), $flager_name, $flag_content) ) );
        $message = sprintf(__(
                        '<p>%1$s flaged for %2$s\'s drēm as "%3$s":</p>

<div style="margin: 20px; padding: 10px; border-left: 1px solid #e4e4e4;">%4$s</div>

<p>To view and respond to the message, log in and visit: </p><p>%5$s</p>

', 'buddypress'), $flager_name, $poster_name, $flag_content, $content, $message_link);
        // Send the message
		wp_mail( $to, $subject, $message );
    }
}
