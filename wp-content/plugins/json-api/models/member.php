<?php

class JSON_API_Member {

    function JSON_API_Member() {
        
    }

    public static function _get_dremers($user_id, $type, $search_str, $page, $per_page) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";

        global $members_template;

        //$type = active, family, friends, friendship_request, familyship_request;
        //bp_has_members( 'user_id='. $user_id .'&type=' . $type . '&per_page=' . $_POST['max-members'] . '&max=' . $_POST['max-members'] . '&populate_extras=1' ) )
        $user_id_act = 0;
        if (bp_displayed_user_id()) {
            $user_id_act = bp_displayed_user_id();
        }

        bp_has_members(array('user_id' => $user_id_act, 'type' => $type, 'search_terms' => $search_str, 'page' => $page, 'per_page' => $per_page));

        if (isset($members_template->members))
            $members = $members_template->members;
        $ret['data'] = array();
        $ret['data']['count'] = count($members);
        $ret['data']['member'] = array();

        foreach ($members as $member) {
            $member_item['user_id'] = $member->id;
            $member_item['user_registered'] = $member->user_registered;
            $member_item['user_login'] = $member->user_login;
            $member_item['user_nicename'] = $member->user_nicename;
            $member_item['display_name'] = $member->display_name;

            $member_item['fullname'] = $member->fullname;
            $member_item['user_email'] = $member->user_email;

            $member_item['friendship_status'] = BP_Friends_Friendship::check_is_friend($user_id, $member->id);
            $member_item['familyship_status'] = BP_familys_familyship::check_is_family($user_id, $member->id);

            $member_item['is_following'] = bp_follow_is_following(array('leader_id' => $member->id, 'follower_id' => $user_id));
            $member_item['block_type'] = bpb_get_blocked_type($user_id, $member->id);

            $member_item['user_avatar'] = bp_core_fetch_avatar(array('item_id' => $member->ID, 'html' => false));

            if (!$update = bp_get_user_meta($member->ID, 'bp_latest_update', true)) {
                $member_item['latest_update'] = array('id' => '-1', 'content' => "");
            } else {
                if (!is_array($update)) {
                    $update = array('id' => '-1', 'content' => "");
                }
                $member_item['latest_update'] = $update; //array('id' => $update['id'], 'content' => $update['content']);
            }
            $member_item['last_activity'] = bp_get_last_activity($member->ID);

            $ret['data']['member'][] = $member_item;
        }

