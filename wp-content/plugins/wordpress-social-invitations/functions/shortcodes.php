<?php
add_shortcode('wsi-widget','wsi_shortcode_func');

function wsi_shortcode_func($atts){
	extract( shortcode_atts( array(
		'title' => ''
	), $atts ) );
	ob_start();
	WP_Social_Invitations::widget($title);
	$widget = ob_get_contents();
	ob_clean();

	return $widget;

}

add_shortcode('wsi-locker','wsi_locker_func');

function wsi_locker_func($atts, $content){
	extract( shortcode_atts( array(
		'title' => __('Share to Unlock the content','wsi')
	), $atts ) );
	
	global $post;
	
	static $wsi_i = 1;

	if( isset($_COOKIE['wsi-lock'][$wsi_i.'_'.$post->ID]) )
	{
		$wsi_i++;
		return do_shortcode($content);
	}
	else
	{
	
		$widget = '<div id="'.$wsi_i.'_'.$post->ID.'" class="wsi-locker">';
		
		ob_start();
		WP_Social_Invitations::widget($title);
		$widget .= ob_get_contents();
		ob_clean();
		
		$widget .= '</div>';
				
		$wsi_i++;
		return $widget;
	}
}

