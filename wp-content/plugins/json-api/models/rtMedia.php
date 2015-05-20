<?php

class JSON_API_RtMedia {

    var $media_id;          // Integer
    var $activity_id;       // Integer
    var $guid;              // String (url)
    var $category_id;        // int
    var $category_str;       // String
    var $like_str;           // String
    var $favorite_str;       // String

    function JSON_API_RtMedia() {
        if (!$id)
            return null;
        $rtmedia_model = new RTMediaModel();
        $rt_media = $rtmedia_model->get_media(array("id" => $id));
        return $rt_media;
    }

    public static function _get_drems($user_id, $author_id = false, $album_id = false, $category, $search_str, $last_media_id, $per_page) {
        // id, activity_id, media_title, category, media_type, guid, favorite, like;
        $rtmedia_model = new RTMediaModel();
        //$rtmedias = $rtmedia_model->get_media(array('media_type' => 'photo'), false, $page_num);

        $columns = array('source' => 'drems');
        if ($album_id) {
            $columns = array('album_id' => $album_id);
        } else if ($author_id) {
            $columns = array('author_id' => $author_id);
        }

        $rtmedias = self::get_rtmedia($user_id, $columns, $last_media_id, $category, $search_str, $per_page, $media_type = "('photo')");
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";

        $ret['data'] = array();
        $ret['data']['count'] = count($rtmedias);
        $ret['data']['media'] = array();

        //print_r(count($rtmedias));
        //$ret['data']['last_media_id'] = $rtmedias[$ret['data']['count'] - 1]['id'];

        foreach ($rtmedias as $rtmedia) {
            $media = array();
            $media['id'] = $rtmedia->id;
            $media['activity_id'] = $rtmedia->activity_id;
            $media['media_title'] = $rtmedia->media_title;
            $media['category'] = get_rtmedia_category($rtmedia->media_id);
            $media['media_type'] = $rtmedia->media_type;
            $media['album_id'] = $rtmedia->album_id;
            $media['guid'] = rtmedia_image('rt_media_thumbnail', $rtmedia->id, false);
            $media['favorite'] = self::get_favorite_str($user_id, $rtmedia->activity_id);
            $media['like'] = self::get_like_str($user_id, $rtmedia->activity_id);
            $ret['data']['media'][] = $media;
        }

        return $ret;
    }

    public static function _get_dremboards($user_id, $author_id = false, $category, $search_str, $last_media_id, $per_page) {
        // id, activity_id, media_title, category, media_type, guid, favorite, like;
        $rtmedia_model = new RTMediaModel();

        $columns = array();
        if ($author_id) {
            $columns = array('author_id' => $author_id);
        }
        //$rtmedias = $rtmedia_model->get_media(array('media_type' => 'photo'), false, $page_num);
        $rtmedias = self::get_rtmedia($user_id, $columns, $last_media_id, $category, $search_str, $per_page, $media_type = "('album')");
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";

        $ret['data'] = array();
        $ret['data']['count'] = count($rtmedias);
        $ret['data']['media'] = array();

        //$ret['data']['last_media_id'] = $rtmedias[$ret['data']['count'] - 1]['id'];

        foreach ($rtmedias as $rtmedia) {
            $media['id'] = $rtmedia->id;
            $media['media_title'] = $rtmedia->media_title;
            $media['media_author_id'] = $rtmedia->media_author;
            $media['media_author_avatar'] = bp_core_fetch_avatar(array('item_id' => $rtmedia->media_author, 'html' => false));
            $media['media_author_name'] = bp_core_get_user_displayname($rtmedia->media_author);
            $media['media_type'] = $rtmedia->media_type;
            $media['guid'] = rtmedia_image('rt_media_thumbnail', $rtmedia->id, false);
            $media['album_count'] = rtmedia_album_count($echo = false, $rtmedia->media_id);
            $ret['data']['media'][] = $media;
        }

        return $ret;
    }

