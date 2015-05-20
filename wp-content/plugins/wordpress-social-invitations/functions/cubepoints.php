<?php
/**
 * 
 * functions to add Cubepoints. 
 * Wrapped here to keep things organized
 *
 */
 
cp_module_register( 

	__( 'Wordpress Social Invitations', 'cp', 'wsi' ),
	'wsi_pointsforinvites',
	'1.0',
	'Damian Logghe',
	'http://wp.timersys.com',
	'http://wp.timersys.com/wordpress-social-invitations/',
	__( 'This module awards points when a user sends an invitation though Wordpress Social Invitations Plugin or when a user accepts an invitations.', 'wsi', 'wsi' ), 1 );

// If our module is activated and WSI is installed
if ( cp_module_activated( 'wsi_pointsforinvites' ) && class_exists( 'WP_Social_Invitations' ) ) {

	/** Module Configuration */
	add_action( 'cp_config_form', 'wsi_points_admin' );
	function wsi_points_admin() {
	
		// Grab Settings
		$points_per_invite = get_option( 'wsi_points_invite' );
		if ( !$points_per_invite )
			$points_per_invite = 1;

		$max_invites = get_option( 'wsi_max_invite' );
		if ( !$max_invites )
			$max_invites = 0;

		$points_per_accept = get_option( 'wsi_points_accept' );
		if ( !$points_per_accept )
			$points_per_accept = 5;

		$max_accepts = get_option( 'wsi_max_accept' );
		if ( !$max_accepts )
			$max_accepts = 0;

		// Echo settings
		echo '
		<br />

		<h3>Wordpress Social Invitations Module</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label>' . __( 'Points per Invite', 'wsi' ) . '</label></th>
				<td valign="middle">
					<input type="text" name="cpc-points-per-invite" id="cpc-points-per-invite" value="' . $points_per_invite . '" /><br />
					' . __( 'Points for Sending an Invitation.', 'wsi' ) . '
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>' . __( 'Maximum Number', 'wsi' ) . '</label></th>
				<td valign="middle">
					<input type="text" name="cpc-max-invites" id="cpc-max-invites" value="' . $max_invites . '" /><br />
					' . __( 'Maximum number of invites that grants points. Zero for unlimited.', 'wsi' ) . '
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>' . __( 'Points per Acceptance', 'wsi' ) . '</label></th>
				<td valign="middle">
					<input type="text" name="cpc-points-per-accept" id="cpc-points-per-accept" value="' . $points_per_accept . '" /><br />
					' . __( 'Points for each Accepted Invitation.', 'wsi' ) . '
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>' . __( 'Maximum Number', 'wsi' ) . '</label></th>
				<td valign="middle">
					<input type="text" name="cpc-max-accept" id="cpc-max-accept" value="' . $max_accepts . '" /><br />
					' . __( 'Maximum number of accepted invitations that grants points. Zero for unlimited.', 'wsi' ) . '
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>' . __( 'Reset', 'wsi' ) . '</label></th>
				<td valign="middle">
					<input type="text" name="cpc-reset" id="cpc-reset" value="" /><br />
					' . __( 'Type in: reset and click "Update Options" to reset all counters.', 'wsi' ) . '
					<input type="hidden" name="invite-points-nonce" id="invite-points-custom-nonce" value="' . wp_create_nonce( 'wsi-custom-module-points-for-invites' ) . '" />
				</td>
			</tr>
		</table>' . "\n";
	}
	
	/** Save Module Congif */
	add_action( 'cp_config_process', 'wsi_points_save' );
	function wsi_points_save( $post_id ) {
	
		// Checks
		if ( !wp_verify_nonce( $_POST['invite-points-nonce'], 'wsi-custom-module-points-for-invites' ) ) return $post_id;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
		
		// Save Points per invite
		$points_invite = $_POST['cpc-points-per-invite'];
		if ( empty( $points_invite ) || !is_numeric( $points_invite ) ) $points_invite = 0;
		update_option( 'wsi_points_invite', $points_invite );
		
		// Save Max invites
		$max_invite = $_POST['cpc-max-invites'];
		if ( empty( $max_invite ) || !is_numeric( $max_invite ) ) $max_invite = 0;
		update_option( 'wsi_max_invite', $max_invite );
		
		// Save Points per accept
		$points_accept = $_POST['cpc-points-per-accept'];
		if ( empty( $points_accept ) || !is_numeric( $points_accept ) ) $points_accept = 0;
		update_option( 'wsi_points_accept', $points_accept );
		
		// Save Max accepts
		$max_accept = $_POST['cpc-max-accept'];
		if ( empty( $max_accept ) || !is_numeric( $max_accept ) ) $max_accept = 0;
		update_option( 'wsi_max_accept', $max_accept );
		
		// If a reset has been requested
		if ( isset( $_POST['cpc-reset'] ) && $_POST['cpc-reset'] === 'reset' )
			wsi_reset_all_invite_counts();
	}
	
	/* Hook WSI Invitations sent */
	add_action( 'wsi_invitation_sent', 'wsi_points', 10 );
	function wsi_points( $user_id ) {
	
		//this is not a registered user
		if( empty($user_id)) return;
		
		// Grab settings
		$points_per_invite = get_option( 'wsi_points_invite' );
		$max_invites = get_option( 'wsi_max_invite' );
		
		if ( $max_invites != 0 ) {
			// Get number of invites already sent by this user
			$existing_number_of_invites = get_user_meta( $user_id, 'wsi_invites_counter', true );
			if ( !$existing_number_of_invites ) $existing_number_of_invites = 0;
		}
		else $existing_number_of_invites = 0;
		
		// Check if this invite gives points
		if ( $existing_number_of_invites <= $max_invites ) {
		
			// Give Points
			cp_points( 'wsi_sentinvite', $user_id, $points_per_invite, '' );
			
			if ( $max_invites != 0 ) {
				// Update counter
				$new_number_of_invites = $existing_number_of_invites+1;
				update_user_meta( $user_id, 'wsi_invites_counter', $new_number_of_invites );
			}
		}
	}
	
	/* Hook into WSI Invite Acceptance */
	add_action( 'wsi_invitation_accepted', 'wsi_accepted', 10, 2 );
	function wsi_accepted( $user_id, $stat ) {
	
		// Grab settings
		$points_per_accept = get_option( 'wsi_points_accept' );
		$max_accepts = get_option( 'wsi_max_accept' );
		
		//this is not a registered user
		if( empty($user_id)) return;		
		
			if ( $max_accepts != 0 ) {
				// Grab inviters existing number of acceptances
				$existing_number_of_accepts = get_user_meta( $inviter_id, 'wsi_invite_acceptance_counter', true );
				if ( !$existing_number_of_accepts ) $existing_number_of_accepts = 0;
			}
			else $existing_number_of_accepts = 0;
			
			// Check if this acceptance gives points
			if ( $existing_number_of_accepts <= $max_accepts ) {
			
				// Give Points
				cp_points( 'wsi_accept_invite', $user_id, $points_per_accept, '' );
			
				if ( $max_accepts != 0 ) {
					// Update counter
					$new_number_of_accepts = $existing_number_of_accepts+1;
					update_user_meta( $inviter_id, 'wsi_invite_acceptance_counter', $new_number_of_accepts );
				}
			}
		}
	
	
	/** Adjust the Log. */
	add_action( 'cp_logs_description', 'wsi_points_logging', 10, 4 );
	function wsi_points_logging( $type, $uid, $points, $data ) {
		
		// Log
		if ( $type == 'wsi_sentinvite' )
			_e( 'Points for sending an invite.' ,'wsi');
		elseif ( $type == 'wsi_accept_invite' )
			_e( 'Points for invitation accepted','wsi' );
		else
			return;
	}
	
	/* Remove all invite and acceptance counters */
	function wsi_reset_all_invite_counts() {
	
		// Grab all users that have sent an invite at some point
		$users = get_users( array( 'meta_key' => 'wsi_invites_counter' ) );
		foreach ( $users as $user ) {
			// Delete counters
			delete_user_meta( $user->ID, 'wsi_invites_counter' );
			delete_user_meta( $user->ID, 'wsi_invite_acceptance_counter' );
		}
	}
}
?>