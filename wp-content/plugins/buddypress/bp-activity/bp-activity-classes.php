<?php
/**
 * BuddyPress Activity Classes
 *
 * @package BuddyPress
 * @subpackage Activity
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Database interaction class for the BuddyPress activity component.
 *
 * Instance methods are available for creating/editing an activity,
 * static methods for querying activities.
 *
 * @since BuddyPress (1.0)
 */
class BP_Activity_Activity {

	/** Properties ************************************************************/

	/**
	 * ID of the activity item.
	 *
	 * @var int
	 */
	var $id;

	/**
	 * ID of the associated item.
	 *
	 * @var int
	 */
	var $item_id;

	/**
	 * ID of the associated secondary item.
	 *
	 * @var int
	 */
	var $secondary_item_id;

	/**
	 * ID of user associated with the activity item.
	 *
	 * @var int
	 */
	var $user_id;

	/**
	 * The primary URL for the activity in RSS feeds.
	 *
	 * @var string
	 */
	var $primary_link;

	/**
	 * BuddyPress component the activity item relates to.
	 *
	 * @var string
	 */
	var $component;

	/**
	 * Activity type, eg 'new_blog_post'.
	 *
	 * @var string
	 */
	var $type;

	/**
	 * Description of the activity, eg 'Alex updated his profile.'
	 *
	 * @var string
	 */
	var $action;

	/**
	 * The content of the activity item.
	 *
	 * @var string
	 */
	var $content;

	/**
	 * The date the activity item was recorded, in 'Y-m-d h:i:s' format.
	 *
	 * @var string
	 */
	var $date_recorded;

	/**
	 * Whether the item should be hidden in sitewide streams.
	 *
	 * @var int
	 */
	var $hide_sitewide = false;

	/**
	 * Node boundary start for activity or activity comment.
	 *
	 * @var int
	 */
	var $mptt_left;

	/**
	 * Node boundary end for activity or activity comment.
	 *
	 * @var int
	 */
	var $mptt_right;

	/**
	 * Whether this item is marked as spam.
	 *
	 * @var int
	 */
	var $is_spam;

	/**
	 * Constructor method.
	 *
	 * @param int $id Optional. The ID of a specific activity item.
	 */
	public function __construct( $id = false ) {
		if ( !empty( $id ) ) {
			$this->id = $id;
			$this->populate();
		}
	}

	/**
	 * Populate the object with data about the specific activity item.
	 */
	public function populate() {
		global $wpdb, $bp;

		if ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->activity->table_name} WHERE id = %d", $this->id ) ) ) {
			$this->id                = $row->id;
			$this->item_id           = $row->item_id;
			$this->secondary_item_id = $row->secondary_item_id;
			$this->user_id           = $row->user_id;
			$this->primary_link      = $row->primary_link;
			$this->component         = $row->component;
			$this->type              = $row->type;
			$this->action            = $row->action;
			$this->content           = $row->content;
			$this->date_recorded     = $row->date_recorded;
			$this->hide_sitewide     = $row->hide_sitewide;
			$this->mptt_left         = $row->mptt_left;
			$this->mptt_right        = $row->mptt_right;
			$this->is_spam           = $row->is_spam;

