<?php
/*
    Plugin Name: Buddypress Pricavy
    Plugin URI: http://tomas.zhu.bz
    Description: Buddypress Pricavy
    Version: 1.0.0
    Author: Tomas Zhu
    Author URI: http://tomas.zhu.bz
*/
function bp_privacy($arg1 = '')
{
	global $bp, $wpdb;
	/*
	if ( empty( $bp->table_prefix ) )
	{
		$bp->table_prefix = bp_core_get_table_prefix();
	}
	if (empty($bp->table_prefix)) $bp->table_prefix = 'wp_';
	*/
	//var_dump("1001");
	$viewed_user_id = bp_displayed_user_id();
	//var_dump($viewed_user_id);
	$current_user_id = bp_loggedin_user_id();
	//var_dump($current_user_id);
	$viewed_user_privacy_setting = get_user_privacy_setting($viewed_user_id);
	$current_user_privacy_setting = get_user_privacy_setting($current_user_id);
	//var_dump($viewed_user_privacy_setting);
	//var_dump($current_user_privacy_setting);
	if ($viewed_user_privacy_setting ==  'public can view my content')
	{
		return 'YES';
	}

	if ($viewed_user_privacy_setting ==  'only myself can view my content')
	{
		if ($viewed_user_id == $current_user_id)
		{
			//var_dump("9001");
			return 'YES';
		}
		else 
		{
			//var_dump("9002");
			return 'Sorry, the user settings "only myself can view my content"';
		}
	}
	
	if ($viewed_user_privacy_setting ==  'family and friends can view my content')
	{
		//var_dump("9100");
		if ($viewed_user_id == $current_user_id)
		{
			return 'YES';
		}
	$familyship_status = BP_familys_familyship::check_is_family( $current_user_id, $viewed_user_id );
	//var_dump($familyship_status);
	if ( 'is_family' == $familyship_status )
	{
		//var_dump("aa");
		return 'YES';
	}
	$friendship_status = BP_Friends_Friendship::check_is_friend(  $current_user_id, $viewed_user_id  );	
	if ( 'is_friend' == $friendship_status )
	{
		//var_dump("bb");
		return 'YES';
	}	

/*
	if ( 'not_familys' == $familyship_status ) {

		if ( !check_admin_referer( 'familys_add_family' ) )
			//!!!return false;
			{
				//!!!!!
	
	update_option("test003",'yes');
	return false;
			}
		if ( !familys_add_family( bp_loggedin_user_id(), $potential_family_id ) ) {
			bp_core_add_message( __( 'familyship could not be requested.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( 'familyship requested', 'buddypress' ) );
		}

	} else if ( 'is_family' == $familyship_status ) {
		bp_core_add_message( __( 'You are already familys with this user', 'buddypress' ), 'error' );
	} else {
		bp_core_add_message( __( 'You already have a pending familyship request with this user', 'buddypress' ), 'error' );
	}
		*/
		//var_dump("9003");
		return 'Sorry, the user settings "only myself can view my content"';
		//if ($viewed_user_id == $current_user_id)
	}
	
}

add_action ( 'wp_head', 'bp_privacy', 2 );

//add_filter( 'bp_get_activity_latest_update', 'bp_privacy', 10, 1);

function bp_privacy_activity($arg1 = '',$arg2 = '')
{
	global $bp, $wpdb;
	/*
	if ( empty( $bp->table_prefix ) )
	{
		$bp->table_prefix = bp_core_get_table_prefix();
	}
	if (empty($bp->table_prefix)) $bp->table_prefix = 'wp_';
	*/
	//var_dump("1001");
	$viewed_user_id = bp_displayed_user_id();
	//var_dump($viewed_user_id);
	$current_user_id = bp_loggedin_user_id();
	//var_dump($current_user_id);
	$viewed_user_privacy_setting = get_user_privacy_setting($viewed_user_id);
	$current_user_privacy_setting = get_user_privacy_setting($current_user_id);
	//var_dump($viewed_user_privacy_setting);
	//var_dump($current_user_privacy_setting);
	if ($viewed_user_privacy_setting ==  'public can view my content')
	{
		return $arg1;
	}

	if ($viewed_user_privacy_setting ==  'only myself can view my content')
	{
		if ($viewed_user_id == $current_user_id)
		{
			//var_dump("9001");
			return $arg1;
		}
		else 
		{
			//var_dump("9002");
			return 'Sorry, the user settings "only myself can view my content"';
		}
	}
	
	if ($viewed_user_privacy_setting ==  'family and friends can view my content')
	{
		//var_dump("9100");
		if ($viewed_user_id == $current_user_id)
		{
			return $arg1;
		}
	$familyship_status = BP_familys_familyship::check_is_family( $current_user_id, $viewed_user_id );
	//var_dump($familyship_status);
	if ( 'is_family' == $familyship_status )
	{
		//var_dump("aa");
		return $arg1;
	}
	$friendship_status = BP_Friends_Friendship::check_is_friend(  $current_user_id, $viewed_user_id  );	
	if ( 'is_friend' == $friendship_status )
	{
		//var_dump("bb");
		return $arg1;
	}	

/*
	if ( 'not_familys' == $familyship_status ) {

		if ( !check_admin_referer( 'familys_add_family' ) )
			//!!!return false;
			{
				//!!!!!
	
	update_option("test003",'yes');
	return false;
			}
		if ( !familys_add_family( bp_loggedin_user_id(), $potential_family_id ) ) {
			bp_core_add_message( __( 'familyship could not be requested.', 'buddypress' ), 'error' );
		} else {
			bp_core_add_message( __( 'familyship requested', 'buddypress' ) );
		}

	} else if ( 'is_family' == $familyship_status ) {
		bp_core_add_message( __( 'You are already familys with this user', 'buddypress' ), 'error' );
	} else {
		bp_core_add_message( __( 'You already have a pending familyship request with this user', 'buddypress' ), 'error' );
	}
		*/
		//var_dump("9003");
		return 'Sorry, the user settings "only myself can view my content"';
		//if ($viewed_user_id == $current_user_id)
	}
	
}
add_filter( 'bp_get_activity_content_body', 'bp_privacy_activity', 10, 2);


function get_user_privacy_setting($user_id)
{
	global $bp, $wpdb;
	if ( empty( $bp->table_prefix ) )
	{
		$bp->table_prefix = bp_core_get_table_prefix();
	}
	$privacy_result = '';
	if (empty($bp->table_prefix)) $bp->table_prefix = 'wp_';
	$privacy_sql = "select value from ".$bp->table_prefix."bp_xprofile_data where user_id =$user_id and value='family and friends can view my content' limit 1";
	$privacy_result = $wpdb->get_var($privacy_sql);
	if ($privacy_result)
	{
		return $privacy_result;
	}
	$privacy_sql = "select value from ".$bp->table_prefix."bp_xprofile_data where user_id =$user_id and value='only myself can view my content' limit 1";
	$privacy_result = $wpdb->get_var($privacy_sql);
	if ($privacy_result)
	{
		return $privacy_result;
	}
	if (empty($privacy_result))
	{
		return 'public can view my content';
	}
}