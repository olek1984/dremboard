<?php
/**
 * BuddyPress familys Classes
 *
 * @package BuddyPress
 * @subpackage familysClasses
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * BuddyPress familyship object.
 */
class BP_familys_familyship {

	/**
	 * ID of the familyship.
	 *
	 * @access public
	 * @var int
	 */
	public $id;

	/**
	 * User ID of the familyship initiator.
	 *
	 * @access public
	 * @var int
	 */
	public $initiator_user_id;

	/**
	 * User ID of the 'family' - the one invited to the familyship.
	 *
	 * @access public
	 * @var int
	 */
	public $family_user_id;

	/**
	 * Has the familyship been confirmed/accepted?
	 *
	 * @access public
	 * @var int
	 */
	public $is_confirmed;

	/**
	 * Is this a "limited" familyship?
	 *
	 * Not currently used by BuddyPress.
	 *
	 * @access public
	 * @var int
	 */
	public $is_limited;

	/**
	 * Date the familyship was created.
	 *
	 * @access public
	 * @var string
	 */
	public $date_created;

	/**
	 * Is this a request?
	 *
	 * Not currently used in BuddyPress.
	 *
	 * @access public
	 * @var unknown
	 */
	public $is_request;

	/**
	 * Should additional family details be queried?
	 *
	 * @access public
	 * @var bool
	 */
	public $populate_family_details;

	/**
	 * Details about the family.
	 *
	 * @access public
	 * @var BP_Core_User
	 */
	public $family;

	/**
	 * Constructor method.
	 *
	 * @param int $id Optional. The ID of an existing familyship.
	 * @param bool $is_request Deprecated.
	 * @param bool $populate_family_details True if family details should
	 *        be queried.
	 */
	public function __construct( $id = null, $is_request = false, $populate_family_details = true ) {
		$this->is_request = $is_request;

		if ( !empty( $id ) ) {
			$this->id                      = $id;
			$this->populate_family_details = $populate_family_details;
			$this->populate( $this->id );
		}
	}

	/**
	 * Set up data about the current familyship.
	 */
	public function populate() {
		global $wpdb, $bp;

		if ( $familyship = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->familys->table_name} WHERE id = %d", $this->id ) ) ) {
			$this->initiator_user_id = $familyship->initiator_user_id;
			$this->family_user_id    = $familyship->family_user_id;
			$this->is_confirmed      = $familyship->is_confirmed;
			$this->is_limited        = $familyship->is_limited;
			$this->date_created      = $familyship->date_created;
		}

