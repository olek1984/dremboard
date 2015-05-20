<?php

/*
  Controller name: Rest
  Controller description: Basic introspection methods
 */

class JSON_API_Rest_Controller {

    // login
    public function user_login() {
        global $json_api;
        extract($json_api->query->get(array('username', 'password')));

        $ret = JSON_API_LogIn::_user_login($username, $password);

        return $json_api->response($ret);
    }

    public function retrieve_password() {
        global $json_api;
        extract($json_api->query->get(array('user_login')));

        $ret = JSON_API_LogIn::_retrieve_password($user_login);
        return $json_api->response($ret);
    }

    public function user_register() {
        global $json_api;
        $ret = bp_core_screen_signup_action($rest_api = true);
        return $json_api->response($ret);
    }

    public function get_init_params() {
        global $json_api;
        $ret = JSON_API_LogIn::_get_init_params();
        return $json_api->response($ret);
    }

    // rtmedia
    public function get_drems() {// String user_id, String category, int last_media_id, int page_num
        global $json_api;

        extract($json_api->query->get(array('user_id', 'author_id', 'album_id', 'category', 'search_str', 'last_media_id', 'per_page')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $ret = JSON_API_RtMedia::_get_drems($user_id, $author_id, $album_id, $category, $search_str, $last_media_id, $per_page);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function get_dremboards() {// String user_id, String category, int last_media_id, int page_num
        global $json_api;

        extract($json_api->query->get(array('user_id', 'author_id','category', 'search_str', 'last_media_id', 'per_page')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $ret = JSON_API_RtMedia::_get_dremboards($user_id, $author_id, $category, $search_str, $last_media_id, $per_page);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function get_memories() {
        global $json_api;

        extract($json_api->query->get(array('user_id', 'author_id', 'search_str', 'last_media_id', 'per_page')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $ret = JSON_API_RtMedia::_get_memories($user_id, $author_id, $search_str, $last_media_id, $per_page);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function set_favorite() {
        global $json_api;
        extract($json_api->query->get(array('user_id', 'activity_id', 'favorite_str')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $ret = JSON_API_RtMedia::_set_favorite($user_id, $activity_id, $favorite_str);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function set_like() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'activity_id', 'like_str')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            ob_start();
            $ret = JSON_API_RtMedia::_set_like($user_id, $activity_id, $like_str);
            ob_get_clean();
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function share_drem() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'activity_id', 'description', 'share_user', 'share_mode')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_RtMedia::_share_drem($user_id, $activity_id, $description, $share_user, $share_mode);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function flag_drem() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'activity_id', 'flag_slug')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            ob_start();
            $ret = JSON_API_RtMedia::_flag_drem($user_id, $activity_id, $flag_slug);
            ob_get_clean();
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    // activity
    public function get_activities() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id', 'activity_scope', 'last_activity_id', 'per_page')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            if($disp_user_id){
                $bp->displayed_user->id = $disp_user_id;
            }
            $bp->current_action = $activity_scope;
            $ret = JSON_API_Activity::_get_activities($user_id, $activity_scope, $last_activity_id, $per_page);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function set_activity_comment() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'activity_id', 'comment', 'photo')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Activity::_set_activity_comment($user_id, $activity_id, $comment, $photo);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    // member
    public function get_dremers() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id', 'type', 'search_str', 'page', 'per_page')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            if($disp_user_id){
                $bp->displayed_user->id = $disp_user_id;
            }

            $ret = JSON_API_Member::_get_dremers($user_id, $type, $search_str, $page, $per_page);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function get_single_dremer() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            
            $ret = JSON_API_Member::_get_single_dremer($user_id, $disp_user_id);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function set_single_dremer_profile() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id', 'profiles_json')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            $ret = JSON_API_Member::_set_single_dremer_profile($user_id, $disp_user_id, $profiles_json);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function set_single_dremer_image() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id', 'photo')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            $ret = JSON_API_Member::_set_single_dremer_image($user_id, $disp_user_id, $photo);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function set_single_dremer_general(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id', 'email', 'password')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            $ret = JSON_API_Member::_set_single_dremer_general($user_id, $disp_user_id, $email, $password);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function get_single_dremer_email_note(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            $ret = JSON_API_Member::_get_single_dremer_email_note($user_id, $disp_user_id);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function set_single_dremer_email_note(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id', 'notifications_json')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            $ret = JSON_API_Member::_set_single_dremer_email_note($user_id, $disp_user_id, $notifications_json);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function get_single_dremer_default_privacy(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            $ret = JSON_API_Member::_get_single_dremer_default_privacy($user_id, $disp_user_id);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function set_single_dremer_default_privacy(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id', 'privacy')));
        if ($user_id && $disp_user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $bp->displayed_user->id = $disp_user_id;
            $ret = JSON_API_Member::_set_single_dremer_default_privacy($user_id, $disp_user_id, $privacy);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function change_dremer_friendship() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'dremer_id', 'action')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;

            $ret = JSON_API_Member::_change_dremer_friendship($user_id, $dremer_id, $action);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    public function change_dremer_familyship() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'dremer_id', 'action')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;

            $ret = JSON_API_Member::_change_dremer_familyship($user_id, $dremer_id, $action);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function change_dremer_following() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'dremer_id', 'action')));
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;

            $ret = JSON_API_Member::_change_dremer_following($user_id, $dremer_id, $action);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    public function change_dremer_blocking() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'dremer_id', 'action', 'block_type')));
        if ($user_id) {
            $ret = JSON_API_Member::_change_dremer_blocking($user_id, $dremer_id, $action, $block_type);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }

    public function get_notifications(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'disp_user_id','type', 'page', 'per_page'))); // type= 'read', 'unread'
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            if($disp_user_id){
                $bp->displayed_user->id = $disp_user_id;
            }
            $ret = JSON_API_Member::_get_notifications($user_id, $type, $page, $per_page);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function set_notification_action(){ // action ://read, unread, delete
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'notification_id','action'))); // type= 'read', 'unread'
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Member::_set_notification_action($user_id, $notification_id, $action);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function get_messages(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'type', 'page', 'per_page'))); // type= 'inbox', 'sentbox', 'notices'
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Member::_get_messages($user_id, $type, $page, $per_page);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function message_compose(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'recipients', 'subject', 'content', 'is_notice'))); // type= 'inbox', 'sentbox', 'notices'
        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Member::_message_compose($user_id, $recipients, $subject, $content, $is_notice);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function get_message_single_view(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'message_id')));

        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Member::_get_message_single_view($user_id, $message_id);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        return $json_api->response($ret);
    }
    
    public function message_reply(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'message_id', 'subject', 'content')));

        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Member::_message_reply($user_id, $message_id, $subject, $content);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        
        return $json_api->response($ret);
    }
    
    public function set_message_action() {
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'message_id', 'type', 'action')));

        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Member::_set_message_action($user_id, $message_id, $type, $action);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        
        return $json_api->response($ret);
    }
    
    public function post_activity(){
        global $json_api, $bp;
        extract($json_api->query->get(array('user_id', 'message_id', 'type', 'action')));

        if ($user_id) {
            wp_set_current_user($user_id);
            $bp->loggedin_user->id = $user_id;
            $ret = JSON_API_Member::_post_activity($user_id, $message_id, $type, $action);
        } else {
            $ret = array("status" => "error", "msg" => "User is not logined.");
        }
        
        return $json_api->response($ret);
    }
}

?>
