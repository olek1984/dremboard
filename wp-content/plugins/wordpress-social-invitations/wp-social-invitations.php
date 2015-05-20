<?php
/*
Plugin Name: WordPress Social Invitations
Plugin URI: http://www.timersys.com/plugins-wordpress/wordpress-social-invitations/
Description: Allow your visitors to invite friends of their social networks such as Twitter, Facebook, Linkedin, Google, Yahoo, Hotmail and more.
Version: 2.4
Author: timersys
Author URI: http://www.timersys.com
License: MIT License
Text Domain: wsi
Domain Path: languages
*/

/*

****************************************************************************
* License http://codecanyon.net/licenses/regular
****************************************************************************
*/
//token we use in url to fire cron
global $blog_id;
define('WSI_CRON_TOKEN',md5(__FILE__.$blog_id));

@ session_start();
$_SESSION["wsi::plugin"] = "WordPress Social Invitations ";
@ session_write_close();

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//translations
if ( function_exists ('load_plugin_textdomain') ){
	load_plugin_textdomain ( 'wsi', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}


require_once(dirname (__FILE__).'/WP_Plugin_Base.class.php');
require_once(dirname (__FILE__).'/classes/class.Wsi_Widget.php');
require_once(dirname (__FILE__).'/functions/template-functions.php');

// Load our custom updater
if( ! class_exists( 'WSI_License' ) ) {
	require_once(dirname (__FILE__).'/classes/WSI_License_Handler.php' );
}
  
class WP_Social_Invitations extends Wsi_Plugin_Base
{

	
	var $_options;
	var $_credits;
	var $_defaults;
	var $assets_url;
	private $hybridauth;
	protected $sections;
	protected $providers;
	 /** Refers to a single instance of this class. */
    private static $instance = null;
    private static $PREFIX;
	private static $_profile; 
	private static $_current_url;
	private static $_obj_id;
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Creates or returns an instance of this class.
     *
     * @return  Foo A single instance of this class.
     */
    public static function get_instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
 
    } // end get_instance;
    
	function __construct() {
		
		$this->WPB_PREFIX		=	'wsi';
		self::$PREFIX			=	'wsi';
		$this->WPB_SLUG			=	'wordpress-social-invitations'; // Need to match plugin folder name
		$this->WPB_PLUGIN_NAME	=	'Wordpress Social Invitations';
		$this->WPB_VERSION		=	'2.4';
		$this->PLUGIN_FILE		=   plugin_basename(__FILE__);
		$this->options_name		=   $this->WPB_PREFIX.'_settings';
		$this->CLASSES_DIR		=	dirname( __FILE__ ) . '/classes';
		$this->WPB_PLUGIN_URL	=	plugins_url('', __FILE__ );// for domain mapping
		
		$this->providers 		= 	array('facebook' 	=> __('Facebook','wsi'),
										  'google' 		=> __('Gmail','wsi'),
										  'yahoo'		=> __('Yahoo Mail','wsi'),
										  'linkedin'	=> __('LinkedIn','wsi'),
										  'live'		=> __('Live, Hotmail','wsi'),
										  'twitter'		=> __('Twitter','wsi'),
										  'foursquare'	=> __('Foursquare','wsi'),
										  'mail'	    => __('Emails','wsi')
									); 
		
		$this->sections['wsi_general']      		= __( 'Main Settings', $this->WPB_PREFIX );
		$this->sections['wsi_messages']        		= __( 'Default Messages', $this->WPB_PREFIX );
		$this->sections['wsi_emails']        		= __( 'Emails', $this->WPB_PREFIX );
		$this->sections['wsi_styling']        		= __( 'Styling', $this->WPB_PREFIX );
		$this->sections['wsi_stats']        		= __( 'Stats', $this->WPB_PREFIX );
		$this->sections['wsi_debug']        		= __( 'Debug', $this->WPB_PREFIX );
		
		//Filter cron schedules && cron
		add_filter( 'cron_schedules', array( &$this, 'filter_cron_schedules' ) );
		
		//activation hook
		register_activation_hook( __FILE__, array(&$this,'activate' ));        
		
		//deactivation hook
		register_deactivation_hook( __FILE__, array(&$this,'deactivate' ));   
		
		//admin menu
		add_action( 'admin_menu',array(&$this,'register_menu' ) );
		
		//load js and css 
		add_action( 'init',array(&$this,'load_back_scripts' ),50 );	
		add_action( 'wp_enqueue_scripts',array(&$this,'load_front_scripts' ));
		
		#$this->upgradePlugin();
			
		$this->setDefaults();
		
		$this->loadOptions();
		
		//Where to hook the widget
		add_action('init',array($this, 'hookWidgetChecks'),9999);
		
		//Ajax hooks here	
		add_action('wp_ajax_add_to_wsi_queue', array(&$this,'add_to_wsi_queue_callback'));
		add_action('wp_ajax_nopriv_add_to_wsi_queue', array(&$this,'add_to_wsi_queue_callback'));
		add_action('wp_ajax_get_fb_registration_url', array(&$this,'get_fb_registration_url_callback'));
		add_action('wp_ajax_nopriv_get_fb_registration_url', array(&$this,'get_fb_registration_url_callback'));

		if( !defined('WSI_SERVER_CRON'))
		add_action( 'wsi_queue_cron', array( &$this, 'run_cron' ) );
		
		add_action('init', array( &$this, 'server_cron' ));
		
		add_action('wp_ajax_wsi_order', array(&$this,'change_widget_order'));
		
		//Info boxes
		add_action('wsi_general_wpb_print_box' ,array(&$this,'print_general_box'));
		add_action('wsi_messages_wpb_print_box' ,array(&$this,'print_messages_box'));
		add_action('wsi_emails_wpb_print_box' ,array(&$this,'print_emails_box'));
		add_action('wsi_styling_wpb_print_box' ,array(&$this,'print_styling_box'));
		add_action('wsi_stats_wpb_print_box' ,array(&$this,'print_stats_box'));
		add_action('wsi_debug_wpb_print_box' ,array(&$this,'print_debug_box'));
		
		//hook to proccess auth if detected
		add_action( 'init', array(&$this, 'process_auth' ));
		//hook to handle accepted invitations
		add_action( 'register_form', array(&$this, 'process_invitations' ));
		add_action( 'bp_before_register_page', array(&$this, 'bp_process_invitations' ));
		//hook that check if invited user just arrived to site
		add_action( 'init', array(&$this, 'check_new_invited_user' ),10, 1);
		add_action( 'bp_init', array(&$this, 'check_new_invited_user' ),10, 1);
		//hook to check if an invited user just registered
		add_action( 'user_register', array(&$this, 'check_new_registered_user' ),10, 1);
		
		//Activate sidebar Widget
		add_action( 'widgets_init', array(&$this, 'register_widget'));
		
		//Bypass registration lock if enabled
		add_action( 'wp',  array(&$this, 'bypass_registration_lock'), 1 );
		
		// Mycreed Hook
		add_filter( 'mycred_setup_hooks', array(&$this, 'mycreed') );
		
		//Add menus and screens for buddypress
		add_action( 'bp_include', array(&$this, 'bp_includes') );
		
		// Get license key from DB
		$wsi_license_key = isset( $this->_options['wsi_license_key'] ) ? trim( $this->_options['wsi_license_key'] ) : '';

		$eddc_license = new WSI_License( __FILE__, $this->WPB_PLUGIN_NAME, $this->WPB_VERSION	, 'Damian Logghe', $wsi_license_key );
		
		parent::__construct();
		
		$this->WSI_HYBRIDAUTH_ENDPOINT_URL = $this->WPB_PLUGIN_URL. '/hybridauth/';
		$this->assets_url  		= $this->WPB_PLUGIN_URL . '/assets/img/';
		
		if (version_compare($this->WPB_VERSION, '1.4', '=') && !isset($_GET['wsi-dismiss'])) {
			add_action( 'admin_notices', array(&$this, 'admin_notice') );
		}
		if (version_compare($this->WPB_VERSION, '1.4.0.4', '=') && !isset($_GET['wsi-dismiss'])) {
			add_action( 'admin_notices', array(&$this, 'admin_notice') );
		}
		elseif( isset($_GET['wsi-dismiss']))
		{
			update_option('wsi_dismiss','yes');
		}
		
		//check if cron was added
		if ( ! wp_next_scheduled( 'wsi_queue_cron' ) ) {
			wp_schedule_event( time(), 'wsi_one_min', 'wsi_queue_cron' );
		}
		//check for fb share and that is not facebook
		add_action('init', array(&$this, 'facebook_check') );
	}	
	
	/**
	 *
	 * Admin notices after new version
	 */	
	function admin_notice(){
	
		if ( !get_option('wsi_dismiss') && !get_option('wsi_dismiss2'))
		{
		?>
	    <div class="error">
	    	<h3>Wordpress Social Invitations</h3>
	        <p><?php _e(sprintf('Please go to the <a href="%s">settings page</a> and check the new settings and default messages. | <a href="%s">Hide Notice</a>', '?page=wordpress-social-invitations','?wsi-dismiss=yes'),$this->WPB_PREFIX);?></p>
	    </div>
	    <?php
		}
	} 
	/**
	* Check technical requirements before activating the plugin. 
	* Wordpress 3.0 or newer required
	*/
	function activate()
	{
		parent::activate();
		
		$providers_order = get_option('wsi_widget_order',true);
		
		if( is_array($providers_order))
		{
			$missing_provider = array_diff($this->providers , $providers_order);
			
			if( !empty($missing_provider))
			{
				$providers_order = $providers_order + $missing_provider;
				update_option( 'wsi_widget_order' , $providers_order);
			}
		}
		global $wpdb;
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."wsi_stats` (
				  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Index ID',
				  `provider` varchar(32) NOT NULL COMMENT 'Provider Name',
				  `user_id` INT NULL COMMENT 'User''s ID',
				  `quantity` INT NULL COMMENT 'Quantity of friends invited',
				  `queue_id` INT NULL COMMENT 'original id from queue',
				  `i_datetime` datetime NOT NULL,
				  `display_name` varchar(120) COMMENT 'Display name in provider',
			  PRIMARY KEY (`id`),
			  KEY (`provider`),
			  INDEX (`i_datetime`, `user_id`, `queue_id`)
			) ENGINE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
		");

		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."wsi_queue` (
				  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Index ID',
				  `provider` varchar(32) NOT NULL COMMENT 'Provider Name',
				  `user_id` INT NULL COMMENT 'User''s ID',
				  `sdata` text COMMENT 'hybrid session data',
				  `friends` text COMMENT 'serialized array of emails or facebook ids etc',
				  `i_count` INT NULL COMMENT 'Quantity of Invitations',
				  `date_added` datetime NOT NULL,
			          `display_name` varchar(120) COMMENT 'Display name in provider',
				  `subject` text COMMENT 'message subject',
				  `message` text COMMENT 'message',
				  `send_at` int(10) COMMENT 'When to send invitation',
			  PRIMARY KEY (`id`),
			  KEY (`provider`),
			  INDEX (`date_added`, `user_id`)
			) ENGINE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
		");
		
		wp_schedule_event( time(), 'wsi_one_min', 'wsi_queue_cron' );
		
		if( ! get_option('wsi-version') || version_compare( get_option('wsi-version'), '1.4', '<' ))
		{
			$wpdb->query("ALTER TABLE `".$wpdb->base_prefix."wsi_stats`  ADD COLUMN display_name varchar(120), ADD COLUMN queue_id INT(11), DROP COLUMN data");
			update_option('wsi-version', $this->WPB_VERSION); 
		}
		if( ! get_option('wsi-version') || version_compare( get_option('wsi-version'), '2.3.4.1', '<' ))
		{
			$wpdb->query("ALTER TABLE `".$wpdb->base_prefix."wsi_queue`  ADD COLUMN wsi_obj_id int(10)");
			$wpdb->query("ALTER TABLE `".$wpdb->base_prefix."wsi_stats`  ADD COLUMN wsi_obj_id int(10)");
			update_option('wsi-version', $this->WPB_VERSION); 
		}
		do_action( $this->WPB_PREFIX.'_activate' );
		
		
	}	

	/**
	* Run when plugin is deactivated
	* Wordpress 3.0 or newer required
	*/
	function deactivate()
	{
		
	#	global $wpdb;
	#	$wpdb->query("DROP TABLE  `".$wpdb->base_prefix."wsm_monitor_index`");
	
		wp_clear_scheduled_hook( 'wsi_queue_cron' );
		do_action( $this->WPB_PREFIX.'_deactivate' );
	}
	


	/**
	* function that register the menu link in the settings menu	and editor section inside the option page
	*/
	 function register_menu()
	{
		add_menu_page( 'WP Social Invitations', 'WP Social Invitations', 'manage_options', $this->WPB_SLUG ,array(&$this, 'display_page') );
		
	}
	
	/**
	* Register the sidebar widget
	*/
	function register_widget() { 
	
		register_widget( 'Wsi_Widget' ); 
		
	}

	/**
	* Load scripts for backend
	*/
	function load_back_scripts()
	{
		if( is_admin()){
			wp_enqueue_style('wsi-back-css', plugins_url( 'admin/assets/style.css', __FILE__ ) ,'',$this->WPB_VERSION, 'all' );
			wp_enqueue_script('codemirror');
		}
	}
	
	/**
	* Load frontend scripts
	*/
	function load_front_scripts(){
			wp_enqueue_style('wsi-css', plugins_url( 'assets/css/style.css', __FILE__ ) ,'',$this->WPB_VERSION,'all' );
			
	}
	
	
	/**
	* Function to load the javscript for big widget
	*/
	public function load_wsi_js(){
	
			wp_enqueue_script('wsi-js', plugins_url( 'assets/js/wsi.js', __FILE__ ), array('jquery'),$this->WPB_VERSION,true);
			$token = uniqid(mt_rand(), TRUE);
			@ session_start();
			$_SESSION['wsi-token'] = $token;
			@ session_write_close();
			
			wp_localize_script( 'wsi-js', 'WsiMyAjax', array( 
				'url' 				=> site_url( 'wp-login.php' ),
				'site_url' 			=> site_url( ),
				'admin_url'			=> admin_url( 'admin-ajax.php' ), 
				'nonce' 			=> wp_create_nonce( 'wsi-ajax-nonce' ),
				'locale' 			=> get_bloginfo('language'), 
				'appId' 			=> $this->_options['facebook_key'],
			 	'fburl' 			=> $this->_options['facebook_share_url'],
			 	'fbCustomurl'		=> apply_filters('wsi_placeholder_custom_url'	, ''),
			 	'redirect_url'		=> $this->_options['redirect_url'],
			 	'wsi_token'			=> 'wsi-token='.$token,
			) );
	}
	/**
	* Function to load the javscript for invite anyone plugin
	*/
	function load_wsi_anyone_js(){
	
			wp_enqueue_script('wsi-js', plugins_url( 'assets/js/wsi.js', __FILE__ ), array('jquery'),$this->WPB_VERSION,true);
			wp_enqueue_script('wsi-anyone-js', plugins_url( 'assets/js/wsi-invite-anyone.js', __FILE__ ), array('jquery','wsi-js'),$this->WPB_VERSION,true);
			wp_localize_script( 'wsi-anyone-js', 'WsiMyAjax', array( 'url' => site_url( 'wp-login.php' ),'admin_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'wsi-ajax-nonce' ) ) );
	}
	
	
	/**
	* Load options to use later
	*/	
	function loadOptions()
	{

		$this->_options = get_option($this->WPB_PREFIX.'_settings',$this->_defaults);

	}
	
	/**
	* Get options to use later
	*/	
	function getOptions()
	{

		return $this->_options;

	}
			
	/**
	* loads plugins defaults
	*/
	function setDefaults()
	{
		$this->_defaults = array( 
			'version' 				=> $this->WPB_VERSION, 
			'hook_buddypress' 		=> 'true', 
			'hook_invite_anyone' 	=> 'true',
			'fb_title'				=> '%%INVITERNAME%% would like you to join %%SITENAME%%', //added in v2.3.1
			'fb_message'			=> 'Please accept the invitation!', //added in v2.3.1
			);		
	}
	
	/**
	* Print general section Box
	*/
	function print_general_box(){
	
	?>

		<div class="info-box">
		<h2><?php printf(__('Welcome to <a href="%s">Wordpress Social Invitations</a>!',$this->WPB_PREFIX),'http://wp.timersys.com/wordpress-social-invitations/');?></h2>
		<p><?php printf(__('To start using the plugin you need to fill out the OAuth settings of the following providers. If you need help, please read the <a href="%s" target="_blank">documentation</a> or go to the <a href="%s" target="_blank">support forum</a>.',$this->WPB_PREFIX),'http://wp.timersys.com/wordpress-social-invitations/docs/','http://support.timersys.com/');?></p>
		<p><?php _e('You can place the invitation widget on any page by using the following shortcode:',$this->WPB_PREFIX);?></p>
		<code>[wsi-widget title="Invite your friends"]</code>
		<p>Or you can place it in your templates with:</p>
		<code>WP_Social_Invitations::widget('Some title');</code>
		
		<p><?php echo sprintf(__('If you have any question please carefully <a href="%s">read documentation</a> before opening a ticket',$this->WPB_PREFIX), 'http://wp.timersys.com/wordpress-social-invitations/docs/configuration/');?></p>
				
		</div><?php
	}

	/**
	* Print STATS
	*/
	function print_stats_box(){
		global $wpdb;
		
		$total_invites = $wpdb->get_var("SELECT SUM(quantity) as c FROM {$wpdb->prefix}wsi_stats");
	?>

		<div class="info-box">
		<h2><?php printf(__('So far %s invitations were sent using WSI!',$this->WPB_PREFIX),$total_invites);?></h2>
		<br>
		<table class="widefat ia-stats" style="min-width:1000px">
			<thead><tr>
				<th scope="col" class="in-the-last"><?php _e('In the last...',$this->WPB_PREFIX);?></th>
				<?php
				#$providers = $wpdb->get_results("SELECT DISTINCT provider FROM {$wpdb->prefix}wsi_stats  ORDER BY provider ASC");
				$providers = $this->providers;
				foreach( $providers as $p )
				{
					echo '<th scope="col">'.ucfirst($p).'</th>';
				}
					echo '<th scope="col">Total</th>';
				?>
			</tr></thead>
			<tbody>
				<tr>
					<th scope="row"><?php _e('24 Hours',$this->WPB_PREFIX);?></th>
					<?php
						foreach( $this->providers as $p => $p_name)
						{
							
							$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE provider = '{$p}' AND i_datetime >= ( NOW( ) - INTERVAL 1 DAY ) ");
							echo '<td>'.$stat.'</td>';
						}
						$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE i_datetime >= ( NOW( ) - INTERVAL 1 DAY ) ");
						echo '<td>'.$stat.'</td>';
						
					?>	
				</tr>
				<tr>
					<th scope="row"><?php _e('3 Days',$this->WPB_PREFIX);?></th>
					<?php
						foreach( $this->providers as $p => $p_name)
						{
							$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE provider = '{$p}' AND i_datetime >= ( NOW( ) - INTERVAL 3 DAY ) ");
							echo '<td>'.$stat.'</td>';
						}
						$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE i_datetime >= ( NOW( ) - INTERVAL 3 DAY ) ");
						echo '<td>'.$stat.'</td>';
					?>	

				</tr>
				<tr>
					<th scope="row"><?php _e('1 Week',$this->WPB_PREFIX);?></th>
					<?php
						foreach( $this->providers as $p => $p_name)
						{
							$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE provider = '{$p}' AND i_datetime >= ( NOW( ) - INTERVAL 7 DAY ) ");
							echo '<td>'.$stat.'</td>';
						}
						$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE i_datetime >= ( NOW( ) - INTERVAL 7 DAY ) ");
						echo '<td>'.$stat.'</td>';
					?>	
	
				</tr>
				<tr>
					<th scope="row"><?php _e('1 Month',$this->WPB_PREFIX);?></th>
					<?php
						foreach( $this->providers as $p => $p_name)
						{
							$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE provider = '{$p}' AND i_datetime >= ( NOW( ) - INTERVAL 1 MONTH ) ");
							echo '<td>'.$stat.'</td>';
						}
						$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE i_datetime >= ( NOW( ) - INTERVAL 1 MONTH ) ");
						echo '<td>'.$stat.'</td>';
					?>		
				</tr>
				<tr>
					<th scope="row"><?php _e('3 Months',$this->WPB_PREFIX);?></th>
					<?php
						foreach( $this->providers as $p => $p_name)
						{
							$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE provider = '{$p}' AND i_datetime >= ( NOW( ) - INTERVAL 3 MONTH ) ");
							echo '<td>'.$stat.'</td>';
						}
						$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE i_datetime >= ( NOW( ) - INTERVAL 3 MONTH ) ");
						echo '<td>'.$stat.'</td>';
					?>		
				</tr>
				<tr>
					<th scope="row"><?php _e('All Time',$this->WPB_PREFIX);?></th>
					<?php
						foreach( $this->providers as $p => $p_name)
						{
							$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats WHERE provider = '{$p}' ");
							echo '<td>'.$stat.'</td>';
						}
						$stat = $wpdb->get_var("SELECT IFNULL(SUM(quantity),0) as c FROM {$wpdb->prefix}wsi_stats");
						echo '<td>'.$stat.'</td>';
					?>		
				</tr>
				
			</tbody>
		</table>
				
		</div><?php
	}

	/**
	* Print Default messages Box
	*/
	function print_messages_box(){
		global $bp;
	?>
		<div class="info-box">
		<p><?php _e('By default your users will be able to edit the default invitation message. Here you will be able to change the default message and forbid users to change it.',$this->WPB_PREFIX);?></p>
		<p><?php _e('Default messages are divided in several sections. Message for HTML providers, message for non HTML providers, message for twitter, non enditable section and footer.',$this->WPB_PREFIX);?></p>
		<?php if(!get_option('users_can_register') && empty($bp)) :?>
			<div class="error">
				<?php _e('Registration is not allowed. Go to settings -> General to enable it or %%ACCEPTURL%% won\'t work.',$this->WPB_PREFIX); ?>
			</div>
		<?php endif;?>	
		<p><?php _e('You can use the following placeholders on your message:',$this->WPB_PREFIX);?></p>	
		<ul>
			<li><strong>%%INVITERNAME%%</strong>: <?php _e('Display name of the inviter',$this->WPB_PREFIX);?></li>
			<li><strong>%%SITENAME%%</strong>: <?php _e('Name of your website',$this->WPB_PREFIX);?> - <?php echo bloginfo('name');?></li>
			<li><strong>%%ACCEPTURL%%</strong>: <?php _e('Link that invited users can click to accept the invitation and register',$this->WPB_PREFIX);?></li>
			<li><strong>%%INVITERURL%%</strong>: <?php _e('If Buddypress is enabled, URL to the profile of the inviter',$this->WPB_PREFIX);?></li>
			<li><strong>%%CUSTOMURL%%</strong>: <?php _e('A custom URL that you can edit with a simple filter',$this->WPB_PREFIX);?></li>
			<li><strong>%%CURRENTURL%%</strong>: <?php _e('Prints the url where the widget was clicked',$this->WPB_PREFIX);?></li>
			<li><strong>%%CURRENTTITLE%%</strong>: <?php _e('Title of the post / page where the widget was clicked',$this->WPB_PREFIX);?></li>			
		</ul>	
		<p><?php echo sprintf(__('If you have any question please carefully <a href="%s">read the documentation</a> before opening a ticket',$this->WPB_PREFIX), 'http://wp.timersys.com/wordpress-social-invitations/docs/defaults-messages/');?></p>
		
			<script type="text/javascript">
			jQuery(document).ready(function($) { 
				$('#char_left').css('color','green');
				$('#tw_message').keyup(function(){
					char_calc_tw($(this));
				});
				char_calc_tw($('#tw_message'));
					function char_calc_tw(esto){
						$('#char_left').text(140 - esto.val().length);
						if( esto.val().length > 120 )
						{
							$('#char_left').css('color','red');
						}
						else
						{
							$('#char_left').css('color','green');
						}
					}
				
				$('#char_left_lk').css('color','green');
				$('#message').keyup(function(){
					char_calc($(this));
				});
				char_calc($('#message'));
					function char_calc(esto){
						$('#char_left_lk').text(200 - esto.val().length);
						if( esto.val().length > 180 )
						{
							$('#char_left_lk').css('color','red');
						}
						else
						{
							$('#char_left_lk').css('color','green');
						}
					}
			}); 
			</script>	
		</div><?php
	}


	/**
	* Print Emails  Box
	*/
	function print_emails_box(){
	
	?>
		<div id="own" style="display:none">
			<p><?php _e('The simplest solution for small lists. Your web host sets a daily email limit.',$this->WPB_PREFIX);?></p>
		</div>
	
		<div id="gmail" style="display:none">
			<p><?php _e('Easy to setup. Limited to 500 emails a day. We recommend that you open a dedicated Gmail account for this purpose.',$this->WPB_PREFIX);?></p>
		</div>
	
		<div id="smtp" style="display:none">
			<p><?php _e('Send with a professional SMTP provider, a great choice for big sites',$this->WPB_PREFIX);?></p>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($) { 

					
			$('.send_with').parent('td').append('<div id="emails-info"></div>');
			
			var send_method = $('.send_with').val();
			
			show_email_settings(send_method);
			
			$('#emails-info').html($('#'+ send_method ).html());
			
			$('.send_with').change(function(){
				var send_method = $(this).val();
				$('#emails-info').html($('#'+ send_method ).html());
					
				show_email_settings(send_method);
				
			});
			
			$('.wsi_test_email').unbind('click').on('click',function(){
			
				$('#sending').fadeIn();
				$.post( ajaxurl, {action : 'wsi_test_email'}, function(response){
					
					if( response != '' )
					{
						$('#sending').html('<div class="error">'+response+'</div>');
					}
					else
					{
						$('#sending').html('<div class="updated"><?php _e('Test email sent to '. get_bloginfo('admin_email'),$this->WPB_PREFIX);?> </div>');
					}	
	
				} );
				
				return false;
			});
			
			
		function show_email_settings(method)
		{
			//hide settings and headings
			$('.gmail_settings').parent('td').parent('tr').hide();
			$('.gmail_settings').parent('tr').hide();
			$('.smtp_settings').parent('td').parent('tr').hide();
			$('.smtp_settings').parent('tr').hide();
			
			switch(method)
			{
				case 'own':
					
						break;
				case 'gmail':
						$('.gmail_settings').parent('td').parent('tr').fadeIn();
						$('.gmail_settings').parent('tr').fadeIn();			
						break;
				case 'smtp':
						$('.smtp_settings').parent('td').parent('tr').fadeIn();
						$('.smtp_settings').parent('tr').fadeIn();
					break;
			}
			
		}
		
		}); 
		</script>
		
		
	
	<?php
	}	
	
	
	/**
	* Print Styling  Box
	*/
	function print_styling_box(){
	
	?>
		<div class="info-box">
			<p><?php _e('Here you can drag and drop the providers to change the order in the widget and also you will be able to add custom CSS.',$this->WPB_PREFIX);?></p>
			
			<p><?php echo sprintf(__('If you have any question please carefully <a href="%s">read documentation</a> first opening a ticket',$this->WPB_PREFIX), 'http://wp.timersys.com/wordpress-social-invitations/docs/styling/');?></p>
		</div><?php
	}

	/**
	* Print Debug section Box
	*/
	function print_debug_box(){
	
	?>

		<div class="info-box">
			<?php	require_once(dirname (__FILE__).'/siteinfo.php');?>				
		</div><?php
	}

	/**
	* Check display options and hook widget properly
	*/
	function hookWidgetChecks(){
		
		$settings = $this->_options;
		$prefix = $this->WPB_PREFIX;
		global $bp;
		
		if ( isset($settings['hook_buddypress']) && $settings['hook_buddypress'] == 'true' && defined('BP_VERSION') && $bp->current_component == 'activate' )
		{
			add_action( 'bp_after_activate_content', array(&$this, 'widget'));
		}
		
		if ( isset($settings['hook_invite_anyone']) && $settings['hook_invite_anyone'] == 'true' )
		{
			add_action( 'invite_anyone_after_addresses', array(&$this, 'display_widget_ia'));
			add_action( 'invite_anyone_after_addresses', array(&$this, 'load_wsi_anyone_js'));
			
		}
		
	}
	

	/**
	* Function to display the extended widget in Invite Anyone
	*/
	public function display_widget_ia()
	{
		$title = apply_filters('wsi_invite_anyone_title',__('You can also add email addresses from:', $this->WPB_PREFIX));
		$providers = $this->get_providers();

		$CURRENT_URL = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		
		wsi_get_template('widget/widget.php', array( 
			'WPB_PREFIX' 	=> $this->WPB_PREFIX, 
			'assets_url' 	=> $this->assets_url, 
			'options' 		=> $this->_options, 
			'providers' 	=> $providers,
			'CURRENT_URL'	=> $CURRENT_URL,
			'title'			=> $title
			)
		);
		
	}
	
	/**
	* Function to use inside themes that display widget and enqueue necessary js
	*/
	public static function widget($title="")
	{
		$wsi = WP_Social_Invitations::get_instance();
		$providers = $wsi->get_providers();
		$wsi->load_wsi_js();
		$CURRENT_URL = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		
		wsi_get_template('widget/widget.php', array( 
			'WPB_PREFIX' 	=> $wsi->WPB_PREFIX, 
			'assets_url' 	=> $wsi->assets_url, 
			'options' 		=> $wsi->_options, 
			'providers' 	=> $providers,
			'CURRENT_URL'	=> $CURRENT_URL,
			'title'			=> $title
			)
		);
	}
	
	/**
	* Procces auth proccess capturing the popup
	*/
	function process_auth(){
		
		if( ! isset( $_REQUEST[ 'action' ] ) || $_REQUEST['action'] != 'wsi_authenticate'  )
		{
				return null;
		}
		
		if( $_REQUEST[ 'action' ] == "wsi_authenticate" )
		{
				$this->process_login_auth();
		}
		
		if( $_REQUEST[ 'wsi-accept-invitation' ] == "wsi_authenticate" )
		{
				$this->process_login_auth();
		}

	}
	/**
	* Procces the accepting invitation process for buddypress
	*/
	function bp_process_invitations(){
	
		global $bp;
		if( defined('BP_VERSION') && ! isset( $bp->current_action) &&  $bp->current_action != 'wsi-accept-invitation' )
		{
			return null;
		}
		
		if( defined('BP_VERSION') && isset($bp->action_variables[0]) && $bp->action_variables[0] != '' )
		{
			$this->bp_handle_user_invitations();
		}
		
	}
	/**
	* Procces the accepting invitation process
	*/
	function process_invitations(){
	
		
		if( ! isset( $_REQUEST[ 'wsi-accept-invitation' ] )  )
		{
				return null;
		}
		
		if( $_REQUEST[ 'wsi-accept-invitation' ] != "" &&  isset($_REQUEST['action']) && $_REQUEST['action'] == 'register' )
		{
				$this->handle_user_invitations();
		}
		
	}
	
	/**
	 * Bypass the registation lock if enabled
	 * Thanks to Boone Gorges (Invite Anyone Plugin) for this bit
	 *
	 */
	function bypass_registration_lock(){
		
		global $bp;
		
		if( ! isset( $_REQUEST[ 'wsi-accept-invitation' ] ) || ( defined('BP_VERSION') && $bp->current_action != 'wsi-accept-invitation' )  )
		{
				return;
		}
		
		if ( empty( $this->_options['bypass_registration_lock'] ) || !array_key_exists('yes',$this->_options['bypass_registration_lock'] ) )
			return;
		
		// This is a royal hack until there is a filter on bp_get_signup_allowed()
		if ( is_multisite() ) 
		{
			if ( defined('BP_VERSION') && !empty( $bp->site_options['registration'] ) && $bp->site_options['registration'] == 'blog' ) {
				$bp->site_options['registration'] = 'all';
			} else if (defined('BP_VERSION') && !empty( $bp->site_options['registration'] ) && $bp->site_options['registration'] == 'none' ) {
				$bp->site_options['registration'] = 'user';
			}
		} 
		else {
			add_filter( 'option_users_can_register', create_function( false, 'return true;' ) );
		}
		
		
	}
	
		
	/**
	 * Function to process invitations
	 *
	 */
	 
	 function handle_user_invitations(){

	 	global $wpdb;
	 	
	 	$queue_id = (int)base64_decode( $_REQUEST[ 'wsi-accept-invitation' ] );
	 	
	 	$stat 	  = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->base_prefix}wsi_stats WHERE queue_id = %d", array($queue_id)));
	 	 	
	 	$html = '';
	 	
	 	
	 	if( isset($stat->id) )
	 	{
	 		$inviter_text = '';
			if( $stat->display_name != '' ) $inviter_text = sprintf( __("by %s", $this->WPB_PREFIX),$stat->display_name );	 		
	 		ob_start();
	
	 		wsi_get_template('registration.php', array( 
				'options' 					=> $this->_options,
				'WPB_PREFIX' 				=> $this->WPB_PREFIX, 
				'assets_url'				=> $this->assets_url, 
				'data' 						=> $stat,
				'inviter_text'				=> $inviter_text,
				'is_bp'						=> 'no'
				) 
			);
			$html = ob_get_contents();
			ob_clean();
	 	}
		
		echo $html;
	 }

