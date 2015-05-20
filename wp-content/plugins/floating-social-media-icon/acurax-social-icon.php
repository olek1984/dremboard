<?php
/* 
Plugin Name: Customized Floating Social Media Icon
Plugin URI: http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/
Description: An easy to use plugin to show social media icons which floats on your browsers right bottom, which links to your social media profiles, You have option in plugin settings to configure social media profile urls and also can select icon style,order and size.
Author: Acurax 
Version: 9.3.4
Author URI: http://www.acurax.com 
License: GPLv2 or later
*/

/*

Copyright 2008-current  Acurax International  ( website : www.acurax.com )

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/ 
 define('ACX_SOCIALMEDIA_TOTAL_THEMES', 24);
?>
<?php
//*************** Include JS in Header ********
function enqueue_acx_social_icon_script()
{
	wp_enqueue_script ( 'jquery' );
}	add_action( 'get_header', 'enqueue_acx_social_icon_script' );
//*************** Include JS in Header Ends Here ********


//*********** Include Additional Menu ********************
function AcuraxLinks($links, $file) {
	$plugin = plugin_basename(__FILE__);
	// create link
	if ($file == $plugin) {
	
		return array_merge( $links, array( 
			'<div id="plugin_page_links"><a href="http://www.acurax.com?utm_source=wp&utm_medium=link&utm_campaign=plugin-page" target="_blank">' . __('Acurax International') . '</a>',
			'<a href="https://twitter.com/#!/acuraxdotcom" target="_blank">' . __('Acurax on Twitter') . '</a>',
			'<a href="http://www.facebook.com/AcuraxInternational" target="_blank">' . __('Acurax on Facebook') . '</a>',
			'<a href="http://www.acurax.com/services/web-designing.php?utm_source=wp&utm_medium=link&utm_campaign=plugin-page" target="_blank">' . __('Wordpress Theme Design') . '</a>',
			'<a href="http://www.acurax.com/services/blog-design.php?utm_source=wp&utm_medium=link&utm_campaign=plugin-page" target="_blank">' . __('Wordpress Blog Design') . '</a>',
			'<a href="http://www.acurax.com/contact.php?utm_source=wp&utm_medium=link&utm_campaign=plugin-page" target="_blank" style="border:0px;">' . __('Contact Acurax') . '</a></div>' 
		));
	}
	return $links;
}	add_filter('plugin_row_meta', 'AcuraxLinks', 10, 2 );
//*********************************************************

include('function.php');

//*************** Admin function ***************
function acx_social_icon_admin() 
{
	include('social-icon.php');
}
function acx_social_icon_help() 
{
	include('social-help.php');
}

function acx_social_icon_premium() 
{
	include('premium.php');
}

function acx_social_icon_misc() 
{
	include('fsmi-misc.php');
}

function acx_social_icon_admin_actions()
{
	add_menu_page(  'Acurax Social Icon Plugin Configuration', 'Floating Social Media Settings', 8, 'Acurax-Social-Icons-Settings','acx_social_icon_admin',plugin_dir_url( __FILE__ ).'/images/admin.ico' ); // 8 for admin
	
	add_submenu_page('Acurax-Social-Icons-Settings', 'Acurax Social Icon Premium', 'Premium', 8, 'Acurax-Social-Icons-Premium' ,'acx_social_icon_premium');
	
	add_submenu_page('Acurax-Social-Icons-Settings', 'Acurax Social Icon Misc Settings', 'Misc', 8, 'Acurax-Social-Icons-Misc' ,'acx_social_icon_misc');
	
	add_submenu_page('Acurax-Social-Icons-Settings', 'Acurax Social Icon Help and Support', 'Help', 8, 'Acurax-Social-Icons-Help' ,'acx_social_icon_help');
}
	
if ( is_admin() )
{
	add_action('admin_menu', 'acx_social_icon_admin_actions');
}
	
// Adding WUM Starts Here
function acurax_social_icon_update( $plugin_data, $r )
{
	// Get Current Plugin Data () Starts Here
	function current_plugin_info($value) 
	{
		if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		return $plugin_folder[$plugin_file][$value];
	} // Get Current Plugin Data () Starts Here
	
	$curr_ver   = current_plugin_info('Version');
	define ( 'CURRENT_VERSION', $curr_ver );
	$folder = basename( dirname( __FILE__ ) );
	// readme contents
	$data = file_get_contents( 'http://plugins.trac.wordpress.org/browser/'.$folder.'/trunk/readme.txt?format=txt' );
	if ($data)
	{
		$matches = null;
		$regexp = '~==\s*Changelog\s*==\s*=\s*[0-9.]+\s*=(.*)(=\s*' . preg_quote ( CURRENT_VERSION ) . '\s*=|$)~Uis';
		if ( preg_match ( $regexp, $data, $matches) )
		{
			$changelog = (array) preg_split ( '~[\r\n]+~', trim ( $matches[1] ) );
			$ret = '<div style="color: #c00;font-size: small; margin-top:8px;margin-bottom:8px">The Floating Social Media Plugin has been updated. Here is a change list, so you can see what\'s been changed or fixed:</div>';
			$ret .= '<div style="font-weight: normal;">';
			$ret .= '<p style="margin: 5px 0; font-weight:bold; font-size:small">= Latest Version =</p>';
			$ul = false;
			$first = false;
			foreach ( $changelog as $index => $line )
			{
				if ( preg_match ( '~^\s*\*\s*~', $line) )
				{
					if ( !$ul )
					{
						$ret .= '<ul style="list-style: disc; margin-left: 20px;">';
						$ul = true;
						$first = true;
					}
					$line = preg_replace ( '~^\s*\*\s*~', '', $line );
					if ( $first )
					{
						$ret .= '<li style="list-style-type:none;margin-left: -1.5em; font-weight:bold">Release Date:' . $line . '</li>';
						$first = false;
					}
					else
					{
						$ret .= '<li>' . $line . '</li>';
					}
				}
				else
				{
					if ( $ul )
					{
						$ret .= '</ul><div style="clear: left;"></div>';
						$ul = false;
					}
					$ret .= '<p style="margin: 5px 0; font-weight:bold; font-size:small">' . $line . '</p>';
				}
			}
			if ( $ul )
			{
				$ret .= '</ul>';
			}
			$ret .= '</div>';
		}
	}
	echo $ret;	
}
/**
* Add update messages that can be attached to the CURRENT release (not
* this one), but only for 2.8+
*/
global $wp_version;
if ( version_compare('2.8', $wp_version, '<=') ) 
{
	global $pagenow;
	if ( 'plugins.php' === $pagenow )
	{
		// Better update message
		$file   = basename( __FILE__ );
		$folder = basename( dirname( __FILE__ ) );
		$acx_add = "in_plugin_update_message-{$folder}/{$file}";
		add_action( $acx_add, 'acurax_social_icon_update', 20, 2 );
	}
}
// Adding WUM Ends Here