			bp_activity_update_meta_cache( $this->id );
		}
	}

	/**
	 * Save the activity item to the database.
	 *
	 * @return bool True on success.
	 */
	public function save() {
		global $wpdb, $bp;

		$this->id                = apply_filters_ref_array( 'bp_activity_id_before_save',                array( $this->id,                &$this ) );
		$this->item_id           = apply_filters_ref_array( 'bp_activity_item_id_before_save',           array( $this->item_id,           &$this ) );
		$this->secondary_item_id = apply_filters_ref_array( 'bp_activity_secondary_item_id_before_save', array( $this->secondary_item_id, &$this ) );
		$this->user_id           = apply_filters_ref_array( 'bp_activity_user_id_before_save',           array( $this->user_id,           &$this ) );
		$this->primary_link      = apply_filters_ref_array( 'bp_activity_primary_link_before_save',      array( $this->primary_link,      &$this ) );
		$this->component         = apply_filters_ref_array( 'bp_activity_component_before_save',         array( $this->component,         &$this ) );
		$this->type              = apply_filters_ref_array( 'bp_activity_type_before_save',              array( $this->type,              &$this ) );
		$this->action            = apply_filters_ref_array( 'bp_activity_action_before_save',            array( $this->action,            &$this ) );
		$this->content           = apply_filters_ref_array( 'bp_activity_content_before_save',           array( $this->content,           &$this ) );
		$this->date_recorded     = apply_filters_ref_array( 'bp_activity_date_recorded_before_save',     array( $this->date_recorded,     &$this ) );
		$this->hide_sitewide     = apply_filters_ref_array( 'bp_activity_hide_sitewide_before_save',     array( $this->hide_sitewide,     &$this ) );
		$this->mptt_left         = apply_filters_ref_array( 'bp_activity_mptt_left_before_save',         array( $this->mptt_left,         &$this ) );
		$this->mptt_right        = apply_filters_ref_array( 'bp_activity_mptt_right_before_save',        array( $this->mptt_right,        &$this ) );
		$this->is_spam           = apply_filters_ref_array( 'bp_activity_is_spam_before_save',           array( $this->is_spam,           &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'bp_activity_before_save', array( &$this ) );

		if ( !$this->component || !$this->type )
			return false;

		if ( !$this->primary_link )
			$this->primary_link = bp_loggedin_user_domain();

		// If we have an existing ID, update the activity item, otherwise insert it.
		if ( $this->id )
			$q = $wpdb->prepare( "UPDATE {$bp->activity->table_name} SET user_id = %d, component = %s, type = %s, action = %s, content = %s, primary_link = %s, date_recorded = %s, item_id = %d, secondary_item_id = %d, hide_sitewide = %d, is_spam = %d WHERE id = %d", $this->user_id, $this->component, $this->type, $this->action, $this->content, $this->primary_link, $this->date_recorded, $this->item_id, $this->secondary_item_id, $this->hide_sitewide, $this->is_spam, $this->id );
		else
			$q = $wpdb->prepare( "INSERT INTO {$bp->activity->table_name} ( user_id, component, type, action, content, primary_link, date_recorded, item_id, secondary_item_id, hide_sitewide, is_spam ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d )", $this->user_id, $this->component, $this->type, $this->action, $this->content, $this->primary_link, $this->date_recorded, $this->item_id, $this->secondary_item_id, $this->hide_sitewide, $this->is_spam );

		if ( false === $wpdb->query( $q ) )
			return false;

		// If this is a new activity item, set the $id property
		if ( empty( $this->id ) )
			$this->id = $wpdb->insert_id;

		// If an existing activity item, prevent any changes to the content generating new @mention notifications.
		else
			add_filter( 'bp_activity_at_name_do_notifications', '__return_false' );

		do_action_ref_array( 'bp_activity_after_save', array( &$this ) );

		return true;
	}

	/** Static Methods ***************************************************/

	/**
	 * Get activity items, as specified by parameters
	 *
	 * @see BP_Activity_Activity::get_filter_sql() for a description of the
	 *      'filter' parameter.
	 * @see WP_Meta_Query::queries for a description of the 'meta_query'
	 *      parameter format.
	 *
	 * @param array $args {
	 *     An array of arguments. All items are optional.
	 *     @type int $page Which page of results to fetch. Using page=1
	 *                     without per_page will result in no pagination.
	 *                     Default: 1.
	 *     @type int|bool $per_page Number of results per page. Default: 25.
	 *     @type int|bool $max Maximum number of results to return.
	 *                         Default: false (unlimited).
	 *     @type string $sort ASC or DESC. Default: 'DESC'.
	 *     @type array $exclude Array of activity IDs to exclude.
	 *                          Default: false.
	 *     @type array $in Array of ids to limit query by (IN).
	 *                     Default: false.
	 *     @type array $meta_query An array of meta_query conditions.
	 *                             See WP_Meta_Query::queries for description.
	 *     @type array $filter See BP_Activity_Activity::get_filter_sql().
	 *     @type string $search_terms Limit results by a search term.
	 *                                Default: false.
	 *     @type bool $display_comments Whether to include activity comments.
	 *                                  Default: false.
	 *     @type bool $show_hidden Whether to show items marked hide_sitewide.
	 *                             Default: false.
	 *     @type string $spam Spam status. Default: 'ham_only'.
	 * }
	 * @return array The array returned has two keys:
	 *     - 'total' is the count of located activities
	 *     - 'activities' is an array of the located activities
	 */
	public static function get( $args = array() ) {
		global $wpdb, $bp;

		// Backward compatibility with old method of passing arguments
		if ( !is_array( $args ) || func_num_args() > 1 ) {
			_deprecated_argument( __METHOD__, '1.6', sprintf( __( 'Arguments passed to %1$s should be in an associative array. See the inline documentation at %2$s for more details.', 'buddypress' ), __METHOD__, __FILE__ ) );

			$old_args_keys = array(
				0 => 'max',
				1 => 'page',
				2 => 'per_page',
				3 => 'sort',
				4 => 'search_terms',
				5 => 'filter',
				6 => 'display_comments',
				7 => 'show_hidden',
				8 => 'exclude',
				9 => 'in',
				10 => 'spam'
			);

			$func_args = func_get_args();
			$args      = bp_core_parse_args_array( $old_args_keys, $func_args );
		}

		$defaults = array(
			'page'             => 1,          // The current page
			'per_page'         => 25,         // Activity items per page
			'max'              => false,      // Max number of items to return
			'sort'             => 'DESC',     // ASC or DESC
			'exclude'          => false,      // Array of ids to exclude
			'in'               => false,      // Array of ids to limit query by (IN)
			'meta_query'       => false,      // Filter by activitymeta
			'filter'           => false,      // See self::get_filter_sql()
			'search_terms'     => false,      // Terms to search by
			'display_comments' => false,      // Whether to include activity comments
			'show_hidden'      => false,      // Show items marked hide_sitewide
			'spam'             => 'ham_only', // Spam status
			'nt_flag'		   => false,
            'scope'            => false,
            'last_activity_id' => false,
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		// Select conditions
		$select_sql = "SELECT DISTINCT a.*, u.user_email, u.user_nicename, u.user_login, u.display_name";

		$from_sql = " FROM {$bp->activity->table_name} a LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID";

		$join_sql = '';
        
        if (intval($last_activity_id) > 0) {
            $last_where = " AND (a.id < {$last_activity_id}) ";
        }

		// Where conditions
		$where_conditions = array();

		// Spam
		if ( 'ham_only' == $spam )
			$where_conditions['spam_sql'] = 'a.is_spam = 0';
		elseif ( 'spam_only' == $spam )
			$where_conditions['spam_sql'] = 'a.is_spam = 1';

		// Searching
		if ( $search_terms ) {
			$search_terms = esc_sql( $search_terms );
			$where_conditions['search_sql'] = "a.content LIKE '%%" . esc_sql( like_escape( $search_terms ) ) . "%%'";
		}

		// Filtering
		if ( $filter && $filter_sql = BP_Activity_Activity::get_filter_sql( $filter ) )
			$where_conditions['filter_sql'] = $filter_sql;

		// Sorting
		if ( $sort != 'ASC' && $sort != 'DESC' )
			$sort = 'DESC';

		// Hide Hidden Items?
		if ( !$show_hidden )
			$where_conditions['hidden_sql'] = "a.hide_sitewide = 0";

		// Exclude specified items
		if ( !empty( $exclude ) ) {
			$exclude = implode( ',', wp_parse_id_list( $exclude ) );
			$where_conditions['exclude'] = "a.id NOT IN ({$exclude})";
		}

		// The specific ids to which you want to limit the query
		if ( !empty( $in ) ) {
			$in = implode( ',', wp_parse_id_list( $in ) );
			$where_conditions['in'] = "a.id IN ({$in})";
		}

		// Process meta_query into SQL
		$meta_query_sql = self::get_meta_query_sql( $meta_query );

		if ( ! empty( $meta_query_sql['join'] ) ) {
			$join_sql .= $meta_query_sql['join'];
		}

		if ( ! empty( $meta_query_sql['where'] ) ) {
			$where_conditions[] = $meta_query_sql['where'];
		}

		// Alter the query based on whether we want to show activity item
		// comments in the stream like normal comments or threaded below
		// the activity.
		if ( false === $display_comments || 'threaded' === $display_comments ) {
			$where_conditions[] = "a.type != 'activity_comment'";
		}
        $where_conditions[] = "a.del_flag != '1'";
		// Filter the where conditions
		$where_conditions = apply_filters( 'bp_activity_get_where_conditions', $where_conditions, $r, $select_sql, $from_sql, $join_sql );

		// Join the where conditions together
		$where_sql = 'WHERE ' . join( ' AND ', $where_conditions );
        
        $where_sql .= $last_where;
		// Define the preferred order for indexes
		$indexes = apply_filters( 'bp_activity_preferred_index_order', array( 'user_id', 'item_id', 'secondary_item_id', 'date_recorded', 'component', 'type', 'hide_sitewide', 'is_spam' ) );

		foreach( $indexes as $key => $index ) {
			if ( false !== strpos( $where_sql, $index ) ) {
				$the_index = $index;
				break; // Take the first one we find
			}
		}
		
				// where_condition for share wall, own, friend, group
		$login_user_id = bp_loggedin_user_id();
		$show_user_id = bp_displayed_user_id();
		//$groups = BP_Groups_Member::get_group_ids( $login_user_id );
		//$groups = esc_sql( implode( ',', wp_parse_id_list( $groups['groups'] ) ) );
		
		/*
		$share_cond = "a.type = 'activity_wall'";
		if ($login_user_id == $show_user_id || $show_user_id==0){
			$share_cond .= " OR (a.type = 'activity_own' AND a.user_id={$login_user_id})";
			$share_cond .= " OR ((a.type = 'activity_friend' AND a.user_id={$login_user_id}) OR (a.type = 'activity_friend' AND a.item_id={$login_user_id}))";
			//$share_cond .= " OR (a.type = 'activity_group' AND a.item_id IN ({$groups}))";
		}
		$share_cond .= " OR (a.type NOT IN ('activity_wall', 'activity_own', 'activity_friend', 'activity_group' ))";

		$where_sql .= " AND (".$share_cond.")";
		*/
		// order by
        if ($scope = 'just-me' && $show_user_id != 0){
            $share_cond .= "a.type = 'activity_friend' AND a.item_id={$show_user_id}";
            $where_sql .= " OR (".$share_cond.")";
        }
        
		$order_by = 'ORDER BY a.date_recorded';

		if ( !empty( $the_index ) ) {
			$index_hint_sql = "USE INDEX ({$the_index})";
		} else {
			$index_hint_sql = '';
		}
		
		if ($nt_flag){
			$join_sql = "left outer join {$bp->notifications->table_name} n on n.item_id = a.id";
			//$where_sql
			$where_sql = "where a.del_flag != '1' and (a.user_id = ".$login_user_id." and a.component != 'activity') or (n.user_id = ".$login_user_id." and (n.component_name in ('activity','ac_notifier', 'ac_like')))";
            $where_sql .= $last_where;			
            //$order_by
			$order_by = "order by GREATEST(COALESCE(n.date_notified, '0000-00-00 00:00:00'),COALESCE(a.date_recorded, '0000-00-00 00:00:00'))";
			
			$total_activities_sql = "SELECT count(DISTINCT a.id) {$from_sql} {$join_sql} {$where_sql} {$order_by} {$sort}";
		}
		
		$pag_sql = '';
		
		if ( !empty( $per_page ) && !empty( $page ) ) {

			// Make sure page values are absolute integers
			$page     = absint( $page     );
			$per_page = absint( $per_page );

			$pag_sql    = $wpdb->prepare( "LIMIT %d, %d", absint( ( $page - 1 ) * $per_page ), $per_page );
		}
		
        $rtmedia_model = new RTMediaModel();
        $join_sql .= " LEFT JOIN {$rtmedia_model->table_name} ON {$rtmedia_model->table_name}.activity_id = a.ID  ";
        $where_sql = apply_filters('rtmedia-model-where-query', $where_sql, $rtmedia_model->table_name, $join_sql);

        $sql = "{$select_sql} {$from_sql} {$join_sql} {$where_sql} {$order_by} {$sort} {$pag_sql}";
		
		if(nt_flag == false){
            $sql = apply_filters( 'bp_activity_get_user_join_filter', "{$select_sql} {$from_sql} {$join_sql} {$where_sql} {$order_by} {$sort} {$pag_sql}", $select_sql, $from_sql, $where_sql, $sort, $pag_sql );
			
			$total_activities_sql = apply_filters( 'bp_activity_total_activities_sql', "SELECT count(DISTINCT a.id) FROM {$bp->activity->table_name} a {$index_hint_sql} {$join_sql} {$where_sql} {$order_by} {$sort}", $where_sql, $sort );
		}
		
		$activities = $wpdb->get_results($sql);
		$total_activities = $wpdb->get_var( $total_activities_sql );

		// Get the fullnames of users so we don't have to query in the loop
		if ( bp_is_active( 'xprofile' ) && !empty( $activities ) ) {
			$activity_user_ids = wp_list_pluck( $activities, 'user_id' );
			$activity_user_ids = implode( ',', wp_parse_id_list( $activity_user_ids ) );

			if ( !empty( $activity_user_ids ) ) {
				if ( $names = $wpdb->get_results( "SELECT user_id, value AS user_fullname FROM {$bp->profile->table_name_data} WHERE field_id = 1 AND user_id IN ({$activity_user_ids})" ) ) {
					foreach ( (array) $names as $name )
						$tmp_names[$name->user_id] = $name->user_fullname;

					foreach ( (array) $activities as $i => $activity ) {
						if ( !empty( $tmp_names[$activity->user_id] ) )
							$activities[$i]->user_fullname = $tmp_names[$activity->user_id];
					}

					unset( $names );
					unset( $tmp_names );
				}
			}
		}

		// Get activity meta
		$activity_ids = array();
		foreach ( (array) $activities as $activity ) {
			$activity_ids[] = $activity->id;
		}

		if ( !empty( $activity_ids ) ) {
			bp_activity_update_meta_cache( $activity_ids );
		}

		if ( $activities && $display_comments )
			$activities = BP_Activity_Activity::append_comments( $activities, $spam );

		// If $max is set, only return up to the max results
		if ( !empty( $max ) ) {
			if ( (int) $total_activities > (int) $max )
				$total_activities = $max;
		}

		return array( 'activities' => $activities, 'total' => (int) $total_activities );
	}

	/**
	 * Get the SQL for the 'meta_query' param in BP_Activity_Activity::get().
	 *
	 * We use WP_Meta_Query to do the heavy lifting of parsing the
	 * meta_query array and creating the necessary SQL clauses. However,
	 * since BP_Activity_Activity::get() builds its SQL differently than
	 * WP_Query, we have to alter the return value (stripping the leading
	 * AND keyword from the 'where' clause).
	 *
	 * @since BuddyPress (1.8)
	 *
	 * @param array $meta_query An array of meta_query filters. See the
	 *   documentation for WP_Meta_Query for details.
	 * @return array $sql_array 'join' and 'where' clauses.
	 */
	public static function get_meta_query_sql( $meta_query = array() ) {
		global $wpdb;

		$sql_array = array(
			'join'  => '',
			'where' => '',
		);

		if ( ! empty( $meta_query ) ) {
			$activity_meta_query = new WP_Meta_Query( $meta_query );

			// WP_Meta_Query expects the table name at
			// $wpdb->activitymeta
			$wpdb->activitymeta = buddypress()->activity->table_name_meta;

			$meta_sql = $activity_meta_query->get_sql( 'activity', 'a', 'id' );

			// Strip the leading AND - BP handles it in get()
			$sql_array['where'] = preg_replace( '/^\sAND/', '', $meta_sql['where'] );
			$sql_array['join']  = $meta_sql['join'];
		}

		return $sql_array;
	}

	/**
	 * In BuddyPress 1.2.x, this was used to retrieve specific activity stream items (for example, on an activity's permalink page).
	 *
	 * As of 1.5.x, use BP_Activity_Activity::get() with an 'in' parameter instead.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @deprecated 1.5
	 * @deprecated Use BP_Activity_Activity::get() with an 'in' parameter instead.
	 *
	 * @param mixed $activity_ids Array or comma-separated string of activity IDs to retrieve
	 * @param int $max Maximum number of results to return. (Optional; default is no maximum)
	 * @param int $page The set of results that the user is viewing. Used in pagination. (Optional; default is 1)
	 * @param int $per_page Specifies how many results per page. Used in pagination. (Optional; default is 25)
	 * @param string MySQL column sort; ASC or DESC. (Optional; default is DESC)
	 * @param bool $display_comments Retrieve an activity item's associated comments or not. (Optional; default is false)
	 * @return array
	 */
	public static function get_specific( $activity_ids, $max = false, $page = 1, $per_page = 25, $sort = 'DESC', $display_comments = false ) {
		_deprecated_function( __FUNCTION__, '1.5', 'Use BP_Activity_Activity::get() with the "in" parameter instead.' );
		return BP_Activity_Activity::get( $max, $page, $per_page, $sort, false, false, $display_comments, false, false, $activity_ids );
	}

	/**
	 * Get the first activity ID that matches a set of criteria.
	 *
	 * @param int $user_id The user ID to filter by.
	 * @param string $component The component to filter by.
	 * @param string $type The activity type to filter by.
	 * @param int $item_id The associated item to filter by.
	 * @param int $secondary_item_id The secondary associated item to filter by.
	 * @param string $action The action to filter by.
	 * @param string $content The content to filter by.
	 * @param string $date_recorded The date to filter by.
	 * @return int|bool Activity ID on success, false if none is found.
	 */
	public static function get_id( $user_id, $component, $type, $item_id, $secondary_item_id, $action, $content, $date_recorded ) {
		global $bp, $wpdb;

		$where_args = false;

		if ( !empty( $user_id ) )
			$where_args[] = $wpdb->prepare( "user_id = %d", $user_id );

		if ( !empty( $component ) )
			$where_args[] = $wpdb->prepare( "component = %s", $component );

		if ( !empty( $type ) )
			$where_args[] = $wpdb->prepare( "type = %s", $type );

		if ( !empty( $item_id ) )
			$where_args[] = $wpdb->prepare( "item_id = %d", $item_id );

		if ( !empty( $secondary_item_id ) )
			$where_args[] = $wpdb->prepare( "secondary_item_id = %d", $secondary_item_id );

		if ( !empty( $action ) )
			$where_args[] = $wpdb->prepare( "action = %s", $action );

		if ( !empty( $content ) )
			$where_args[] = $wpdb->prepare( "content = %s", $content );

		if ( !empty( $date_recorded ) )
			$where_args[] = $wpdb->prepare( "date_recorded = %s", $date_recorded );

		if ( !empty( $where_args ) )
			$where_sql = 'WHERE ' . join( ' AND ', $where_args );
		else
			return false;

		return $wpdb->get_var( "SELECT id FROM {$bp->activity->table_name} {$where_sql}" );
	}

	/**
	 * Delete activity items from the database.
	 *
	 * To delete a specific activity item, pass an 'id' parameter.
	 * Otherwise use the filters.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @param array $args {
	 *     @int $id Optional. The ID of a specific item to delete.
	 *     @string $action Optional. The action to filter by.
	 *     @string $content Optional. The content to filter by.
	 *     @string $component Optional. The component name to filter by.
	 *     @string $type Optional. The activity type to filter by.
	 *     @string $primary_link Optional. The primary URL to filter by.
	 *     @int $user_id Optional. The user ID to filter by.
	 *     @int $item_id Optional. The associated item ID to filter by.
	 *     @int $secondary_item_id Optional. The secondary associated item ID to filter by.
	 *     @string $date_recorded Optional. The date to filter by.
	 *     @int $hide_sitewide Optional. Default: false.
	 * }
	 * @return array|bool An array of deleted activity IDs on success, false on failure.
	 */
	public static function delete( $args = array() ) {
		global $wpdb, $bp;

		$defaults = array(
			'id'                => false,
			'action'            => false,
			'content'           => false,
			'component'         => false,
			'type'              => false,
			'primary_link'      => false,
			'user_id'           => false,
			'item_id'           => false,
			'secondary_item_id' => false,
			'date_recorded'     => false,
			'hide_sitewide'     => false
		);
		$params = wp_parse_args( $args, $defaults );
		extract( $params );

		$where_args = false;

		if ( !empty( $id ) )
			$where_args[] = $wpdb->prepare( "id = %d", $id );

		if ( !empty( $user_id ) )
			$where_args[] = $wpdb->prepare( "user_id = %d", $user_id );

		if ( !empty( $action ) )
			$where_args[] = $wpdb->prepare( "action = %s", $action );

		if ( !empty( $content ) )
			$where_args[] = $wpdb->prepare( "content = %s", $content );

		if ( !empty( $component ) )
			$where_args[] = $wpdb->prepare( "component = %s", $component );

		if ( !empty( $type ) )
			$where_args[] = $wpdb->prepare( "type = %s", $type );

		if ( !empty( $primary_link ) )
			$where_args[] = $wpdb->prepare( "primary_link = %s", $primary_link );

		if ( !empty( $item_id ) )
			$where_args[] = $wpdb->prepare( "item_id = %d", $item_id );

		if ( !empty( $secondary_item_id ) )
			$where_args[] = $wpdb->prepare( "secondary_item_id = %d", $secondary_item_id );

		if ( !empty( $date_recorded ) )
			$where_args[] = $wpdb->prepare( "date_recorded = %s", $date_recorded );

		if ( !empty( $hide_sitewide ) )
			$where_args[] = $wpdb->prepare( "hide_sitewide = %d", $hide_sitewide );

		if ( !empty( $where_args ) )
			$where_sql = 'WHERE ' . join( ' AND ', $where_args );
		else
			return false;

		// Fetch the activity IDs so we can delete any comments for this activity item
		$activity_ids = $wpdb->get_col( "SELECT id FROM {$bp->activity->table_name} {$where_sql}" );

        if ( ! $wpdb->query( "DELETE FROM {$bp->activity->table_name} {$where_sql}" ) ) {
			return false;
		}

		// Handle accompanying activity comments and meta deletion
		if ( $activity_ids ) {
			$activity_ids_comma          = implode( ',', wp_parse_id_list( $activity_ids ) );
			$activity_comments_where_sql = "WHERE type = 'activity_comment' AND item_id IN ({$activity_ids_comma})";

			// Fetch the activity comment IDs for our deleted activity items
			$activity_comment_ids = $wpdb->get_col( "SELECT id FROM {$bp->activity->table_name} {$activity_comments_where_sql}" );

			// We have activity comments!
			if ( ! empty( $activity_comment_ids ) ) {
				// Delete activity comments
                
                $wpdb->query( "DELETE FROM {$bp->activity->table_name} {$activity_comments_where_sql}" );
                
				// Merge activity IDs with activity comment IDs
				$activity_ids = array_merge( $activity_ids, $activity_comment_ids );
			}

			// Delete all activity meta entries for activity items and activity comments
			BP_Activity_Activity::delete_activity_meta_entries( $activity_ids );
		}

		return $activity_ids;
	}

	/**
	 * Delete the comments associated with a set of activity items.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @todo Mark as deprecated?  Method is no longer used internally.
	 *
	 * @param array $activity_ids Activity IDs whose comments should be deleted.
	 * @param bool $delete_meta Should we delete the activity meta items for these comments?
	 * @return bool True on success.
	 */
	public static function delete_activity_item_comments( $activity_ids = array(), $delete_meta = true ) {
		global $bp, $wpdb;

		$delete_meta = (bool) $delete_meta;

		$activity_ids = implode( ',', wp_parse_id_list( $activity_ids ) );

		if ( $delete_meta ) {
			// Fetch the activity comment IDs for our deleted activity items
			$activity_comment_ids = $wpdb->get_col( "SELECT id FROM {$bp->activity->table_name} WHERE type = 'activity_comment' AND item_id IN ({$activity_ids})" );

			if ( ! empty( $activity_comment_ids ) ) {
				self::delete_activity_meta_entries( $activity_comment_ids );
			}
		}

		return $wpdb->query( "DELETE FROM {$bp->activity->table_name} WHERE type = 'activity_comment' AND item_id IN ({$activity_ids})" );
	}

	/**
	 * Delete the meta entries associated with a set of activity items.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @param array $activity_ids Activity IDs whose meta should be deleted.
	 * @return bool True on success.
	 */
	public static function delete_activity_meta_entries( $activity_ids = array() ) {
		global $bp, $wpdb;

		$activity_ids = implode( ',', wp_parse_id_list( $activity_ids ) );

		foreach ( (array) $activity_ids as $activity_id ) {
			bp_activity_clear_meta_cache_for_activity( $activity_id );
		}

		return $wpdb->query( "DELETE FROM {$bp->activity->table_name_meta} WHERE activity_id IN ({$activity_ids})" );
	}

	/**
	 * Append activity comments to their associated activity items.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @global wpdb $wpdb WordPress database object
	 *
	 * @param array $activities Activities to fetch comments for.
	 * @param bool $spam Optional. 'ham_only' (default), 'spam_only' or 'all'.
	 * @return array The updated activities with nested comments.
	 */
	public static function append_comments( $activities, $spam = 'ham_only' ) {
		$activity_comments = array();

		// Now fetch the activity comments and parse them into the correct position in the activities array.
		foreach( (array) $activities as $activity ) {
			$top_level_parent_id = 'activity_comment' == $activity->type ? $activity->item_id : 0;
			$activity_comments[$activity->id] = BP_Activity_Activity::get_activity_comments( $activity->id, $activity->mptt_left, $activity->mptt_right, $spam, $top_level_parent_id );
		}

		// Merge the comments with the activity items
		foreach( (array) $activities as $key => $activity )
			if ( isset( $activity_comments[$activity->id] ) )
				$activities[$key]->children = $activity_comments[$activity->id];

		return $activities;
	}

	/**
	 * Get activity comments that are associated with a specific activity ID.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @global BuddyPress $bp The one true BuddyPress instance.
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $activity_id Activity ID to fetch comments for.
	 * @param int $left Left-most node boundary.
	 * @param into $right Right-most node boundary.
	 * @param bool $spam Optional. 'ham_only' (default), 'spam_only' or 'all'.
	 * @param int $top_level_parent_id Optional. The id of the root-level parent activity item.
	 * @return array The updated activities with nested comments.
	 */
	public static function get_activity_comments( $activity_id, $left, $right, $spam = 'ham_only', $top_level_parent_id = 0 ) {
		global $wpdb, $bp;

		if ( empty( $top_level_parent_id ) ) {
			$top_level_parent_id = $activity_id;
		}

		if ( !$comments = wp_cache_get( 'bp_activity_comments_' . $activity_id ) ) {

			// Select the user's fullname with the query
			if ( bp_is_active( 'xprofile' ) ) {
				$fullname_select = ", pd.value as user_fullname";
				$fullname_from = ", {$bp->profile->table_name_data} pd ";
				$fullname_where = "AND pd.user_id = a.user_id AND pd.field_id = 1";

			// Prevent debug errors
			} else {
				$fullname_select = $fullname_from = $fullname_where = '';
			}

			// Don't retrieve activity comments marked as spam
			if ( 'ham_only' == $spam ) {
				$spam_sql = 'AND a.is_spam = 0';
			} elseif ( 'spam_only' == $spam ) {
				$spam_sql = 'AND a.is_spam = 1';
			} else {
				$spam_sql = '';
			}

			// The mptt BETWEEN clause allows us to limit returned descendants to the right part of the tree
			$sql = apply_filters( 'bp_activity_comments_user_join_filter', $wpdb->prepare( "SELECT a.*, u.user_email, u.user_nicename, u.user_login, u.display_name{$fullname_select} FROM {$bp->activity->table_name} a, {$wpdb->users} u{$fullname_from} WHERE u.ID = a.user_id {$fullname_where} AND a.type = 'activity_comment' {$spam_sql} AND a.item_id = %d AND a.mptt_left > %d AND a.mptt_left < %d ORDER BY a.date_recorded ASC", $top_level_parent_id, $left, $right ), $activity_id, $left, $right, $spam_sql );

			// Retrieve all descendants of the $root node
			$descendants = $wpdb->get_results( $sql );
			$ref         = array();

			// Loop descendants and build an assoc array
			foreach ( (array) $descendants as $d ) {
				$d->children = array();

				// If we have a reference on the parent
				if ( isset( $ref[ $d->secondary_item_id ] ) ) {
					$ref[ $d->secondary_item_id ]->children[ $d->id ] = $d;
					$ref[ $d->id ] =& $ref[ $d->secondary_item_id ]->children[ $d->id ];

				// If we don't have a reference on the parent, put in the root level
				} else {
					$comments[ $d->id ] = $d;
					$ref[ $d->id ] =& $comments[ $d->id ];
				}
			}
			wp_cache_set( 'bp_activity_comments_' . $activity_id, $comments, 'bp' );
		}

		return $comments;
	}

	/**
	 * Rebuild nested comment tree under an activity or activity comment.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @global BuddyPress $bp The one true BuddyPress instance.
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $parent_id ID of an activty or activity comment.
	 * @param int $left Node boundary start for activity or activity comment.
	 * @return int Right node boundary of activity or activity comment.
	 */
	public static function rebuild_activity_comment_tree( $parent_id, $left = 1 ) {
		global $wpdb, $bp;

		// The right value of this node is the left value + 1
		$right = $left + 1;

		// Get all descendants of this node
		$descendants = BP_Activity_Activity::get_child_comments( $parent_id );

		// Loop the descendants and recalculate the left and right values
		foreach ( (array) $descendants as $descendant )
			$right = BP_Activity_Activity::rebuild_activity_comment_tree( $descendant->id, $right );

		// We've got the left value, and now that we've processed the children
		// of this node we also know the right value
		if ( 1 == $left )
			$wpdb->query( $wpdb->prepare( "UPDATE {$bp->activity->table_name} SET mptt_left = %d, mptt_right = %d WHERE id = %d", $left, $right, $parent_id ) );
		else
			$wpdb->query( $wpdb->prepare( "UPDATE {$bp->activity->table_name} SET mptt_left = %d, mptt_right = %d WHERE type = 'activity_comment' AND id = %d", $left, $right, $parent_id ) );

		// Return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Get child comments of an activity or activity comment.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @global BuddyPress $bp The one true BuddyPress instance.
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param int $parent_id ID of an activty or activity comment.
	 * @return object Numerically indexed array of child comments.
	 */
	public static function get_child_comments( $parent_id ) {
		global $bp, $wpdb;

		return $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$bp->activity->table_name} WHERE type = 'activity_comment' AND secondary_item_id = %d", $parent_id ) );
	}

	/**
	 * Get a list of components that have recorded activity associated with them
	 *
	 * @return array List of component names.
	 */
	public static function get_recorded_components() {
		global $wpdb, $bp;
		return $wpdb->get_col( "SELECT DISTINCT component FROM {$bp->activity->table_name} ORDER BY component ASC" );
	}

	/**
	 * Get sitewide activity items for use in an RSS feed.
	 *
	 * @param int $limit Optional. Number of items to fetch. Default: 35.
	 * @return array $activity_feed List of activity items, with RSS data added.
	 */
	public static function get_sitewide_items_for_feed( $limit = 35 ) {
		$activities    = bp_activity_get_sitewide( array( 'max' => $limit ) );
		$activity_feed = array();

		for ( $i = 0, $count = count( $activities ); $i < $count; ++$i ) {
			$title                            = explode( '<span', $activities[$i]['content'] );
			$activity_feed[$i]['title']       = trim( strip_tags( $title[0] ) );
			$activity_feed[$i]['link']        = $activities[$i]['primary_link'];
			$activity_feed[$i]['description'] = @sprintf( $activities[$i]['content'], '' );
			$activity_feed[$i]['pubdate']     = $activities[$i]['date_recorded'];
		}

		return $activity_feed;
	}

	/**
	 * Create SQL IN clause for filter queries.
	 *
	 * @since BuddyPress (1.5)
	 *
	 * @see BP_Activity_Activity::get_filter_sql()
	 *
	 * @param string $field The database field.
	 * @param array|bool $items The values for the IN clause, or false when none are found.
	 */
	public static function get_in_operator_sql( $field, $items ) {
		global $wpdb;

		// split items at the comma
		$items_dirty = explode( ',', $items );

		// array of prepared integers or quoted strings
		$items_prepared = array();

		// clean up and format each item
		foreach ( $items_dirty as $item ) {
			// clean up the string
			$item = trim( $item );
			// pass everything through prepare for security and to safely quote strings
			$items_prepared[] = ( is_numeric( $item ) ) ? $wpdb->prepare( '%d', $item ) : $wpdb->prepare( '%s', $item );
		}

		// build IN operator sql syntax
		if ( count( $items_prepared ) )
			return sprintf( '%s IN ( %s )', trim( $field ), implode( ',', $items_prepared ) );
		else
			return false;
	}

	/**
	 * Create filter SQL clauses.
	 *
	 * @since BuddyPress (1.5)
	 *
	 * @param array $filter_array Fields and values to filter by. Should be
	 *     in the format:
	 *         $filter_array = array(
	 *             'filter1' => $value,
	 *             'filter2' => $value,
	 *         )
	 *     Possible filters are as follows. Each can be either a single
	 *     string, a comma-separated list, or an array of values.
	 *       - 'user_id' User ID(s)
	 *       - 'object' Corresponds to the 'component' column in the database.
	 *       - 'action' Corresponds to the 'type' column in the database.
	 *       - 'primary_id' Corresponds to the 'item_id' column in the database.
	 *       - 'secondary_id' Corresponds to the 'secondary_item_id' column in the database.
	 * @return string The filter clause, for use in a SQL query.
	 */
	public static function get_filter_sql( $filter_array ) {

		$filter_sql = array();

		if ( !empty( $filter_array['user_id'] ) ) {
			$user_sql = BP_Activity_Activity::get_in_operator_sql( 'a.user_id', $filter_array['user_id'] );
			if ( !empty( $user_sql ) )
				$filter_sql[] = $user_sql;
		}

		if ( !empty( $filter_array['object'] ) ) {
			$object_sql = BP_Activity_Activity::get_in_operator_sql( 'a.component', $filter_array['object'] );
			if ( !empty( $object_sql ) )
				$filter_sql[] = $object_sql;
		}

		if ( !empty( $filter_array['action'] ) ) {
			$action_sql = BP_Activity_Activity::get_in_operator_sql( 'a.type', $filter_array['action'] );
			if ( !empty( $action_sql ) )
				$filter_sql[] = $action_sql;
		}

		if ( !empty( $filter_array['primary_id'] ) ) {
			$pid_sql = BP_Activity_Activity::get_in_operator_sql( 'a.item_id', $filter_array['primary_id'] );
			if ( !empty( $pid_sql ) )
				$filter_sql[] = $pid_sql;
		}

		if ( !empty( $filter_array['secondary_id'] ) ) {
			$sid_sql = BP_Activity_Activity::get_in_operator_sql( 'a.secondary_item_id', $filter_array['secondary_id'] );
			if ( !empty( $sid_sql ) )
				$filter_sql[] = $sid_sql;
		}

		if ( empty( $filter_sql ) )
			return false;

		return join( ' AND ', $filter_sql );
	}

	/**
	 * Get the date/time of last recorded activity.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @return string ISO timestamp.
	 */
	public static function get_last_updated() {
		global $bp, $wpdb;

		return $wpdb->get_var( "SELECT date_recorded FROM {$bp->activity->table_name} ORDER BY date_recorded DESC LIMIT 1" );
	}

	/**
	 * Get favorite count for a given user.
	 *
	 * @since BuddyPress (1.2)
	 *
	 * @param int The ID of the user whose favorites you're counting.
	 * @return int A count of the user's favorites.
	 */
	public static function total_favorite_count( $user_id ) {
		if ( !$favorite_activity_entries = bp_get_user_meta( $user_id, 'bp_favorite_activities', true ) )
			return 0;

		return count( maybe_unserialize( $favorite_activity_entries ) );
	}

	/**
	 * Check whether an activity item exists with a given string content.
	 *
	 * @param string $content The content to filter by.
	 * @return int|bool The ID of the first matching item if found, otherwise false.
	 */
	public static function check_exists_by_content( $content ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->activity->table_name} WHERE content = %s", $content ) );
	}

	/**
	 * Hide all activity for a given user.
	 *
	 * @param int $user_id The ID of the user whose activity you want to mark hidden.
	 * @param int
	 */
	public static function hide_all_for_user( $user_id ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "UPDATE {$bp->activity->table_name} SET hide_sitewide = 1 WHERE user_id = %d", $user_id ) );
	}
}

