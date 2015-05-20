<?php

class JSON_API_Activity {

    var $media_id;          // Integer
    var $activity_id;       // Integer
    var $guid;              // String (url)
    var $category_id;        // int
    var $category_str;       // String
    var $like_str;           // String
    var $favorite_str;       // String

    function JSON_API_Activity() {
        
    }

    public static function _get_activities($user_id, $activity_scope, $last_activity_id, $per_page) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";

        global $activities_template;
        bp_has_activities(array('last_activity_id' => $last_activity_id, 'per_page' => $per_page));

        if (isset($activities_template->activities))
            $activities = $activities_template->activities;

        $ret['data'] = array();
        $ret['data']['activity'] = array();

        $ret['data']['count'] = count($activities);
        foreach ($activities as $activity) {
            $activity_item['activity_id'] = $activity->id;
            $activity_item['author_id'] = $activity->user_id;
            $activity_item['author_name'] = bp_core_get_user_displayname( $activity->user_id );
            $activity_item['author_avatar'] = bp_core_fetch_avatar(array('item_id' => $activity->user_id, 'html' => false));
            $activity_item['action'] = $activity->action;

            //$activity->id;
            $activity_item['last_modified'] = $activity->date_recorded;
            $activity_item['media_list'] = array();
            $activity_item['comment_list'] = array();

            $activity_content = $activity->content;
            if (strpos($activity_content, '<ul') !== false) {
                $activity_item['description'] = strip_tags(strstr($activity_content, '<ul', true));
            }else{
                $activity_item['description'] = $activity_content;
            }
            $activity_item['like'] = JSON_API_RtMedia::get_like_str($user_id, $activity->id);

            $dom = new DOMDocument;
            $dom->loadHTML($activity_content);

            foreach ($dom->getElementsByTagName("img") as $node) {
                $media = array();
                $media['media_type'] = "photo";
                $media['media_guid'] = $node->getAttribute('src');
                $activity_item['media_list'][] = $media;
            }

            foreach ($dom->getElementsByTagName("video") as $node) {
                $media = array();
                $media['media_type'] = "video";
                $media['media_guid'] = $node->getAttribute('src');
                $activity_item['media_list'][] = $media;
            }
            $comment_list = array();
            $comments = $activity->children;
            foreach ($activity->children as $comment) {
                $comment_item = array();
                $comment_item['activity_id'] = $comment->id;
                $comment_item['author_id'] = $comment->user_id;
                $comment_item['author_name'] = bp_core_get_user_displayname( $comment->user_id );
                $comment_item['author_avatar'] = bp_core_fetch_avatar(array('item_id' => $comment->user_id, 'html' => false));
                $comment_item['like'] = JSON_API_RtMedia::get_like_str($user_id, $comment->id);
                $comment_content = $comment->content;
                

                if (strpos($comment_content, '<ul') !== false) {
                    $comment_item['description'] = strip_tags(strstr($comment_content, '<ul', true));
                }else{
                    $comment_item['description'] = $comment_content;
                }
                $dom = new DOMDocument;
                $dom->loadHTML($comment_content);

                $comment_item['media_guid'] = "";
                foreach ($dom->getElementsByTagName("img") as $node) {

                    $comment_item['media_guid'] = $content_img = $node->getAttribute('src');
                    break;
                }
                $comment_item['last_modified'] = $comment->date_recorded;
                $comment_list[] = $comment_item;
            }

            $activity_item['comment_list'] = $comment_list;

            $ret['data']['activity'][] = $activity_item;
        }

        return $ret;
    }

    public static function _set_activity_comment($user_id, $activity_id, $comment, $photo) {
        $ret = array();
        $ret['status'] = "ok";
        $ret['msg'] = "";
        global $activities_template;

        $picture_flag = false;
        if (count($_FILES) > 0)
            $picture_flag = true;

        if (($picture_flag == false) && empty($comment)) {
            $ret['status'] = "error";
            $ret['msg'] = "Please do not leave the comment area blank.";
            return $ret;
        }

        $content_data = $comment;
        if ($picture_flag) {
            $uploaded = array();
            $uploaded['context'] = "profile";
            $uploaded['context_id'] = 1;
            $uploaded['privacy'] = "0";
            $uploaded['album_id'] = 1;
            $uploaded['title'] = $photo;
            $uploaded['description'] = $comment;
            $uploaded['media_author'] = $user_id;
            $uploaded['category'] = "39"; // Other category.
            $rtmediaUploadFile = new RTMediaUploadFile();
            $rtmediaUploadFile->fake = false;
            $rtmediaUploadFile->populate_file_array($_FILES['file']);
            $file_object = $rtmediaUploadFile->process();
            $mediaObj = new RTMediaMedia();
            $media_ids = $mediaObj->add($uploaded, $file_object);
            $media = $mediaObj->model->get(array('id' => $media_ids[0]));
            //$activity_id = $mediaObj->insert_activity ( $media[ 0 ]->media_id, $media[ 0 ],  $_POST['content']);
            $activity = new RTMediaActivity($media[0]->id, 0);
            $activity_content = $activity->create_activity_html($comment, true);
            $content_data = $activity_content;
        }

        $comment_id = bp_activity_new_comment(array(
            'activity_id' => $activity_id,
            'content' => $content_data,
            'parent_id' => $activity_id,
        ));

        if ($picture_flag) {
            $mediaObj->model->update(
                    array('activity_id' => $comment_id), array('id' => $media[0]->id)
            );
        }

        // Load the new activity item into the $activities_template global
        bp_has_activities('display_comments=stream&hide_spam=false&include=' . $comment_id);

        // Swap the current comment with the activity item we just loaded
        if (isset($activities_template->activities[0])) {
            $activities_template->activity = new stdClass();
            $activities_template->activity->id = $activities_template->activities[0]->item_id;
            $activities_template->activity->current_comment = $activities_template->activities[0];
        }

        $comment = $activities_template->activity->current_comment;

        $comment_item = array();
        $comment_item['activity_id'] = $comment->id;
        $comment_item['author_id'] = $comment->user_id;
        $comment_item['author_avatar'] = bp_core_fetch_avatar(array('item_id' => $comment->user_id, 'html' => false));
        $comment_item['like'] = JSON_API_RtMedia::get_like_str($user_id, $comment->id);
        $comment_content = $comment->content;

        if (strpos($comment_content, '<ul') !== false) {
            $comment_item['description'] = strip_tags(strstr($comment_content, '<ul', true));
        }else{
            $comment_item['description'] = $comment_content;
        }
        $dom = new DOMDocument;
        $dom->loadHTML($comment_content);

        $comment_item['media_guid'] = "";
        foreach ($dom->getElementsByTagName("img") as $node) {

            $comment_item['media_guid'] = $content_img = $node->getAttribute('src');
            break;
        }
        $comment_item['last_modified'] = $comment->date_recorded;
        $ret['data']['comment'] = $comment_item;

        unset($activities_template);
        return $ret;
    }

}

?>