        return $ret;
    }

    public static function _get_single_dremer($user_id, $disp_user_id) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";

        $user_data = get_userdata($disp_user_id);
        $member = $user_data->data;

        $profile_visibility_levels = bp_get_user_meta($disp_user_id, 'bp_xprofile_visibility_levels', true);

        $ret['data'] = array();

        $member_item['user_id'] = $member->ID;
        $member_item['user_registered'] = $member->user_registered;
        $member_item['user_login'] = $member->user_login;
        $member_item['user_nicename'] = $member->user_nicename;
        $member_item['display_name'] = $member->display_name;

        $member_item['fullname'] = bp_core_get_user_displayname($member->ID);
        $member_item['user_email'] = $member->user_email;

        $member_item['friendship_status'] = BP_Friends_Friendship::check_is_friend($user_id, $member->ID);
        $member_item['familyship_status'] = BP_familys_familyship::check_is_family($user_id, $member->ID);

        $member_item['is_following'] = bp_follow_is_following(array('leader_id' => $member->ID, 'follower_id' => $user_id));
        $member_item['block_type'] = bpb_get_blocked_type($user_id, $member->ID);

        $member_item['user_avatar'] = bp_core_fetch_avatar(array('item_id' => $member->ID, 'html' => false));

        if (!$update = bp_get_user_meta($member->ID, 'bp_latest_update', true)) {
            $member_item['latest_update'] = array('id' => '-1', 'content' => "");
        } else {
            if (!is_array($update)) {
                $update = array('id' => '-1', 'content' => "");
            }
            $member_item['latest_update'] = $update; //array('id' => $update['id'], 'content' => $update['content']);
        }
        $member_item['last_activity'] = bp_get_last_activity($member->ID);


        $ret['data']['member'] = $member_item;
        $ret['data']['profile'] = array();

        if (bp_has_profile('profile_group_id=' . bp_get_current_profile_group_id())) {
            while (bp_profile_groups()) {
                bp_the_profile_group();
                while (bp_profile_fields()) {
                    bp_the_profile_field();
                    global $field;
                    $field->data->value = bp_unserialize_profile_field($field->data->value);
                    $profile_item['id'] = bp_get_the_profile_field_id();
                    $profile_item['name'] = bp_get_the_profile_field_name();
                    $profile_item['value'] = $field->data->value;
                    $profile_item['visibility'] = bp_get_the_profile_field_visibility_level_label();
                    $ret['data']['profiles'][] = $profile_item;
                }
            }
        }
        return $ret;
    }

    public static function _set_single_dremer_profile($user_id, $disp_user_id, $profiles_json) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        $profiles = json_decode(stripcslashes($profiles_json), true);
        if (count($profiles) > 0) {
            foreach ($profiles as $profile) {
                xprofile_set_field_data($profile['id'], $disp_user_id, $profile['value'], $is_required = false);
                xprofile_set_field_visibility_level($profile['id'], $disp_user_id, $profile['visibility']);
            }
        } else {
            $ret['status'] = "error";
            $ret['msg'] = "profile is not set.";
        }

        return $ret;
    }

    public static function _set_single_dremer_image($user_id, $disp_user_id, $photo) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        if (!empty($_FILES)) {
            bp_core_avatar_handle_upload($_FILES, 'xprofile_avatar_upload_dir');
        }
        global $bp;
        $type = ( 'success' == $bp->template_message_type ) ? 'updated' : 'error';

        if ($type == 'error') {
            $ret['status'] = "error";
            $ret['msg'] = $bp->template_message;
        }
        return $ret;
    }

    public static function _set_single_dremer_general($user_id, $disp_user_id, $email, $password) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";

        $update_user = get_userdata($disp_user_id);
        $email_error = false;
        $pass_error = false;
        if (!empty($email)) {
            $user_email = sanitize_email(esc_html($email));
            $email_checks = bp_core_validate_email_address($user_email);
            if (true !== $email_checks) {
                if (isset($email_checks['invalid'])) {
                    $email_error = 'invalid';
                }

                if (isset($email_checks['domain_banned']) || isset($email_checks['domain_not_allowed'])) {
                    $email_error = 'blocked';
                }

                if (isset($email_checks['in_use'])) {
                    $email_error = 'taken';
                }
            }

            // Yay we made it!
            if (false === $email_error) {
                $update_user->user_email = $user_email;
                $email_changed = true;
            }
        } else {
            $email_error = 'empty';
        }
        $pass_changed = false;
        if (!empty($password)) {
            $update_user->user_pass = $password;
            $pass_changed = true;
        } else {
            $ret['msg'] = "Password is not changed.";
        }



        if (isset($update_user->data) && is_object($update_user->data)) {
            $update_user = $update_user->data;
            $update_user = get_object_vars($update_user);

            if (false === $pass_changed) {
                unset($update_user['user_pass']);
            }
        }

        if (( false === $email_error ) && ( false === $pass_error ) && ( wp_update_user($update_user) )) {
            wp_cache_delete('bp_core_userdata_' . $disp_user_id, 'bp');
            $bp->displayed_user->userdata = bp_core_get_core_userdata($disp_user_id);
        }

        switch ($email_error) {
            case 'invalid' :
                $ret['status'] = "error";
                $ret['msg'] = __('That email address is invalid. Check the formatting and try again.', 'buddypress');
                break;
            case 'blocked' :
                $ret['status'] = "error";
                $ret['msg'] = __('That email address is currently unavailable for use.', 'buddypress');
                break;
            case 'taken' :
                $ret['status'] = "error";
                $ret['msg'] = __('That email address is already taken.', 'buddypress');
                break;
            case 'empty' :
                $ret['status'] = "error";
                $ret['msg'] = __('Email address cannot be empty.', 'buddypress');
                break;
            case false :
                // No change
                break;
        }

        return $ret;
    }

    public static function _get_single_dremer_email_note($user_id, $disp_user_id) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        $ret['data']['notifications'] = array();
        // activity;
        if (bp_activity_do_mentions()) {
            if (!$mention = bp_get_user_meta(bp_displayed_user_id(), 'notification_activity_new_mention', true)) {
                $mention = 'yes';
            }
        }

        if (!$reply = bp_get_user_meta(bp_displayed_user_id(), 'notification_activity_new_reply', true)) {
            $reply = 'yes';
        }
        $note_item['id'] = "notification_activity_new_mention";
        $note_item['description'] = sprintf(__('A member mentions you in an update using "@%s"', 'buddypress'), bp_core_get_username(bp_displayed_user_id()));
        $note_item['value'] = $mention;
        $ret['data']['notifications'][] = $note_item;

        $note_item['id'] = "notification_activity_new_reply";
        $note_item['description'] = __("A member replies to an update or comment you've posted", 'buddypress');
        $note_item['value'] = $reply;
        $ret['data']['notifications'][] = $note_item;

        // message
        if (!$new_messages = bp_get_user_meta(bp_displayed_user_id(), 'notification_messages_new_message', true)) {
            $new_messages = 'yes';
        }
        $note_item['id'] = "notification_messages_new_message";
        $note_item['description'] = __('A member sends you a new message', 'buddypress');
        $note_item['value'] = $new_messages;
        $ret['data']['notifications'][] = $note_item;


        // friends
        if (!$send_requests = bp_get_user_meta(bp_displayed_user_id(), 'notification_friends_friendship_request', true))
            $send_requests = 'yes';

        $note_item['id'] = "notification_friends_friendship_request";
        $note_item['description'] = __('A member sends you a friendship request', 'buddypress');
        $note_item['value'] = $send_requests;
        $ret['data']['notifications'][] = $note_item;

        if (!$accept_requests = bp_get_user_meta(bp_displayed_user_id(), 'notification_friends_friendship_accepted', true))
            $accept_requests = 'yes';

        $note_item['id'] = "notification_friends_friendship_accepted";
        $note_item['description'] = __('A member accepts your friendship request', 'buddypress');
        $note_item['value'] = $accept_requests;
        $ret['data']['notifications'][] = $note_item;

        // groups
        if (!$group_invite = bp_get_user_meta(bp_displayed_user_id(), 'notification_groups_invite', true))
            $group_invite = 'yes';

        $note_item['id'] = "notification_groups_invite";
        $note_item['description'] = __('A member invites you to join a group', 'buddypress');
        $note_item['value'] = $group_invite;
        $ret['data']['notifications'][] = $note_item;

        if (!$group_update = bp_get_user_meta(bp_displayed_user_id(), 'notification_groups_group_updated', true))
            $group_update = 'yes';

        $note_item['id'] = "notification_groups_group_updated";
        $note_item['description'] = __('Group information is updated', 'buddypress');
        $note_item['value'] = $group_update;
        $ret['data']['notifications'][] = $note_item;

        if (!$group_promo = bp_get_user_meta(bp_displayed_user_id(), 'notification_groups_admin_promotion', true))
            $group_promo = 'yes';

        $note_item['id'] = "notification_groups_admin_promotion";
        $note_item['description'] = __('You are promoted to a group administrator or moderator', 'buddypress');
        $note_item['value'] = $group_promo;
        $ret['data']['notifications'][] = $note_item;

        if (!$group_request = bp_get_user_meta(bp_displayed_user_id(), 'notification_groups_membership_request', true))
            $group_request = 'yes';

        $note_item['id'] = "notification_groups_membership_request";
        $note_item['description'] = __('A member requests to join a private group for which you are an admin', 'buddypress');
        $note_item['value'] = $group_request;
        $ret['data']['notifications'][] = $note_item;

        // family
        if (!$send_requests = bp_get_user_meta(bp_displayed_user_id(), 'notification_familys_familyship_request', true))
            $send_requests = 'yes';

        $note_item['id'] = "notification_familys_familyship_request";
        $note_item['description'] = __('A member sends you a familyship request', 'buddypress');
        $note_item['value'] = $send_requests;
        $ret['data']['notifications'][] = $note_item;

        if (!$accept_requests = bp_get_user_meta(bp_displayed_user_id(), 'notification_familys_familyship_accepted', true))
            $accept_requests = 'yes';

        $note_item['id'] = "notification_familys_familyship_accepted";
        $note_item['description'] = __('A member accepts your familyship request', 'buddypress');
        $note_item['value'] = $accept_requests;
        $ret['data']['notifications'][] = $note_item;

        // follow
        if (!$notify = bp_get_user_meta(bp_displayed_user_id(), 'notification_starts_following', true))
            $notify = 'yes';

        $note_item['id'] = "notification_starts_following";
        $note_item['description'] = __('A member starts following your activity', 'bp-follow');
        $note_item['value'] = $notify;
        $ret['data']['notifications'][] = $note_item;

        return $ret;
    }

    public static function _set_single_dremer_email_note($user_id, $disp_user_id, $notifications_json) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        $notifications = json_decode(stripcslashes($notifications_json), true);

        if (count($notifications) > 0) {
            foreach ($notifications as $notification) {
                bp_update_user_meta((int) bp_displayed_user_id(), $notification['id'], $notification['value']);
            }
        } else {
            $ret['status'] = "error";
            $ret['msg'] = "notifications is not set.";
        }

        return $ret;
    }

    public static function _get_single_dremer_default_privacy($user_id, $disp_user_id) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        $default_privacy = get_user_meta($disp_user_id, 'rtmedia-default-privacy', true);
        if ($default_privacy === false) {
            $default_privacy = get_rtmedia_default_privacy();
        }
        $ret['data']['privacy'] = $default_privacy;

        return $ret;
    }

    public static function _set_single_dremer_default_privacy($user_id, $disp_user_id, $privacy) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        if (!empty($privacy)) {
            update_user_meta($disp_user_id, 'rtmedia-default-privacy', $privacy);
        } else {
            $ret['status'] = "error";
            $ret['msg'] = "privacy is not set.";
        }

        return $ret;
    }

    public static function _change_dremer_friendship($user_id, $dremer_id, $action) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        $friendship_status = BP_Friends_Friendship::check_is_friend($user_id, $dremer_id);

        switch ($action) {
            case 'add-friend':
                if (!friends_add_friend($user_id, $dremer_id)) {
                    $ret['status'] = "error";
                    $ret['msg'] = "Friendship could not be requested.";
                } else {
                    $ret['msg'] = "Friendship requested.";
                }

                break;
            case 'remove-friend':
                if (!friends_remove_friend($user_id, $dremer_id)) {
                    $ret['status'] = "error";
                    $ret['msg'] = "Friendship could not be canceled.";
                } else {
                    $ret['msg'] = "Friendship canceled.";
                }
                break;
            case 'cancel':
                if (friends_withdraw_friendship($user_id, $dremer_id)) {
                    $ret['msg'] = "Friendship request withdrawn.";
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = "Friendship request could not be withdrawn";
                }
                break;
            case 'reject':
                $friendship_id = friends_get_friendship_id($dremer_id, $user_id);
                if (friends_reject_friendship($friendship_id)) {
                    $ret['msg'] = "Friendship rejected.";
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = "Friendship could not be rejected.";
                }
                break;
            case 'accept':
                $friendship_id = friends_get_friendship_id($dremer_id, $user_id);
                if (friends_accept_friendship($friendship_id)) {
                    $ret['msg'] = "Friendship accepted.";
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = "Friendship could not be accepted.";
                }
                break;
            default:
                $ret['status'] = "error";
                $ret['msg'] = "Friendship could not be changed.";
                break;
        }

        $friendship_status = BP_Friends_Friendship::check_is_friend($user_id, $dremer_id);
        $ret['data']['friendship_status'] = $friendship_status;
        return $ret;
    }

    public static function _change_dremer_familyship($user_id, $dremer_id, $action) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        $familyship_status = BP_familys_familyship::check_is_family($user_id, $dremer_id);

        switch ($action) {
            case 'add-family':
                if (!familys_add_family($user_id, $dremer_id)) {
                    $ret['status'] = "error";
                    $ret['msg'] = "Familyship could not be requested.";
                } else {
                    $ret['msg'] = "Familyship requested.";
                }

                break;
            case 'remove-family':
                if (!familys_remove_family($user_id, $dremer_id)) {
                    $ret['status'] = "error";
                    $ret['msg'] = "Familyship could not be canceled.";
                } else {
                    $ret['msg'] = "Familyship canceled.";
                }
                break;
            case 'cancel':
                if (familys_withdraw_familyship($user_id, $dremer_id)) {
                    $ret['msg'] = "Familyship request withdrawn.";
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = "Familyship request could not be withdrawn";
                }
                break;
            case 'reject':
                $familyship_id = familys_get_familyship_id($dremer_id, $user_id);
                if (familys_reject_familyship($familyship_id)) {
                    $ret['msg'] = "Familyship rejected.";
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = "Familyship could not be rejected.";
                }
                break;
            case 'accept':
                $familyship_id = familys_get_familyship_id($dremer_id, $user_id);
                if (familys_accept_familyship($familyship_id)) {
                    $ret['msg'] = "Familyship accepted.";
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = "Familyship could not be accepted.";
                }
                break;
            default:
                $ret['status'] = "error";
                $ret['msg'] = "Familyship could not be changed.";
                break;
        }

        $familyship_status = BP_familys_familyship::check_is_family($user_id, $dremer_id);
        $ret['data']['familyship_status'] = $familyship_status;
        return $ret;
    }

    public static function _change_dremer_following($user_id, $dremer_id, $action) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        switch ($action) {
            case 'start':
                if (bp_follow_is_following(array('leader_id' => $dremer_id, 'follower_id' => $user_id))) {
                    $ret['status'] = "error";
                    $ret['msg'] = "You are already following.";
                } else {
                    if (!bp_follow_start_following(array('leader_id' => $dremer_id, 'follower_id' => $user_id))) {
                        $ret['status'] = "error";
                        $ret['msg'] = "There was a problem when trying to follow.";
                    } else {
                        $ret['msg'] = "You are now following.";
                    }
                }
                break;
            case 'stop':
                if (!bp_follow_is_following(array('leader_id' => $dremer_id, 'follower_id' => $user_id))) {
                    $ret['status'] = "error";
                    $ret['msg'] = "You are not following.";
                } else {
                    if (!bp_follow_stop_following(array('leader_id' => $dremer_id, 'follower_id' => $user_id))) {
                        $ret['status'] = "error";
                        $ret['msg'] = "There was a problem when trying to stop following.";
                    } else {
                        $ret['msg'] = "You are no longer following.";
                    }
                }
                break;

            default:
                $ret['status'] = "error";
                $ret['msg'] = "";
                break;
        }

        $is_follow = bp_follow_is_following(array('leader_id' => $dremer_id, 'follower_id' => $user_id));
        $ret['data']['is_follow'] = $is_follow;
        return $ret;
    }

    public static function _change_dremer_blocking($user_id, $dremer_id, $action, $block_type) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();


        switch ($action) {
            case 'block':
                $current = bpb_get_blocked_users((int) $user_id);
                if (user_can((int) $dremer_id, BPB_ADMIN_CAP)) {
                    $ret['status'] = "error";
                    $ret['msg'] = "You can not block administrators / moderators.";
                } else {
                    $current[$dremer_id] = $block_type;
                    update_user_meta((int) $user_id, '_block', $current);
                    do_action('bpb_action_block', $current);
                    $ret['msg'] = "User successfully blocked";
                }
                break;
            case 'unblock':
                $current = bpb_get_blocked_users((int) $user_id);
                if (isset($current[$dremer_id])) {
                    unset($current[$dremer_id]);
                    update_user_meta((int) $user_id, '_block', $current);

                    do_action('bpb_action_unblock', $current);
                    $ret['msg'] = "User successfully unblocked";
                }
                break;
            default:
                $ret['status'] = "error";
                $ret['msg'] = "";
                break;
        }

        $ret['data']['block_type'] = bpb_get_blocked_type($user_id, $dremer_id);
        return $ret;
    }

    public static function get_block_type($user_id, $member_id) {
        $current = bpb_get_blocked_users((int) $user_id);
        if (isset($current[$member_id])) {
            return $current[$member_id];
        } else {
            return 0;
        }
    }

    public static function _get_notifications($user_id, $type, $page = 1, $per_page = 25) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        $is_new = 1;
        if ($type == "read")
            $is_new = 0;

        if (bp_has_notifications(array('is_new' => $is_new, 'page' => $page, 'per_page' => $per_page))) {
            while (bp_the_notifications()) {
                bp_the_notification();
                $notification_item['id'] = bp_get_the_notification_id();
                $notification_item['desc'] = strip_tags(bp_get_the_notification_description());
                $notification_item['component'] = bp_get_the_notification_component_name();
                $notification_item['since'] = bp_get_the_notification_time_since();
                $notification_item['type'] = (buddypress()->notifications->query_loop->notification->is_new) ? "unread" : "read";
                $notification[] = $notification_item;
            }
        }

        $ret['data']['notification'] = $notification;
        return $ret;
    }

    public static function _set_notification_action($user_id, $notification_id, $action) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        switch ($action) {
            case "read":
            case "unread":
                $is_new = 0;
                if ($action == "unread")
                    $is_new = 1;
                bp_notifications_mark_notification($notification_id, $is_new);
                break;
            case "delete":
                bp_notifications_delete_notification($notification_id);
                break;

            default:
                break;
        }
    }

    public static function _get_messages($user_id, $type, $page, $per_page) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();



        if (bp_has_message_threads(array('box' => $type, 'page' => $page, 'per_page' => $per_page))) {
            while (bp_message_threads()) {
                bp_message_thread();
                global $messages_template;
                $message_item['type'] = $type;
                switch ($type) {
                    case 'inbox':
                    case 'sentbox':
                        $message_item['id'] = bp_get_message_thread_id();

                        if ($type == 'inbox') {
                            $message_item['from'] = self::get_member_info($messages_template->thread->last_sender_id);
                        }
                        if ($type == 'sentbox') {
                            $message_item['to'] = array();
                            $recipients = $messages_template->thread->recipients;
                            foreach ((array) $recipients as $recipient) {
                                $member_info = self::get_member_info($recipient->user_id);
                                $message_item['to'][] = $member_info;
                            }
                        }
                        $message_item['unread_count'] = (int) bp_get_message_thread_unread_count();
                        $message_item['post_date'] = bp_get_message_thread_last_post_date();
                        $message_item['title'] = bp_get_message_thread_subject();
                        $message_item['excerpt'] = bp_get_message_thread_excerpt();

                        break;
                    case 'notices':
                        $message_item['id'] = bp_get_message_notice_id();
                        $message_item['title'] = bp_get_message_notice_subject();
                        $message_item['text'] = bp_get_message_notice_text();
                        $message_item['status'] = ( bp_messages_is_active_notice() ) ? "active" : "disable";
                    default:
                        break;
                }

                $message[] = $message_item;
            }
        }

        $ret['data']['messages'] = $message;
        return $ret;
    }

    public static function _message_compose($user_id, $recipients, $subject, $content, $is_notice) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        global $bp;

        // Remove any saved message data from a previous session.
        messages_remove_callback_values();
        // Check we have what we need
        if (empty($subject)) {
            $subject = substr($content, 0, 10);
            $subject = $subject . "...";
        }


        if (empty($subject) || empty($content)) {
            $ret['status'] = "error";
            $ret['msg'] = "There was an error sending that message, please try again";
        } else {
            // If this is a notice, send it
            if ($is_notice == "notice") {
                if (messages_send_notice($subject, $content)) {
                    $ret['msg'] = 'Notice sent successfully!';
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = 'There was an error sending that notice, please try again';
                }
            } else {
                // Filter recipients into the format we need - array( 'username/userid', 'username/userid' )
                $recipients = explode(',', $recipients);
                $recipients = apply_filters('bp_messages_recipients', $recipients);

                // Send the message
                if ($thread_id = messages_new_message(array('recipients' => $recipients, 'subject' => $subject, 'content' => $content))) {
                    $ret['msg'] = 'Message sent successfully!';
                } else {
                    $ret['status'] = "error";
                    $ret['msg'] = 'There was an error sending that message, please try again';
                }
            }
        }
        return $ret;
    }

    public static function _get_message_single_view($user_id, $message_id) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        if (bp_thread_has_messages(array('thread_id' => $message_id))) {
            global $thread_template;
            $message['id'] = bp_get_the_thread_id();
            $message['subject'] = bp_get_the_thread_subject();
            $message['recipients'] = array();
            foreach ((array) $thread_template->thread->recipients as $recipient) {
                $message['recipients'][] = self::get_member_info($recipient->user_id);
            }
            $message['thread'] = array();

            while (bp_thread_messages()) {
                bp_thread_the_message();
                $message_item['sender'] = self::get_member_info($thread_template->message->sender_id);
                $message_item['time_since'] = bp_get_the_thread_message_time_since();
                $message_item['content'] = strip_tags(bp_get_the_thread_message_content());
                $message['thread'][] = $message_item;
            }
        }
        $ret['data']['messages'] = $message;
        return $ret;
    }

    public static function _message_reply($user_id, $message_id, $subject, $content) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        global $bp;

        // Check we have what we need
        if (empty($subject)) {
            $subject = substr($content, 0, 10);
            $subject = $subject . "...";
        }


        if (empty($subject) || empty($content)) {
            $ret['status'] = "error";
            $ret['msg'] = "There was an error reply that message, please try again";
        } else {
            if (messages_new_message(array('thread_id' => $message_id, 'subject' => !empty($subject) ? $subject : false, 'content' => $content))) {
                $ret['msg'] = 'Your reply was sent successfully';
                messages_mark_thread_read($message_id);
            } else {
                $ret['status'] = "error";
                $ret['msg'] = 'There was a problem sending your reply, please try again';
            }
        }
        return $ret;
    }

    public static function _set_message_action($user_id, $message_id, $type, $action) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        if ($type == 'notices') {
            if (!bp_current_user_can('bp_moderate')) {
                $ret['status'] = "error";
                $ret['msg'] = 'You haven\'t got permission for this action';
                return $ret;
            }
            $notice = new BP_Messages_Notice($message_id);
            switch ($action) {
                case "deactivate":
                    if ($notice->deactivate()) {
                        $ret['msg'] = 'Notice deactivated.';
                    } else {
                        $ret['status'] = "error";
                        $ret['msg'] = 'There was a problem deactivating that notice.';
                    }

                    break;
                case "activate":
                    if ($notice->activate()) {
                        $ret['msg'] = 'Notice activated.';
                    } else {
                        $ret['status'] = "error";
                        $ret['msg'] = 'There was a problem activating that notice.';
                    }

                    break;
                case "delete":
                    if ($notice->delete()) {
                        $ret['msg'] = 'Notice deleted.';
                    } else {
                        $ret['status'] = "error";
                        $ret['msg'] = 'There was a problem deleting that notice.';
                    }
                    break;
            }
        }
        if ($type != 'notices' && $action = "delete") {
            if (messages_delete_thread($message_id)) {
                $ret['msg'] = 'Message deleted.';
            } else {
                $ret['status'] = "error";
                $ret['msg'] = 'There was an error deleting that message.';
            }
        }
        return $ret;
    }

    public static function get_member_info($member_id) {
        $member['id'] = $member_id;
        $member['name'] = bp_core_get_user_displayname($member_id);
        $member['avatar'] = bp_core_fetch_avatar(array('item_id' => $member_id, 'html' => false));
        return $member;
    }

}
?>