//!!!
add_action('admin_bar_menu', 'add_items');

function add_items($admin_bar)
{
//echo "<pre>";
//print_r($admin_bar);
//echo "<pre>";
$title = '';

		global $acx_si_display, $acx_si_icon_size;;
	if ($acx_si_display != "auto" || $acx_si_display == "both") 
	{
		/*
		$title .= "\n\n\n<!-- Starting Styles For Social Media Icon (PHP CODE) From Acurax International www.acurax.com 
		-->\n<style 
		type='text/css'>\n";
		$title .= "#short_code_si_icon img \n{\n";
		$title .= "width: " . $acx_si_icon_size . "px; \n}\n";
		$title .= "</style>\n<!-- Ending Styles For Social Media Icon (PHP CODE) From Acurax International www.acurax.com 
		-->\n\n\n\n";
		*/
		$title .= "<div id='short_code_si_icon1' style='text-align:right;'>";
		$title .= my_acurax_si_simple();
		$title .= "</div>";
	} 
	else $title .= "<!-- Select Display Mode as Manual To Show The Acurax Social Media Icons -->";
	
$admin_bar->add_menu( array(
    'id'    => 'my-item',
            'parent' => 'top-secondary',
    'title' => $title,
    'meta'  => array(
        'tabindex' => 10,
    ),
) );
}


