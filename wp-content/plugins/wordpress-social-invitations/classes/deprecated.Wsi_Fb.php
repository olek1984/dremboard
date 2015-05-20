<?php
/**
 * DEPRECATED
 * not being used any more as we changed to SEND DIALOG METHOD
 * Handles Facebook invitations
 * @since 1.4
 * @version 1.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; 

$wsi = WP_Social_Invitations::get_instance();

class Wsi_Fb{
 
    private $_friends;
    private $_session_data;
    private $_subject;
    private $_message;
	private $_display_name;
    private $_user_id;	
	private $_options;
    private $adapter;
    
 	public function __construct( $queue_data )
 	{
 		global $wsi;
 		

 		$this->_session_data 	= $queue_data->sdata;
 		$this->_friends 		= unserialize($queue_data->friends);
 		$this->_options			= $wsi->getOptions();
 		//this won't work with local url
 		$this->_message 		= stripslashes($queue_data->message .'
 		'. $this->_options['text_non_editable_message'].' ');
 		$this->_display_name	= $queue_data->display_name;
 		$this->_user_data 		= get_userdata($queue_data->user_id);
 		$this->_user_id 		= $queue_data->user_id;
 		$this->_id				= $queue_data->id;
 		$this->_i_count 		= $queue_data->i_count;
 		
 		try{
 			$hybrid 	= $wsi->create_hybridauth('facebook');
	 		$hybrid->restoreSessionData(base64_decode($this->_session_data));
	 		
	 		$this->adapter = $hybrid->getAdapter('facebook');
	 		
	 		
	 	 }
	 	 catch( Exception $e ){
		 	 Wsi_Logger::log( " - Wsi_FB: cannot load adapter " . $e->getMessage());
		 }	
	 	
	 	//lets add some limits to not block fb. 100 friends every 3 min
	 	$this->_limit			= 100;
 		$this->_every			= 180; //3 min
 	}
 	
 	function process(){
	 	
	 	global $wpdb;
	 	
	 	$sent_on_batch = 0;
	 	$this->replacePlaceHolders();
	 	$delete_row = true;
 		
 		$CLOSE_XML = '</stream:stream>';
 		$profile = $this->adapter->getUserProfile();
 		$at 	 = $this->adapter->getAccessToken();
 		
 		$status  = false;
 		

		$options = array(
		    'uid' => $profile->identifier.'@chat.facebook.com',
		    'app_id' => $this->adapter->config['keys']['id'],
		    'server' => 'chat.facebook.com',
		   );
		
			try{   	
			   	$fp = $this->xmpp_connect($options, $at['access_token']); 
			}
			catch( Exception $e ){
			 	 Wsi_Logger::log( " - Wsi_FB: cannot xmpp connect " . $e->getMessage());
			}   	

			if(! is_resource($fp))
			{
				error_log("An error ocurred, could not connect to chat.facebook.com . Enable debug to see error - Falling back to post wall",0);
		    
				$this->post_to_wall();
				$sent_on_batch = 1;
				do_action('wsi_invitation_sent', $this->_user_id );				

			}
			else
			{
			
				//we are connected, let send messages
					
				foreach(  $this->_friends as $key => $friend_id )
				{
				    $this->send_xml($fp,'<presence xmlns="jabber:client" id="3"><status>available!</status><show>dnd</show><priority>10</priority></presence><message xmlns="jabber:client" to="-'.$friend_id.'@chat.facebook.com"><body>'.$this->_message.'</body></message>', 'sending message');
					usleep(25000);

					$this->_total_sent++;
					$sent_on_batch++;
					
					do_action('wsi_invitation_sent', $this->_user_id );									
					
					unset($this->_friends[$key]);
 			
		 			//if we reach our limit
		 			if( $this->_total_sent == $this->_limit )
		 			{
		 				$send_at = time() + $this->_every; //when to send next bacth
		 				
		 				//if we still have friends on this batch
		 				if( $sent_on_batch < $this->_i_count)
		 				{
		 					//we update count and send date
		 					$mails_left = $this->_i_count - $sent_on_batch;
		 					
		 					$friends_a 	= serialize($this->_friends);
		 					
		 					$wpdb->query( "UPDATE {$wpdb->base_prefix}wsi_queue SET i_count = '$mails_left', send_at = '$send_at', friends = '$friends_a'  WHERE id = '$this->_id'");
		 					
		 					$delete_row = false; // we can't delete this yet
		 				}
		 				else //we don't have more mails on this batch but we reached our $this->_limit  limit every $this->_every
		 				{
		 					//be sure to update the next record in db that send emails
		 					$next_id = $wpdb->get_var("SELECT id FROM {$wpdb->base_prefix}wsi_queue WHERE id > '$this->_id' AND (provider = 'facebook') ORDER BY id ASC LIMIT 1");
		 					
		 					$wpdb->query( "UPDATE {$wpdb->base_prefix}wsi_queue SET send_at = '$send_at' WHERE id = '$next_id' ");
		 				}
		 				//exit our sending routine
		 				break;
 					}
 					 				
				}
				
				// we made it! Close connection
				$this->send_xml($fp, $CLOSE_XML);
				Wsi_Logger::log('Closing Connection');
			
				if( $this->find_xmpp($fp, 'ERROR')) { 
			    
			    	error_log("An error ocurred, could not connect to chat.facebook.com . Enable debug to see error - Falling back to post wall",0);
			    
					$this->post_to_wall();
					$sent_on_batch = 1;
					do_action('wsi_invitation_sent', $this->_user_id );				
			   
				}
				fclose($fp);
			}
		  		 		
 		//we shared once let save to stats
 		Wsi_Logger::log_stat('facebook',$this->_user_id, $sent_on_batch, $this->_id, $this->_display_name );
 		
 		// we finish with this row, lets delete it
 		if( $delete_row ) $wpdb->query("DELETE FROM {$wpdb->base_prefix}wsi_queue WHERE id ='$this->_id'");
 		
 		//IF we finish our batch and we haven't reach our limit we proccess next row in db
 		if( $this->_total_sent < $this->_limit )
 		{
 			
 			$queue_data = $wpdb->get_row("SELECT id, sdata, friends, subject, message, send_at, i_count, user_id, display_name FROM {$wpdb->base_prefix}wsi_queue WHERE provider = 'facebook' AND id > '$this->_id' ORDER BY id ASC LIMIT 1");
 			
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
						Wsi_Logger::log( "Wsi_FB: Facebook queue proccesing error - " . $e->getMessage());
				}	
			}	
 			
 		}
		

 	}
 	
 	function post_to_wall(){
 			$attachment = array(
		    'message' => $this->_message, 
		   # 'name' => 'This is my demo Facebook application!',
		   # 'caption' => "Caption of the Post",
		   # 'link' => 'http://mylink.com',
		   # 'description' => 'this is a description',
		   # 'picture' => 'http://mysite.com/pic.gif',
		    'actions' => array(
		        array(
		            'name' => 'WP Social Invitations',
		            'link' => 'http://www.timersys.com/plugins-wordpress/wordpress-social-invitations/'
		        )
		    )
			);
		
			$result = $this->adapter->api()->api('/me/feed/', 'post', $attachment);	
 	
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
	}

	
 	private function setNewData( $queue_data ,$total_sent = 0)
 	{
 		global $wsi;
 		
 		$this->_id 				= $queue_data->id;
 		$this->_friends 		= unserialize($queue_data->friends);
 		$this->_message 		= stripslashes($queue_data->message .'
 		'. $this->_options['text_non_editable_message']);
 		$this->_i_count 		= $queue_data->i_count;
 		$this->_total_sent 		= $total_sent;
 		
 		try{
 			$hybrid 	= $wsi->create_hybridauth('facebook');
	 		$hybrid->restoreSessionData(base64_decode($this->_session_data));
	 		
	 		$this->adapter = $hybrid->getAdapter('facebook');
	 		
	 		
	 	 }
	 	 catch( Exception $e ){
		 	 echo " - Wsi_FB: cannot load adapter " . $e->getMessage();
		 }		 	
 	}		
 	
 	function xmpp_connect($options, $access_token) {
	  	$STREAM_XML = '<stream:stream '.
		  'xmlns:stream="http://etherx.jabber.org/streams" '.
		  'version="1.0" xmlns="jabber:client" to="chat.facebook.com" '.
		  'xml:lang="en" xmlns:xml="http://www.w3.org/XML/1998/namespace">';
		
		$AUTH_XML = '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" '.
		  'mechanism="X-FACEBOOK-PLATFORM"></auth>';
		
		$CLOSE_XML = '</stream:stream>';
		
		$RESOURCE_XML = '<iq type="set" id="3">'.
		  '<bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">'.
		  '<resource>fb_xmpp_script</resource></bind></iq>';
		
		$SESSION_XML = '<iq type="set" id="4" to="chat.facebook.com">'.
		  '<session xmlns="urn:ietf:params:xml:ns:xmpp-session"/></iq>';
		
		$START_TLS = '<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>';
	
	  $fp = $this->open_connection($options['server']);
	  if (!$fp || $fp === false) {
	  	Wsi_Logger::log('Could not open connection to ' . $options['server']);
	    return false;
	  }
	
	  // initiates auth process (using X-FACEBOOK_PLATFORM)
	  $this->send_xml($fp,  $STREAM_XML, 'STREAM_XML');
	  if (!$this->find_xmpp($fp, 'STREAM:STREAM')) {
	    return false;
	  }
	  if (!$this->find_xmpp($fp,  'MECHANISM', 'X-FACEBOOK-PLATFORM')) {
	    return false;
	  }
	
	  // starting tls - MANDATORY TO USE OAUTH TOKEN!!!!
	  $this->send_xml($fp,  $START_TLS, 'START_TLS');
	  if (!$this->find_xmpp($fp, 'PROCEED', null, $proceed)) {
	    return false;
	  }
	  
	  if( !stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT))
	  {
	  	
	  	throw new Exception('stream_socket_enable_crypto failed');
	  	
	  }
	   
	  $this->send_xml($fp, $STREAM_XML, 'STREAM_XML');
	  if (!$this->find_xmpp($fp, 'STREAM:STREAM')) {
	    return false;
	  }
	  if (!$this->find_xmpp($fp, 'MECHANISM', 'X-FACEBOOK-PLATFORM')) {
	    return false;
	  }
	
	  // gets challenge from server and decode it
	 $this->send_xml($fp, $AUTH_XML,'AUTH_XML');
	 
	  if (!$this->find_xmpp($fp,  'CHALLENGE', null, $challenge)) {
	    return false;
	  }
	  
	  $challenge = base64_decode($challenge);
	  $challenge = urldecode($challenge);
	  parse_str($challenge, $challenge_array);
	
	  // creates the response array
	  $resp_array = array(
	    'method' => $challenge_array['method'],
	    'nonce' => $challenge_array['nonce'],
	    'access_token' => $access_token,
	    'api_key' => $options['app_id'],
	    'call_id' => 0,
	    'v' => '1.0',
	  );
	  // creates signature
	  $response = http_build_query($resp_array);
	
	  // sends the response and waits for success
	  $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'.
	    base64_encode($response).'</response>';
	  $this->send_xml($fp, $xml,'xml response');
	  
	  if (!$this->find_xmpp($fp, 'SUCCESS')) {
	    return false;
	  }
	
	  // finishes auth process
	  $this->send_xml($fp, $STREAM_XML, 'STREAM_XML');
	  if (!$this->find_xmpp($fp,'STREAM:STREAM')) {
	    return false;
	  }
	  if (!$this->find_xmpp($fp, 'STREAM:FEATURES')) {
	    return false;
	  }
	 $this->send_xml($fp, $RESOURCE_XML, 'RESOURCE_XML');
	  if (!$this->find_xmpp($fp, 'JID')) {
	    return false;
	  }
	  $this->send_xml($fp, $SESSION_XML, 'SESSION_XML');
	  if (!$this->find_xmpp($fp, 'SESSION')) {
	    return false;
	  }
	  
	
	  return $fp;
	}

 	function open_connection($server) {
	  
	  Wsi_Logger::log("[INFO] Opening connection... ");
	  
	  $port = 5222;
	  
	
	  $fp = fsockopen($server, $port, $errno, $errstr,999);
	  if (!$fp || $fp === false) {
	    Wsi_Logger::log("$errstr ($errno)");
	  } else {
	    Wsi_Logger::log("connnection open");
	  }
	  stream_set_timeout($fp, 999);
	  return $fp;
	}
	
	function send_xml($fp, $xml, $action = '') {
	  if( ! is_resource($fp)) return null;
	  	
	  $result = fwrite($fp, $xml);
	  Wsi_Logger::log("SEND XML: ".$action.' - ' . $result.' - ' . $xml);
	  return $result;
	}
	
	function recv_xml($fp,  $size=4096) {
		  
		  if( ! is_resource($fp)) return null;
		 
		  $xml = fread($fp, $size);
		  
		  //not sure why fsockopen is returning chains splited in two
		  //pos of <
		  $pos = strpos($xml, '<');
		  Wsi_Logger::log("RECEIVED XML:  - Position of < " . $pos);
		  if( $pos != 0 || $pos === false ) $xml = '<' . $xml; 
		  	 
		  Wsi_Logger::log("RECEIVED XML:  - " . $xml);
		  
		  if ($xml === "") {
		     return null;
		  }
		
		  // parses xml
		  $xml_parser = xml_parser_create();
		  xml_parse_into_struct($xml_parser, $xml, $val, $index);
		  xml_parser_free($xml_parser);
		
		  return array($val, $index);
		}
		
	function find_xmpp($fp,  $tag, $value=null, &$ret=null) {
	  static $val = null, $index = null;
	  
	   if( ! is_resource($fp)) return null;
	
	  do {
	    if ($val === null && $index === null) {
	      list($val, $index) = $this->recv_xml($fp);
	      if ($val === null || $index === null) {
	      	Wsi_Logger::log("FIND XMPP -1: ".$tag);
	        return false;
	      }
	    }
	
	    foreach ($index as $tag_key => $tag_array) {
	      if ($tag_key === $tag) {
	        if ($value === null) {
	          if (isset($val[$tag_array[0]]['value'])) {
	            $ret = $val[$tag_array[0]]['value'];
	          }
	          return true;
	        }
	        foreach ($tag_array as $i => $pos) {
	          if ($val[$pos]['tag'] === $tag && isset($val[$pos]['value']) &&
	            $val[$pos]['value'] === $value) {
	              $ret = $val[$pos]['value'];
	              return true;
	          }
	        }
	      }
	    }
	    $val = $index = null;
	  } while (!feof($fp));
	  Wsi_Logger::log("FIND XMPP 2: ".$tag);
	  return false;
	}

	 	
 	
}