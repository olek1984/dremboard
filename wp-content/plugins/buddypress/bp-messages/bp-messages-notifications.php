<?php

/**
 * BuddyPress Messages Notifications
 *
 * @package BuddyPress
 * @subpackage MessagesNotifications
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Email *********************************************************************/

/**
 * Email message recipients to alert them of a new unread private message
 *
 * @since BuddyPress (1.0)
 * @param array $raw_args
 */
function messages_notification_new_message( $raw_args = array() ) {

	// Cast possible $message object as an array
	if ( is_object( $raw_args ) ) {
		$args = (array) $raw_args;
	} else {
		$args = $raw_args;
	}

	// These should be extracted below
	$recipients    = array();
	$email_subject = $email_content = '';
	$sender_id     = 0;

	// Barf
	extract( $args );

	// Get the sender display name
	$sender_name = bp_core_get_user_displayname( $sender_id );

	// Bail if no recipients
	if ( ! empty( $recipients ) ) {

		foreach ( $recipients as $recipient ) {

			if ( $sender_id == $recipient->user_id || 'no' == bp_get_user_meta( $recipient->user_id, 'notification_messages_new_message', true ) ) {
				continue;
			}

			// User data and links
			$ud = get_userdata( $recipient->user_id );

			// Bail if user cannot be found
			if ( empty( $ud ) ) {
				continue;
			}

			$message_link  = bp_core_get_user_domain( $recipient->user_id ) . bp_get_messages_slug() .'/';
			$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
			$settings_link = bp_core_get_user_domain( $recipient->user_id ) . $settings_slug . '/notifications/';

			// Sender info
			$sender_name   = stripslashes( $sender_name );
			$subject       = stripslashes( wp_filter_kses( $subject ) );
			$content       = stripslashes( wp_filter_kses( $message ) );

			// Set up and send the message
			$email_to      = $ud->user_email;
			$email_subject = bp_get_email_subject( array( 'text' => sprintf( __( 'New message from %s', 'buddypress' ), $sender_name ) ) );

			$email_content = sprintf( __(
'%1$s sent you a new message:

Subject: %2$s

"%3$s"

To view and read your messages please log in and visit: %4$s

---------------------
', 'buddypress' ), $sender_name, $subject, $content, $message_link );

			// Only show the disable notifications line if the settings component is enabled
			if ( bp_is_active( 'settings' ) ) {
				$email_content .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );
			}

			// Send the message
			$email_to      = apply_filters( 'messages_notification_new_message_to',      $email_to );
			$email_subject = apply_filters( 'messages_notification_new_message_subject', $email_subject, $sender_name );
			$email_content = apply_filters( 'messages_notification_new_message_message', $email_content, $sender_name, $subject, $content, $message_link, $settings_link );

			wp_mail( $email_to, $email_subject, $email_content );
		}
	}

	do_action( 'bp_messages_sent_notification_email', $recipients, $email_subject, $email_content, $args );
}
add_action( 'messages_message_sent', 'messages_notification_new_message', 10 );

/** Notifications *************************************************************/

/**
 * Format the BuddyBar/Toolbar notifications for the Messages component
 *
 * @since BuddyPress (1.0)
 * @param string $action The kind of notification being rendered
 * @param int $item_id The primary item id
 * @param int $secondary_item_id The secondary item id
 * @param int $total_items The total number of messaging-related notifications waiting for the user
 * @param string $format 'string' for BuddyBar-compatible notifications; 'array' for WP Toolbar
 */
function messages_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

        $messages_avatar_html = '';
	if ( 'new_message' === $action ) {
		$link  = trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() . '/inbox' );
		$title = __( 'Inbox', 'buddypress' );
                
                $avatar = bp_core_fetch_avatar( array( 'item_id' => $secondary_item_id, 'width' => 40, 'height' => 40 ) );
                $empty_avatar_html = '<div class="notification avatar empty">'
                                                    .'<a href="'
                                                    .$link
                                                    .'">'
                                                    .$avatar
                                                    .'</a></div>';
		if ( (int) $total_items > 1 ) {
			$text   = sprintf( __('You have %d new messages', 'buddypress' ), (int) $total_items );
			$filter = 'bp_messages_multiple_new_message_notification';
                        $messages_avatar_html = $empty_avatar_html;
		} else {
			if ( !empty( $secondary_item_id ) ) {
				$text = sprintf( __('You have %d new message from <span class="username">%s</span>', 'buddypress' ), (int) $total_items, bp_core_get_user_displayname( $secondary_item_id ) );

                                $user_link = bp_core_get_user_domain($secondary_item_id);
                                $avatar = bp_core_fetch_avatar( array( 'item_id' => $secondary_item_id, 'width' => 40, 'height' => 40 ) );
                                $messages_avatar_html = '<div class="notification avatar">'
                                                            .'<a href="'
                                                            .$user_link
                                                            .'">'
                                                            .$avatar
                                                            .'</a></div>';
			} else {
				$text = sprintf( __('You have %d new message',         'buddypress' ), (int) $total_items );
			}
			$filter = 'bp_messages_single_new_message_notification';
		}
                $messages_message_html = '<div class="notification message">'.'<a class="" href="'.$link.'">'.$text.'</a></div>';


                $bp_messages_html = '<div class="ab-notification-item" >';

                $my_link = bp_core_get_user_domain(bp_loggedin_user_id());
                $my_avatar = bp_core_fetch_avatar( array( 'item_id' => bp_loggedin_user_id(), 'width' => 40, 'height' => 40 ) );
                $my_avatar_html = '<div class="notification avatar">'
                                        .'<a href="'
                                        .$my_link
                                        .'">'
                                        .$my_avatar
                                        .'</a></div>';

                $bp_messages_html .= $my_avatar_html.$messages_message_html.$messages_avatar_html.'</div>';
	}

	if ( 'string' === $format ) {
		//$return = apply_filters( $filter, '<a href="' . esc_url( $link ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $text ) . '</a>', (int) $total_items, $text, $link, $item_id, $secondary_item_id );
                $return = apply_filters( $filter, $bp_messages_html, (int) $total_items );
	} else {
//		$return = apply_filters( $filter, array(
//			'text' => $text,
//			'link' => $link
//		), $link, (int) $total_items, $text, $link, $item_id, $secondary_item_id );
            $return = apply_filters( $filter, array(
			'text' => $bp_messages_html,
		), (int) $total_items);
	}

	do_action( 'messages_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return $return;
}

