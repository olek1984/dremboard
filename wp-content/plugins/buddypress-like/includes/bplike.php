<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !defined( 'BP_LIKE_VERSION' ) ) {
    define( 'BP_LIKE_VERSION' , '0.1.7' );
}

if ( !defined( 'BP_LIKE_DB_VERSION' ) ) {
    define( 'BP_LIKE_DB_VERSION' , '21' );
}

if ( !defined( 'BPLIKE_PATH' ) ) {
    define( 'BPLIKE_PATH' , plugin_dir_path( dirname( __FILE__ ) ) );
}

load_plugin_textdomain( 'buddypress-like' , false , BPLIKE_PATH . '/languages/' );

/**
 * bp_like_get_text()
 *
 * Returns a custom text string from the database
 *
 */
function bp_like_get_text( $text = false , $type = 'custom' ) {

    $settings = get_site_option( 'bp_like_settings' );
    $text_strings = $settings['text_strings'];
    $string = $text_strings[$text];
    return $string[$type];
}

if ( is_admin() ) {
    require_once BPLIKE_PATH . 'includes/admin.php';
}
require_once BPLIKE_PATH . 'includes/button-functions.php';
require_once BPLIKE_PATH . 'includes/install-functions.php';
require_once BPLIKE_PATH . 'includes/activity-functions.php';
require_once BPLIKE_PATH . 'includes/ajax-functions.php';
require_once BPLIKE_PATH . 'includes/like-functions.php';
require_once BPLIKE_PATH . 'includes/scripts.php';
require_once BPLIKE_PATH . 'includes/settings.php';
require_once BPLIKE_PATH . 'includes/blogpost.php';

/**
 * for notifications
 */

function likes_new_like_notification( $author_id, $initiator_id, $activity_id ) {

	$initiator_name = bp_core_get_user_displayname( $initiator_id );

	if ( 'no' == bp_get_user_meta( (int) $author_id, 'notification_like_selected', true ) )
		return false;

	$ud                = get_userdata( $author_id );
	$liked_drem_link   = bp_core_get_root_domain() . '/activity/'.$activity_id;
	$settings_slug     = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
	$settings_link     = trailingslashit( bp_core_get_user_domain( $author_id ) .  $settings_slug . '/notifications' );
	$initiator_link    = bp_core_get_user_domain( $initiator_id );

	// Set up and send the message
	$to       = $ud->user_email;
	$subject  = bp_get_email_subject( array( 'text' => sprintf( __( '%s likes your drēm', 'buddypress' ), $initiator_name ) ) );
	$message  = sprintf( __(
                    '%1$s likes your drēm.

                    To view that liked drēm: %2$s

                    To view %3$s\'s profile: %4$s

                    ---------------------
                    ', 'buddypress' ), $initiator_name, $liked_drem_link, $initiator_name, $initiator_link );

	// Only show the disable notifications line if the settings component is enabled
	if ( bp_is_active( 'settings' ) ) {
		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );
	}

	// Send the message
	$to      = apply_filters( 'likes_new_like_notification_to', $to );
	$subject = apply_filters( 'likes_new_like_notification_subject', $subject, $initiator_name );
	$message = apply_filters( 'likes_new_like_notification_message', $message, $initiator_name, $initiator_link, $liked_drem_link, $settings_link );
	wp_mail( $to, $subject, $message );

	do_action( 'bp_like_email', $author_id, $subject, $message, $activity_id, $initiator_id );
}

function bp_likes_new_like_notification( $author_id, $initiator_user_id, $activity_id ) {
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_add_notification( array(
			'user_id'           => $author_id,
			'item_id'           => $activity_id,
			'secondary_item_id' => $initiator_user_id,
			'component_name'    => 'ac_like',
			'component_action'  => 'new_like',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );
	}
}

add_action( 'notify_who_likes', 'bp_core_clear_cache' );
add_action( 'notify_who_likes', 'likes_new_like_notification', 10, 3 );
add_action( 'notify_who_likes', 'bp_likes_new_like_notification', 10, 3 );

function likes_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
        
	switch ( $action ) {
		case 'new_like':
			$media_model = new RTMediaModel();  
                        $media_obj = $media_model->get( array( 'activity_id' => $item_id ) );  
                        //var_dump($media_obj);
                        //$media_obj = bp_get_media_by_activity($item_id);
                        //var_dump($media_obj);
                        $drem_text = "activity";
                        $drem_src = array();
                        if (count($media_obj) > 0){
                            foreach ( $media_obj as $media ) {
                                if ( $media->media_type == 'photo' )
                                {
                                    $drem_text = 'photo';
                                    $drem_src = wp_get_attachment_image_src( $media->media_id );
                                }
                                else if ( $media->media_type == 'video' )
                                {
                                    $drem_text = 'video';
                                    $drem_src = wp_get_attachment_url ( $media->media_id );
                                }
                                break;
                            }
                        }
                        //$link = trailingslashit( bp_core_get_root_domain() . '/activity/'.$item_id.'?initiator_id='.$secondary_item_id );
                        $link = trailingslashit( bp_core_get_root_domain() . '/activity/'.$item_id );
			// Set up the string and the filter
			if ( (int) $total_items > 1 ) {
                            //$text = sprintf( __( '%s and %d other people like your drēm', 'buddypress' ), bp_core_get_user_displayname( $secondary_item_id ), (int) $total_items - 1 );
                                $user_display_name = bp_core_get_user_displayname( $secondary_item_id );
                                if (strlen($user_display_name) >= 10)
                                {
                                    $user_display_name = substr($user_display_name, 0, 3).'~ ';
                                }
                                $text = sprintf( __( '<span class="username">%s</span> and %d other people like your '.$drem_text, 'buddypress' ), $user_display_name, (int) $total_items - 1 );
                            $filter = 'bp_likes_multiple_new_like_notification';
			} else {
                            //$text = sprintf( __( '%s likes your drēm', 'buddypress' ),  bp_core_get_user_displayname( $secondary_item_id ) );
                            $user_display_name = bp_core_get_user_displayname( $secondary_item_id );
                            if (strlen($user_display_name) >= 10)
                            {
                                $user_display_name = substr($user_display_name, 0, 3).'~ ';
                            }
                            $text = sprintf( __( '<span class="username">%s</span> likes your '.$drem_text, 'buddypress' ),  $user_display_name );
                            $filter = 'bp_likes_single_new_like_notification';
			}
                        
                        $user_link = bp_core_get_user_domain($secondary_item_id);
                        $avatar = bp_core_fetch_avatar( array( 'item_id' => $secondary_item_id, 'width' => 40, 'height' => 40 ) );
                        $avatar_html = '<div class="notification avatar">'
                                                    .'<a href="'
                                                    .$user_link
                                                    .'" >'
                                                    .$avatar
                                                    .'</a></div>';
                        
                        $message_html = '<div class="notification message" >'.'<a class="" href="'.$link.'">'.$text.'</a></div>';
                        
                        $bp_like_html = '<div class="ab-notification-item">';
                        
                        $empty_avatar_html = '<div class="notification avatar empty">'
                                                    .'<a href="'
                                                    .$link
                                                    .'">'
                                                    .$avatar
                                                    .'</a></div>';
                        if ($drem_text != "activity")
                        {
                            if ($drem_text == "photo")
                            {
                                $drem = '<img src="'.$drem_src[0].'" class="drem photo" alt="Liked Drem" >';
                            }
                            else if ($drem_text == "video")
                            {
                                $drem = '<video src="'.$drem_src.'" class="drem video" type="video/mp4" preload="true" >';

                            }
                            $drem_link = $link;
                            $drem_html = '<div class="notification drem">'
                                                        .'<a href="'
                                                        .$drem_link
                                                        .'">'
                                                        .$drem
                                                        .'</a></div>';
                            $bp_like_html .= $avatar_html.$message_html.$drem_html.'</div>';
                        }
                        else
                        {
                            $bp_like_html .= $avatar_html.$message_html.$empty_avatar_html.'</div>';
                        }

		break;

	}

	// Return either an HTML link or an array, depending on the requested format
	if ( 'string' == $format ) {
            //$return = apply_filters( $filter, '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>', (int) $total_items );
            $return = apply_filters( $filter, $bp_like_html, (int) $total_items );
            //$return = apply_filters( $filter, '<a href="' . esc_url( $link ) . '">' . $bp_like_html . '</a>', (int) $total_items );
	} else {
		$return = apply_filters( $filter, array(
			//'link' => $link,
			//'text' => $text
                        'text'=> $bp_like_html
		), (int) $total_items );
	}
        
	do_action( 'likes_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $return );

	return $return;
}

function ac_like_remove_notification_on_activity_delete( $activity_id){
    ac_delete_like_notification( $activity_id );
}
add_action( 'bp_activity_action_delete_activity', 'ac_like_remove_notification_on_activity_delete', 10, 1 );

function  ac_delete_like_notification( $activity_id, $action_name = false ){
    global $bp, $wpdb;
    $component_name = $bp->ac_like->id;
    
    $and_condition='';
     
    if( !empty( $action_name ) )
        $and_condition = $wpdb->prepare( ' AND component_action = %s', $component_action );

    return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->core->table_name_notifications} WHERE item_id = %d AND component_name = %s {$and_condition}", $activity_id, $component_name ) );
}

