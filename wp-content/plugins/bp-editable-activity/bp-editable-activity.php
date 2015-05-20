<?php

/**
 * Plugin Name: BuddyPress Editable Activity
 * Version: 1.0.2
 * Plugin URI: http://buddydev.com/plugins/bp-editable-activity/
 * Author: Brajesh Singh (BuddyDev)
 * Author URI: http://buddydev.com
 * Description: Allow Users to Edit their activity/comment on BuddyPress based social network
 * License: GPL
 * 
 */

class BP_Editable_Activity_Helper{
    
    
    private static $instance;
    private $plugin_url;
    private $plugin_path;
    
    private function __construct() {
    
        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
        
        add_action( 'bp_loaded', array( $this, 'load' ) );
        
        add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );
       
        add_action( 'bp_enqueue_scripts', array( $this, 'load_css' ) );
        
        //add_action( 'bp_activity_entry_meta', array( $this, 'edit_activity_btn' ) );
        add_action( 'bp_activity_comment_options', array( $this, 'edit_activity_comment_btn' ) );
        
        add_action( 'bp_include', array( $this, 'load_textdomain' ) );
        
        
    }
    
    public static function get_instance(){
        
        if( !isset( self::$instance ) )
            self::$instance = new self();
        
        return self::$instance;
    }
    
    
    public function load(){
        
        if( !bp_is_active( 'activity' ) )
            return;
        
        require_once $this->plugin_path . 'functions.php';
        require_once $this->plugin_path . 'ajax.php';
        
        if( is_admin() )
            require_once $this->plugin_path . 'admin/admin.php';
        
    }
    
     /**
     * Load plugin textdomain for translation
     */
    public function load_textdomain(){
        
         $locale = apply_filters( 'bp_editab_activity_get_locale', get_locale() );
        
        // if load .mo file
        if ( !empty( $locale ) ) {
            $mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );
            $mofile = apply_filters( 'bp_ditable_activity_textdomain_mofile', $mofile_default );

                if (is_readable( $mofile ) ) 
                    // make sure file exists, and load it
                    load_textdomain( 'bp-editable-activity', $mofile );
        }
       
    }
    
    public function load_js(){
        
        if( is_admin() || !is_user_logged_in() )
            return ;
           
        if( $this->should_load_assets() ){
            wp_register_script( 'jquery-editable', $this->plugin_url . 'assets/jqe/jqueryui-editable/js/jqueryui-editable.min.js', array( 'jquery', 'jquery-ui-tooltip','jquery-ui-button' ) );
            wp_register_script( 'editable-activity', $this->plugin_url . 'assets/editable-activity.js', array( 'jquery', 'jquery-editable' ) );


            wp_enqueue_script( 'jquery-editable' );
            wp_enqueue_script( 'editable-activity' );

            $this->localize_js();
        }
        
        
        
    }
    
    public function localize_js(){
        
        $data = array(
            'edit_activity_title' => __( 'Edit Activity', 'bp-editable-activity' ),
            'edit_comment_title' => __( 'Edit Comment', 'bp-editable-activity')
        );
        
        wp_localize_script('editable-activity', 'BPEditableActivity', $data );
    }
    
    public function load_css(){
        
        if( is_admin() || !is_user_logged_in() )
            return ;
        //only on activity pages
        if( $this->should_load_assets() ){
            wp_register_style( 'jquery-editable-css', $this->plugin_url . 'assets/jqe/jqueryui-editable/css/jqueryui-editable.css');
            wp_register_style( 'jquery-editable-ui-css', $this->plugin_url . 'assets/jqui/jquery-ui-1.10.4.custom.css');

            wp_enqueue_style( 'jquery-editable-5css' );
            wp_enqueue_style( 'jquery-editable-css' );
            wp_enqueue_style( 'jquery-editable-ui-css' );

        }
    }
    
    public function should_load_assets(){
        $load = false;
        
        if( bp_is_activity_component() || bp_is_current_action( 'activity' ) || function_exists('bp_is_group_home') && bp_is_group_home() )
            $load = true;
        //sometimes , you may want to load it on other pafge
        return apply_filters( 'bp_editable_activity_should_load_assets', $load );
        
    }

    /**
     * Show edit activity button?
     * 
     * @return type
     */
    public function edit_activity_btn(){
        //other wise check time
        if( bp_editable_activity_get_setting( 'allow_activity_editing' ) !='yes' )
            return;
        
        global $activities_template;
        $activity = $activities_template->activity;
        
        $activity_id = bp_get_activity_id();
        
        if( !bp_editable_is_editable_activity( $activity ) || !bp_editable_activity_activity_has_remaining_time( $activity ) || !bp_editable_activity_can_edit_activity( $activity )  )
            return ;
        
        
        //only if admin or my own activity
        
        $data = bp_get_activity_content_body();
        
        $edit_label = __( 'Edit', 'bp-editable-activity' );
        
        $btn = "<a href='#' class='button acomment-edit bp-primary-action' id='acomment-edit-" . $activity_id . "' data-type='textarea' data-value='" . esc_attr( $data ) . "' data-id='" . $activity_id . "'>{$edit_label}</a>";
        
        echo $btn;
        
        wp_nonce_field( 'edit-activity-' . $activity_id, '_activity_edit_nonce_' . $activity_id );
    }
    
    /**
     * Shgow Edit comment button
     * 
     * @return type'
     */
    public function edit_activity_comment_btn(){
        
        //if editing is disabled
        if( bp_editable_activity_get_setting( 'allow_comment_editing' ) !='yes' )
            return ;

        $activity_id = bp_get_activity_comment_id();
        
        $comment = bp_activity_current_comment();
        
        if( !bp_editable_activity_comment_has_remaining_time( $comment ) || !bp_editable_activity_can_edit_comment( $comment ) )
            return ;
        
        //only if admin or my own activity
        
        $content = bp_get_activity_comment_content();
        
        $activity_text = strip_tags(strstr($content, '<ul', true));
        if(strstr($content, '<ul', true) == false){
            $activity_text = $content;
        }
         
        $edit_label = __( 'Edit', 'bp-editable-activity' );
       
        $btn = "<a href='#' class='acomment-reply-edit bp-primary-action' id='acomment-reply-edit-" . $activity_id . "' data-type='textarea' data-value='" . esc_attr( $activity_text ) . "' data-id='" . $activity_id . "'>{$edit_label}</a>";
        
        echo $btn;
        
        wp_nonce_field( 'edit-activity-'. $activity_id, '_activity_edit_nonce_'. $activity_id );
    }
}

BP_Editable_Activity_Helper::get_instance();
