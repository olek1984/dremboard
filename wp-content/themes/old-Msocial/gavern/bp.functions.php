<?php

// disable direct access to the file	
defined('GAVERN_WP') or die('Access denied');	

// This file can be used to add functions for the BuddyPress Plugin.

global $bp;


// change size of the avatars
function gk_member_avatar () {
	return bp_get_member_avatar('type=full&width=125&height=125');
}

add_filter('bp_member_avatar', 'gk_member_avatar' );

// EOF