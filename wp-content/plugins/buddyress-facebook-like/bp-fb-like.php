<?php
/**
 * Plugin Name: BuddyPress Facebook Like In Activity
 * Plugin URI: http://tomas.zhu.bz
 * Author: Tomas Zhu
 * Author URI: http://tomas.zhu.bz
 * Description: BuddyPress Facebook Like In Activity
 * Version: 1.0.0
 * License: GPL
 */

if ( !defined( 'ABSPATH' ) ) exit;

function bp_fb_link() {
	global $bp;

	//not for forums posts/topics - already auto updates on forum edits
	if ( 'new_forum_topic' == bp_get_activity_type() || 'new_forum_post' == bp_get_activity_type() )
		return;
/*
	//not for minor updates... status only
	if ( !$bp->loggedin_user->is_super_admin && bp_get_activity_user_id() == $bp->loggedin_user->id && 'activity_update' != bp_get_activity_type() )
		return;
	*/	
	//$bp->is_item_admin && $bp->is_single_item

	//Come and see the violence inherent in the system. Help! Help! I'm being repressed!
	/*
	if ( !etivite_bp_edit_activity_check_date_recorded( bp_get_activity_date_recorded() ) )
		return;
	*/
	//echo apply_filters( 'bp_fb_link', '<a rel="nofollow" href="' . $bp->root_domain . '/' . bp_get_activity_slug() . '/edit/' . bp_get_activity_id() . '" class="button item-button bp-secondary-action edit-activity">' . __( 'Like', 'buddypress' ) . '</a>' );
	echo apply_filters( 'bp_fb_link', '<iframe style="margin-top:0px;padding-top:0px; border: none; overflow: hidden; width: 70px; height: 18px;" src="http://www.facebook.com/plugins/like.php?href=' .bp_get_activity_thread_permalink() .'&layout=button&show-faces=true&width=450&action=like&font=arial&colorscheme=light" frameborder="0" scrolling="no" width="70" height="18"></iframe>');
	
}
/*add_action( 'bp_activity_entry_meta', 'bp_fb_link', 1 );*/

?>