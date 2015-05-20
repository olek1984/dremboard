<?php 
/*
 * Plugin Name: Customized Buddypress Social Share
 * Plugin URI: 
 * Description: Customized Buddypress Social Share
 * Version: 9.0
 * Author: 
 * Author URI: 
 * License: GPL2
 */

function bp_social_init() {
	require ( dirname( __FILE__ ) . '/admin.php' );
	require( dirname( __FILE__ ) . '/includes/activity-sharing.php' );
	require( dirname( __FILE__ ) . '/includes/drem-sharing.php' ); //!!!!!!
}
add_action( 'bp_include', 'bp_social_init' );

    /**
     * Register buddy social styles
     */
    add_action( 'wp_enqueue_scripts', 'buddy_social_icons_stylesheet' );
    add_action( 'admin_init', 'buddy_social_icons_stylesheet' );

    /**
     * Enqueue buddy social styles
     */
    function buddy_social_icons_stylesheet() {
        // Respects SSL, Style.css is relative to the current file
        wp_register_style( 'buddy-social', plugins_url('css/buddy-social.css', __FILE__) );
        wp_register_style( 'buddy-social-socialicons', plugins_url('css/social_foundicons.css', __FILE__) );
        wp_register_style( 'buddy-social-generalicons', plugins_url('css/general_enclosed_foundicons.css', __FILE__) );
        wp_enqueue_style( 'buddy-social' );
        wp_enqueue_style( 'buddy-social-socialicons' );
        wp_enqueue_style( 'buddy-social-generalicons' );
    }

    // include the js for to toggle the social buttons
    function buddy_social_scripts_method() {
        wp_enqueue_script(
            'custom-script',
            plugins_url('/js/buddy-social.js', __FILE__),
            array( 'jquery' )
        );
    }

    // enqueue the iris colour picker
    add_action( 'wp_enqueue_scripts', 'buddy_social_scripts_method' );

    // add the iris colour picker
    add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
    function mw_enqueue_color_picker() {

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'my-script-handle', plugins_url('js/buddy-social-iris.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
    }

    // add custom social button colors to the header
    add_action( 'wp_head', 'my_custom_css_hook' );
    function my_custom_css_hook( ) {
        echo '<style type="text/css">
        .social-buttons a {
            color: ' . get_option('social_button_color') . ';
        }
        .social-buttons a:hover {
            color: ' . get_option('social_button_color_hover') . ';
        }
        </style>';
    }

?>