/**
 * Create a RSS feed using the activity component.
 *
 * You should only construct a new feed when you've validated that you're on
 * the appropriate screen.
 *
 * See {@link bp_activity_action_sitewide_feed()} as an example.
 *
 * Accepted parameters:
 *   id	              - internal id for the feed; should be alphanumeric only
 *                      (required)
 *   title            - RSS feed title
 *   link             - Relevant link for the RSS feed
 *   description      - RSS feed description
 *   ttl              - Time-to-live (see inline doc in constructor)
 *   update_period    - Part of the syndication module (see inline doc in
 *                      constructor for more info)
 *   update_frequency - Part of the syndication module (see inline doc in
 *                      constructor for more info)
 *   max              - Number of feed items to display
 *   activity_args    - Arguments passed to {@link bp_has_activities()}
 *
 * @since BuddyPress (1.8)
 */
class BP_Activity_Feed {
	/**
	 * Holds our custom class properties.
	 *
	 * These variables are stored in a protected array that is magically
	 * updated using PHP 5.2+ methods.
	 *
	 * @see BP_Feed::__construct() This is where $data is added
	 * @var array
	 */
	protected $data;

	/**
	 * Magic method for checking the existence of a certain data variable.
	 *
	 * @param string $key
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting a certain data variable.
	 *
	 * @param string $key
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Constructor.
	 *
	 * @param array $args Optional
	 */
	public function __construct( $args = array() ) {
		// If feeds are disabled, stop now!
		if ( false === (bool) apply_filters( 'bp_activity_enable_feeds', true ) ) {
			global $wp_query;

			// set feed flag to false
			$wp_query->is_feed = false;

			return false;
		}

		// Setup data
		$this->data = wp_parse_args( $args, array(
			// Internal identifier for the RSS feed - should be alphanumeric only
			'id'               => '',

			// RSS title - should be plain-text
			'title'            => '',

			// relevant link for the RSS feed
			'link'             => '',

			// RSS description - should be plain-text
			'description'      => '',

			// Time-to-live - number of minutes to cache the data before an aggregator
			// requests it again.  This is only acknowledged if the RSS client supports it
			//
			// See: http://www.rssboard.org/rss-profile#element-channel-ttl
			//      http://www.kbcafe.com/rss/rssfeedstate.html#ttl
			'ttl'              => '30',

			// Syndication module - similar to ttl, but not really supported by RSS
			// clients
			//
			// See: http://web.resource.org/rss/1.0/modules/syndication/#description
			//      http://www.kbcafe.com/rss/rssfeedstate.html#syndicationmodule
			'update_period'    => 'hourly',
			'update_frequency' => 2,

			// Number of items to display
			'max'              => 50,

			// Activity arguments passed to bp_has_activities()
			'activity_args'    => array()
		) );

		// Plugins can use this filter to modify the feed before it is setup
		do_action_ref_array( 'bp_activity_feed_prefetch', array( &$this ) );

		// Setup class properties
		$this->setup_properties();

		// Check if id is valid
		if ( empty( $this->id ) ) {
			_doing_it_wrong( 'BP_Activity_Feed', __( "RSS feed 'id' must be defined", 'buddypress' ), 'BP 1.8' );
			return false;
		}

		// Plugins can use this filter to modify the feed after it's setup
		do_action_ref_array( 'bp_activity_feed_postfetch', array( &$this ) );

		// Setup feed hooks
		$this->setup_hooks();

		// Output the feed
		$this->output();

		// Kill the rest of the output
		die();
	}

