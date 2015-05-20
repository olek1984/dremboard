<?php
/**
 * BuddyPress familys Template Functions.
 *
 * @package BuddyPress
 * @subpackage familysTemplate
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the familys component slug.
 *
 * @since BuddyPress (1.5.0)
 *
 * @uses bp_get_familys_slug()
 */
function bp_familys_slug() {
	echo bp_get_familys_slug();
}
	/**
	 * Return the familys component slug.
	 *
	 * @since BuddyPress (1.5.0)
	 */
	function bp_get_familys_slug() {
		return apply_filters( 'bp_get_familys_slug', buddypress()->familys->slug );
	}

/**
 * Output the familys component root slug.
 *
 * @since BuddyPress (1.5.0)
 *
 * @uses bp_get_familys_root_slug()
 */
function bp_familys_root_slug() {
	echo bp_get_familys_root_slug();
}
	/**
	 * Return the familys component root slug.
	 *
	 * @since BuddyPress (1.5.0)
	 */
	function bp_get_familys_root_slug() {
		return apply_filters( 'bp_get_familys_root_slug', buddypress()->familys->root_slug );
	}

/**
 * Output a block of random familys.
 *
 * No longer used in BuddyPress.
 *
 * @todo Deprecate
 */
function bp_familys_random_familys() {

	if ( !$family_ids = wp_cache_get( 'familys_family_ids_' . bp_displayed_user_id(), 'bp' ) ) {
		$family_ids = BP_familys_familyship::get_random_familys( bp_displayed_user_id() );
		wp_cache_set( 'familys_family_ids_' . bp_displayed_user_id(), $family_ids, 'bp' );
	} ?>

	<div class="info-group">
		<h4><?php bp_word_or_name( __( "My familys", 'buddypress' ), __( "%s's familys", 'buddypress' ) ) ?>  (<?php echo BP_familys_familyship::total_family_count( bp_displayed_user_id() ) ?>) <span><a href="<?php echo trailingslashit( bp_displayed_user_domain() . bp_get_familys_slug() ) ?>"><?php _e('See All', 'buddypress') ?></a></span></h4>

		<?php if ( $family_ids ) { ?>

			<ul class="horiz-gallery">

			<?php for ( $i = 0, $count = count( $family_ids ); $i < $count; ++$i ) { ?>

				<li>
					<a href="<?php echo bp_core_get_user_domain( $family_ids[$i] ) ?>"><?php echo bp_core_fetch_avatar( array( 'item_id' => $family_ids[$i], 'type' => 'thumb' ) ) ?></a>
					<h5><?php echo bp_core_get_userlink($family_ids[$i]) ?></h5>
				</li>

			<?php } ?>

			</ul>

		<?php } else { ?>

			<div id="message" class="info">
				<p><?php bp_word_or_name( __( "You haven't added any family connections yet.", 'buddypress' ), __( "%s hasn't created any family connections yet.", 'buddypress' ) ) ?></p>
			</div>

		<?php } ?>

		<div class="clear"></div>
	</div>

<?php
}

/**
 * Pull up a group of random members, and display some profile data about them.
 *
 * This function is no longer used by BuddyPress core.
 *
 * @todo Deprecate
 *
 * @param int $total_members The number of members to retrieve.
 */
function bp_familys_random_members( $total_members = 5 ) {

	if ( !$user_ids = wp_cache_get( 'familys_random_users', 'bp' ) ) {
		$user_ids = BP_Core_User::get_users( 'random', $total_members );
		wp_cache_set( 'familys_random_users', $user_ids, 'bp' );
	}

	?>

	<?php if ( $user_ids['users'] ) { ?>

		<ul class="item-list" id="random-members-list">

		<?php for ( $i = 0, $count = count( $user_ids['users'] ); $i < $count; ++$i ) { ?>

			<li>
				<a href="<?php echo bp_core_get_user_domain( $user_ids['users'][$i]->id ) ?>"><?php echo bp_core_fetch_avatar( array( 'item_id' => $user_ids['users'][$i]->id, 'type' => 'thumb' ) ) ?></a>
				<h5><?php echo bp_core_get_userlink( $user_ids['users'][$i]->id ) ?></h5>

				<?php if ( bp_is_active( 'xprofile' ) ) { ?>

					<?php $random_data = xprofile_get_random_profile_data( $user_ids['users'][$i]->id, true ); ?>

					<div class="profile-data">
						<p class="field-name"><?php echo $random_data[0]->name ?></p>

						<?php echo $random_data[0]->value ?>

					</div>

				<?php } ?>

				<div class="action">

					<?php if ( bp_is_active( 'familys' ) ) { ?>

						<?php bp_add_family_button( $user_ids['users'][$i]->id ) ?>

					<?php } ?>

				</div>
			</li>

		<?php } ?>

		</ul>

	<?php } else { ?>

		<div id="message" class="info">
			<p><?php _e( "There aren't enough site members to show a random sample just yet.", 'buddypress' ) ?></p>
		</div>

	<?php } ?>
<?php
}

