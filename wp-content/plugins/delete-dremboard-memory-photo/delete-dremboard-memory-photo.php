<?php
/*
  Plugin Name: Delete photos/Dremboards/MEMORIES
  Plugin URI: http://tomas.zhu.bz
  Description: Delete photos/Dremboards/MEMORIES
  Version: 1.0.0
  Author: Tomas Zhu
 */

add_action( 'pre_get_posts', 'load_my_album' );
function load_my_album( $query )
{
    if ( is_admin() && $query->is_main_query() && (isset($_GET['post_type'])) && ($_GET['post_type']=='rtmedia_album') )
    {
    	if ((isset($_GET['post_status'])) && ($_GET['post_status'] == 'trash'))
    	{
    		$query->set( 'post_status', 'trash' );
    	}
    	else 
    	{
    		//$query->set( 'post_status', 'hidden' );
    		$query->set( 'post_status', 'any' );
    	}
        //$query->set( 'post_status', 'any' );
        
        //$query->set( 'post_type', array('rtmedia_album','trash'));
        //$query->set( 'post_type', array('rtmedia_album','attachment'));
        $query->set( 'post_type', array('rtmedia_album'));
	}
}
?>