/**
 * Send notifications to message recipients
 *
 * @since BuddyPress (1.9.0)
 * @param obj $message
 */
function bp_messages_message_sent_add_notification( $message ) {
	if ( bp_is_active( 'notifications' ) && ! empty( $message->recipients ) ) {
		foreach ( (array) $message->recipients as $recipient ) {
			bp_notifications_add_notification( array(
				'user_id'           => $recipient->user_id,
				'item_id'           => $message->id,
				'secondary_item_id' => $message->sender_id,
				'component_name'    => buddypress()->messages->id,
				'component_action'  => 'new_message',
				'date_notified'     => bp_core_current_time(),
				'is_new'            => 1,
			) );
		}
	}
}
add_action( 'messages_message_sent', 'bp_messages_message_sent_add_notification', 10 );

/**
 * Mark new message notifications when member views their inbox.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_messages_screen_inbox_mark_notifications() {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->messages->id, 'new_message' );
	}
}
add_action( 'messages_screen_inbox', 'bp_messages_screen_inbox_mark_notifications', 10 );

/**
 * Mark new message notification when member reads a message thread directly.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_messages_screen_conversation_mark_notifications() {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), (int) bp_action_variable( 0 ), buddypress()->messages->id, 'new_message' );
	}
}
add_action( 'messages_screen_conversation', 'bp_messages_screen_inbox_mark_notifications', 10 );
