<?php
/**
* MyCred Hook
* @since v2
* @version 1.2
*/

class Wsi_MyCreed extends myCRED_Hook {
	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type = 'mycred_default' ) {
		parent::__construct( array(
			'id'       => 'wsi',
			'defaults' => array(
				'send_invite'   => array(
					'creds'        => 1,
					'log'          => '%plural% for sending an invitation',
					'limit'        => 0
				),
				'accept_invite' => array(
					'creds'        => 5,
					'log'          => '%plural% for accepted invitation',
					'limit'        => 0
				)
			)
		), $hook_prefs, $type );
	}

	/**
	 * Run
	 * @since v2
	 * @version 1.0
	 */
	public function run() {
		
		if ( $this->prefs['send_invite']['creds'] != 0 ) {
			add_action( 'wsi_invitation_sent',     array( $this, 'send_invite' ), 10, 2 );
		}
		if ( $this->prefs['accept_invite']['creds'] != 0 ) {
			add_action( 'wsi_invitation_accepted', array( $this, 'accept_invite' ), 10, 3 );
		}
	}

	/**
	 * Sending Invites
	 * @since v2
	 * @version 1.0
	 */
	public function send_invite( $user_id, $wsi_obj_id ) {

		//this is not a registered user
		if ( empty( $user_id ) ) return;

		// Make sure we only execute for the point type nominated via this filter.
		if ( apply_filters( 'wsi_cred_type', $this->mycred_type, $wsi_obj_id ) != $this->mycred_type ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit Check
		if ( $this->prefs['send_invite']['limit'] != 0 ) {
			$user_log = get_user_meta( $user_id, 'mycred_wsi', true );
			if ( empty( $user_log['sent'] ) ) $user_log['sent'] = 0;
			// Return if limit is reached
			if ( $user_log['sent'] >= $this->prefs['send_invite']['limit'] ) return;
		}

		// Award Points
		$this->core->add_creds(
			'wsi_sending_an_invite',
			$user_id,
			$this->prefs['send_invite']['creds'],
			$this->prefs['send_invite']['log'],
			'',
			'',
			$this->mycred_type
		);

		// Update limit
		if ( $this->prefs['send_invite']['limit'] != 0 ) {
			$user_log['sent'] = $user_log['sent']+1;
			update_user_meta( $user_id, 'mycred_wsi', $user_log );
		}

	}

	/**
	 * Accepting Invites
	 * @since v2
	 * @version 1.0
	 */
	public function accept_invite( $user_id, $stats ) {

		//this is not a registered user
		if ( empty( $user_id ) ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Make sure we only execute for the point type nominated via this filter.
		if ( apply_filters( 'wsi_cred_type', $this->mycred_type, $wsi_obj_id ) != $this->mycred_type ) return;

		// Limit Check
		if ( $this->prefs['send_invite']['limit'] != 0 ) {
			$user_log = get_user_meta( $user_id, 'mycred_wsi', true );
			if ( empty( $user_log['sent'] ) ) $user_log['sent'] = 0;
			// Return if limit is reached
			if ( $user_log['sent'] >= $this->prefs['send_invite']['limit'] ) return;
		}
		// Award Points
		$this->core->add_creds(
			'wsi_accepting_an_invite',
			$user_id,
			$this->prefs['accept_invite']['creds'],
			$this->prefs['accept_invite']['log'],
			'',
			'',
			$this->mycred_type
		);

		// Update Limit
		if ( $this->prefs['accept_invite']['limit'] != 0 ) {
			$user_log['accepted'] = $user_log['accepted']+1;
			update_user_meta( $user_id, 'mycred_wsi', $user_log );
		}

	}

	/**
	 * Preferences
	 * @since v2
	 * @version 1
	 */
	public function preferences() {
		$prefs = $this->prefs; ?>

			<!-- Creds for Sending Invites -->
			<label for="<?php echo $this->field_id( array( 'send_invite', 'creds' ) ); ?>" class="subheader"><?php echo $this->core->template_tags_general( __( '%plural% for Sending An Invite', 'mycred' ) ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'send_invite', 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'send_invite', 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs['send_invite']['creds'] ); ?>" size="8" /></div>
				</li>
				<li class="empty">&nbsp;</li>
				<li>
					<label for="<?php echo $this->field_id( array( 'send_invite', 'log' ) ); ?>"><?php _e( 'Log template', 'mycred' ); ?></label>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'send_invite', 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'send_invite', 'log' ) ); ?>" value="<?php echo $prefs['send_invite']['log']; ?>" class="long" /></div>
					<span class="description"><?php _e( 'Available template tags: General', 'mycred' ); ?></span>
				</li>
			</ol>
			<label for="<?php echo $this->field_id( array( 'send_invite', 'limit' ) ); ?>" class="subheader"><?php _e( 'Limit', 'mycred' ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'send_invite', 'limit' ) ); ?>" id="<?php echo $this->field_id( array( 'send_invite', 'limit' ) ); ?>" value="<?php echo $prefs['send_invite']['limit']; ?>" size="8" /></div>
					<span class="description"><?php echo $this->core->template_tags_general( __( 'Maximum number of invites that grants %_plural%. Use zero for unlimited.', 'mycred' ) ); ?></span>
				</li>
			</ol>
			<!-- Creds for Accepting Invites -->
			<label for="<?php echo $this->field_id( array( 'accept_invite', 'creds' ) ); ?>" class="subheader"><?php echo $this->core->template_tags_general( __( '%plural% for Accepting An Invite', 'mycred' ) ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'accept_invite', 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'accept_invite', 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs['accept_invite']['creds'] ); ?>" size="8" /></div>
					<span class="description"><?php echo $this->core->template_tags_general( __( '%plural% for each invited user that accepts an invitation.', 'mycred' ) ); ?></span>
				</li>
				<li class="empty">&nbsp;</li>
				<li>
					<label for="<?php echo $this->field_id( array( 'accept_invite', 'log' ) ); ?>"><?php _e( 'Log template', 'mycred' ); ?></label>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'accept_invite', 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'accept_invite', 'log' ) ); ?>" value="<?php echo $prefs['accept_invite']['log']; ?>" class="long" /></div>
					<span class="description"><?php _e( 'Available template tags: General', 'mycred' ); ?></span>
				</li>
			</ol>
			<label for="<?php echo $this->field_id( array( 'accept_invite', 'limit' ) ); ?>" class="subheader"><?php _e( 'Limit', 'mycred' ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'accept_invite', 'limit' ) ); ?>" id="<?php echo $this->field_id( array( 'accept_invite', 'limit' ) ); ?>" value="<?php echo $prefs['accept_invite']['limit']; ?>" size="8" /></div>
					<span class="description"><?php echo $this->core->template_tags_general( __( 'Maximum number of accepted invitations that grants %_plural%. Use zero for unlimited.', 'mycred' ) ); ?></span>
				</li>
			</ol>