	/** SETUP ****************************************************************/

	/**
	 * Setup and validate the class properties.
	 *
	 * @access protected
	 */
	protected function setup_properties() {
		$this->id               = sanitize_title( $this->id );
		$this->title            = strip_tags( $this->title );
		$this->link             = esc_url_raw( $this->link );
		$this->description      = strip_tags( $this->description );
		$this->ttl              = (int) $this->ttl;
		$this->update_period    = strip_tags( $this->update_period );
		$this->update_frequency = (int) $this->update_frequency;

		$this->activity_args    = wp_parse_args( $this->activity_args, array(
			'max'              => $this->max,
			'per_page'         => $this->max,
			'display_comments' => 'stream'
		) );

	}

	/**
	 * Setup some hooks that are used in the feed.
	 *
	 * Currently, these hooks are used to maintain backwards compatibility with
	 * the RSS feeds previous to BP 1.8.
	 *
	 * @access protected
	 */
	protected function setup_hooks() {
		add_action( 'bp_activity_feed_rss_attributes',   array( $this, 'backpat_rss_attributes' ) );
		add_action( 'bp_activity_feed_channel_elements', array( $this, 'backpat_channel_elements' ) );
		add_action( 'bp_activity_feed_item_elements',    array( $this, 'backpat_item_elements' ) );
	}

