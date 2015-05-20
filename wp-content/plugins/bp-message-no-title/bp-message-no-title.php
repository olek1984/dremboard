<?php
/*
Plugin Name: Buddypress Message No Title
Plugin URI: http://toams.zhu.bz
Description: Buddypress Message No Title
Version: 1.0
Author: Tomas Zhu
*/
function bp_message_no_title($subject)
{
	update_option("test20002",$subject);
		if ( !empty( $_POST['content'] ) )
		{
			$subject = substr($_POST['content'],0,10);
			$subject = $subject."...";
		}
	update_option("test20003",$subject);		
	return $subject;
}

//add_filter( 'bp_get_messages_subject_value', $subject );

function my_check_admin_referer($action)
{
	if ('messages_send_message' == $action)
	{
	update_option("test20002",$subject);
		if ( !empty( $_POST['content'] ) )
		{
			$subject = substr($_POST['content'],0,10);
			$subject = $subject."...";
			$_POST['subject'] = $subject;
		}
	update_option("test20003",$subject);				
	}
}

add_action('check_admin_referer','my_check_admin_referer');

function before_subject()
{
	
}

//add_filter( 'messages_message_subject_before_save', 'before_subject', 1 );
?>