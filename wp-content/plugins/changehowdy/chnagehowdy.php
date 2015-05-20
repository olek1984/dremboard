<?php
/*
Plugin Name: Change Howdy
Plugin URI: http://tomas.zhu.bz
Description: Change Howdy
Version: 1.0
Author: Tomas Zhu
Author URI: http://tomas.zhu.bz
License: GPL2
*/
function replace_howdy( $wp_admin_bar ) {
 $my_account=$wp_admin_bar->get_node('my-account');
 $newtitle = str_replace( 'Howdy,', 'Hello', $my_account->title );
 $wp_admin_bar->add_node( array(
 'id' => 'my-account',
 'title' => $newtitle,
 ) );
}
add_filter( 'admin_bar_menu', 'replace_howdy',25 );
function my_dremer_search_form($formnow)
{
	$formnow = str_ireplace('Search Members','Search Drēmers',$formnow);
 	return $formnow;
}
add_filter( 'bp_directory_members_search_form', 'my_dremer_search_form' );
?>