/**
	 * Function to process invitations in BP
	 *
	 */
	 
	 function bp_handle_user_invitations(){

	 	global $wpdb,$bp;
	 	

	 	$queue_id = (int)base64_decode(  $bp->action_variables[0] );
	 	
	 	$stat 	  = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->base_prefix}wsi_stats WHERE queue_id = %d", array($queue_id)));
	 	
	 	$html = '';
	 	

	 	if( isset($stat->id) )
	 	{
	 		$inviter_text = '';
			if( $stat->display_name != '' ) $inviter_text = sprintf( __("by %s", $this->WPB_PREFIX),$stat->display_name );	 		
	 		ob_start();
	
	 		wsi_get_template('registration.php', array( 
				'options' 					=> $this->_options,
				'WPB_PREFIX' 				=> $this->WPB_PREFIX, 
				'assets_url'				=> $this->assets_url, 
				'data' 						=> $stat,
				'inviter_text'				=> $inviter_text,
				'is_bp'						=> 'yes'
				) 
			);
			$html = ob_get_contents();
			ob_clean();
	 	}
		
		echo $html;
	 }

	/**
	 * Function that check for new users to see if they were invited by our plugin
	 * @since 2.1.3
	 * @return void
	 */ 
	 function check_new_registered_user($user_id)
	 {
	 	global $wpdb;
	 	
	 	if( !isset($_COOKIE['wsi-pre-accept']) )
	 		return;
	 	
	 	//if the cookie exists, the user was invited with our plugin. Lets give some points aways
	 	
	 	$queue_id = (int)base64_decode($_COOKIE['wsi-pre-accept']);
	 	
	 	$stat 	  = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->base_prefix}wsi_stats WHERE queue_id = %d", array($queue_id)));
	 	
	 	do_action('wsi_invitation_accepted', $stat->user_id, $stat);
	 	
	 	//clear the cookie
	 	setcookie ("wsi-pre-accept", "", time() - 3600, '/');
	 }
	/**
	 * Function that check for users that clicked on invitation link and creates the cookie
	 * @since 2.1.3
	 * @return void
	 *
	 */ 
	 function check_new_invited_user(){
	 	
	 	global $bp;
	 
	 	if( !empty($_REQUEST[ 'wsi-accept-invitation' ] ) &&  isset($_REQUEST['action']) && $_REQUEST['action'] == 'register' )
		{
	 		setcookie ("wsi-pre-accept", $_REQUEST[ 'wsi-accept-invitation' ], time() + 84600, '/');
	 		
	 		return null;
	 	}
	 	
	 	if( defined('BP_VERSION') && ! isset( $bp->current_action) &&  $bp->current_action != 'wsi-accept-invitation' )
		{
			return null;
		}
		
		if( defined('BP_VERSION') &&  isset($bp->action_variables[0]) && $bp->action_variables[0] != '' )
		{
			setcookie ("wsi-pre-accept", $bp->action_variables[0], time() + 84600, '/');
			
	 		return;
		}
		
	 }
	 	 
	/**
	* Function that handle auth
	*/
	function process_login_auth() {
		
		@ session_start();
		// let display a loading message. should be better than a white screen
		if( isset( $_REQUEST["provider"] ) && ! isset( $_REQUEST["redirect_to_provider"] ) ){
			$this->process_login_render_loading_page();
		}	
		
		try{
		$adapter = $hybridauth = '';
		$settings = $this->_options;
		$provider = @ trim( strip_tags( $_REQUEST["provider"] ) );
		if( $provider != 'live' && $provider != 'mail')
		{
			$adapter = $this->connect_to_provider();
			$hybridauth = $this->hybridauth;
			
		}	

		
		if( ! empty( $provider ) && ($provider == 'live' || $provider == 'mail' || $hybridauth->isConnectedWith( $provider ) ))
		{
			$return_to = @ $_GET['redirect_to'];
			$return_to = $return_to . ( strpos( $return_to, '?' ) ? '&' : '?' ) . "connected_with=" . $provider ;
			
			?>
			<!DOCTYPE html>
			<html>
			<head>
			<link rel="stylesheet" href="<?php echo apply_filters('collector_css_file',plugins_url( 'assets/css/collector.css?v='.$this->WPB_VERSION, __FILE__ ));?>" type="text/css" media="all">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta http-equiv="Content-Type" content="text/html;charset=<?php echo get_bloginfo('charset');?>">
			<title><?php _e('Select your Friends',$this->WPB_PREFIX);?> - Wordpress Social Invitations</title>
				<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
				<script src="<?php echo plugins_url( 'assets/js/collector.js', __FILE__ );?>"></script>
				<script>
					$(document).ready(function(){

					<?php if ( isset($_GET['wsi_hook']) && $_GET['wsi_hook'] == 'anyone') : ?>
						$('#collect_emails').submit(function(e){
							e.preventDefault();
							$('body *').hide();
							$('#wsi_loading,#wsi_loading * ').fadeIn();
							var emails = '';
							$(".friends_container input:checkbox:checked").each(function(){
								emails += $(this).val()+'\n';
							});
							$('#invite-anyone-email-addresses',window.opener.document).html(emails);
							$('#<?php echo $_GET['widget_id'];?> #<?php echo $provider;?>-provider',window.opener.document).addClass('completed');
							$('#<?php echo $_GET['widget_id'];?> #wsi_provider',window.opener.document).html('<?php echo ucfirst($provider);?>');
							$('#<?php echo $_GET['widget_id'];?> .wsi_success',window.opener.document).fadeIn('slow',function(){
								
								window.self.close();  
							});
									
						});	
					
					<?php else:?>
						$('#collect_emails').submit(function(e){
							e.preventDefault();
							$('body *').hide();
							$('#wsi_loading,#wsi_loading * ').fadeIn();
							
							$.post(window.opener.WsiMyAjax.admin_url, $('#collect_emails').serialize(), function(response){
								$('#<?php echo $_GET['widget_id'];?> #<?php echo $provider;?>-provider',window.opener.document).addClass('completed');
								$('#<?php echo $_GET['widget_id'];?> #wsi_provider',window.opener.document).html('<?php echo ucfirst($provider);?>');
								$('#<?php echo $_GET['widget_id'];?> .wsi_success',window.opener.document).fadeIn('slow',function(){
								<?php if( $_GET['wsi_locker'] == 'true' ) :?>
									setCookie("wsi-lock[<?php echo $_GET['widget_id'];?>]",1,365);
									window.opener.location.reload();
								<?php endif;?>

								window.self.close();  
							<?php if( isset( $settings['redirect_url']) && $settings['redirect_url'] != '' ) :?>	
								window.opener.location.href = '<?php echo $settings['redirect_url'];?>';
							<?php endif;?>	
							});
			
								
								
							});
							return false;							
						});
					<?php endif;?>	
					
			
						
					});			
				function init() {
			
			
					if(  window.opener ){
						
						window.opener.parent.location.href = "<?php echo $return_to; ?>";
					}
				
					window.self.close();
				}
				function setCookie(c_name,value,exdays)
				{
					var exdate=new Date();
					exdate.setDate(exdate.getDate() + exdays);
					var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString()) + "; path=/";
					document.cookie=c_name + "=" + c_value;
				}				
				</script>
				<?php //wp_head(); ?>
			</head>
			<body>
			<?php 
			/**
			 * Live importer
			 */
			if( $provider == 'live' ) : 
					
					require( 'admin/my-providers/live.php');
					
			endif; //if provider is live we showed the uploader	
			
			/**
			 * Manual email invitations
			 */
			if( $provider == 'mail' )
			{
			
				require( 'admin/my-providers/mail.php');
			
			}
			
			$hybridauth_session_data = $profile = $display_name = '';
		
			if ( $provider != 'live' && $provider != 'mail')
			{	
				$hybridauth_session_data = $hybridauth->getSessionData();	
				$profile 				 = $adapter->getUserProfile();
				$display_name  			 = $profile->displayName;
				
			}
			//to use later	
			self::$_profile 	=  $profile;
			self::$_current_url =  $_GET['current_url'];
			self::$_obj_id 		=  $_GET['wsi_obj_id'];
				
				//load the collector and pass all variables needed
				wsi_get_template('popup/collector.php', array( 
					'options' 					=> $this->_options,
					'WPB_PREFIX' 				=> $this->WPB_PREFIX, 
					'assets_url'				=> $this->assets_url, 
					'provider' 					=> $provider ,
					'hybridauth' 				=> $hybridauth, 
					'adapter' 					=> $adapter, 
					'return_to'					=> $return_to,
					'settings'					=> $settings,
					'hybridauth_session_data'	=> $hybridauth_session_data,
					'profile'					=> $profile,
					'display_name'				=> $display_name,
					'wsi_obj_id'				=> self::$_obj_id 
					) 
				);
			
			?>

			<?php wsi_get_template('popup/sending.php', array( 'WPB_PREFIX' => $this->WPB_PREFIX, 'assets_url' => $this->assets_url, 'provider' => $provider  ));

				global $wp_scripts, $wp_styles, $wp_filter;

				//remove all scripts and style
			    if( !empty( $wp_scripts->queue ))
			    {
				    foreach ($wp_scripts->queue as $handle) {
				        wp_dequeue_script ($handle);
				    }
				}    
			    if( !empty( $wp_styles->queue ))
			    {
				    foreach ($wp_styles->queue as $handle) {
				        wp_dequeue_style ($handle);
				    }
			    }
			   //remove all actions
			   remove_all_actions('wp_footer',52);
			   //but print scripts
			   add_action('wp_footer','wp_print_footer_scripts',10);
			?>	

			<div id="footer">
				<div id="credits">
					Powered by <a href="http://wp.timersys.com/wordpress-social-invitations/" target="_blank">Wordpress Social Invitations</a>
				</div>
				<script type="text/javascript">
				jQuery(function($) { 
					//remove all divs added by wp_footer
					$('#footer div').not('#credits').remove();	
				}); 
				jQuery(document).ready(function($) { 
					setTimeout(function(){
					//Fix wp_editor height
					$('#message_ifr').css('min-height','100px');
					},500 )
				});
				</script>
			<?php 
				//if we are using wp_editor load necesary files
				if (  class_exists( '_WP_Editors' ) ):
				
					_WP_Editors::editor_js();
					_WP_Editors::enqueue_scripts();
				endif;
				wp_footer();
					?>				
			
				</body>
			</html>
			<?php	
			session_write_close();		
		}// if we are connected to a provider or is live
	}
	catch( Exception $e ){
		@$this->process_login_render_error_page( $e, $config, $hybridauth, $adapter, $profile );
	} 

	die();
	}
	
	
	
	/**
	 * Function that returns the subject field to the collector if enabled/available
	 *
	 *
	 */
	public static function printSubjectField($provider ='', $settings = '')
	{

	 		
	 		 if($settings['subject_editable'] == 'false' || $provider == 'facebook' || $provider == 'twitter') :?>
			
	 		 	<?php if( $provider == 'linkedin' ) : ?>

					<input type="hidden" name="subject" value="<?php self::printFieldValue( apply_filters('wsi_lk_subject', $settings['subject']));?>"/>

				<?php else: ?>

					<input type="hidden" name="subject" value="<?php self::printFieldValue( apply_filters('wsi_html_subject', $settings['subject']));?>"/>

				<?php endif;?>

			<?php else: ?>
				
				<label for="subject"><?php _e('Subject', 'wsi');?></label>
				
				<?php if( $provider == 'linkedin' ) : ?>
				<div class="box-wrapper">
					<input type="text" name="subject" value="<?php self::printFieldValue(strip_tags(apply_filters('wsi_lk_subject',$settings['text_subject'])));?>" />
				</div>
				<?php else: ?>
				<div class="box-wrapper">	
					<input type="text" name="subject" value="<?php self::printFieldValue(apply_filters('wsi_html_subject',$settings['subject']));?>" />
				</div>
				<?php endif;?>	

			<?php endif;
	 
	 
	}

	/**
	 * Function that returns the message field to the collector if enabled/available
	 *
	 *
	 */
	public static function printMessageField($provider='', $settings='')
	{
			
	 		
	 		 if( $provider == 'twitter') :
	 		 	
	 		 	if($settings['tw_message_editable'] == false ) : ?>
			
					<textarea  style="display:none;" name="message" ><?php self::printFieldValue(strip_tags(apply_filters('tw_message',$settings['tw_message'])));?></textarea>
				
				<?php else: ?>
					
					<label for="message"><?php _e('Message', 'wsi');?></label>

					<div class="box-wrapper">
						<textarea name="message" id="tw_message"><?php self::printFieldValue(strip_tags(apply_filters('tw_message',$settings['tw_message'])));?></textarea>
					</div>
					<?php echo sprintf(__('Keep it under 140 characters. Characters left: %s','wsi'),'<span id="char_left">140</span>');?>
						<script type="text/javascript">
						jQuery(document).ready(function($) { 
							$('#char_left').css('color','green');
							$('#tw_message').keyup(function(){
								char_calc($(this));
							});
							char_calc($('#tw_message'));
							function char_calc(esto){
								$('#char_left').text(140 - esto.val().length);
								if( esto.val().length > 120 )
								{
									$('#char_left').css('color','red');
								}
								else
								{
									$('#char_left').css('color','green');
								}
							}
						});  
						</script>
				<?php endif;?>	
			
			<?php 
			 //end twitter
			 
			 elseif( $provider == 'facebook'  ) :
			
				if( $settings['fb_message_editable'] == 'false' ) : ?>
				
					<textarea  style="display:none;" name="message" ><?php self::printFieldValue(strip_tags(apply_filters('fb_message',$settings['fb_message'])));?></textarea>
					
				<?php else: ?>
					<label for="message"><?php _e('Message', 'wsi');?></label>

					<div class="box-wrapper">
						<textarea name="message" id="message"><?php self::printFieldValue(strip_tags(apply_filters('fb_message',$settings['fb_message'])));?></textarea>
					</div>
				<?php endif;?>
			<?php 
			//end facebook so we linkedin
			 elseif( $provider == 'linkedin'  ) :
			
				if( $settings['message_editable'] == 'false' ) : ?>
				
					<textarea  style="display:none;"  name="message" ><?php self::printFieldValue(strip_tags(apply_filters('lk_message',$settings['message'])));?></textarea>
					
				<?php else: ?>
					<label for="message"><?php _e('Message', 'wsi');?></label>

					<div class="box-wrapper">
						<textarea name="message" id="message"><?php self::printFieldValue(strip_tags(apply_filters('lk_message',$settings['message'])));?></textarea>
					</div>
					<?php echo sprintf(__('Keep it under 200 characters. Characters left: %s','wsi'),'<span id="char_left_lk">200</span>');?>
						<script type="text/javascript">
						jQuery(document).ready(function($) { 
							$('#char_left_lk').css('color','green');
							$('#message').keyup(function(){
								char_calc($(this));
							});
							char_calc($('#message'));
							function char_calc(esto){
								$('#char_left_lk').text(200 - esto.val().length);
								if( esto.val().length > 180 )
								{
									$('#char_left_lk').css('color','red');
								}
								else
								{
									$('#char_left_lk').css('color','green');
								}
							}
						});  
						</script>
				
				<?php endif;?>
				
			<?php 
			//end linkedin so we start email providers
			
			else: 
				
				if( $settings['html_message_editable'] == 'false' ) : ?>
				
					<textarea  style="display:none;" name="message" ><?php self::printFieldValue(apply_filters('html_message',$settings['html_message']));?></textarea>
					
				<?php else: ?>
				
					<label for="message"><?php _e('Message', 'wsi');?></label>

					<div class="box-wrapper">
						<?php wp_editor( self::getFieldValue(apply_filters('html_message',$settings['html_message'])),'message' , array('media_buttons' => false,'quicktags' => false,'textarea_rows' => 15));?>
					</div>
						
				<?php endif;?>	

			<?php endif;
				//end html email providers
	 
	 
	}
	/**
	* Check for mbstring function and use it if available
	*/
	public static function printName($name){

		if( function_exists('mb_convert_encoding'))
		{
			echo mb_convert_encoding($name, "HTML-ENTITIES", "UTF-8");																				
		}
		else
		{
			echo utf8_decode($name);
		}	
		
										
	}
	
	/*
	* Check for mbstring function and use it if available for fields
	* We use a simple replace shortcode function
	*/
	public static function printFieldValue($name){

		if( function_exists('mb_convert_encoding'))
		{
			echo mb_convert_encoding(self::replaceShortcodes($name), "HTML-ENTITIES", "UTF-8");																				
		}
		else
		{
			echo utf8_decode(self::replaceShortcodes($name));
		}	
		
										
	}

	/**
	* Check for mbstring function and use it if available for fields
	* We use a simple replace shortcode function
	*/
	public static function getFieldValue($name){

		if( function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding(self::replaceShortcodes($name), "HTML-ENTITIES", "UTF-8");																				
		}
		else
		{
			return utf8_decode(self::replaceShortcodes($name));
		}	
		
										
	}

	/**
	 * Function that replace basic shortcodes
	 */
	 
	 static function replaceShortcodes($content){
	 
		 /*
			%%INVITERNAME%%: Display name of the inviter
			%%SITENAME%%: Name of your website 
			%%ACCEPTURL%%: Link that invited users can click to accept the invitation and register
			%%INVITERURL%%: If available, URL to the profile of the inviter
			%%CUSTOMURL%%: A custom URL that you can edit with a simple filter
			%%CURRENTURL%%: Prints urls where the widget was clicked
			*/
			$que = array(
				'%%INVITERNAME%%',
				'%%SITENAME%%',
				'%%CURRENTURL%%',
				'%%CUSTOMURL%%',
				'%%CURRENTTITLE%%',
			);
			
			$wsi =  self::get_instance();

			$post_data = get_post(self::$_obj_id);
			
			$por = array(
				apply_filters('wsi_placeholder_invitername'	, isset(self::$_profile->displayName) ? self::$_profile->displayName : __('A friend of you', 'wsi')),
				apply_filters('wsi_placeholder_sitename'	, get_bloginfo('name')),
				apply_filters('wsi_current_url'				, isset(self::$_current_url) ? self::$_current_url : ''),
				apply_filters('wsi_placeholder_custom_url'  , $wsi->_options['custom_url'] ),
				apply_filters('wsi_current_title'			, isset($post_data->post_title) ? $post_data->post_title : '')
				
			);
	
			return str_replace($que, $por, $content);
	}	

	
	/**
	* Check for mbstring function and use it if available
	*/
	public static function getName($name){

		if( function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($name, "HTML-ENTITIES", "UTF-8");																				
		}
		else
		{
			return utf8_decode($name);
		}	
		
										
	}

	/**
	* Check for provider and return identifier or email depending the situatio
	*/
	public static function getValue($provider, $friend){

		echo $provider == 'linkedin' || $provider == 'google' || $provider == 'facebook' || $provider == 'twitter' ? $friend->identifier : $friend->email;	
		
										
	}
	
	
	/**
	* AJAX that add invitations to queue
	*/
	function add_to_wsi_queue_callback(){
		
			
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'wsi-ajax-nonce' ) )
			 die ( 'Not good not good');
		
		$q = new Wsi_Queue;
		
		if( $_POST['provider'] == 'mail' )
		{
			//lets parse our textare and add it to the friend post var
			$f_array	 		= explode(PHP_EOL, $_POST['friend']);
			$f_array 			= array_filter($f_array, 'trim');
			$_POST['friend'] 	= $f_array;
		
		}
		
		$q->add_to_queue($_POST['provider'], $_POST['sdata'], $_POST['friend'], $_POST['subject'], $_POST['message'], $_POST['display_name'], $_POST['wsi_obj_id']);
	
		die();
	}
	
	/**
	 * We need to add a fake queue to have and id to generate the registration URL
	 * @since  2.3
	 * @return string registration URL
	 * 
	 */
	function get_fb_registration_url_callback() {

		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'wsi-ajax-nonce' ) )
			 die ( 'Not good not good');
		
		$q = new Wsi_Queue;

		$queue_id = $q->add_to_queue('facebook', '','', '', '', '', $_POST['wsi_obj_id']);

		if ( 'registration' == $this->_options['facebook_share_url'] ){
			$accept_url 	= 'wsi-fb-accept-invitation='.base64_encode( $queue_id );
		} 
		
		echo $accept_url;	
		die();
	}			
	
	
	
	/**
	* Loading Page
	*/
	function process_login_render_loading_page()
	{
		
	
		// selected provider 
		$provider = @ trim( strip_tags( $_REQUEST["provider"] ) ); 
		wsi_get_template('popup/loading.php', array( 'options' => $this->_options, 'WPB_PREFIX' => $this->WPB_PREFIX, 'assets_url' => $this->assets_url, 'provider' => $provider  ) ); 
		die();
	}
	
	/**
	* Errors pages
	*/
	function process_login_render_error_page( $e, $config, $hybridauth, $adapter, $profile )
	{
		
	 	wsi_get_template('popup/error.php', array( 'e' => $e, 'options' => $this->_options, 'WPB_PREFIX' => $this->WPB_PREFIX, 'assets_url' => $this->assets_url  ) ); 
	 	die();
	
	}// error page
	
	/**
	 * Connect to given provider
	 *
	 *
	 */
	 private function connect_to_provider($provider = '')
	{
		
		// selected provider name 
		$provider = @ $provider != '' ? $provider : trim( strip_tags( $_REQUEST["provider"]));
		$hybridauth = $this->create_hybridauth($provider);


		// try to authenticate the selected $provider
		$params  = array();

		// if callback_url defined, overwrite Hybrid_Auth::getCurrentUrl(); 
		if( isset($callback_url) ){
			$params["hauth_return_to"] = $callback_url;
		}
		
		$adapter = $hybridauth->authenticate( $provider, $params );
		$this->hybridauth = $hybridauth;
		return $adapter;
	}
	
	/**
	 * Creates the hybridauth object
	 *
	 *
	 */
	 function create_hybridauth($provider){
	 			$settings = $this->_options;
		
	
			// load hybridauth
			require_once $this->WPB_ABS_PATH . "/hybridauth/Hybrid/Auth.php";
	
			$provider = strtolower($provider);
			// build required configuratoin for this provider
			
			if( !isset( $settings['enable_'.$provider] ) || $settings['enable_'.$provider] != 'true' ){
				throw new Exception( _e( 'Unknown or disabled provider', $this->WPB_PREFIX) );
			}
	
			// default endpoint_url/callback_url
			$endpoint_url = $this->WSI_HYBRIDAUTH_ENDPOINT_URL;
			$callback_url = null; // autogenerated by hybridauth
	
	
			// check hybridauth_base_url
			if( ! strstr( $endpoint_url, "http://" ) && ! strstr( $endpoint_url, "https://" ) ){
				throw new Exception( 'Invalid base_url: ' . $endpoint_url, 9 );
			}
	
			$config = array();
			$config["base_url"]  = $endpoint_url; 
			$config["providers"] = array();
			$config["providers"][$provider] = array();
			$config["providers"][$provider]["enabled"] = true;
	
			// provider application id ?
			if( isset($settings[$provider.'_key']) && $settings[$provider.'_key'] != '' ) {
				$config["providers"][$provider]["keys"]["id"] = $settings[$provider.'_key'];
				$config["providers"][$provider]["keys"]["key"] = $settings[$provider.'_key'];
			}
	
			
	
			// provider application secret ?
			if( isset($settings[$provider.'_secret']) && $settings[$provider.'_secret'] != '' ){
				$config["providers"][$provider]["keys"]["secret"] = $settings[$provider.'_secret'];
			}

	
			if( strtolower( $provider ) == "facebook" ){
				$config["providers"][$provider]["scope"]   = "xmpp_login, email, user_about_me, user_birthday, user_hometown, user_website, offline_access, read_stream, publish_stream, read_friendlists";  
			}
			#$config["debug_mode"] = true;
			#$config["debug_file"] = dirname (__FILE__).'/uploads/debug.log';
			
		
			// create an instance for Hybridauth
			$hybridauth = new Hybrid_Auth( $config );
			
			return $hybridauth;
	}
	
	/**
	* Return plugin providers
	*/
	
	public function get_providers(){
		$providers = get_option('wsi_widget_order',true);
		return is_array($providers) ? $providers : $this->providers;
	}
	
	/**
	* Ajax function that handle widget order
	*/
	function change_widget_order(){
	
		$providers = $this->get_providers();
		$new_order = array();
		$order = explode(',', $_POST['order']);
		foreach( $order as $p )
		{
			$new_order[$p] = $providers[$p]; 
		}
		
		update_option('wsi_widget_order', $new_order);
		
		die();
	}
	
	/**
	* Return domain
	*/
	public function get_domain(){
	
		return $this->WPB_PREFIX;
	}

	/**
	* Return slug
	*/
	public function get_slug(){
	
		return $this->WPB_SLUG;
	}

	/**
	* Return rel path
	*/
	public function get_abs_path(){
	
		return $this->WPB_ABS_PATH;
	}


	
	/**
	 *
	 * Filter for cron schedules. Took it from Wysija
	 */
	public static function filter_cron_schedules( $param ) {
	
        $frequencies=array(
            'wsi_one_min' => array(
                'interval' => 60,
                'display' => __( 'Once every minute','wsi')
                )
            );

        return array_merge($param, $frequencies);
    }
    
    /**
     * We proccess the queue every minute
     *
     */
     function run_cron(){
     		$q = new Wsi_Queue;
	 		$q->process_queue();
     }
    
     /**
      * Function that capture the server cron request and process the queue
      * @since 2.2
      * @returns void
      */
	  function server_cron(){
	  		
	  		if( isset($_REQUEST['wsi_server_cron']) && $_REQUEST['wsi_server_cron'] == WSI_CRON_TOKEN )
	  		{
	  			define('DOING_CRON',true);
	  			$q = new Wsi_Queue;
	  			$q->process_queue();
	  			define('DOING_CRON',false);
	  			die();
	  		}
	  		
	  
	  }
	  
     /**
      * MyCreed hook
      * @ Since v2
      * @returns void
      */
      function mycreed($installed){
      	$installed['wsi'] = array(
			'title'       => 'Wordpress Social Invitations',
			'description' => __( 'This hook award points to users who send invitations and points for every accepted invitation.', 'textdomain' ),
			'callback'    => array( 'Wsi_Mycreed' )
		);
		return $installed;
      }
      
     /**
      * Bp Includes
      * @Since v2.1
      * @returns void
      */ 
      function bp_includes(){
     		
     		require_once( dirname (__FILE__).'/functions/bp.php');

     		add_action( 'bp_setup_globals', 'wsi_setup_globals', 2 );
	      	add_action( 'bp_setup_nav', 'wsi_setup_nav' );
	      	add_action( 'admin_bar_menu', 'wsi_menu', 99 );
      	
      }

      /**
       * Check if the site is being rendered for facebook and show og tags. If not then we redirect the user to the registration url
       * @return void
       * @since  v2.3.2
       */
      function facebook_check(){

      		if( empty( $_GET['wsi-fb-accept-invitation'] ) )
      			return;
      		
      		$token  = $_GET['wsi-token'];
      		//if token don't exist it means facebook it's rendering the send dialog page.
      		if( empty( $_GET['wsi-token']) || $_GET['wsi-token'] !== $_SESSION['wsi-token'] ){
	      	
	      		add_action('wp_head', array(&$this, 'add_my_og_tags'), 1);
      		
      		}  elseif( !empty( $_GET['wsi-fb-registration'] ) ) {

      			if( function_exists('bp_get_root_domain') ) {
					//BP is alive
					$accept_url 	= bp_get_root_domain() . '/' . bp_get_signup_slug() . '/wsi-accept-invitation/' . $_GET['wsi-fb-accept-invitation'];
				}
				else
				{
					$accept_url 	= site_url('/wp-login.php?action=register&wsi-accept-invitation='.$_GET['wsi-fb-accept-invitation'] );
				}
      			wp_safe_redirect( $accept_url );
      			die();
      		}

      }

      /**
       * Add custom message to login header
       * @since  v2.3.1
       */
      function add_my_og_tags(){
      	
	      	$title 			= self::replaceShortcodes( $this->_options['fb_title'] );
	      	$description 	= self::replaceShortcodes( $this->_options['fb_message'] );
	      	echo '<!-- Wordpress Social Invitations-->
	      	<meta property="og:title" content="'.$title.'">
	      	<meta property="og:description" content="'.$description.'">';

      }     
}

$wsi = WP_Social_Invitations::get_instance();

/**
 * INCLUDE queue class for cron
 */
require(dirname (__FILE__).'/classes/class.Wsi_Queue.php');
/**
 * Logger class
 */
require(dirname (__FILE__).'/classes/class.Wsi_Logger.php');

/**
 * MyCreed class
 */
if ( !class_exists( 'Wsi_MyCreed' ) && class_exists( 'myCRED_Hook' ) ) {
	require(dirname (__FILE__).'/classes/class.Wsi_Mycreed.php');
}	
/**
 * Cubepoints
 */
if (function_exists( 'cp_admin_modules' ) )
{
	require(dirname (__FILE__).'/functions/cubepoints.php');
}
/**
 * Shortcodes
 */
require(dirname (__FILE__).'/functions/shortcodes.php');