	/** BACKPAT HOOKS ********************************************************/

	/**
	 * Fire a hook to ensure backward compatibility for RSS attributes.
	 */
	public function backpat_rss_attributes() {
		do_action( 'bp_activity_' . $this->id . '_feed' );
	}

	/**
	 * Fire a hook to ensure backward compatibility for channel elements.
	 */
	public function backpat_channel_elements() {
		do_action( 'bp_activity_' . $this->id . '_feed_head' );
	}

	/**
	 * Fire a hook to ensure backward compatibility for item elements.
	 */
	public function backpat_item_elements() {
		switch ( $this->id ) {

			// sitewide and friends feeds use the 'personal' hook
			case 'sitewide' :
			case 'friends' :
				$id = 'personal';

				break;

			default :
				$id = $this->id;

				break;
		}

		do_action( 'bp_activity_' . $id . '_feed_item' );
	}

	/** HELPERS **************************************************************/

	/**
	 * Output the feed's item content.
	 *
	 * @access protected
	 */
	protected function feed_content() {
		bp_activity_content_body();

		switch ( $this->id ) {

			// also output parent activity item if we're on a specific feed
			case 'favorites' :
			case 'friends' :
			case 'mentions' :
			case 'personal' :

				if ( 'activity_comment' == bp_get_activity_action_name() ) :
			?>
				<strong><?php _e( 'In reply to', 'buddypress' ) ?></strong> -
				<?php bp_activity_parent_content() ?>
			<?php
				endif;

				break;
		}
	}