		if ( !empty( $this->populate_family_details ) ) {
			if ( $this->family_user_id == bp_displayed_user_id() ) {
				$this->family = new BP_Core_User( $this->initiator_user_id );
			} else {
				$this->family = new BP_Core_User( $this->family_user_id );
			}
		}
	}

	/**
	 * Save the current familyship to the database.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $wpdb, $bp;

		$this->initiator_user_id = apply_filters( 'familys_familyship_initiator_user_id_before_save', $this->initiator_user_id, $this->id );
		$this->family_user_id    = apply_filters( 'familys_familyship_family_user_id_before_save',    $this->family_user_id,    $this->id );
		$this->is_confirmed      = apply_filters( 'familys_familyship_is_confirmed_before_save',      $this->is_confirmed,      $this->id );
		$this->is_limited        = apply_filters( 'familys_familyship_is_limited_before_save',        $this->is_limited,        $this->id );
		$this->date_created      = apply_filters( 'familys_familyship_date_created_before_save',      $this->date_created,      $this->id );

		do_action_ref_array( 'familys_familyship_before_save', array( &$this ) );

		// Update
		if (!empty( $this->id ) ) {
							//!!!!!
	update_option("test004",'121');
			$result = $wpdb->query( $wpdb->prepare( "UPDATE {$bp->familys->table_name} SET initiator_user_id = %d, family_user_id = %d, is_confirmed = %d, is_limited = %d, date_created = %s ) WHERE id = %d", $this->initiator_user_id, $this->family_user_id, $this->is_confirmed, $this->is_limited, $this->date_created, $this->id ) );

		// Save
		} else {

			//!!!!!
			$aa = sprintf("INSERT INTO {$bp->familys->table_name} ( initiator_user_id, family_user_id, is_confirmed, is_limited, date_created ) VALUES ( %d, %d, %d, %d, %s )", $this->initiator_user_id, $this->family_user_id, $this->is_confirmed, $this->is_limited, $this->date_created );
	update_option("test004",$aa);
			$result = $wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->familys->table_name} ( initiator_user_id, family_user_id, is_confirmed, is_limited, date_created ) VALUES ( %d, %d, %d, %d, %s )", $this->initiator_user_id, $this->family_user_id, $this->is_confirmed, $this->is_limited, $this->date_created ) );
			$this->id = $wpdb->insert_id;
		}

		do_action( 'familys_familyship_after_save', array( &$this ) );

		return $result;
	}

	public function delete() {
		global $wpdb, $bp;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->familys->table_name} WHERE id = %d", $this->id ) );
	}

	/** Static Methods ********************************************************/

	/**
	 * Get the IDs of a given user's familys.
	 *
	 * @param int $user_id ID of the user whose familys are being retreived.
	 * @param bool $family_requests_only Optional. Whether to fetch
	 *        unaccepted requests only. Default: false.
	 * @param bool $assoc_arr Optional. True to receive an array of arrays
	 *        keyed as 'user_id' => $user_id; false to get a one-dimensional
	 *        array of user IDs. Default: false.
	 */
	public static function get_family_user_ids( $user_id, $family_requests_only = false, $assoc_arr = false ) {
		global $wpdb, $bp;
//var_dump("15896");
//var_dump($bp->familys->table_name);
		if ( !empty( $family_requests_only ) ) {
			$oc_sql = 'AND is_confirmed = 0';
			$family_sql = $wpdb->prepare( " WHERE family_user_id = %d", $user_id );
		} else {
			$oc_sql = 'AND is_confirmed = 1';
			$family_sql = $wpdb->prepare( " WHERE (initiator_user_id = %d OR family_user_id = %d)", $user_id, $user_id );
		}
//var_dump("SELECT family_user_id, initiator_user_id FROM {$bp->familys->table_name} {$family_sql} {$oc_sql} ORDER BY date_created DESC");
		$familys = $wpdb->get_results( "SELECT family_user_id, initiator_user_id FROM {$bp->familys->table_name} {$family_sql} {$oc_sql} ORDER BY date_created DESC" );
		$fids = array();

		for ( $i = 0, $count = count( $familys ); $i < $count; ++$i ) {
			if ( !empty( $assoc_arr ) ) {
				$fids[] = array( 'user_id' => ( $familys[$i]->family_user_id == $user_id ) ? $familys[$i]->initiator_user_id : $familys[$i]->family_user_id );
			} else {
				$fids[] = ( $familys[$i]->family_user_id == $user_id ) ? $familys[$i]->initiator_user_id : $familys[$i]->family_user_id;
			}
		}
//var_dump($fids);
		return $fids;
	}

	/**
	 * Get the ID of the familyship object, if any, between a pair of users.
	 *
	 * @param int $user_id The ID of the first user.
	 * @param int $family_id The ID of the second user.
	 * @return int|bool The ID of the familyship object if found, otherwise
	 *         false.
	 */
	public static function get_familyship_id( $user_id, $family_id ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->familys->table_name} WHERE ( initiator_user_id = %d AND family_user_id = %d ) OR ( initiator_user_id = %d AND family_user_id = %d ) AND is_confirmed = 1", $user_id, $family_id, $family_id, $user_id ) );
	}

	/**
	 * Get a list of IDs of users who have requested familyship of a given user.
	 *
	 * @param int $user_id The ID of the user who has received the
	 *        familyship requests.
	 * @return array|bool An array of user IDs, or false if none are found.
	 */
	public static function get_familyship_request_user_ids( $user_id ) {
		global $wpdb, $bp;

		return $wpdb->get_col( $wpdb->prepare( "SELECT initiator_user_id FROM {$bp->familys->table_name} WHERE family_user_id = %d AND is_confirmed = 0", $user_id ) );
	}

	/**
	 * Get a total family count for a given user.
	 *
	 * @param int $user_id Optional. ID of the user whose familyships you
	 *        are counting. Default: displayed user (if any), otherwise
	 *        logged-in user.
	 * @return int family count for the user.
	 */
	public static function total_family_count( $user_id = 0 ) {
		global $wpdb, $bp;

		if ( empty( $user_id ) )
			$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();

		/* This is stored in 'total_family_count' usermeta.
		   This function will recalculate, update and return. */

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$bp->familys->table_name} WHERE (initiator_user_id = %d OR family_user_id = %d) AND is_confirmed = 1", $user_id, $user_id ) );

		// Do not update meta if user has never had familys
		if ( empty( $count ) && !bp_get_user_meta( $user_id, 'total_family_count', true ) )
			return 0;

		bp_update_user_meta( $user_id, 'total_family_count', (int) $count );

		return absint( $count );
	}

	/**
	 * Search the familys of a user by a search string.
	 *
	 * @param string $filter The search string, matched against xprofile
	 *        fields (if available), or usermeta 'nickname' field.
	 * @param int $user_id ID of the user whose familys are being searched.
	 * @param int $limit Optional. Max number of familys to return.
	 * @param int $page Optional. The page of results to return. Default:
	 *        null (no pagination - return all results).
	 * @return array|bool On success, an array: {
	 *     @type array $familys IDs of familys returned by the query.
	 *     @type int $count Total number of familys (disregarding
	 *           pagination) who match the search.
	 * }. Returns false on failure.
	 */
	public static function search_familys( $filter, $user_id, $limit = null, $page = null ) {
		global $wpdb, $bp;

		// TODO: Optimize this function.

		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		$filter = esc_sql( like_escape( $filter ) );

		$pag_sql = '';
		if ( !empty( $limit ) && !empty( $page ) )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( !$family_ids = BP_familys_familyship::get_family_user_ids( $user_id ) )
			return false;

		// Get all the user ids for the current user's familys.
		$fids = implode( ',', wp_parse_id_list( $family_ids ) );

		if ( empty( $fids ) )
			return false;

		// filter the user_ids based on the search criteria.
		if ( bp_is_active( 'xprofile' ) ) {
			$sql       = "SELECT DISTINCT user_id FROM {$bp->profile->table_name_data} WHERE user_id IN ({$fids}) AND value LIKE '{$filter}%%' {$pag_sql}";
			$total_sql = "SELECT COUNT(DISTINCT user_id) FROM {$bp->profile->table_name_data} WHERE user_id IN ({$fids}) AND value LIKE '{$filter}%%'";
		} else {
			$sql       = "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE user_id IN ({$fids}) AND meta_key = 'nickname' AND meta_value LIKE '{$filter}%%' {$pag_sql}";
			$total_sql = "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE user_id IN ({$fids}) AND meta_key = 'nickname' AND meta_value LIKE '{$filter}%%'";
		}

		$filtered_family_ids = $wpdb->get_col( $sql );
		$total_family_ids    = $wpdb->get_var( $total_sql );

		if ( empty( $filtered_family_ids ) )
			return false;

		return array( 'familys' => $filtered_family_ids, 'total' => (int) $total_family_ids );
	}

	/**
	 * Check familyship status between two users.
	 *
	 * Note that 'pending' means that $initiator_userid has sent a family
	 * request to $possible_family_userid that has not yet been approved,
	 * while 'awaiting_response' is the other way around ($possible_family_userid
	 * sent the initial request)
	 *
	 * @param int $initiator_userid The ID of the user who is the initiator
	 *        of the potential familyship/request.
	 * @param int $possible_family_userid The ID of the user who is the
	 *        recipient of the potential familyship/request.
	 * @return string The familyship status, from among 'not_familys',
	 *        'is_family', 'pending', and 'awaiting_response'.
	 */
	public static function check_is_family( $initiator_userid, $possible_family_userid ) {
		global $wpdb, $bp;

		if ( empty( $initiator_userid ) || empty( $possible_family_userid ) ) {
			return false;
		}

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT id, initiator_user_id, is_confirmed FROM {$bp->familys->table_name} WHERE (initiator_user_id = %d AND family_user_id = %d) OR (initiator_user_id = %d AND family_user_id = %d)", $initiator_userid, $possible_family_userid, $possible_family_userid, $initiator_userid ) );

		if ( ! empty( $result ) ) {
			if ( 0 == (int) $result[0]->is_confirmed ) {
				$status = $initiator_userid == $result[0]->initiator_user_id ? 'pending' : 'awaiting_response';
			} else {
				$status = 'is_family';
			}
		} else {
			$status = 'not_familys';
		}

		return $status;
	}

	/**
	 * Get the last active date of many users at once.
	 *
	 * @todo Why is this in the familys component?
	 *
	 * @param array $user_ids IDs of users whose last_active meta is
	 *        being queried.
	 * @return array Array of last_active values + user_ids.
	 */
	public static function get_bulk_last_active( $user_ids ) {
		global $wpdb;

		$user_ids = implode( ',', wp_parse_id_list( $user_ids ) );

		return $wpdb->get_results( $wpdb->prepare( "SELECT meta_value as last_activity, user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id IN ( {$user_ids} ) ORDER BY meta_value DESC", bp_get_user_meta_key( 'last_activity' ) ) );
	}

	/**
	 * Mark a familyship as accepted.
	 *
	 * @param int $familyship_id ID of the familyship to be accepted.
	 * @return int Number of database rows updated.
	 */
	public static function accept($familyship_id) {
		global $wpdb, $bp;
	 	return $wpdb->query( $wpdb->prepare( "UPDATE {$bp->familys->table_name} SET is_confirmed = 1, date_created = %s WHERE id = %d AND family_user_id = %d", bp_core_current_time(), $familyship_id, bp_loggedin_user_id() ) );
	}

	/**
	 * Remove a familyship or a familyship request INITIATED BY the logged-in user.
	 *
	 * @param int $familyship_id ID of the familyship to be withdrawn.
	 * @return int Number of database rows deleted.
	 */
	public static function withdraw($familyship_id) {
		global $wpdb, $bp;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->familys->table_name} WHERE id = %d AND initiator_user_id = %d", $familyship_id, bp_loggedin_user_id() ) );
	}

	/**
	 * Remove a familyship or a familyship request MADE OF the logged-in user.
	 *
	 * @param int $familyship_id ID of the familyship to be rejected.
	 * @return int Number of database rows deleted.
	 */
	public static function reject($familyship_id) {
		global $wpdb, $bp;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->familys->table_name} WHERE id = %d AND family_user_id = %d", $familyship_id, bp_loggedin_user_id() ) );
	}

	/**
	 * Search users.
	 *
	 * @todo Why does this exist, and why is it in bp-familys?
	 *
	 * @param string $filter String to search by.
	 * @param int $user_id A user ID param that is unused.
	 * @param int $limit Optional. Max number of records to return.
	 * @param int $page Optional. Number of the page to return. Default:
	 *        false (no pagination - return all results).
	 * @return array $filtered_ids IDs of users who match the query.
	 */
	public static function search_users( $filter, $user_id, $limit = null, $page = null ) {
		global $wpdb, $bp;

		$filter = esc_sql( like_escape( $filter ) );

		$usermeta_table = $wpdb->base_prefix . 'usermeta';
		$users_table    = $wpdb->base_prefix . 'users';

		$pag_sql = '';
		if ( !empty( $limit ) && !empty( $page ) )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * intval( $limit ) ), intval( $limit ) );

		// filter the user_ids based on the search criteria.
		if ( bp_is_active( 'xprofile' ) ) {
			$sql = "SELECT DISTINCT d.user_id as id FROM {$bp->profile->table_name_data} d, {$users_table} u WHERE d.user_id = u.id AND d.value LIKE '{$filter}%%' ORDER BY d.value DESC {$pag_sql}";
		} else {
			$sql = "SELECT DISTINCT user_id as id FROM {$usermeta_table} WHERE meta_value LIKE '{$filter}%%' ORDER BY d.value DESC {$pag_sql}";
		}

		$filtered_fids = $wpdb->get_col($sql);

		if ( empty( $filtered_fids ) )
			return false;

		return $filtered_fids;
	}

	/**
	 * Get a count of users who match a search term.
	 *
	 * @todo Why does this exist, and why is it in bp-familys?
	 *
	 * @param string $filter Search term.
	 * @return int Count of users matching the search term.
	 */
	public static function search_users_count( $filter ) {
		global $wpdb, $bp;

		$filter = esc_sql( like_escape( $filter ) );

		$usermeta_table = $wpdb->prefix . 'usermeta';
		$users_table    = $wpdb->base_prefix . 'users';

		// filter the user_ids based on the search criteria.
		if ( bp_is_active( 'xprofile' ) ) {
			$sql = "SELECT COUNT(DISTINCT d.user_id) FROM {$bp->profile->table_name_data} d, {$users_table} u WHERE d.user_id = u.id AND d.value LIKE '{$filter}%%'";
		} else {
			$sql = "SELECT COUNT(DISTINCT user_id) FROM {$usermeta_table} WHERE meta_value LIKE '{$filter}%%'";
		}

		$user_count = $wpdb->get_col($sql);

		if ( empty( $user_count ) )
			return false;

		return $user_count[0];
	}

	/**
	 * Sort a list of user IDs by their display names.
	 *
	 * @todo Why does this exist, and why is it in bp-familys?
	 *
	 * @param array $user_ids Array of user IDs.
	 * @return array User IDs, sorted by the associated display names.
	 */
	public static function sort_by_name( $user_ids ) {
		global $wpdb, $bp;

		if ( !bp_is_active( 'xprofile' ) )
			return false;

		$user_ids = implode( ',', wp_parse_id_list( $user_ids ) );

		return $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM {$bp->profile->table_name_data} pd, {$bp->profile->table_name_fields} pf WHERE pf.id = pd.field_id AND pf.name = %s AND pd.user_id IN ( {$user_ids} ) ORDER BY pd.value ASC", bp_xprofile_fullname_field_name() ) );
	}

	/**
	 * Get a list of random family IDs.
	 *
	 * @param int $user_id ID of the user whose familys are being retrieved.
	 * @param int $total_familys Optional. Number of random familys to get.
	 *        Default: 5.
	 * @return array|bool An array of random family user IDs on success;
	 *         false if none are found.
	 */
	public static function get_random_familys( $user_id, $total_familys = 5 ) {
		global $wpdb, $bp;

		$fids    = array();
		$sql     = $wpdb->prepare( "SELECT family_user_id, initiator_user_id FROM {$bp->familys->table_name} WHERE (family_user_id = %d || initiator_user_id = %d) && is_confirmed = 1 ORDER BY rand() LIMIT %d", $user_id, $user_id, $total_familys );
		$results = $wpdb->get_results( $sql );

		for ( $i = 0, $count = count( $results ); $i < $count; ++$i ) {
			$fids[] = ( $results[$i]->family_user_id == $user_id ) ? $results[$i]->initiator_user_id : $results[$i]->family_user_id;
		}

		// remove duplicates
		if ( count( $fids ) > 0 )
			return array_flip( array_flip( $fids ) );
		else
			return false;
	}

	/**
	 * Get a count of a user's familys who can be invited to a given group.
	 *
	 * Users can invite any of their familys except:
	 *
	 * - users who are already in the group
	 * - users who have a pending invite to the group
	 * - users who have been banned from the group
	 *
	 * @param int $user_id ID of the user whose familys are being counted.
	 * @param int $group_id ID of the group familys are being invited to.
	 * @return int $invitable_count Eligible family count.
	 */
	public static function get_invitable_family_count( $user_id, $group_id ) {

		// Setup some data we'll use below
		$is_group_admin  = BP_Groups_Member::check_is_admin( $user_id, $group_id );
		$family_ids      = BP_familys_familyship::get_family_user_ids( $user_id );
		$invitable_count = 0;

		for ( $i = 0, $count = count( $family_ids ); $i < $count; ++$i ) {

			// If already a member, they cannot be invited again
			if ( BP_Groups_Member::check_is_member( (int) $family_ids[$i], $group_id ) )
				continue;

			// If user already has invite, they cannot be added
			if ( BP_Groups_Member::check_has_invite( (int) $family_ids[$i], $group_id )  )
				continue;

			// If user is not group admin and family is banned, they cannot be invited
			if ( ( false === $is_group_admin ) && BP_Groups_Member::check_is_banned( (int) $family_ids[$i], $group_id ) )
				continue;

			$invitable_count++;
		}

		return $invitable_count;
	}

	/**
	 * Get the family user IDs for a given familyship.
	 *
	 * @param int $familyship_id ID of the familyship.
	 * @return object family_user_id and initiator_user_id.
	 */
	public static function get_user_ids_for_familyship( $familyship_id ) {
		global $wpdb, $bp;
		return $wpdb->get_row( $wpdb->prepare( "SELECT family_user_id, initiator_user_id FROM {$bp->familys->table_name} WHERE id = %d", $familyship_id ) );
	}

	/**
	 * Delete all familyships and family notifications related to a user.
	 *
	 * @param int $user_id ID of the user being expunged.
	 */
	public static function delete_all_for_user( $user_id ) {
		global $wpdb, $bp;

		// Get familys of $user_id
		$family_ids = BP_familys_familyship::get_family_user_ids( $user_id );

		// Delete all familyships related to $user_id
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->familys->table_name} WHERE family_user_id = %d OR initiator_user_id = %d", $user_id, $user_id ) );

		// Delete family request notifications for members who have a
		// notification from this user.
		if ( bp_is_active( 'notifications' ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->notifications->table_name} WHERE component_name = 'familys' AND ( component_action = 'familyship_request' OR component_action = 'familyship_accepted' ) AND item_id = %d", $user_id ) );
		}

		// Loop through family_ids and update their counts
		foreach ( (array) $family_ids as $family_id ) {
			BP_familys_familyship::total_family_count( $family_id );
		}
	}
}