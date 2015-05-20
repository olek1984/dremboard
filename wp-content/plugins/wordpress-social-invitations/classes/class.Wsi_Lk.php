<?php
/**
 * Handles Linkedin invitations
 * @since 1.4
 * @version 1.1.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; 

$wsi = WP_Social_Invitations::get_instance();


class Wsi_Lk{
 
    private $_friends;
    private $_session_data;
    private $_message;
    private $_total_sent;
    private $_i_count;
    private $_user_data;
    private $_user_id;
    private $_display_name;
    private $_options;
    private $adapter;
	
	private $limit;
    
 	public function __construct( $queue_data ,$total_sent = 0)
 	{
 		global $wsi;
 		
 		$this->_id 				= $queue_data->id;
 		$this->_friends 		= unserialize($queue_data->friends);
 		$this->_options			= $wsi->getOptions();
 		$this->_message 		= stripslashes($queue_data->message);
 		$this->_subject 		= stripslashes($queue_data->subject);
 		$this->_i_count 		= $queue_data->i_count;
 		$this->_display_name	= $queue_data->display_name;
 		$this->_user_data 		= get_userdata($queue_data->user_id);
 		$this->_user_id 		= $queue_data->user_id;
 		$this->_total_sent 		= $total_sent;
 		$this->_session_data 	= $queue_data->sdata;
 		$this->_wsi_obj_id 		= $queue_data->wsi_obj_id;

 		try{
 			$hybrid 	= $wsi->create_hybridauth('linkedin');
	 		$hybrid->restoreSessionData(base64_decode($this->_session_data));
	 		
	 		$this->adapter = $hybrid->getAdapter('linkedin');
	 		

	 		
	    }
	 	catch( Exception $e ){
		 	 Wsi_Logger::log( " - Wsi_Lk: cannot load adapter " . $e->getMessage());
		}
		 
 		
 		// Sends a message to up to 10 connections
 		// Application: 5k
 		// Per User: 10
 		
 		
 		$this->_limit			= 5000; //250
 		$this->_every			= strtotime('tomorrow'); //tomorrow 00 utc
	 	
 	}
 	
 	function process(){
 		
 		global $wsi,$wpdb;
 		
 		$delete_row = true;
 		
 		$sent_on_batch = 0;

 		$sent_messages = 0;
 		
 		$this->replacePlaceholders();
 		
 		$chunks = array_chunk($this->_friends , 10, true); //As per linkedin limits we sent 10 messaged with 10 connections each

 		foreach ( $chunks as $c )
 		{
 			$emails = array();
 			
	 		foreach( $c as $key => $f )
	 		{
	 			
	 			$emails[] = $f;
	 			 
	 			$this->_total_sent++;
	 			
	 			$sent_on_batch++;
				
				do_action('wsi_invitation_sent', $this->_user_id, $this->_wsi_obj_id );
					 				 			
	 			unset($this->_friends[$key]);
	 			
	 		}//endforeach
 			
 			//we can only send 10 messages
 			$sent_messages++;
 			
 			$args = array(
 				'body' 			=> $this->_message, 
 				'subject'		=> $this->_subject,
 				'recipients' 	=> $emails
 			);
			$this->adapter->sendMessages($args);
 					   			
 			usleep(500);
	 			
 			//if we reach our limit for user or our limit per app
 			if( $sent_messages == 10 || $this->_total_sent >= 5000 )
 			{
 				$send_at =  $this->_every; //when to send next bacth
 				
 				//if we still have mails on this user
 				if( $sent_on_batch < $this->_i_count)
 				{
 					//we update count and send date
 					$mails_left = $this->_i_count - $sent_on_batch;
 					
 					$friends_a 	= serialize($this->_friends);
 					
 					$wpdb->query( "UPDATE {$wpdb->base_prefix}wsi_queue SET i_count = '$mails_left', send_at = '$send_at', friends = '$friends_a'  WHERE id = '$this->_id'");
 					
 					$delete_row = false; // we can't delete this yet
 				}
 				else //we don't have more mails on this user but we reached our $this->_limit  limit every $this->_every
 				{
 					//be sure to update the next record in db that send emails
 					$next_id = $wpdb->get_var("SELECT id FROM {$wpdb->base_prefix}wsi_queue WHERE id > '$this->_id' AND provider = 'linkedin' ORDER BY id ASC LIMIT 1");
 					
 					$wpdb->query( "UPDATE {$wpdb->base_prefix}wsi_queue SET send_at = '$send_at' WHERE id = '$next_id' ");
 				}
 				
 				//exit our sending routine
 				break;
 			}
	 		
 		}
 		
 		Wsi_Logger::log_stat('linkedin',$this->_user_id, $sent_on_batch, $this->_id, $this->_display_name, $this->_wsi_obj_id);
 		
 		// we finish with this row, lets delete it
 		if( $delete_row ) $wpdb->query("DELETE FROM {$wpdb->base_prefix}wsi_queue WHERE id ='$this->_id'");
 		
 		
 		//IF we finish our batch and we haven't reach our limit we proccess next row in db
 		if( $this->_total_sent < $this->_limit )
 		{ 		
 			//Let's see if we have more in queue
 			
 			$queue_data = $wpdb->get_row("SELECT id, sdata, friends, subject, message, send_at, i_count, user_id, display_name, wsi_obj_id FROM {$wpdb->base_prefix}wsi_queue WHERE provider = 'linkedin' AND id > '$this->_id' AND display_name != '$this->_display_name' ORDER BY id ASC LIMIT 1");
 			
			//if we have more rows, proccess them
			if( isset($queue_data->id) )
			{
				$this->setNewData($queue_data, $this->_total_sent);
			
				try{
					$result = $this->process();
				}
				catch( Exception $e ){
						//delete it from queue to avoid same error everytime
						#$wpdb->query("DELETE FROM {$wpdb->base_prefix}wsi_queue WHERE id = $queue_data->id");
						Wsi_Logger::log( "Wsi_LK: Linkedin queue proccesing error - " . $e->getMessage());
				}
			}	
 		}	
 		
 		return $this->_total_sent;	
 	}
 	
 	private function setNewData( $queue_data ,$total_sent = 0)
 	{
 		global $wsi;
 		
 		$this->_id 				= $queue_data->id;
 		$this->_friends 		= unserialize($queue_data->friends);
 		$this->_message 		= stripslashes($queue_data->message);
 		$this->_subject 		= stripslashes($queue_data->subject);
 		$this->_i_count 		= $queue_data->i_count;
 		$this->_total_sent 		= $total_sent;
 		$this->_wsi_obj_id 		= $queue_data->wsi_obj_id;
 		
 		try{
 			$hybrid 	= $wsi->create_hybridauth('twitter');
	 		$hybrid->restoreSessionData(base64_decode($this->_session_data));
	 		
	 		$this->adapter = $hybrid->getAdapter('twitter');
	 		
	 		
	    }
	 	catch( Exception $e ){
		 	 Wsi_Logger::log( " - Wsi_Lk: cannot load adapter " . $e->getMessage());
		}	 	
 	}		
	
	function replacePlaceholders(){
		
		if(	$this->_display_name != '' )
		{
			$display_name = $this->_display_name;
		}
		elseif( $this->_user_data )
		{
			$display_name = $this->_user_data->display_name;
		}
		else
		{
			$display_name = '%%INVITERNAME%%'; // need to fix this for live users non registered
		}
		add_filter('wsi_placeholder_accepturl', array( 'Wsi_Queue', 'shorten_url'));
		
		$this->_message 			= Wsi_Queue::replacePlaceholders($display_name, $this->_id, $this->_user_id, $this->_message);
		$this->_subject 			= Wsi_Queue::replacePlaceholders($display_name, $this->_id, $this->_user_id, $this->_subject);
		
		
	}
	
	
}	