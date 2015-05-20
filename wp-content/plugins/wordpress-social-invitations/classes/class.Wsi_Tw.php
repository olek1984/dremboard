<?php
/**
 * Handles Twitter invitations
 * @since 1.4
 * @version 1.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; 

$wsi = WP_Social_Invitations::get_instance();

require_once (dirname (__FILE__) . '/Googl.class.php');

class Wsi_Tw{
 
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
 		$this->_i_count 		= $queue_data->i_count;
 		$this->_display_name	= $queue_data->display_name;
 		$this->_user_data 		= get_userdata($queue_data->user_id);
 		$this->_user_id 		= $queue_data->user_id;
 		$this->_total_sent 		= $total_sent;
 		$this->_session_data 	= $queue_data->sdata;
 		$this->_wsi_obj_id		= $queue_data->wsi_obj_id;
 		
 		try{
 			$hybrid 	= $wsi->create_hybridauth('twitter');
	 		$hybrid->restoreSessionData(base64_decode($this->_session_data));
	 		
	 		$this->adapter = $hybrid->getAdapter('twitter');
	 		

	 		
	    }
	 	catch( Exception $e ){
		 	 Wsi_Logger::log( " - Wsi_TW: cannot load adapter " . $e->getMessage());
		}
		 
 		
 		//this limits are per user per day
 		$this->_limit			= 250; //250
 		$this->_every			= 60 * 60 * 24; //one day
	 	
 	}
 	
 	function process(){
 		
 		global $wsi,$wpdb;
 		
 		$delete_row = true;
 		
 		$sent_on_batch = 0;
 		
 		$this->replacePlaceholders();
 		
 		foreach( $this->_friends as $key => $f )
 		{
 			
 			try{
	 			$this->adapter->sendDM(
	 				array( 'uid'	=> $f, 
	 					   'msg' 	=> $this->_message
	 					  )
	 					   			);
 			}
 			catch( Exception $e ){
 			
 				 Wsi_Logger::log( " - Wsi_TW: cannot post DM " . $e->getMessage());
 				 $dm_failed = true;
 				 break;
 				
 				 
 			}   			
 			usleep(500);
 			
 			do_action('wsi_invitation_sent', $this->_user_id, $this->_wsi_obj_id );		   			
 		
 			$this->_total_sent++;
 		
 			$sent_on_batch++;
 			
 			unset($this->_friends[$key]);
 			
 			//if we reach our limit
 			if( $sent_on_batch == $this->_limit )
 			{
 				$send_at = time() + $this->_every; //when to send next bacth
 				
 				//if we still have mails on this batch
 				if( $sent_on_batch < $this->_i_count)
 				{
 					//we update count and send date
 					$mails_left = $this->_i_count - $sent_on_batch;
 					
 					$friends_a 	= serialize($this->_friends);
 					
 					$wpdb->query( "UPDATE {$wpdb->base_prefix}wsi_queue SET i_count = '$mails_left', send_at = '$send_at', friends = '$friends_a'  WHERE id = '$this->_id'");
 					
 					$delete_row = false; // we can't delete this yet
 				}
 			/* we don't need this on tw as limits are per user
 				else //we don't have more mails on this batch but we reached our $this->_limit  limit every $this->_every
 				{
 					//be sure to update the next record in db that send emails
 					$next_id = $wpdb->get_var("SELECT id FROM {$wpdb->base_prefix}wsi_queue WHERE id > '$this->_id' AND provider = 'twitter' ORDER BY id ASC LIMIT 1");
 					
 					$wpdb->query( "UPDATE {$wpdb->base_prefix}wsi_queue SET send_at = '$send_at' WHERE id = '$next_id' ");
 				}
 			*/	
 				//exit our sending routine
 				break;
 			}
 		
 		}//endforeach
 		
 		//IF DM failed we need to update status
 		 if( isset($dm_failed))
 		 {
	 		 $this->adapter->setUserStatus($this->_message);
	 		 $this->_total_sent++;
	 		 $sent_on_batch++;
	 		 do_action('wsi_invitation_sent', $this->_user_id, $this->_wsi_obj_id );
 		 }
 		//save stats
 		Wsi_Logger::log_stat('twitter',$this->_user_id, $sent_on_batch, $this->_id,$this->_display_name, $this->_wsi_obj_id);
 		
 		// we finish with this row, lets delete it
 		if( $delete_row ) $wpdb->query("DELETE FROM {$wpdb->base_prefix}wsi_queue WHERE id ='$this->_id'");
 		
 		//Let's see if we have more in queue
 			
 		$queue_data = $wpdb->get_row("SELECT id, sdata, friends, subject, message, send_at, i_count, user_id, display_name, wsi_obj_id FROM {$wpdb->base_prefix}wsi_queue WHERE provider = 'twitter' AND id > '$this->_id' AND display_name != '$this->_display_name' ORDER BY id ASC LIMIT 1");
 			
		//if we have more rows, proccess them
		if( isset($queue_data->id) )
		{
			$this->setNewData($queue_data, 0);
		
			try{
				$result = $this->process();
			}
			catch( Exception $e ){
					//delete it from queue to avoid same error everytime
					#$wpdb->query("DELETE FROM {$wpdb->base_prefix}wsi_queue WHERE id = $queue_data->id");
					Wsi_Logger::log( "Wsi_Tw: Twitter queue proccesing error - " . $e->getMessage());
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
 		$this->_wsi_obj_id		= $queue_data->wsi_obj_id;
 		
 		try{
 			$hybrid 	= $wsi->create_hybridauth('twitter');
	 		$hybrid->restoreSessionData(base64_decode($this->_session_data));
	 		
	 		$this->adapter = $hybrid->getAdapter('twitter');
	 		
	 		
	    }
	 	catch( Exception $e ){
		 	 Wsi_Logger::log( " - Wsi_TW: cannot load adapter " . $e->getMessage());
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
		add_filter('wsi_placeholder_accepturl', array( $this, 'shortern_url'));
		
		$this->_message 			= Wsi_Queue::replacePlaceholders($display_name, $this->_id, $this->_user_id, $this->_message);
		
		
	}
	
	function shortern_url($url){
		$googl 		= new Googl();
		$shortened 	= $googl->shorten($url);
		unset($googl);
		
		return $shortened;
	}
	
}	