/**
 * Display a familys search form.
 *
 * No longer used in BuddyPress.
 *
 * @todo Deprecate
 */
function bp_family_search_form() {

	$action = bp_displayed_user_domain() . bp_get_familys_slug() . '/my-familys/search/';
	$label  = __( 'Filter familys', 'buddypress' ); ?>

		<form action="<?php echo $action ?>" id="family-search-form" method="post">

			<label for="family-search-box" id="family-search-label"><?php echo $label ?></label>
			<input type="search" name="family-search-box" id="family-search-box" value="<?php echo $value ?>"<?php echo $disabled ?> />

			<?php wp_nonce_field( 'familys_search', '_wpnonce_family_search' ) ?>

			<input type="hidden" name="initiator" id="initiator" value="<?php echo esc_attr( bp_displayed_user_id() ) ?>" />

		</form>

	<?php
}

/**
 * Output the Add family button in member directories.
 */
function bp_member_add_family_button() {
	global $members_template;
//var_dump("1088");
	if ( !isset( $members_template->member->is_family ) || null === $members_template->member->is_family )
		$family_status = 'not_familys';
	else
		$family_status = ( 0 == $members_template->member->is_family ) ? 'pending' : 'is_family';

	echo bp_get_add_family_button( $members_template->member->id, $family_status );
}
add_action( 'bp_directory_familys_actions', 'bp_member_add_family_button' );

/**
 * Output the family count for the current member in the loop.
 */
function bp_member_total_family_count() {
	echo bp_get_member_total_family_count();
}
	/**
	 * Return the family count for the current member in the loop.
	 *
	 * Return value is a string of the form "x familys".
	 *
	 * @return string A string of the form "x familys".
	 */
	function bp_get_member_total_family_count() {
		global $members_template;

		if ( 1 == (int) $members_template->member->total_family_count )
			return apply_filters( 'bp_get_member_total_family_count', sprintf( __( '%d family', 'buddypress' ), (int) $members_template->member->total_family_count ) );
		else
			return apply_filters( 'bp_get_member_total_family_count', sprintf( __( '%d familys', 'buddypress' ), (int) $members_template->member->total_family_count ) );
	}

/**
 * Output the ID of the current user in the family request loop.
 *
 * @see bp_get_potential_family_id() for a description of arguments.
 *
 * @param int $user_id See {@link bp_get_potential_family_id()}.
 */
function bp_potential_family_id( $user_id = 0 ) {
	echo bp_get_potential_family_id( $user_id );
}
	/**
	 * Return the ID of current user in the family request loop.
	 *
	 * @global object $familys_template
	 *
	 * @param int $user_id Optional. If provided, the function will simply
	 *        return this value.
	 * @return int ID of potential family.
	 */
	function bp_get_potential_family_id( $user_id = 0 ) {
		global $familys_template;

		if ( empty( $user_id ) && isset( $familys_template->familyship->family ) )
			$user_id = $familys_template->familyship->family->id;
		else if ( empty( $user_id ) && !isset( $familys_template->familyship->family ) )
			$user_id = bp_displayed_user_id();

		return apply_filters( 'bp_get_potential_family_id', (int) $user_id );
	}

/**
 * Check whether a given user is a family of the logged-in user.
 *
 * Returns - 'is_family', 'not_familys', 'pending'.
 *
 * @param int $user_id ID of the potential family. Default: the value of
 *        {@link bp_get_potential_family_id()}.
 * @return string 'is_family', 'not_familys', or 'pending'.
 */
function bp_is_family( $user_id = 0 ) {

	if ( !is_user_logged_in() )
		return false;

	if ( empty( $user_id ) )
		$user_id = bp_get_potential_family_id( $user_id );

	if ( bp_loggedin_user_id() == $user_id )
		return false;

	return apply_filters( 'bp_is_family', familys_check_familyship_status( bp_loggedin_user_id(), $user_id ), $user_id );
}

/**
 * Output the Add family button.
 *
 * @see bp_get_add_family_button() for information on arguments.
 *
 * @param int $potential_family_id See {@link bp_get_add_family_button()}.
 * @param int $family_status See {@link bp_get_add_family_button()}.
 */