    public static function _get_memories($user_id, $author_id = false, $search_str, $last_media_id, $per_page) {
        // id, activity_id, media_title, category, media_type, guid, favorite, like;
        $rtmedia_model = new RTMediaModel();
        //$rtmedias = $rtmedia_model->get_media(array('media_type' => 'photo'), false, $page_num);
        $columns = array();
        if ($author_id) {
            $columns = array('author_id' => $author_id);
        }

        $rtmedias = self::get_rtmedia($user_id, $columns, $last_media_id, $category = "-1", $search_str, $per_page, $media_type = "('video')");
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";

        $ret['data'] = array();
        $ret['data']['count'] = count($rtmedias);
        $ret['data']['media'] = array();

        //$ret['data']['last_media_id'] = $rtmedias[$ret['data']['count'] - 1]['id'];

        foreach ($rtmedias as $rtmedia) {
            $media['id'] = $rtmedia->id;
            $media['media_title'] = $rtmedia->media_title;
            $media['media_author_id'] = $rtmedia->media_author;
            $media['media_author_avatar'] = bp_core_fetch_avatar(array('item_id' => $rtmedia->media_author, 'html' => false));
            $media['media_author_name'] = bp_core_get_user_displayname($rtmedia->media_author);
            $media['media_type'] = $rtmedia->media_type;
            $media['guid'] = wp_get_attachment_url($rtmedia->media_id);
            $media['view_count'] = $rtmedia->views;
            $ret['data']['media'][] = $media;
        }

        return $ret;
    }

