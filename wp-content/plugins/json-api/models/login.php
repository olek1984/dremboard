<?php

class JSON_API_LogIn {

    function JSON_API_LogIn() {
        
    }

    public static function _user_login($username, $password) {
        $ret = array();
        $ret['status'] = 'error';
        $ret['msg'] = '';
        $ret['data'] = array();

        if (empty($username)) {
            $ret['msg'] = 'The username field is empty.';
            return $ret;
        }

        if (empty($password)) {
            $ret['msg'] = 'The password field is empty.';
            return $ret;
        }
        $userdata = get_user_by('login', $username);
        if (!$userdata) {
            $ret['msg'] = 'Invalid username.';
            $ret['data']['action_name'] = 'lostpassword';
            $ret['data']['action_url'] = site_url('wp-login.php?action=lostpassword', 'login');
            return $ret;
        }

        $userdata = apply_filters('wp_authenticate_user', $userdata, $password);
        if (is_wp_error($userdata)) {
            $ret['data'] = $userdata;
            return $ret;
        }

        if (!wp_check_password($password, $userdata->user_pass, $userdata->ID)) {
            $ret['msg'] = 'Incorrect password.';
            $ret['data']['action_name'] = 'lost password';
            $ret['data']['action_url'] = site_url('wp-login.php?action=lostpassword', 'login');
            return $ret;
        }

        $user = get_userdata($userdata->ID);
        $avatar = bp_core_fetch_avatar(array('item_id' => $userdata->ID, 'html' => false));
        $data['id'] = $user->data->ID;
        $data['user_login'] = $user->data->user_login; 
        $data['avatar'] = $avatar;
                //new WP_User($userdata->ID);
        $ret['data'] = $data;
        $ret['status'] = 'ok';
        //todo:
        return $ret;
    }

    public static function _retrieve_password($user_login) {
        global $wpdb, $current_site;

        $ret = array();
        $ret['status'] = 'error';

        if (empty($user_login)) {
            $ret['msg'] = 'Enter a username or e-mail address.';
            return $ret;
        }

        if (strpos($user_login, '@')) {
            $user_data = get_user_by('email', trim($user_login));
            if (empty($user_data)) {
                $ret['msg'] = 'There is no user registered with that email address.';
                return $ret;
            }
        } else {
            $login = trim($user_login);
            $user_data = get_user_by('login', $login);
        }
        do_action('lostpassword_post');

        if (!$user_data) {
            $ret['msg'] = 'Invalid username or e-mail.';
            return $ret;
        }

        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        do_action('retreive_password', $user_login);
        do_action('retrieve_password', $user_login);
        $allow = apply_filters('allow_password_reset', true, $user_data->ID);
        if (!$allow) {
            $ret['msg'] = 'Password reset is not allowed for this user';
            return $ret;
        }

        $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));

        if (empty($key)) {
            // Generate something random for a key...
            $key = wp_generate_password(20, false);
            do_action('retrieve_password_key', $user_login, $key);
            // Now insert the new md5 key into the db
            $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
        }
        $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
        $message .= network_home_url('/') . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

        if (is_multisite()) {
            $blogname = $current_site->site_name;
        } else {
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }

        $title = sprintf(__('[%s] Password Reset'), $blogname);
        $title = apply_filters('retrieve_password_title', $title, $user_data->ID);
        $message = apply_filters('retrieve_password_message', $message, $key, $user_data->ID);

        if ($message && !wp_mail($user_email, $title, $message)) {
            $ret['msg'] = 'The e-mail could not be sent.';
            return $ret;
        }

        $ret['status'] = 'ok';
        return $ret;
    }

    public static function _get_init_params() {
        // category. (cat_id, cat_name)
        // user_datas(user_login, user_img, )
        // countries.
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        $ret['data']['category'] = array();

        $cats_all = get_category_children(6);
        if ($cats_all) {
            $cats_all = ltrim($cats_all, "/");
            $cats_array = split("/", $cats_all);
            global $table_prefix, $wpdb;
            $wpTableTerms = $table_prefix . "terms";
            $wpTableRelation = $table_prefix . "term_relationships";
            $wpTableTaxonomy = $table_prefix . "term_taxonomy";
            $resultGetCategory1 = $cats_array;
            if (sizeof($resultGetCategory1) > 0) {
                $wpTableTerms = $table_prefix . "terms";
                $wpTableRelation = $table_prefix . "term_relationships";
                $wpTableTaxonomy = $table_prefix . "term_taxonomy";
                $ret['data']['category']['keys'] = array();
                $ret['data']['category']['values'] = array();
                $ret['data']['category']['keys'][] = "-1";
                $ret['data']['category']['values'][] = "All categories";
                $ret['data']['category']['keys'][] = "0";
                $ret['data']['category']['values'][] = "Uncategorized";

                foreach ($resultGetCategory1 as $tempCategory1) {
                    $sqlGetCategory2 = "SELECT * FROM `" . $wpTableTerms . "` WHERE `term_id` = '" . $tempCategory1 . "' LIMIT 1";
                    $resultGetCategory2 = $wpdb->get_results($sqlGetCategory2, ARRAY_A);
                    if (sizeof($resultGetCategory2) > 0) {
                        $ret['data']['category']['keys'][] = $resultGetCategory2[0]['term_id'];
                        $ret['data']['category']['values'][] = htmlspecialchars_decode($resultGetCategory2[0]['name']);
                    }
                }
            }
        }

        return $ret;
    }

}

?>