function bp_add_family_button( $potential_family_id = 0, $family_status = false ) {
	echo bp_get_add_family_button( $potential_family_id, $family_status );
}
	/**
	 * Create the Add family button.
	 *
	 * @param int $potential_family_id ID of the user to whom the button
	 *        applies. Default: value of {@link bp_get_potential_family_id()}.
	 * @param bool $family_status Not currently used.
	 * @return string HTML for the Add family button.
	 */
	function bp_get_add_family_button( $potential_family_id = 0, $family_status = false ) {

		if ( empty( $potential_family_id ) )
			$potential_family_id = bp_get_potential_family_id( $potential_family_id );

		$is_family = bp_is_family( $potential_family_id );

		if ( empty( $is_family ) )
			return false;

		switch ( $is_family ) {
			case 'pending' :
				$button = array(
					'id'                => 'pending',
					'component'         => 'familys',
					'must_be_logged_in' => true,
					'block_self'        => true,
					'wrapper_class'     => 'familyship-button pending_family',
					'wrapper_id'        => 'familyship-button-' . $potential_family_id,
					'link_href'         => wp_nonce_url( bp_loggedin_user_domain() . bp_get_familys_slug() . '/requests/cancel/' . $potential_family_id . '/', 'familys_withdraw_familyship' ),
					'link_text'         => __( 'Cancel familyship Request', 'buddypress' ),
					'link_title'        => __( 'Cancel familyship Requested', 'buddypress' ),
					'link_id'			=> 'family-' . $potential_family_id,
					'link_rel'			=> 'remove',
					'link_class'        => 'familyship-button pending_family requested'
				);
				break;

			case 'awaiting_response' :
				$button = array(
					'id'                => 'awaiting_response',
					'component'         => 'familys',
					'must_be_logged_in' => true,
					'block_self'        => true,
					'wrapper_class'     => 'familyship-button awaiting_response_family',
					'wrapper_id'        => 'familyship-button-' . $potential_family_id,
					'link_href'         => bp_loggedin_user_domain() . bp_get_familys_slug() . '/requests/',
					'link_text'         => __( 'familyship Requested', 'buddypress' ),
					'link_title'        => __( 'familyship Requested', 'buddypress' ),
					'link_id'           => 'family-' . $potential_family_id,
					'link_rel'          => 'remove',
					'link_class'        => 'familyship-button awaiting_response_family requested'
				);
				break;

			case 'is_family' :
				$button = array(
					'id'                => 'is_family',
					'component'         => 'familys',
					'must_be_logged_in' => true,
					'block_self'        => false,
					'wrapper_class'     => 'familyship-button is_family',
					'wrapper_id'        => 'familyship-button-' . $potential_family_id,
					'link_href'         => wp_nonce_url( bp_loggedin_user_domain() . bp_get_familys_slug() . '/remove-family/' . $potential_family_id . '/', 'familys_remove_family' ),
					'link_text'         => __( 'Cancel familyship', 'buddypress' ),
					'link_title'        => __( 'Cancel familyship', 'buddypress' ),
					'link_id'           => 'family-' . $potential_family_id,
					'link_rel'          => 'remove',
					'link_class'        => 'familyship-button is_family remove'
				);
				break;

			default:
				$button = array(
					'id'                => 'not_familys',
					'component'         => 'familys',
					'must_be_logged_in' => true,
					'block_self'        => true,
					'wrapper_class'     => 'familyship-button not_familys',
					'wrapper_id'        => 'familyship-button-' . $potential_family_id,
					'link_href'         => wp_nonce_url( bp_loggedin_user_domain() . bp_get_familys_slug() . '/add-family/' . $potential_family_id . '/', 'familys_add_family' ),
					'link_text'         => __( 'Add Family', 'buddypress' ),
					'link_title'        => __( 'Add Family', 'buddypress' ),
					'link_id'           => 'family-' . $potential_family_id,
					'link_rel'          => 'add',
					'link_class'        => 'familyship-button not_familys add'
				);
				break;
		}
//var_dump($button);
//$aa = apply_filters( 'bp_get_add_family_button', $button ) ;
//var_dump($aa);
		// Filter and return the HTML button
		return bp_get_button( apply_filters( 'bp_get_add_family_button', $button ) );
	}

/**
 * Get a comma-separated list of IDs of a user's familys.
 *
 * @param int $user_id Optional. Default: the displayed user's ID, or the
 *        logged-in user's ID.
 * @return string|bool A comma-separated list of family IDs if any are found,
 *         otherwise false.
 */
function bp_get_family_ids( $user_id = 0 ) {

	if ( empty( $user_id ) )
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();

	$family_ids = familys_get_family_user_ids( $user_id );

	if ( empty( $family_ids ) )
		return false;

	return implode( ',', familys_get_family_user_ids( $user_id ) );
}