<?php			unset( $this );
	}

	/**
	 * Sanitize Preferences
	 */
	public function sanitise_preferences( $data ) {
		$new_data = $data;

		// Apply defaults if any field is left empty
		$new_data['send_invite']['send_invite']['send_invite']['creds'] = ( !empty( $data['send_invite']['creds'] ) ) ? $data['send_invite']['creds'] : $this->defaults['send_invite']['send_invite']['send_invite']['creds'];
		$new_data['send_invite']['send_invite']['log'] = ( !empty( $data['send_invite']['log'] ) ) ? sanitize_text_field( $data['send_invite']['log'] ) : $this->defaults['send_invite']['send_invite']['log'];
		$new_data['send_invite']['limit'] = ( !empty( $data['send_invite']['limit'] ) ) ? sanitize_text_field( $data['send_invite']['limit'] ) : $this->defaults['send_invite']['limit'];
		
		$new_data['accept_invite']['accept_invite']['accept_invite']['creds'] = ( !empty( $data['accept_invite']['creds'] ) ) ? $data['accept_invite']['creds'] : $this->defaults['accept_invite']['accept_invite']['accept_invite']['creds'];
		$new_data['accept_invite']['accept_invite']['log'] = ( !empty( $data['accept_invite']['log'] ) ) ? sanitize_text_field( $data['accept_invite']['log'] ) : $this->defaults['accept_invite']['accept_invite']['log'];
		$new_data['accept_invite']['limit'] = ( !empty( $data['accept_invite']['limit'] ) ) ? sanitize_text_field( $data['accept_invite']['limit'] ) : $this->defaults['accept_invite']['limit'];

		return $new_data;
	}
}