function ac_like_remove_notification_on_activity_unlike( $author_id, $initiator_id, $activity_id){
    ac_delete_like_notification_on_activity_unlike( $author_id, $initiator_id, $activity_id );
}
add_action('notify_who_unlikes','ac_like_remove_notification_on_activity_unlike', 10, 3);

function  ac_delete_like_notification_on_activity_unlike( $author_id, $initiator_id, $activity_id, $action_name = false ){
    global $bp, $wpdb;
    $component_name = $bp->ac_like->id;
    
    $and_condition='';
     
    if( !empty( $action_name ) )
        $and_condition = $wpdb->prepare( ' AND component_action = %s', $component_action );

    return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->core->table_name_notifications} WHERE item_id = %d AND component_name = %s AND secondary_item_id = %d {$and_condition}", $activity_id, $component_name, $initiator_id ) );
}

function likes_clear_like_notifications($activity) {
    if ( bp_is_active( 'notifications' ) ) {
        $secondary_item_id = (isset($_GET['initiator_id']))? $_GET['initiator_id']: false;
        bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), $activity->id, 'ac_like', 'new_like', $secondary_item_id);
    }
}
add_action( 'bp_activity_screen_single_activity_permalink', 'likes_clear_like_notifications' );

function ac_like_setup_globals() {
    global $bp;
    $bp->ac_like = new stdClass();
    $bp->ac_like->id = 'ac_like';//I asume others are not going to use this is
    //$bp->ac_like->slug = 'ac_like';
    $bp->ac_like->notification_callback = 'likes_format_notifications';//show the notification
    /* Register this in the active components array */
    $bp->active_components[$bp->ac_like->id] = $bp->ac_like->id;

    do_action( 'ac_like_setup_globals' );
}
add_action( 'bp_setup_globals', 'ac_like_setup_globals' );