	/**
	 * Sets various HTTP headers related to Content-Type and browser caching.
	 *
	 * Most of this class method is derived from {@link WP::send_headers()}.
	 *
	 * @since BuddyPress (1.9.0)
	 *
	 * @access protected
	 */
	protected function http_headers() {
		// set up some additional headers if not on a directory page
		// this is done b/c BP uses pseudo-pages
		if ( ! bp_is_directory() ) {
			global $wp_query;

			$wp_query->is_404 = false;
			status_header( 200 );
		}

		// Set content-type
		@header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		// Cache-related variables
		$last_modified      = mysql2date( 'D, d M Y H:i:s O', bp_activity_get_last_updated(), false );
		$modified_timestamp = strtotime( $last_modified );
		$etag               = md5( $last_modified );

		// Set cache-related headers
		@header( 'Last-Modified: ' . $last_modified );
		@header( 'Pragma: no-cache' );
		@header( 'ETag: ' . '"' . $etag . '"' );

		// First commit of BuddyPress! (Easter egg)
		@header( 'Expires: Tue, 25 Mar 2008 17:13:55 GMT');

		// Get ETag from supported user agents
		if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) {
			$client_etag = wp_unslash( $_SERVER['HTTP_IF_NONE_MATCH'] );

			// Remove quotes from ETag
			$client_etag = trim( $client_etag, '"' );

			// Strip suffixes from ETag if they exist (eg. "-gzip")
			if ( $etag_suffix_pos = strpos( $client_etag, '-' ) ) {
				$client_etag = substr( $client_etag, 0, $etag_suffix_pos );
			}

		// No ETag found
		} else {
			$client_etag = false;
		}

		// Get client last modified timestamp from supported user agents
		$client_last_modified      = empty( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? '' : trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
		$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;

		// Set 304 status if feed hasn't been updated since last fetch
		if ( ( $client_last_modified && $client_etag ) ?
				 ( ( $client_modified_timestamp >= $modified_timestamp ) && ( $client_etag == $etag ) ) :
				 ( ( $client_modified_timestamp >= $modified_timestamp ) || ( $client_etag == $etag ) ) ) {
			$status = 304;
		} else {
			$status = false;
		}

		// If feed hasn't changed as reported by the user agent, set 304 status header
		if ( ! empty( $status ) ) {
			status_header( $status );

			// cached response, so stop now!
			if ( $status == 304 ) {
				exit();
			}
		}
	}

	/** OUTPUT ***************************************************************/

	/**
	 * Output the RSS feed.
	 *
	 * @access protected
	 */
	protected function output() {
		$this->http_headers();
		echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?'.'>';
	?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action( 'bp_activity_feed_rss_attributes' ); ?>
>

<channel>
	<title><?php echo $this->title; ?></title>
	<link><?php echo $this->link; ?></link>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<description><?php echo $this->description ?></description>
	<lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s O', bp_activity_get_last_updated(), false ); ?></lastBuildDate>
	<generator>http://buddypress.org/?v=<?php bp_version(); ?></generator>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<ttl><?php echo $this->ttl; ?></ttl>
	<sy:updatePeriod><?php echo $this->update_period; ?></sy:updatePeriod>
 	<sy:updateFrequency><?php echo $this->update_frequency; ?></sy:updateFrequency>
	<?php do_action( 'bp_activity_feed_channel_elements' ); ?>

	<?php if ( bp_has_activities( $this->activity_args ) ) : ?>
		<?php while ( bp_activities() ) : bp_the_activity(); ?>
			<item>
				<guid isPermaLink="false"><?php bp_activity_feed_item_guid(); ?></guid>
				<title><?php echo stripslashes( bp_get_activity_feed_item_title() ); ?></title>
				<link><?php bp_activity_thread_permalink() ?></link>
				<pubDate><?php echo mysql2date( 'D, d M Y H:i:s O', bp_get_activity_feed_item_date(), false ); ?></pubDate>

				<?php if ( bp_get_activity_feed_item_description() ) : ?>
					<content:encoded><![CDATA[<?php $this->feed_content(); ?>]]></content:encoded>
				<?php endif; ?>

				<?php if ( bp_activity_can_comment() ) : ?>
					<slash:comments><?php bp_activity_comment_count(); ?></slash:comments>
				<?php endif; ?>

				<?php do_action( 'bp_activity_feed_item_elements' ); ?>
			</item>
		<?php endwhile; ?>

	<?php endif; ?>
</channel>
</rss><?php
	}
}

class BP_Activity_Dcma {
    var $case_id = false;
    var $owner_id = false;
    var $agent_id = false;
    var $owner_website_url = false;
    var $reported_content_url = false;
    var $activity_id = false;
    var $remove_all = false;
    var $strike = false;
    var $is_accurate = false;
    var $is_good_faith = false;
    var $is_authorized_agent = false;
    var $dmca_step = false; // created, counter_claim
    var $update_date = false;
    var $table_name = 'wp_bp_activity_dmca';
    
    public function __construct($case_id = false) {
        if( !empty( $case_id ) ){
            $this->case_id = $case_id;
            $this->populate();
        }
    }
    