    public static function _set_favorite($user_id, $activity_id, $favorite_str) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        if ($favorite_str != "Unfavorite") {
            bp_activity_add_user_favorite($activity_id, $user_id);
        } else {
            bp_activity_remove_user_favorite($activity_id, $user_id);
        }
        $ret['data']['favorite_str'] = self::get_favorite_str($user_id, $activity_id);
        return $ret;
    }

    public static function _set_like($user_id, $activity_id, $like_str) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        if ($like_str != "Unlike") {
            bp_like_add_user_like($activity_id, $type = 'activity');
        } else {
            bp_like_remove_user_like($activity_id, $type = 'activity');
        }
        $ret['data']['like_str'] = self::get_like_str($user_id, $activity_id);
        return $ret;
    }

    public static function _share_drem($user_id, $activity_id, $description, $share_user, $share_mode) {
        global $wpdb;

        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();

        if ($activity_id != '') {
            $qur = "SELECT * FROM " . $wpdb->prefix . "bp_activity WHERE id=$activity_id";
            $par = $wpdb->get_results($qur, OBJECT);
            if (count($par) > 0) {
                $p = $par[0];
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

                $content_new = self::activity_content($content, $description);

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
                    $ret['status'] = "ok";
                    $ret['msg'] = "Successfully shared on your own timeline.";
                    return $ret;
                }
                if ($share_mode == "Share with friend") {
                    if ($share_user != "") {
                        $friends = "";
                        if (bp_is_active('friends')) {
                            $friends = esc_sql(implode(',', BP_Friends_Friendship::get_friend_user_ids($user_id)));
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
                            $content_new = self::activity_content($content, $desc, $share_user);

                            $query = "INSERT INTO " . $wpdb->prefix . "bp_activity(`user_id`, `component`, `type`, `action`, `content`, `primary_link`, `item_id`, `secondary_item_id`, `date_recorded`,`hide_sitewide`, `mptt_left`, `mptt_right`, `is_spam`) VALUES ('$user_id', '$component', 'activity_friend', '$action', '$content_new', '$primary_link', '$item_id', '$secondary_item_id', '" . current_time('mysql', 1) . "', '$hide_sitewide', '$mptt_left', '$mptt_right', '$is_spam')";
                            $query_result = $wpdb->query($query);
                            $lastid = $wpdb->insert_id;
                            $ret['status'] = "ok";
                            $ret['msg'] = "Successfully shared with friend(" . $friend_name . ").";
                            bp_activity_at_friend_share_notification($lastid, $friend_id);
                            return $ret;
                        }
                    }
                    $ret['status'] = "error";
                    $ret['msg'] = "Your friend's name was mis-typed.";
                    return $ret;
                }
                if ($share_mode == "In a group") {
                    if ($share_user != "") {
                        $groups = "";
                        if (bp_is_active('groups')) {
                            $groups = BP_Groups_Member::get_group_ids($user_id);
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
                            $content_new = self::activity_content($content, $desc);
                            $query = "INSERT INTO " . $wpdb->prefix . "bp_activity(`user_id`, `component`, `type`, `action`, `content`, `primary_link`, `item_id`, `secondary_item_id`, `date_recorded`,`hide_sitewide`, `mptt_left`, `mptt_right`, `is_spam`) VALUES ('$user_id', '$component', 'activity_group', '$action', '$content_new', '$primary_link', '$item_id', '$secondary_item_id', '" . current_time('mysql', 1) . "', '$hide_sitewide', '$mptt_left', '$mptt_right', '$is_spam')";
                            $wpdb->query($query);
                            $ret['status'] = "ok";
                            $ret['msg'] = "Successfully shared in a group(" . $group_name . ").";
                            return $ret;
                        }
                    }
                    $ret['status'] = "error";
                    $ret['msg'] = "Your group's name was mis-typed.";
                    return $ret;
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
                    $ret['status'] = "ok";
                    $ret['msg'] = "Successfully shared in a message.";
                    return $ret;
                }
            } else {
                $ret['status'] = "error";
                $ret['msg'] = "This media could not be shared.";
                return $ret;
            }
        }
        $ret['status'] = "error";
        $ret['msg'] = "This media could not be shared.";
        return $ret;
    }

    public static function _flag_drem($user_id, $activity_id, $flag_slug) {
        global $wpdb;

        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        $ret['data'] = array();
        bp_set_user_activity_flag($activity_id, $user_id, $flag_slug);
        $flag_content = 'spam';
        switch ($flag_slug) {
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
                $flag_content = 'I think it shouldn’t be on Dremboard';
                break;
            case 'spam':
                $flag_content = 'It\'s Spam';
                break;
            default:
                break;
        }

        bp_activity_flag_notification($activity_id, $user_id, $flag_content);
        $ret['status'] = "ok";
        $ret['msg'] = "The content has been flagged and Dremboard willl be notified of your request.";
        return $ret;
    }

    public static function activity_content($content, $desc, $share_user_name = false) {
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

    public static function get_favorite_str($user_id, $activity_id) {
        $my_favs = bp_get_user_meta($user_id, 'bp_favorite_activities', true);
        $my_favs = array_flip((array) $my_favs);
        if (isset($my_favs[$activity_id])) {
            return "Unfavorite";
        } else {
            return "Favorite";
        }
    }

    public static function get_like_str($user_id, $activity_id) {

        if (bp_activity_get_meta($activity_id, 'liked_count', true)) {
            $users_who_like = array_keys(bp_activity_get_meta($activity_id, 'liked_count', true));
            $liked_count = count($users_who_like);
        }

        if (bp_like_is_liked($activity_id, 'activity', $user_id)) {
            $result .= bp_like_get_text('unlike');
        } else {
            $result .= bp_like_get_text('like');
        }

        if ($liked_count) {
            $result .= ' (' . $liked_count . ')';
        }
        return $result;
    }

    public static function get_rtmedia($user_id, $columns = array(), $last_media_id = '0', $category = "-1", $search = "", $per_page = '4', $media_type = "('photo')") {
        global $wpdb;
        global $rtmedia_query;
        $rtmedia_model = new RTMediaModel();
        $table_name = $rtmedia_model->table_name;
        $meta_table_name = $rtmedia_model->meta_table_name;

        // when get drems 
        if (isset($columns['source'])) {
            if ($columns['source'] == 'drems') {
                unset($columns['source']);
                $no_share = true;
            }
        }
        // when get medias for drembaord and check type as photo
        if (isset($columns['album_id'])) {
            $album_id = $columns['album_id'];
            unset($columns['album_id']);
        }

        if (isset($columns['author_id'])) {
            $author_id = $columns['author_id'];
            unset($columns['author_id']);
        }

        $select = "SELECT {$table_name}.* ";
        if (isset($columns['count'])) {
            if ($columns['count'] == true) {
                $is_count = true;
                $select = "SELECT count(*) ";
            }
            unset($columns['count']);
        }

        $user_meta_data = bp_get_user_meta($user_id, 'bp_activity_flags', true);
        $flag_activities = '';
        if (!empty($user_meta_data)) {
            foreach ($user_meta_data as $key => $meta) {
                if ($flag_activities == '') {
                    $flag_activities .= $key;
                } else {
                    $flag_activities .= ', ' . $key;
                }
            }
        }
        if (!empty($flag_activities)) {
            $flag_where = " AND ({$table_name}.activity_id NOT IN ({$flag_activities}) OR {$table_name}.activity_id IS NULL) ";
        }

        if ($is_count != true) {
            if (intval($last_media_id) > 0) {
                $last_where = " AND ({$table_name}.id < {$last_media_id}) ";
            }
        }

        $media_where = " AND ({$table_name}.media_type in {$media_type}) ";

        $from = " FROM {$table_name} ";
        $join = "";
        $where = " where {$table_name}.del_flag != '1' {$flag_where}{$last_where}{$media_where}";

        $have_cat_filter = $category;

        if ($category != '-1') { // all categorized
            $catejoin = "";
            $catewhere = "";

            if ($media_type == "('album')") {
                $catejoin = " LEFT JOIN $wpdb->term_relationships ON
({$table_name}.media_id = $wpdb->term_relationships.object_id)
LEFT JOIN $wpdb->term_taxonomy ON
($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) ";
                $catewhere = " AND ($wpdb->term_taxonomy.taxonomy = 'category'
AND $wpdb->term_taxonomy.term_id = {$category}) ";
            } else {
                $catejoin = " LEFT JOIN {$wpdb->prefix}{$meta_table_name} ON {$wpdb->prefix}{$meta_table_name}.media_id = {$table_name}.media_id ";
                if ($category == '0') { // uncategorized
                    $catewhere = " AND not( {$wpdb->prefix}{$meta_table_name}.meta_key = 'category' and {$wpdb->prefix}{$meta_table_name}.meta_value > '0')";
                } else {
                    $catewhere = " AND ( {$wpdb->prefix}{$meta_table_name}.meta_key = 'category' and {$wpdb->prefix}{$meta_table_name}.meta_value = '{$category}')";
                }
            }
            $join .= $catejoin;
            $where .= $catewhere;
        }

        $pagename = get_query_var('pagename');
        $origin_page = (isset($_REQUEST['origin_page'])) ? $_REQUEST['origin_page'] : "";

        if ($no_share) {
            $casewhere = " AND (({$table_name}.source != 'drems photo') OR {$table_name}.source IS NULL) ";
            $where .= $casewhere;
        }
        if ($album_id) {
            $casewhere = " AND ({$table_name}.album_id = " . $album_id . ") ";
            $where .= $casewhere;
        }
        if ($author_id) {
            $casewhere = " AND ({$table_name}.media_author = " . $author_id . ") ";
            $where .= $casewhere;
        }

        $rtmedia_query = new RTMediaQuery();
        $where = $rtmedia_query->privacy_filter($where, $table_name);

        if ($search != "") {
            $psq['ps'] = $search;
            $pswhere = "";
            $psorderby = "";
            $psjoin = " LEFT JOIN $wpdb->posts ON $wpdb->posts.id = {$table_name}.media_id ";
            ps_parse_search_params($psq, $pswhere, $psorderby);

            $join .= $psjoin;
            $where .= $pswhere;
        }

        $sql = $select . $from . $join . $where . $qgroup_by . $qorder_by;

        //filter added to change the LIMIT
        if ($is_count != true) {
            $limit = " ORDER BY {$table_name}.id desc LIMIT 0,{$per_page} ";

            $sql .= $limit;
        }
        return $wpdb->get_results($sql);
    }

}

?>
