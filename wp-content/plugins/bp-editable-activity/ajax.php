<?php

class BP_Editable_Activity_Ajax_Helper{
    
    
    private static $instance;
    
    private function __construct() {
        
        add_action( 'wp_ajax_editable_activity_update', array( $this, 'update_activity' ) );
        add_action( 'wp_ajax_editable_activity_comment_update', array( $this, 'update_comment' ) );
    }
    
    public static function get_instance(){
        
        if( !isset( self::$instance ) )
            self::$instance = new self();
        
        return self::$instance;
    }
    
    
    public function update_activity(){
        
       // echo json_encode( $_POST );
        
        $activity_id = $_POST['pk'];
        
        if( !wp_verify_nonce( $_POST['nonce'], 'edit-activity-' . $activity_id ) ){
        
            wp_send_json ( array( 'error' => true, 'message' => __( 'Please Try again later!', 'bp-editable-activity' ) ) );
            
        }   
        
        $activity = new BP_Activity_Activity( $activity_id );
        
        if( empty( $activity ) ){
            
            wp_send_json ( array( 'error' => true, 'message' => __( 'Invalid Activity', 'bp-editable-activity' ) ) );
            
        }
        
        //check if activity editing is allowed
        if( bp_editable_activity_get_setting( 'allow_activity_editing') !='yes' ){
         
               wp_send_json ( array( 'error' => true, 'message' => __( 'Editing Activity is disabled.', 'bp-editable-activity' ) ) );
            
        }
        //now we will check if the user can actually edit the activity or not
        
        //let us see if time is remaining
        if( !bp_editable_activity_activity_has_remaining_time( $activity ) ){
               
            wp_send_json ( array( 'error' => true, 'message' => __( 'Allowed time for editing expired.', 'bp-editable-activity' ) ) );
            
        }
        //let us see if time is remaining
        if( !bp_editable_activity_can_edit_activity( $activity ) ){
               
            wp_send_json ( array( 'error' => true, 'message' => __( 'You are not allowed to edit this activity.', 'bp-editable-activity' ) ) );
            
        }
        
        
        //if we are here, means all is well
        //let us do the thing
        
        
        $old_content = $activity->content;
        //keep old activity and update time in meta
        if( bp_editable_activity_get_setting( 'keep_log' ) =='yes' ) {
            
            bp_activity_update_meta( $activity_id, 'original_activity_content', $old_content );
        
        } 
        
        bp_activity_update_meta( $activity_id, 'last_edited', current_time( 'timestamp' ) );
        
        $activity->content = $_POST['value'];
        
        $activity->save();
        
        ob_start();
        
        if ( bp_has_activities ( 'include=' . $activity_id ) ) {
		while ( bp_activities() ) {
			bp_the_activity();
			bp_locate_template( array( 'activity/entry.php' ), true );
		}
	}
        
        $content = ob_get_clean();
        
        wp_send_json_success( array( 'id' => $activity_id, 'content'=> $content ) );
        
        
    }
    
    
    public function update_comment(){
        	
        global $activities_template;
       // echo json_encode( $_POST );
        
        $activity_id = $_POST['pk'];
        
        if( !wp_verify_nonce($_POST['nonce'], 'edit-activity-'.$activity_id ) ){
         
            wp_send_json ( array('error' => true, 'message'=> __( 'Please Try again later!', 'bp-editable-activity' ) ) );
        
        }   
        
        $activity = new BP_Activity_Activity( $activity_id );
        
        if( empty( $activity ) ) {
            
            wp_send_json ( array( 'error' => true, 'message'=> __( 'Invalid Comment!', 'bp-editable-activity' ) ) );
            
        }
              //if we are here, make sure that the user can update the activity
        //check if activity editing is allowed
        if( bp_editable_activity_get_setting( 'allow_comment_editing') !='yes' ){
         
               wp_send_json ( array( 'error' => true, 'message' => __( 'Editing comment is disabled.', 'bp-editable-activity' ) ) );
            
        }
        //now we will check if the user can actually edit the activity or not
        
        //let us see if time is remaining
        if( !bp_editable_activity_comment_has_remaining_time( $activity ) ){
               
            wp_send_json ( array( 'error' => true, 'message' => __( 'Allowed time for editing expired.', 'bp-editable-activity' ) ) );
            
        }
        //let us see if time is remaining
        if( !bp_editable_activity_can_edit_comment( $activity ) ){
               
            wp_send_json ( array( 'error' => true, 'message' => __( 'You are not allowed to edit this comment.', 'bp-editable-activity' ) ) );
            
        }
        
        
        $old_content = $activity->content;
        
        //keep old activity and update time in meta
        if( bp_editable_activity_get_setting( 'keep_log' ) =='yes' ){
            
            bp_activity_update_meta( $activity_id, 'original_activity_content', $old_content );
        
        }   
        
        bp_activity_update_meta( $activity_id, 'last_edited', current_time( 'timestamp' ) );
        
        if(isset($_POST['name']) && $_POST['name'] == "activity-text"){
            $content_tail = strstr($old_content, '<ul');
            if($content_tail == ""){
                $new_content = $_POST['activity_text'];
            }else{
                $content_head = '<div class="rtmedia-activity-container"><div class="rtmedia-activity-text">'.$_POST['value'].'</div>';
                $new_content = $content_head.$content_tail;
                $new_content = preg_replace("/\'/", '"', $new_content);
                $activity->content = $new_content;
            }
        }else{
            $activity->content = $_POST['value'];
        }
        
        $activity->save();
       	// Load the new activity item into the $activities_template global
	bp_has_activities( 'display_comments=stream&hide_spam=false&include=' . $activity_id );

	// Swap the current comment with the activity item we just loaded
	$activities_template->activity                  = new stdClass;
	$activities_template->activity->id              = $activities_template->activities[0]->item_id;
	$activities_template->activity->current_comment = $activities_template->activities[0];

	$template = bp_locate_template( 'activity/comment.php', false, false );

	
	if ( empty( $template ) )
		$template = BP_PLUGIN_DIR . '/bp-themes/bp-default/activity/comment.php';

	load_template( $template, false );

	unset( $activities_template );
        
        $content = ob_get_clean();
        
        wp_send_json_success( array( 'id' => $activity_id,  'content' => $content ) );
        
        
    }
   
    
   
    
   
}

BP_Editable_Activity_Ajax_Helper::get_instance();