    public function populate() {
        global $wpdb, $bp;
        
        if ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE case_id = %d", $this->case_id ))) {
            $this->case_id              = $row->case_id;
			$this->owner_id             = $row->owner_id;
			$this->agent_id             = $row->agent_id;
			$this->owner_website_url    = $row->owner_website_url;
			$this->reported_content_url = $row->reported_content_url;
			$this->activity_id          = $row->activity_id;
			$this->remove_all           = $row->remove_all;
			$this->strike               = $row->strike;
			$this->is_accurate          = $row->is_accurate;
			$this->is_good_faith        = $row->is_good_faith;
			$this->is_authorized_agent  = $row->is_authorized_agent;
			$this->dmca_step            = $row->dmca_step;
			$this->update_date         = $row->update_date;
            $this->counter_claim_reason = $row->counter_claim_reason;
            $this->dispute_counter_reason = $row->dispute_counter_reason;
        }
    }
    
    public function save() {
        global $wpdb, $bp;
        
        if ( $this->case_id )
			$q = $wpdb->prepare( "UPDATE {$this->table_name} SET owner_id = %d, agent_id = %d, owner_website_url = %s, reported_content_url = %s, activity_id = %d, remove_all = %d, strike = %d, is_accurate = %d, is_good_faith = %d, is_authorized_agent = %d, dmca_step = %s, update_date = %s WHERE case_id = %d", $this->owner_id, $this->agent_id, $this->owner_website_url, $this->reported_content_url, $this->activity_id, $this->remove_all, $this->strike, $this->is_accurate, $this->is_good_faith, $this->is_authorized_agent, $this->dmca_step, $this->update_date, $this->case_id );
		else
			$q = $wpdb->prepare( "INSERT INTO {$this->table_name} ( owner_id, agent_id, owner_website_url, reported_content_url, activity_id, remove_all, strike, is_accurate, is_good_faith, is_authorized_agent, dmca_step, update_date ) VALUES ( %d, %d, %s, %s, %d, %d, %d, %d, %d, %d, %s, %s )", $this->owner_id, $this->agent_id, $this->owner_website_url, $this->reported_content_url, $this->activity_id, $this->remove_all, $this->strike, $this->is_accurate, $this->is_good_faith, $this->is_authorized_agent, $this->dmca_step, $this->update_date );

		if ( false === $wpdb->query( $q ) )
			return false;

		// If this is a new activity item, set the $id property
		if ( empty( $this->case_id ) )
			$this->case_id = $wpdb->insert_id;
        
        return true;
    }
    
    public function checkActivity($activity_id) {
        global $wpdb;
        
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE activity_id = %d", $activity_id ));
        
        if ($row){
            if ($row->dmca_step == "finished"){
                return 'removed';
            }else{
                return 'is_processing';
            }
        }else{
            return "not_processed";
        }
    }
    
    public function update_counter_claim_reason ($counter_claim_reason){
        global $wpdb;
        $wpdb->update ($this->table_name, array('counter_claim_reason' => $counter_claim_reason), array('case_id' => $this->case_id), array("%s"), array("%d"));
        $this->counter_claim_reason = $counter_claim_reason;
    }
    
    public function update_dispute_counter_reason ($dispute_counter_reason){
        global $wpdb;
        $wpdb->update ($this->table_name, array('dispute_counter_reason' => $dispute_counter_reason), array('case_id' => $this->case_id), array("%s"), array("%d"));
        $this->dispute_counter_reason = $dispute_counter_reason;
    }
    
    public function update_step ($dmca_step){
        global $wpdb;
        
        $date = new DateTime();
        $update_date = $date->format('Y-m-d H:i:s');
        
        $wpdb->update ($this->table_name, array('dmca_step' => $dmca_step, 'update_date' => $update_date), array('case_id' => $this->case_id), array("%s", "%s"), array("%d"));
        
        if($this->dmca_step == 'created' && $dmca_step == 'finished'){
            $this->delete_activity_realy();
            $this->dmca_step5(); // deleted; to do send mail, panelty to user.
            $this->panelty_agent();
        }else if($this->dmca_step == 'counter_claim' && $dmca_step == 'finished'){
            $this->restore_activity();
            $this->dmca_step6(); // resotred; to do send mail.
        }
        
        $this->dmca_step = $dmca_step;
    }
    
    public function check_date_update_step (){
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name} where dmca_step != 'finished'";
        $dcmas = $wpdb->get_results ( $sql );
        
        foreach($dcmas as $dcma_atr){
            $update_time = strtotime($dcma_atr->update_date);
            $remaining = time() - $update_time;
            $days = floor($remaining / 86400);
            if ($days > 10){
                $at_dcma = new BP_Activity_Dcma($case_id = $dcma_atr->case_id);
                $at_dcma->update_step ("finished");
            }
        }
    }
    
    public function get() {
        
    }
    
    // send mail to Dremboard, Owner, Agent with DCMA notification.
    public function dmca_step1 () {
        if ( empty( $this->case_id ) )
            return false;
        $to = '';
        $subject = '';
        $message = '';
        
        $owner_id = $this->owner_id;
        $owner = bp_core_get_core_userdata($owner_id);
        $owner_name = $owner->display_name;
        $agent_id = $this->agent_id;
        $agent = bp_core_get_core_userdata($agent_id);
        $agent_name = $agent->display_name;
        
    // Send Email to Dremboard with case Number.
        $to = get_option( 'bp-copyright-mail-address', '' );
        if ($this->remove_all == 1)
            $remove_all_str = "all Drēms";
        else
            $remove_all_str = "only this specific Drēm";
        
        $strike_str = "";
        if ($this->strike == 1)
            $strike_str = "and add a strike ";
        
        if (!empty($to)) {
            $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant #Case: %d', 'buddypress'), $this->case_id)));
            $message = sprintf(__('<p>%1$s has filed a claim and requested that Drēmboard take down the photo/video that has been copyrighted. </p>'
                            . '<p>The photo/video is found on %2$s (or place where tlhey got the photo/photo) and was listed on %3$s (place where they saw the photo/video.)</p> '
                            . '<p>%1$s is always requesting that we remove %4$s that contain the same image file %5$sof the individual who posted the image.</p> '
                            . '<p>%1$s is of good faith belief that the disputed use of the copyrighted material is not authorized by the copyright owner, its agent, or the law (e.g., as a fair use). </p>'
                            . '<p>%1$s state under penalty of perjury that he/she is the owner, or authorized to act on behalf of the owner, of the copyright or of an exclusive right under the copyright that is allegedly infringed.</p>'
                            . '<p style="color:red"><i>Case Number to Copyright Counter Claim : </i>%6$s</p>', 'buddypress')
                    , $owner_name, $this->owner_website_url, $this->reported_content_url, $remove_all_str, $strike_str, $this->case_id);
            wp_mail($to, $subject, $message);
        }
        // Send Email to user making claim with Case Number.
        $to = $owner->user_email;
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                        . '<p>%2$s will be notified by email that a claim of the stated that the photo/video found on %3$s is copyrighted.</p>'
                        . '<p>%2$s will have an opportunity to counter-claim if he/she feels that this claim is in error.</p>'
                        . '<p>An email will be sent to you for you record.</p>'
                        . '<br>'
                        . '<p style="color:red"><i>Case Number to Copyright Counter Claim : </i>%4$s</p>'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $owner_name, $agent_name, $this->reported_content_url, $this->case_id);
        wp_mail($to, $subject, $message);
        // Send Email to user/Infringer who posted up picture/video with Case Number.
        
        $to = $agent->user_email;
        $counter_claim_url = home_url('copyright-complaint/?action=counter_claim');
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                .'<p>We have received a notification from %2$s that the photo/video found on %3$s was copyrighted. </p>'
                .'<p>As a result we have taking down the photo/video in compliance of DMCA.</p>'
                .'<p>If you believe you have the rights to post the content at issue here, you can file a counter claim at %4$s, and it will be sent to copyright@dremboard.com.</p>'
                .'<p>For more information on our DMCA policy, including how to file a counter-claim please visit www.dremboard.com/terms.</p>'
                .'<br>'
                .'<p>A bit of background: the DMCA is a United States copyright law that provides guidelines for online service provider liability in case of copyright infringement. </p>'
                .'<br>'
                .'<p>If this is brought to our attention that you have republished the content we will delete your post and count it as a violation/strike on your account. </p>'
                .'<p>Repeated violations to our Terms may result in further remedial action taken against your Drēmboard account including deleting your post/photo/videos or terminating your account. </p>'
                .'<p>If you have legal questions about this notification, you should retain your own legal counsel.'
                .'<br>'
                . '<p style="color:red"><i>Case Number to Copyright Counter Claim : </i>%5$s</p>'
                . '<br>'
                .'<p>Sincerely,</p>'
                . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $agent_name, $owner_name, $this->reported_content_url, $counter_claim_url, $this->case_id);
        wp_mail($to, $subject, $message);
        return true;
    }
    
    // remove as del_flag for counter_claim period.
    public function dmca_step2 () {
        if ( empty( $this->case_id ) )
            return false;
        $this->delete_activity_as_del_flag();
    }
    
    // change step as 'counter_claim' and email to dremboard, owner, agent.
    public function dmca_step3 () {
        if ( empty( $this->case_id ) )
            return false;
        $to = '';
        $subject = '';
        $message = '';
        
        $owner_id = $this->owner_id;
        $owner = bp_core_get_core_userdata($owner_id);
        $owner_name = $owner->display_name;
        $agent_id = $this->agent_id;
        $agent = bp_core_get_core_userdata($agent_id);
        $agent_name = $agent->display_name;
        
        // Send Email to Dremboard with case Number.
        $to = get_option( 'bp-copyright-mail-address', '' );
        
        if (!empty($to)) {
            $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Counter Claim #Case: %d', 'buddypress'), $this->case_id)));
            $message = sprintf(__('<p>%1$s has counter-claim and stated that the photo/video.</p>'
                        . '<p>The reason is as follows</p>'
                        . '%2$s'
                        . '<p style="color:red"><i>Case Number to Copyright Counter Claim : </i><b>%3$s</b></p>'
                        . '<br>', 'buddypress')
                    , $agent_name, $this->counter_claim_reason, $this->case_id);
            wp_mail($to, $subject, $message);
        }
        
        // Send Email to user making counter-claim with Case Number.
        $to = $owner->user_email;
        $dispute_counter_url  = home_url('copyright-complaint/?action=dispute_counter');

        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Counter Claim #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                        . '<p>%2$s has counter-claim and stated that the photo/video.</p>'
                        . '<p>The reason is as follows</p>'
                        . '%3$s'
                        . '<br>'
                        . '<p>Please provide additional information to dispute this counter-claim within 10 days or the photo or video found on %4$s or it will be restored to %2$s in 10-14 days.</p>'
                        . '<p>you can file a dispute counter at %5$s, and it will be sent to copyright@dremboard.com.</p>'
                        . '<br>'
                        . '<p style="color:red"><i>Case Number to Copyright Counter Claim : </i><b>%6$s</b></p>'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $owner_name, $agent_name, $this->counter_claim_reason, $this->reported_content_url, $dispute_counter_url, $this->case_id);
        wp_mail($to, $subject, $message);
        
        // Send Email to user making claim with Case Number.
        $to = $agent->user_email;
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Counter Claim #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                        . '<p>%2$s will be notified by email that a counter-claim of the stated that the photo/video found on %3$s is copyrighted.</p>'
                        . '<p>%2$s will have an opportunity to dispute-counter if he/she feels that this counter-claim is in error.</p>'
                        . '<p>An email will be sent to you for you record.</p>'
                        . '<br>'
                        . '<p style="color:red"><i>Case Number to Copyright Counter Claim : </i><b>%4$s</b></p>'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $agent_name, $owner_name, $this->reported_content_url, $this->case_id);
        wp_mail($to, $subject, $message);
        
        return true;
    }
    
    // change step as 'finished' and email to dremboard, owner, delete drems really.
    public function dmca_step4 () {
        if ( empty( $this->case_id ) )
            return false;
        $to = '';
        $subject = '';
        $message = '';
        
        $owner_id = $this->owner_id;
        $owner = bp_core_get_core_userdata($owner_id);
        $owner_name = $owner->display_name;
        $agent_id = $this->agent_id;
        $agent = bp_core_get_core_userdata($agent_id);
        $agent_name = $agent->display_name;
        
        // Send Email to Dremboard with case Number.
        $to = get_option( 'bp-copyright-mail-address', '' );
        
        if (!empty($to)) {
            $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Dispute Counter #Case: %d', 'buddypress'), $this->case_id)));
            $message = sprintf(__('<p>%1$s has dispute-counter and stated that the photo/video.</p>'
                        . '<p>The reason is as follows:</p>'
                        . '%2$s'
                        . '<br>'
                        . '<p style="color:red"><i>Case Number to Copyright Dispute Counter: </i>%3$s</p>'
                        . '<br>', 'buddypress')
                    , $owner_name, $this->dispute_counter_reason, $this->case_id);
            wp_mail($to, $subject, $message);
        }
        
        // Send Email to user making claim with Case Number.
        $to = $owner->user_email;
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Dispute Counter #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                        . '<p>%2$s will be notified by email that a claim of the stated that the photo/video found on %3$s is copyrighted.</p>'
                        //. '<p>%2$s will have an opportunity to counter-claim if he/she feels that this claim is in error.</p>'
                        . '<p>An email will be sent to you for you record.</p>'
                        . '<br>'
                        . '<p style="color:red"><i>Case Number to Copyright Dispute Counter: </i>%4$s</p>'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $owner_name, $agent_name, $this->reported_content_url, $this->case_id);
        wp_mail($to, $subject, $message);
        
        // Send Email to user making counter-claim with Case Number.
        $to = $agent->user_email;
        $counter_claim_url = home_url('copyright-complaint/?action=counter_claim');

        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Dispute Counter #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                        . '<p>After reviewing your counter-claim, %2$s has decided that their copyright claim is still vaild.</p>'
                        . '<p>%2$s has dispute-counter and stated that the photo/video.</p>'
                        . '<p>The reason is as follows</p>'
                        . '%3$s'
                        . '<br>'