function my_acurax_si_simple($theme = "") // Added Default "" // Updated << and V (alt added to Images Title Added to Links)
{
	// Getting Globals *****************************	
	global $acx_si_theme, $acx_si_credit, $acx_si_display , $acx_si_twitter, $acx_si_facebook, $acx_si_youtube, 		
	$acx_si_linkedin, $acx_si_gplus, $acx_si_pinterest, $acx_si_feed, $acx_si_icon_size;
	// *****************************************************
	if ($theme == "") { $acx_si_touse_theme = $acx_si_theme; } else { $acx_si_touse_theme = $theme; }
		//******** MAKING EACH BUTTON LINKS ********************
		if	($acx_si_twitter == "") { $twitter_link = ""; } else 
		{
			$twitter_link = "<a style='float:right;margin:2px;padding:0px;' href='http://www.twitter.com/". $acx_si_twitter ."' target='_blank' title='Visit Us On Twitter'>" . "<img src=" . plugins_url('images/themes/'. $acx_si_touse_theme .'/twitter.png', __FILE__) . " style='border:0px;width:24px;' alt='Visit Us On Twitter' /></a>";
		}
		if	($acx_si_facebook == "") { $facebook_link = ""; } else 
		{
			$facebook_link = "<a style='float:right;margin:2px;padding:0px;' href='". $acx_si_facebook ."' target='_blank' title='Visit Us On Facebook'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/facebook.png', __FILE__) . " style='border:0px;width:24px;' alt='Visit Us On Facebook' /></a>";
		}
		if	($acx_si_gplus == "") { $gplus_link = ""; } else 
		{
			$gplus_link = "<a style='float:right;margin:2px;padding:0px;' href='". $acx_si_gplus ."' target='_blank' title='Visit Us On Google Plus'>" . "<img src=" . plugins_url('images/themes/'. $acx_si_touse_theme .'/googleplus.png', __FILE__) . " style='border:0px;width:24px;' alt='Visit Us On Google Plus' /></a>";
		}
		if	($acx_si_pinterest == "") { $pinterest_link = ""; } else 
		{
			$pinterest_link = "<a style='float:right;margin:2px;padding:0px;' href='". $acx_si_pinterest ."' target='_blank' title='Visit Us On Pinterest'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/pinterest.png', __FILE__) . " style='border:0px;width:24px;' alt='Visit Us On Pinterest' /></a>";
		}
		if	($acx_si_youtube == "") { $youtube_link = ""; } else 
		{
			$youtube_link = "<a style='float:right;margin:2px;padding:0px;' href='". $acx_si_youtube ."' target='_blank' title='Visit Us On Youtube'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/youtube.png', __FILE__) . " style='border:0px;width:24px;' alt='Visit Us On Youtube' /></a>";
		}
		if	($acx_si_linkedin == "") { $linkedin_link = ""; } else 
		{
			$linkedin_link = "<a style='float:right;margin:2px;padding:0px;' href='". $acx_si_linkedin ."' target='_blank' title='Visit Us On Linkedin'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/linkedin.png', __FILE__) . " style='border:0px;width:24px;' alt='Visit Us On Linkedin' /></a>";
		}
		if	($acx_si_feed == "") { $feed_link = ""; } else 
		{
			$feed_link = "<a style='float:right;margin:2px;padding:0px;' href='". $acx_si_feed ."' target='_blank' title='Check Our Feed'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/feed.png', __FILE__) . " style='border:0px;width:24px;' alt='Check Our Feed' /></a>";
		}
		$social_icon_array_order = get_option('social_icon_array_order');
	$social_icon_array_order = unserialize($social_icon_array_order);
	$return_title = '';
	foreach ($social_icon_array_order as $key => $value)
	{
		if ($value == 0) { $return_title .= $twitter_link; } 
		else if ($value == 1) { $return_title .= $facebook_link; } 
		else if ($value == 2) { $return_title .= $gplus_link; } 
		else if ($value == 3) { $return_title .= $pinterest_link; } 
		else if ($value == 4) { $return_title .= $youtube_link; } 
		else if ($value == 5) { $return_title .= $linkedin_link; } 
		
		else if ($value == 6) { $return_title .= $feed_link; }
	}
	return $return_title;
}
?>