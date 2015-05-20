<?php



$base_url = 'http://10.70.8.10/api/rest/';
//
//
$base_url = 'http://dremboard.com/api/rest/';



$action="get_activities";
$fields = array("user_id" => "1", 'activity_scope' => 'mentions', 'last_activity_id' => 2860, 'per_page' => 5);
$fields = array("user_id" => "1", 'disp_user_id' => '1', 'activity_scope' => 'following', 'last_activity_id' => 0, 'per_page' => 5);
//$action="get_drems"; //$user_id, $category, $search_str, $last_media_id, $per_page
//$fields = array("user_id" => "1", "album_id" => 66, "category" => "-1", "search_str" => "", "last_media_id" => "0", "per_page" => "5");
//$fields = array("user_id" => "1", "author_id" => 20, "category" => "-1", "search_str" => "", "last_media_id" => "0", "per_page" => "5");
$action="get_dremboards";
$fields = array("user_id" => "1",  "category" => "-1", "search_str" => "", "last_media_id" => "0", "per_page" => "5");
//$action="get_dremers"; //user_id', 'search_str', 'last_dremer_id', 'per_page'
//$fields = array("user_id" => "1", 'disp_user_id' => '1', 'type' => '', "search_str" => "", "page" => "1", "per_page" => "10");
//$action="get_single_dremer"; //user_id', 'search_str', 'last_dremer_id', 'per_page'
//$fields = array("user_id" => "1", 'disp_user_id' => '10');

//$action="set_single_dremer_general"; //user_id', 'search_str', 'last_dremer_id', 'per_page'
//$fields = array("user_id" => "1", 'disp_user_id' => '1', 'email' => 'test@gmail.com', 'password' => '');
//$action="get_single_dremer_email_note"; //user_id', 'search_str', 'last_dremer_id', 'per_page'
//$fields = array("user_id" => "1", 'disp_user_id' => '1');


//$fields = array("user_id" => "1", 'disp_user_id' => '20', "search_str" => "", "page" => "1", "per_page" => "5");
//$action="set_activity_comment"; //set_activity_comment($user_id, $activity_id, $comment, $photo)
//$fields = array("user_id" => "1", "activity_id" => "3351", "page" => "1", "comment" => "this is test comment by mobile", 'photo' => 'test.png');
//$action="change_dremer_friendship"; //($user_id, $dremer_id)
//$fields = array("user_id" => "20", "dremer_id" => "1", "action" => "add-friend");
//$action="change_dremer_familyship"; //($user_id, $dremer_id)
//$fields = array("user_id" => "20", "dremer_id" => "1", "action" => "add-family");
//$fields = array("user_id" => "1", "dremer_id" => "20", "action" => "accept");
//$action="change_drember_following";
//$fields = array("user_id" => "10", "dremer_id" => "1", "action" => "start");
//change_dremer_blocking($user_id, $dremer_id, $action, $block_type)
//$action="change_dremer_blocking";
//$fields = array("user_id" => "1", "dremer_id" => "20", "action" => "unblock", "block_type" => "10");
//$action="get_memories";
//$fields = array("user_id" => "1", "search_str" => "", "last_media_id" => "0", "per_page" => "5");
//$fields = array("user_id" => "1", 'author_id' => "9", "search_str" => "", "last_media_id" => "0", "per_page" => "5");
/*
(
    [user_id] => 1
    [activity_scope] => all
    [per_page] => 3
    [last_activity_id] => 0
)

$action="user_login";
$fields = array("username" => "admin", "password" => "test");
*/
//$action="get_notifications"; //user_id', 'search_str', 'last_dremer_id', 'per_page'
//$fields = array("user_id" => "1", 'type' => 'read', "search_str" => "", "page" => "2", "per_page" => "2");
//$action="set_notification_action"; 
//$fields = array("user_id" => "1", "notification_id" => "966", "action" => "delete");
//get_messages($user_id, $type, $page, $perpage)
//$action="get_messages"; 
//$fields = array("user_id" => "1", "type" => "inbox", "page" => "1", 'per_page' => '3');
//$action="message_compose"; 
//$fields = array("user_id" => "20", "recipients" => "1", "subject" => "", 'content' => 'this is test from rest api', 'is_notice' => 'message');
//$action="get_message_single_view"; 
//$fields = array("user_id" => "1", 'message_id' => "50");
//$action="message_reply"; 
//$fields = array("user_id" => "1", 'message_id' => "50", 'subject'=>'', 'content'=>'reply message by rest api');
//$action="set_message_action"; //set_message_action($user_id, $message_id, $type, $action);
//$fields = array("user_id" => "1", 'message_id' => "1", 'type'=>'notices', 'action'=>'delete');
//$fields = array("user_id" => "1", 'message_id' => "2", 'type'=>'notices', 'action'=>'deactivate');
//$fields = array("user_id" => "1", 'message_id' => "3", 'type'=>'notices', 'action'=>'deactivate');
//$action="set_single_dremer_email_note"; //user_id', 'search_str', 'notifications_json', 'per_page'
//$fields = array("user_id" => "1", 'disp_user_id' => '1', 'notifications_json' => '[{"id":"asdf","value":"asef"},{"id":"aasdfsdf","value":"asasdef"}]');

$ch = curl_init();


$fields_string = '';
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

curl_setopt($ch, CURLOPT_URL,$base_url.$action.'/');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
            $fields_string);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec ($ch);

curl_close ($ch);
//var_dump($server_output);
print_r($server_output);

//error_log(print_r($server_output, true));
// further processing ....
if ($server_output == "OK") { 
    
} else { 
    
    
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*

$base_url = 'api/rest/';

$url = 'http://10.70.8.6/';
//$action="test";
//$post = array("action" => "get_category_all");
$action="user_login";
$fields = array("username" => "test", 'password' => '111');
$fields_string = '';
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

$ch = curl_init($base_url.$action.'/');

curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

$response = curl_exec($ch);
echo "<br>";
print_r($response);
echo "<br>";
curl_close($ch);
 * *
 */
?>