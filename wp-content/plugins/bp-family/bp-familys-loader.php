<?php
/**
 * BuddyPress familys Streams Loader
 *
 * The familys component is for users to create relationships with each other.
 *
 * @package BuddyPress
 * @subpackage familys
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class BP_familys_Component extends BP_Component {

	/**
	 * Start the familys component creation process.
	 *
	 * @since BuddyPress (1.5.0)
	 */
	function __construct() {
		global $bp;
		parent::start(
			'familys',
			__( 'family Connections', 'buddypress' ),
			BP_PLUGIN_DIR,
			array(
				'adminbar_myaccount_order' => 60
			)
		);
		
		$this->includes();
				// register our component as an active component in BP
		$bp->active_components[$this->id] = '1';
	}

	/**
	 * Include bp-familys files.
	 *
	 * @see BP_Component::includes() for description of parameters.
	 *
	 * @param array $includes See {@link BP_Component::includes()}.
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'actions',
			'screens',
			'filters',
			'classes',
			'activity',
			'template',
			'functions',
			'notifications',
			'widgets',
		);
//var_dump("1009");
		parent::includes( $includes );
	}

	/**
	 * Set up bp-familys global settings.
	 *
	 * The BP_familys_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since BuddyPress (1.5.0)
	 *
	 * @see BP_Component::setup_globals() for description of parameters.
	 *
	 * @param array $args See {@link BP_Component::setup_globals()}.
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Deprecated. Do not use.
		// Defined conditionally to support unit tests.
		if ( ! defined( 'BP_familys_DB_VERSION' ) ) {
			define( 'BP_familys_DB_VERSION', '1800' );
		}

		// Define a slug, if necessary
		if ( !defined( 'BP_familys_SLUG' ) )
			define( 'BP_familys_SLUG', $this->id );

		// Global tables for the familys component
		$global_tables = array(
			'table_name'      => $bp->table_prefix . 'bp_familys',
			'table_name_meta' => $bp->table_prefix . 'bp_familys_meta',
		);

		// All globals for the familys component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'                  => BP_familys_SLUG,
			'has_directory'         => false,
			'search_string'         => __( 'Search familys...', 'buddypress' ),
			'notification_callback' => 'familys_format_notifications',
			'global_tables'         => $global_tables
		);

		parent::setup_globals( $args );
	}

	/**
	 * Set up component navigation.
	 *
	 * @since BuddyPress (1.5.0)
	 *
	 * @see BP_Component::setup_nav() for a description of arguments.
	 *
	 * @param array $main_nav Optional. See BP_Component::setup_nav() for
	 *        description.
	 * @param array $sub_nav Optional. See BP_Component::setup_nav() for
	 *        description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		$bp = buddypress();
//var_dump("1003");
		// Add 'familys' to the main navigation
		$main_nav = array(
			'name'                => sprintf( __( 'family <span>%d</span>', 'buddypress' ), familys_get_total_family_count() ),
			'slug'                => $this->slug,
			'position'            => 60,
			'screen_function'     => 'familys_screen_my_familys',
			'default_subnav_slug' => 'my-familys',
			'item_css_id'         => $bp->familys->id
		);

		// Determine user to use
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		$familys_link = trailingslashit( $user_domain . bp_get_familys_slug() );

		// Add the subnav items to the familys nav item
		$sub_nav[] = array(
			'name'            => __( 'family', 'buddypress' ),
			'slug'            => 'my-familys',
			'parent_url'      => $familys_link,
			'parent_slug'     => bp_get_familys_slug(),
			'screen_function' => 'familys_screen_my_familys',
			'position'        => 10,
			'item_css_id'     => 'familys-my-familys'
		);

		$sub_nav[] = array(
			'name'            => __( 'Requests',   'buddypress' ),
			'slug'            => 'requests',
			'parent_url'      => $familys_link,
			'parent_slug'     => bp_get_familys_slug(),
			'screen_function' => 'familys_screen_requests',
			'position'        => 20,
			'user_has_access' => bp_core_can_edit_settings()
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up bp-familys integration with the WordPress admin bar.
	 *
	 * @since BuddyPress (1.5.0)
	 *
	 * @see BP_Component::setup_admin_bar() for a description of arguments.
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar()
	 *        for description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		$bp = buddypress();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain  = bp_loggedin_user_domain();
			$familys_link = trailingslashit( $user_domain . $this->slug );

			// Pending family requests
			$count = count( familys_get_familyship_request_user_ids( bp_loggedin_user_id() ) );
			if ( !empty( $count ) ) {
				$title   = sprintf( __( 'familys <span class="count">%s</span>',          'buddypress' ), number_format_i18n( $count ) );
				$pending = sprintf( __( 'Pending Requests <span class="count">%s</span>', 'buddypress' ), number_format_i18n( $count ) );
			} else {
				$title   = __( 'Family',             'buddypress' );
				$pending = __( 'No Pending Requests', 'buddypress' );
			}

			// Add the "My Account" sub menus
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => trailingslashit( $familys_link )
			);

			// My Groups
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-familyships',
				'title'  => __( 'familyships', 'buddypress' ),
				'href'   => trailingslashit( $familys_link )
			);

			// Requests
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-requests',
				'title'  => $pending,
				'href'   => trailingslashit( $familys_link . 'requests' )
			);
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Set up the title for pages and <title>.
	 */
	function setup_title() {
		$bp = buddypress();

		// Adjust title
		if ( bp_is_familys_component() ) {
			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( 'familyships', 'buddypress' );
			} else {
				$bp->bp_options_avatar = bp_core_fetch_avatar( array(
					'item_id' => bp_displayed_user_id(),
					'type'    => 'thumb',
					'alt'     => sprintf( __( 'Profile picture of %s', 'buddypress' ), bp_get_displayed_user_fullname() )
				) );
				$bp->bp_options_title = bp_get_displayed_user_fullname();
			}
		}

		parent::setup_title();
	}
}

function bp_is_familys_component() {
	if ( bp_is_current_component( 'familys' ) )
		return true;

	return false;
}

/**
 * Set up the bp-forums component.
 */
function bp_setup_familys() {
	//var_dump("1004");
	global $bp;

	$bp->familys = new BP_familys_Component;	
	//!!! buddypress()->familys = new BP_familys_Component();
	//var_dump("1005");
}
//var_dump("1002");
//add_action( 'bp_setup_components', 'bp_setup_familys', 6 );
//var_dump(BP_family_DIR);

require_once( BP_family_DIR . '/bp-familys-functions.php' );
require_once( BP_family_DIR . '/bp-familys-actions.php' );
require_once( BP_family_DIR . '/bp-familys-screens.php' );
require_once( BP_family_DIR . '/bp-familys-filters.php' );
require_once( BP_family_DIR . '/bp-familys-classes.php' );
require_once( BP_family_DIR . '/bp-familys-activity.php' );
require_once( BP_family_DIR . '/bp-familys-template.php' );
require_once( BP_family_DIR . '/bp-familys-notifications.php' );
require_once( BP_family_DIR . '/bp-familys-widgets.php' );

add_action( 'bp_loaded', 'bp_setup_familys' );

