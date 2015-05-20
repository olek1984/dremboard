<?php
/*
Plugin Name: Buddypress User Social Icons
Plugin URI: http://tomas.zhu.bz/
Description: Buddypress User Social Icons
Version: 1.0.0
Author: Tomas Zhu
License: GPLv2 or later
License URI: http://tomas.zhu.bz/
*/

/*
function is_200($url) {
    $options['http'] = array(
        'method' => "HEAD",
        'ignore_errors' => 1,
        'max_redirects' => 0
    );
    $body = file_get_contents($url, NULL, stream_context_create($options));
    sscanf($http_response_header[0], 'HTTP/%*d.%*d %d', $code);
    return $code === 200;
}
 
// Social Media Icons based on the profile user info
function member_social_extend(){
	$dmember_id   = $bp->displayed_user->id;
 
	$profiles = array(
		'Website',
		'Email',
		'Twitter',
		'Facebook profile',
		'Facebook page',
		'Google+',
		'Vimeo',
		'LinkedIn',
		'Kickstarter',
		'Behance',
		'Custom web link'
	);
 
	$profiles_data = array();
 
	foreach( $profiles as $profile ) {
		$profile_content = '';
		$profile_content = xprofile_get_field_data( $profile, $dmember_id );
		if ( !empty( $profile_content ) ) {
			$profiles_data[ $profile ] .= $profile_content;
		}
		
	}
 
	echo '<div class="member-social">';
 
	if( !( empty( $profiles_data ) ) ) {
		echo '<h3>Find me online:</h3>';
		echo '<ul class="social-icons">';
 
		while ( list( $key, $value ) = each( $profiles_data ) ) {
 
			$profile_icon_uri         = get_stylesheet_directory_uri() . '/assets/images/' . sanitize_title( $key ) . '.png';
			$profile_icon_uri_exists  = is_200( $profile_icon_uri );
 
			$default_profile_icon_uri = get_stylesheet_directory_uri() . '/assets/images/custom-web-link.png';
 
		    if( $profile_icon_uri_exists ) {
		    	$profile_icon = $profile_icon_uri;
		    } else {
		    	$profile_icon = $default_profile_icon_uri;
		    }
 
		    echo '<a href="' . $value . '" title="' . $key . '" target="_blank"><img src="' . $profile_icon . '" /></a>';
		}
 
		echo '<ul class="social-icons">';
		echo '</div>';
 
	}
}
add_filter( 'bp_before_member_header_meta', 'member_social_extend' );
*/



//Social Media Icons based on the profile user info
function member_social_extend(){
		$dmember_id = $bp->displayed_user->id;
		$fb_info = xprofile_get_field_data('facebook', $dmember_id);
		$google_info = xprofile_get_field_data('googleplus', $dmember_id);
		//$twitch_info = xprofile_get_field_data('twitch', $dmember_id);
		$twitch_info = '';
		$twitter_info = xprofile_get_field_data('twitter', $dmember_id);
		echo '<div class="my-member-social">';
		if($fb_info||$google_info||$twitch_info||$twitter_info){
			//echo 'My Social: ';
		}
		$pluginurl = plugin_dir_url()."buddypress-user-social-icons";
		
		if ($fb_info) {
		?>
		<span class="fb-info"><a href="https://www.facebook.com/<?php echo $fb_info; ?>"  title="My Facebook" target="_blank"><img src="<?php echo $pluginurl; ?>/assets/images/facebook.png" /></a></span>
	<?php
	}
		?>
		<?php
		if ($google_info) {
		?>
		<span class="google-info"><a href="https://profiles.google.com/<?php echo $google_info; ?>" title="My Googleplus" target="_blank"><img src="<?php echo $pluginurl; ?>/assets/images/googleplus.png" /></a></span>
	<?php
	}
		?>
		<?php
		if ($twitter_info) {
		?>
		<span class="twitter-info"><a href="https://twitter.com/<?php echo $twitter_info; ?>" title="My Twitter" target="_blank" class="twitter-follow-button""><img src="<?php echo $pluginurl; ?>/assets/images/twitter.png" /></a></span>
	<?php
	}
	echo '</div>';
}
add_filter( 'bp_before_member_header_meta', 'member_social_extend' ); ?>