<?php
/*
Plugin Name: BuddyPress Family
Plugin URI: http://tomas.zhu.bz
Description: BuddyPress Family
Version: 1.0.0
Author: Tomas Zhu
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bp-family
Domain Path: /languages
*/

/**
 * BP family
 *
 * @package BP-family
 * @subpackage Loader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Only load the plugin code if BuddyPress is activated.
 */
	define( 'BP_family_DIR', dirname( __FILE__ ) );
	define( 'BP_family_URL', plugin_dir_url( __FILE__ ) );
	
function bp_family_init() {
	// some pertinent defines
	define( 'BP_family_DIR', dirname( __FILE__ ) );
	define( 'BP_family_URL', plugin_dir_url( __FILE__ ) );

	// only supported in BP 1.5+
	if ( version_compare( BP_VERSION, '1.3', '>' ) ) {
		require( constant( 'BP_family_DIR' ) . '/bp-familys-loader.php' );

	// show admin notice for users on BP 1.2.x
	} else {
		add_action( 'admin_notices', create_function( '', "
			echo '<div class=\"error\"><p>' . sprintf( __( \"Hey! BP family v1.2 requires BuddyPress 1.5 or higher.  If you are still using BuddyPress 1.2 and you don't plan on upgrading, use <a href='%s'>BP family v1.1.1 instead</a>.\", 'bp-family' ), 'https://github.com/r-a-y/buddypress-familyers/archive/1.1.x.zip' ) . '</p></div>';
		" ) );

		return;
	}
}
add_action( 'bp_include', 'bp_family_init' );

/**
 * Run the activation routine when BP-family is activated.
 *
 * @uses dbDelta() Executes queries and performs selective upgrades on existing tables.
 */
function bp_family_activate() {
	global $bp, $wpdb;

	$charset_collate = !empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET $wpdb->charset" : '';
	if ( !$table_prefix = $bp->table_prefix )
		$table_prefix = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );
		if ( empty( $bp->table_prefix ) )
			$bp->table_prefix = 'wp_';
			$bp_prefix = $bp->table_prefix;
			
	$sql[] = "CREATE TABLE {$bp_prefix}bp_familys (
	  		    id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  		    initiator_user_id bigint(20) NOT NULL,
	  		    family_user_id bigint(20) NOT NULL,
	  		    is_confirmed bool DEFAULT 0,
			    is_limited bool DEFAULT 0,
	  		    date_created datetime NOT NULL,
		        KEY initiator_user_id (initiator_user_id),
		        KEY family_user_id (family_user_id)
	 	       ) {$charset_collate};";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'bp_family_activate' );

/**
 * Run the deactivation routine when BP-family is deactivated.
 * Not used currently.
 */
function bp_family_deactivate() {
	// Cleanup.
}
//register_deactivation_hook( __FILE__, 'bp_family_deactivate' );

/**
 * Custom textdomain loader.
 *
 * Checks WP_LANG_DIR for the .mo file first, then the plugin's language folder.
 * Allows for a custom language file other than those packaged with the plugin.
 *
 * @uses load_textdomain() Loads a .mo file into WP
 */
function bp_family_localization() {
	$mofile		= sprintf( 'bp-family-%s.mo', get_locale() );
	$mofile_global	= trailingslashit( WP_LANG_DIR ) . $mofile;
	$mofile_local	= plugin_dir_path( __FILE__ ) . 'languages/' . $mofile;

	if ( is_readable( $mofile_global ) )
		return load_textdomain( 'bp-family', $mofile_global );
	elseif ( is_readable( $mofile_local ) )
		return load_textdomain( 'bp-family', $mofile_local );
	else
		return false;
}
add_action( 'plugins_loaded', 'bp_family_localization' );

function bp_familys_setup_globals() {
	global $bp, $wpdb;
		if ( empty( $bp->table_prefix ) )
			$bp->table_prefix = bp_core_get_table_prefix();
				
/*
	if ( !defined( 'BP_FOLLOWERS_SLUG' ) )
		define( 'BP_FOLLOWERS_SLUG', 'followers' );

	if ( !defined( 'BP_FOLLOWING_SLUG' ) )
		define( 'BP_FOLLOWING_SLUG', 'following' );
*/
	// For internal identification
	$bp->familys->id              = 'family';

	$bp->familys->table_name      = $bp->table_prefix . 'bp_familys';
	//var_dump("5036");
	//var_dump($bp->table_prefix);
	$bp->familys->slug = 'familys';

	/* Register this in the active components array */
	$bp->active_components[$bp->familys->id] = $bp->familys->id;

	// BP 1.2.x only
	if ( version_compare( BP_VERSION, '1.3' ) < 0 ) {
		$bp->follow->format_notification_function = 'bp_familys_format_notifications';
	}
	// BP 1.5-specific
	else {
		$bp->follow->notification_callback        = 'bp_familys_format_notifications';
	}
}
add_action( 'bp_setup_globals', 'bp_familys_setup_globals' );

function bp_is_user_familys() {
	if ( bp_is_user() && bp_is_familys_component() )
		return true;

	return false;
}



function bp_example_load_template_filter( $found_template, $templates ) {
	global $bp;

	if ( $bp->current_component != $bp->familys->slug )
		return $found_template;

	foreach ( (array) $templates as $template ) {
		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates[] = STYLESHEETPATH . '/' . $template;
		else
			//$filtered_templates[] = dirname( __FILE__ ) . '/includes/bp-templates/' . $template;
			$filtered_templates[] = dirname( __FILE__ ) . '/templates/' . $template;
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'bp_example_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'bp_example_load_template_filter', 10, 2 );

//if(bp_is_familys_component())
{
			//var_dump("9165000");
			//wp_enqueue_style( $asset['handle'], $asset['location'], array(), $this->version, 'screen' );
			//wp_enqueue_style('gavern-buddypress', 'buddypress.css', array('gavern-extensions'));
			wp_enqueue_style('gavern-buddypress', BP_family_URL.'css/mygk.buddypress.css', array('gavern-extensions'));
			//return;
}