//                        . '<p>Please provide additional information to dispute this dispute-counter within 10 days or the photo or video found on %4$s or it will be deleted in 10-14 days.</p>'
//                        . '<p>you can file a counter claim at %5$s, and it will be sent to copyright@dremboard.com.</p>'
//                        . '<br>'
                        . '<p style="color:red"><i>Case Number to Copyright Dispute Counter: </i>%6$s</p>'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $agent_name, $owner_name, $this->dispute_counter_reason, $this->reported_content_url, $counter_claim_url, $this->case_id);
        wp_mail($to, $subject, $message);
        return true;
    }
    
    public function dmca_step5(){// deleted; to do send mail, panelty to user.
        if ( empty( $this->case_id ) )
            return false;
        
        $to = '';
        $subject = '';
        $message = '';
        
        $owner_id = $this->owner_id;
        $owner = bp_core_get_core_userdata($owner_id);
        $owner_name = $owner->display_name;
        $agent_id = $this->agent_id;
        $agent = bp_core_get_core_userdata($agent_id);
        $agent_name = $agent->display_name;
        
        // Send Email to Dremboard with case Number.
        $to = get_option( 'bp-copyright-mail-address', '' );
        
        if ($this->remove_all == 1)
            $remove_all_str = "All Drēms";
        else
            $remove_all_str = "Only this specific Drēm";
        
        $strike_str = "";
        if ($this->strike == 1)
            $strike_str = "<p>".$agent_name."'s account will be penalized. </p>";
        
        if (!empty($to)) {
            $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant is finished #Case: %d', 'buddypress'), $this->case_id)));
            $message = sprintf(__('<p>In accordance with the Digital Millennium Copyright Act, we\'ve completed processing %1$s\'s copyright complicant regarding these photo/video(s):</p>'
                            . '<li>%2$s</li>'
                            . '<p>%3$s has been removed.</p> '
                            . '%4$s', 'buddypress')
                    , $owner_name, $this->reported_content_url, $remove_all_str, $strike_str);
            wp_mail($to, $subject, $message);
        }
        // Send Email to user making claim with Case Number.
        $to = $owner->user_email;
                
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant finished #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                        . '<p>In accordance with the Digital Millennium Copyright Act, we\'ve completed processing your copyright complicant regarding these photo/video(s):</p>'
                        . '<li>%2$s</li>'
                        . '<p>%3$s has been removed.</p> '
                        . '%4$s'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $owner_name, $this->reported_content_url, $remove_all_str, $strike_str);
        wp_mail($to, $subject, $message);
        // Send Email to user/Infringer who posted up picture/video with Case Number.
        
        $to = $agent->user_email;

        $strike_str = "";
        if ($this->strike == 1)
            $strike_str = "<p>your account will be penalized. </p>";
        
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant finished #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %5$s,</p>'
                        . '<p>In accordance with the Digital Millennium Copyright Act, we\'ve completed processing %1$s\'s copyright complicant regarding these photo/video(s):</p>'
                        . '<li>%2$s</li>'
                        . '<p>%3$s has been removed.</p> '
                        . '%4$s'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $owner_name, $this->reported_content_url, $remove_all_str, $strike_str, $agent_name);

        wp_mail($to, $subject, $message);
        return true;
    }
    
    public function dmca_step6(){// resotred; to do send mail.
        if ( empty( $this->case_id ) )
            return false;
        
        $to = '';
        $subject = '';
        $message = '';
        
        $owner_id = $this->owner_id;
        $owner = bp_core_get_core_userdata($owner_id);
        $owner_name = $owner->display_name;
        $agent_id = $this->agent_id;
        $agent = bp_core_get_core_userdata($agent_id);
        $agent_name = $agent->display_name;
        
        // Send Email to Dremboard with case Number.
        $to = get_option( 'bp-copyright-mail-address', '' );
        
        if ($this->remove_all == 1)
            $remove_all_str = "All Drēms";
        else
            $remove_all_str = "Only this specific Drēm";
        
        $strike_str = "";
        if ($this->strike == 1)
            $strike_str = "<p>".$agent_name."'s account will not be penalized. </p>";
        
        if (!empty($to)) {
            $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant is finished #Case: %d', 'buddypress'), $this->case_id)));
            $message = sprintf(__('<p>In accordance with the Digital Millennium Copyright Act, we\'ve completed processing %1$s\'s copyright complicant regarding these photo/video(s):</p>'
                            . '<li>%2$s</li>'
                            . '<p>%3$s has been restored unless %5$s have deleted the photo/video(s).</p> '
                            . '%4$s', 'buddypress')
                    , $owner_name, $this->reported_content_url, $remove_all_str, $strike_str, $agent_name);
            wp_mail($to, $subject, $message);
        }
        // Send Email to user making claim with Case Number.
        $to = $owner->user_email;
                
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant finished #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %1$s,</p>'
                        . '<p>In accordance with the Digital Millennium Copyright Act, we\'ve completed processing your copyright complicant regarding these photo/video(s):</p>'
                        . '<li>%2$s</li>'
                        . '<p>%3$s has been restored unless unless %5$s have deleted the photo/video(s).</p> '
                        . '%4$s'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $owner_name, $this->reported_content_url, $remove_all_str, $strike_str, $agent_name);
        wp_mail($to, $subject, $message);
        // Send Email to user/Infringer who posted up picture/video with Case Number.
        
        $to = $agent->user_email;

        $strike_str = "";
        if ($this->strike == 1)
            $strike_str = "<p>your account will not be penalized. </p>";
        
        $subject = bp_get_email_subject(array('text' => sprintf(__('Copyright Complicant finished #Case: %d', 'buddypress'), $this->case_id)));
        $message = sprintf(__('<p>Dear %5$s,</p>'
                        . '<p>In accordance with the Digital Millennium Copyright Act, we\'ve completed processing %1$s\'s copyright complicant regarding these photo/video(s):</p>'
                        . '<li>%2$s</li>'
                        . '<p>%3$s has been restored unless you have deleted the photo/video(s).</p> '
                        . '%4$s'
                        . '<br>'
                        . '<p>Sincerely,</p>'
                        . '<p>Drēmboard Copyright Team</p>', 'buddypress')
                , $owner_name, $this->reported_content_url, $remove_all_str, $strike_str, $agent_name);

        wp_mail($to, $subject, $message);
        return true;
    }
    
    public function delete_activity_as_del_flag() {
        if (empty($this->case_id))
            return false;
        
        global $bp, $wpdb;
        
        $activity_id = $this->activity_id;
        
        if($this->remove_all == '1')
            $remove_all = true;
        //$this->activity_action($activity_id, $del_flag='1', $remove_all);
        $del_flag = '1';
        $this->activity_action($activity_id, $del_flag, $remove_all);
    }
    
    public function activity_action($activity_id, $del_flag=false, $remove_all = false){
        // del_flag == false is delete perfactly.
        // del_flag == '1' is delete activity as flag
        // del_flag == '0' restore activity that is deleted as flag.
        global $bp, $wpdb;
        
        if($del_flag == "0" || $del_flag == "1" ){
            $wpdb->query("UPDATE {$bp->activity->table_name} SET del_flag=".$del_flag." where id = " . $activity_id);
        }else{
            $wpdb->query( "DELETE FROM {$bp->activity->table_name} where id = " . $activity_id);
        }

        $rt_model = new RTMediaModel();
        $sql = "SELECT wp_rt_rtm_media.*  FROM wp_rt_rtm_media  where wp_rt_rtm_media.activity_id = ".$activity_id."  ORDER BY wp_rt_rtm_media.media_id desc";
        $all_media = $wpdb->get_results ( $sql );
        $activity_ids = array();
        
        foreach ($all_media as $single_media) {
            if($del_flag == "0" || $del_flag == "1" ){
                $wpdb->update($rt_model->table_name, array('del_flag' => $del_flag), array('id' => $single_media->id), array('%d'), array('%d'));
            }else{
                $wpdb->delete($rt_model->table_name, array('id' => $single_media->id));
            }

            if($remove_all){
                $sql = "SELECT activity_id FROM wp_rt_rtm_media w where album_id=".$single_media->album_id." and (Select count(*) from wp_rt_rtm_media where id=".$single_media->album_id." and media_type='album') > 0 and id != ".$single_media->id." and activity_id != 0 and activity_id is not null";
                $aditional_activities = $wpdb->get_results ( $sql );
                $activity_ids = array_merge( $activity_ids, $aditional_activities );
            }
        }
        if($remove_all && !empty($activity_ids)){
            foreach ($activity_ids as $item){
                $this->activity_action($item->activity_id, $del_flag);
            }
        }
    }

    public function delete_activity_realy(){
        if (empty($this->case_id))
            return false;
        
        global $bp, $wpdb;
        
        $activity_id = $this->activity_id;
        
        if($this->remove_all == 1)
            $remove_all = true;
        
        //$this->activity_action($activity_id, $del_flag=false, $remove_all);
        $del_flag = false;
        $this->activity_action($activity_id, $del_flag, $remove_all);
    }
    
    public function restore_activity(){
        if ( empty( $this->case_id ) )
            return false;
        
        global $bp, $wpdb;
        
        $activity_id = $this->activity_id;
        
        if($this->remove_all == 1)
            $remove_all = true;

        //$this->activity_action($activity_id, $del_flag='0', $remove_all);
        $del_flag = '0';
        $this->activity_action($activity_id, $del_flag, $remove_all);
    }
    
    public function panelty_agent(){
        if ( empty( $this->case_id ) )
            return false;
        
        $current_copyright_panelty = bp_get_user_meta( $user_id, 'bp_copyright_panelty', true );
        
        if ( !$current_copyright_panelty ) {
            $current_copyright_panelty = array();
        }
        
        $current_copyright_panelty[$this->case_id] = $this->owner_id;

        bp_update_user_meta( $user_id, 'bp_copyright_panelty', $current_copyright_panelty );
    }
}