/**
 * Get a user's familyship requests.
 *
 * Note that we return a 0 if no pending requests are found. This is necessary
 * because of the structure of the $include parameter in bp_has_members().
 *
 * @param int $user_id ID of the user whose requests are being retrieved.
 *        Defaults to displayed user.
 * @return array|int An array of user IDs if found, or a 0 if none are found.
 */
function bp_get_familyship_requests( $user_id = 0 ) {
	if ( !$user_id ) {
		$user_id = bp_displayed_user_id();
	}

	if ( !$user_id ) {
		return 0;
	}

	$requests = familys_get_familyship_request_user_ids( $user_id );

	if ( !empty( $requests ) ) {
		$requests = implode( ',', (array) $requests );
	} else {
		$requests = 0;
	}

	return apply_filters( 'bp_get_familyship_requests', $requests );
}

/**
 * Output the ID of the familyship between the logged-in user and the current user in the loop.
 */
function bp_family_familyship_id() {
	echo bp_get_family_familyship_id();
}
	/**
	 * Return the ID of the frinedship between the logged-in user and the current user in the loop.
	 *
	 * @return int ID of the familyship.
	 */
	function bp_get_family_familyship_id() {
		global $members_template;

		if ( !$familyship_id = wp_cache_get( 'familyship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id() ) ) {
			$familyship_id = familys_get_familyship_id( $members_template->member->id, bp_loggedin_user_id() );
			wp_cache_set( 'familyship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), $familyship_id, 'bp' );
		}

		return apply_filters( 'bp_get_family_familyship_id', $familyship_id );
	}

/**
 * Output the URL for accepting the current familyship request in the loop.
 */
function bp_family_accept_request_link() {
	echo bp_get_family_accept_request_link();
}
	/**
	 * Return the URL for accepting the current familyship request in the loop.
	 *
	 * @return string accept-familyship URL.
	 */
	function bp_get_family_accept_request_link() {
		global $members_template;

		if ( !$familyship_id = wp_cache_get( 'familyship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id() ) ) {
			$familyship_id = familys_get_familyship_id( $members_template->member->id, bp_loggedin_user_id() );
			wp_cache_set( 'familyship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), $familyship_id, 'bp' );
		}

		return apply_filters( 'bp_get_family_accept_request_link', wp_nonce_url( bp_loggedin_user_domain() . bp_get_familys_slug() . '/requests/accept/' . $familyship_id, 'familys_accept_familyship' ) );
	}

/**
 * Output the URL for rejecting the current familyship request in the loop.
 */
function bp_family_reject_request_link() {
	echo bp_get_family_reject_request_link();
}
	/**
	 * Return the URL for rejecting the current familyship request in the loop.
	 *
	 * @return string reject-familyship URL.
	 */
	function bp_get_family_reject_request_link() {
		global $members_template;

		if ( !$familyship_id = wp_cache_get( 'familyship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id() ) ) {
			$familyship_id = familys_get_familyship_id( $members_template->member->id, bp_loggedin_user_id() );
			wp_cache_set( 'familyship_id_' . $members_template->member->id . '_' . bp_loggedin_user_id(), $familyship_id, 'bp' );
		}

		return apply_filters( 'bp_get_family_reject_request_link', wp_nonce_url( bp_loggedin_user_domain() . bp_get_familys_slug() . '/requests/reject/' . $familyship_id, 'familys_reject_familyship' ) );
	}

/**
 * Output the total family count for a given user.
 *
 * @param int $user_id See {@link familys_get_total_family_count()}.
 */
function bp_total_family_count( $user_id = 0 ) {
	echo bp_get_total_family_count( $user_id );
}
	/**
	 * Return the total family count for a given user.
	 *
	 * @param int $user_id See {@link familys_get_total_family_count()}.
	 * @return int Total family count.
	 */
	function bp_get_total_family_count( $user_id = 0 ) {
		return apply_filters( 'bp_get_total_family_count', familys_get_total_family_count( $user_id ) );
	}
	add_filter( 'bp_get_total_family_count', 'bp_core_number_format' );

/**
 * Output the total familyship request count for a given user.
 *
 * @see bp_family_get_total_requests_count() for description of arguments.
 *
 * @param int $user_id See {@link bp_family_get_total_requests_count().
 */
function bp_family_total_requests_count( $user_id = 0 ) {
	echo bp_family_get_total_requests_count( $user_id );
}
	/**
	 * Return the total familyship request count for a given user.
	 *
	 * @param int $user_id ID of the user whose requests are being counted.
	 *        Default: ID of the logged-in user.
	 * @return int family count.
	 */
	function bp_family_get_total_requests_count( $user_id = 0 ) {
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		return apply_filters( 'bp_family_get_total_requests_count', count( BP_familys_familyship::get_family_user_ids( $user_id, true ) ) );
	}
