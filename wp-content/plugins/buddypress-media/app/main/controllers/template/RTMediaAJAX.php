<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaAJAX
 *
 * @author udit
 */
class RTMediaAJAX {

	public function __construct() {
		add_action('wp_ajax_rtmedia_backbone_template',array($this,'backbone_template'));
		add_action('wp_ajax_rtmedia_create_album',array($this,'create_album'));
		add_action('wp_ajax_get_dremer_list',array($this,'get_dremer_list'));
	}
	
	function get_dremer_list(){
		global $wpdb, $bp;
		if (isset($_POST['query'])){
			$search = $_POST['query'];
			$user_type = (isset($_POST['user_type'])) ? $_POST['user_type'] : "user";
			
			$users = array();
			$friends = "";
			$cur_user_id = bp_loggedin_user_id();
			if ($user_type == "friend"){
				if (bp_is_active('friends')) {
					$friends = esc_sql(implode(',', BP_Friends_Friendship::get_friend_user_ids( $cur_user_id)));
					if ($friends == "") $friends = "-1";
				}else{
					echo json_encode("");
				}
			}else if ($user_type == "group"){
				if (bp_is_active('groups')) {
					$groups = BP_Groups_Member::get_group_ids( $cur_user_id );
					$groups = esc_sql( implode( ',', wp_parse_id_list( $groups['groups'] ) ) );
					if ($groups == "") $groups = "-1";
					
					$sql = "SELECT id, slug as user_nicename, name as fullname, description as user_email FROM {$bp->groups->table_name} WHERE id IN ({$groups}) AND (name LIKE '%%{$search}%%' OR slug LIKE '%%{$search}%%') AND status != 'hidden'  LIMIT 10";
					$sql_total = "SELECT count(*) FROM {$bp->groups->table_name} WHERE id IN ({$groups}) AND (name LIKE '%%{$search}%%' OR slug LIKE '%%{$search}%%') AND status != 'hidden'  LIMIT 10";
					$users['users'] = $wpdb->get_results($sql);
					$users['total'] = $wpdb->get_var($sql_total);
				}else{
					echo json_encode("");
				}
			}
			
			if ($user_type == "user"){
				$users = BP_Core_User::search_users_by_name($search, 10);
			}else if ($user_type == "friend"){
				$users = BP_Core_User::search_users_by_name($search, 10, $friends);
			}
			
			$results = array();

			if ($users['total'] > 0){
				foreach($users['users'] as $user){
					$result['user_id'] = $user->id;
					$result['user_nicename'] = $user->user_nicename;
					$result['user_fullname'] = $user->fullname;
					$result['user_email'] = $user->user_email;
					$avatar = bp_core_fetch_avatar( array( 'item_id' => $user->id, 'type' => 'full', 'no_grav' => true, 'html' => false ) );
					$result['user_avatar'] = $avatar;
					$results[] = $result;
				}
				echo json_encode($results);
				return;
			}
			echo "fail";
			return;
		}
		echo "fail";
	}

	function backbone_template() {
		include RTMEDIA_PATH.'templates/media/media-gallery-item.php';
	}

        function create_album(){
            if ( isset($_POST['name']) && $_POST['name'] && is_rtmedia_album_enable()) {
                if(isset($_POST['context']) && $_POST['context'] =="group"){
                    $group_id = !empty( $_POST['group_id']) ? $_POST['group_id'] : '';
                    if(can_user_create_album_in_group($group_id) == false){
                        echo false;
                        wp_die();
                    }
                }
                 $create_album = apply_filters("rtm_is_album_create_enable",true);
		if(!$create_album) {
		    echo false;
		    wp_die();
		}
		$create_album = apply_filters("rtm_display_create_album_button",true, $_POST['context_id']);
		if(!$create_album) {
		    echo false;
		    wp_die();
		}
		$album = new RTMediaAlbum();
		//function add ( $title = '', $author_id = false, $new = true, $post_id = false, $context = false, $context_id = false, $description = false, $category = false, $album_dremboard_type = false, $privacy = false) 
                $rtmedia_id = $album->add($_POST['name'], get_current_user_id(), true, false, $_POST['context'], $_POST['context_id'], $_POST['description'], $_POST['category'], $_POST['type'], $_POST['privacy']);
                $rtMediaNav = new RTMediaNav();
                if (  $_POST['context'] == "group" ) {
                    $rtMediaNav->refresh_counts ( $_POST['context_id'], array( "context" =>  $_POST['context'], 'context_id' => $_POST['context_id'] ) );
                } else {
                    $rtMediaNav->refresh_counts ( get_current_user_id(), array( "context" => "profile", 'media_author' => get_current_user_id() ) );
                }
                if ( $rtmedia_id )
                    echo $rtmedia_id;
                else
                    echo false;

            } else {
                echo false;
            }
            wp_die();
        }
}
