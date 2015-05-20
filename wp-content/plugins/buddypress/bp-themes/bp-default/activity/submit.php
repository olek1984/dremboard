<?php

$path = realpath($_SERVER["DOCUMENT_ROOT"]);
include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';
include_once( ABSPATH . 'wp-admin/includes/upgrade.php');

global $wpdb;
$result = array();
$request_action = $_REQUEST['activity_action'];
$curr_user_id = get_current_user_id();

if ($request_action == 'share') {

    $id = $_REQUEST['id'];
    $desc = $_REQUEST['desc'];

    $share_mode = $_REQUEST['share_mode'];
    $share_user = $_REQUEST['share_user'];


    if ($id != '') {
        $qur = "SELECT * FROM " . $wpdb->prefix . "bp_activity WHERE id=$id";
        $par = $wpdb->get_results($qur, OBJECT);
        if (count($par) > 0) {
            $p = $par[0];

            $user_id = $curr_user_id; // user is changed for current user
            $component = 'activity';
            $type = $p->type;

            $content = $p->content;
            $primary_link = $p->primary_link;
            $item_id = 0;
            $secondary_item_id = $p->user_id; // it is used for save post author id.

            $from_user_link = bp_core_get_userlink($user_id);
            $post_user_link = bp_core_get_userlink($secondary_item_id);
            $action = sprintf(__('%s Shared %s post', 'buddypress'), $from_user_link, $post_user_link);


            $date_recorded = $p->date_recorded;
            $hide_sitewide = $p->hide_sitewide;
            $mptt_left = $p->mptt_left;
            $mptt_right = $p->mptt_right;
            $is_spam = $p->is_spam;

            $content_new = activity_content($content, $desc);

            /* if ($share_mode == "On your wall Timeline"){
              $query  = "INSERT INTO ".$wpdb->prefix."bp_activity(`user_id`, `component`, `type`, `action`, `content`, `primary_link`, `item_id`, `secondary_item_id`, `date_recorded`,`hide_sitewide`, `mptt_left`, `mptt_right`, `is_spam`) VALUES ('$user_id', '$component', 'activity_wall', '$action', '$content_new', '$primary_link', '$item_id', '$secondary_item_id', '".current_time('mysql', 1)."', '$hide_sitewide', '$mptt_left', '$mptt_right', '$is_spam')";
              $wpdb->query($query);
              $result['status'] = 1;
              $result['msg'] = "Successfully shared on your wall.";
              echo json_encode($result);
              } */
            if ($share_mode == "On your own Timeline") {
                $query = "INSERT INTO " . $wpdb->prefix . "bp_activity(`user_id`, `component`, `type`, `action`, `content`, `primary_link`, `item_id`, `secondary_item_id`, `date_recorded`,`hide_sitewide`, `mptt_left`, `mptt_right`, `is_spam`) VALUES ('$user_id', '$component', 'activity_own', '$action', '$content_new', '$primary_link', '$item_id', '$secondary_item_id', '" . current_time('mysql', 1) . "', '$hide_sitewide', '$mptt_left', '$mptt_right', '$is_spam')";
                $wpdb->query($query);
                $result['status'] = 1;
                $result['msg'] = "Successfully shared on your own timeline.";
                echo json_encode($result);
            }
            if ($share_mode == "Share with friend") {
                if ($share_user != "") {
                    $friends = "";
                    if (bp_is_active('friends')) {
                        $friends = esc_sql(implode(',', BP_Friends_Friendship::get_friend_user_ids($curr_user_id)));
                    }
                    if ($friends == "")
                        $friends = "-1";
                    $sql = "select id, display_name from {$wpdb->users} where user_nicename='{$share_user}' AND id IN ($friends)";
                    $sql_result = $wpdb->get_results($sql);
                    $friend_id = $wpdb->get_var($sql);
                    if (count($sql_result) > 0) {
                        $friend_id = $sql_result[0]->id;
                        $friend_name = $sql_result[0]->display_name;
                        $item_id = $friend_id;
                        $content_new = activity_content($content, $desc, $share_user);

                        $query = "INSERT INTO " . $wpdb->prefix . "bp_activity(`user_id`, `component`, `type`, `action`, `content`, `primary_link`, `item_id`, `secondary_item_id`, `date_recorded`,`hide_sitewide`, `mptt_left`, `mptt_right`, `is_spam`) VALUES ('$user_id', '$component', 'activity_friend', '$action', '$content_new', '$primary_link', '$item_id', '$secondary_item_id', '" . current_time('mysql', 1) . "', '$hide_sitewide', '$mptt_left', '$mptt_right', '$is_spam')";
                        $query_result = $wpdb->query($query);
                        $lastid = $wpdb->insert_id;
                        $result['status'] = 1;
                        $result['msg'] = "Successfully shared with friend(" . $friend_name . ").";
                        bp_activity_at_friend_share_notification($lastid, $friend_id);
                        echo json_encode($result);
                        return;
                    }
                }
                $result['status'] = 0;
                $result['msg'] = "Your friend's name was mis-typed.";
                echo json_encode($result);
            }
            if ($share_mode == "In a group") {
                if ($share_user != "") {
                    $groups = "";
                    if (bp_is_active('groups')) {
                        $groups = BP_Groups_Member::get_group_ids($curr_user_id);
                        $groups = esc_sql(implode(',', wp_parse_id_list($groups['groups'])));
                    }
                    if ($groups == "")
                        $groups = "-1";
                    $sql = "SELECT id, name, slug FROM {$bp->groups->table_name} WHERE id IN ({$groups}) AND slug='{$share_user}'";
                    $sql_result = $wpdb->get_results($sql);

                    if (count($sql_result) > 0) {
                        $group_id = $sql_result[0]->id;
                        $group_name = $sql_result[0]->name;
                        $item_id = $group_id;
                        $component = 'groups';
                        $content_new = activity_content($content, $desc);
                        $query = "INSERT INTO " . $wpdb->prefix . "bp_activity(`user_id`, `component`, `type`, `action`, `content`, `primary_link`, `item_id`, `secondary_item_id`, `date_recorded`,`hide_sitewide`, `mptt_left`, `mptt_right`, `is_spam`) VALUES ('$user_id', '$component', 'activity_group', '$action', '$content_new', '$primary_link', '$item_id', '$secondary_item_id', '" . current_time('mysql', 1) . "', '$hide_sitewide', '$mptt_left', '$mptt_right', '$is_spam')";
                        $wpdb->query($query);
                        $result['status'] = 1;
                        $result['msg'] = "Successfully shared in a group(" . $group_name . ").";
                        echo json_encode($result);
                        return;
                    }
                }
                $result['status'] = 0;
                $result['msg'] = "Your group's name was mis-typed.";
                echo json_encode($result);
            }
            if ($share_mode == "In a private message") {
                $args = array(
                    'sender_id' => bp_loggedin_user_id(),
                    'thread_id' => false, // false for a new message, thread id for a reply to a thread.
                    'recipients' => $share_user, // Can be an array of usernames, user_ids or mixed.
                    'subject' => 'Shared message',
                    'content' => $content_new,
                    'date_sent' => bp_core_current_time()
                );
                messages_new_message($args);
                $result['status'] = 1;
                $result['msg'] = "Successfully shared in a message.";
                echo json_encode($result);
            }
        } else {
            $result['status'] = 0;
            $result['msg'] = "This media could not be shared.";
            echo json_encode($result);
        }
    }
} else if ($request_action == 'flag') {
    $activity_id = $_REQUEST['activity_id'];
    $user_id = $curr_user_id;
    $flag = $_REQUEST['flag_slug'];
    bp_set_user_activity_flag($activity_id, $user_id, $flag);
    $flag_content = 'spam';
    switch ($flag) {
        case 'annoy':
            $flag_content = 'It\'s annoying or not interesting';
            break;
        case 'nudity':
            $flag_content = 'Nudity or Pornography';
            break;
        case 'graphic':
            $flag_content = 'Graphic Violence';
            break;
        case 'attack':
            $flag_content = 'Attacks a group or individual';
            break;
        case 'improper':
            $flag_content = 'I think it shouldn’t be on Drēmboard';
            break;
        case 'spam':
            $flag_content = 'It\'s Spam';
            break;
        default:
            break;
    }
    
    bp_activity_flag_notification($activity_id, $user_id, $flag_content);
    $result['status'] = 1;
    $result['msg'] = "This content has been flagged and Drēmboard will be notified of your request.";
    echo json_encode($result);
}

function activity_content($content, $desc, $share_user_name = false) {
    if ($share_user_name) {
        $desc = '@' . $share_user_name . ' ' . $desc;
    }

    $desc = bp_activity_at_name_filter($desc);
    $content_tail = strstr($content, '<ul');
    $content_head = '<div class="rtmedia-activity-container"><div class="rtmedia-activity-text">' . $desc . '</div>';
    $content_new = $content_head . $content_tail;
    $content_new = preg_replace("/\'/", '"', $content_new);
    return $content_new;
}

?>
