<?php 
	
	/**
	 *
	 * Template header
	 *
	 **/
	
	// create an access to the template main object
	global $tpl;
	$at_dcma = new BP_Activity_Dcma();
    $at_dcma->check_date_update_step();
?>
<?php do_action('gavernwp_doctype'); ?>
<html <?php do_action('gavernwp_html_attributes'); ?>>
<head>
	<title><?php do_action('gavernwp_title'); ?></title>
	<?php do_action('gavernwp_metatags'); ?>
	
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="shortcut icon" href="<?php get_stylesheet_directory_uri(); ?>/favicon.ico" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	
	<?php
	   wp_enqueue_style('gavern-normalize', gavern_file_uri('css/normalize.css'), false);
	   wp_enqueue_style('gavern-font-awesome', gavern_file_uri('css/font-awesome.css'), array('gavern-normalize'));
	   wp_enqueue_style('gavern-template', gavern_file_uri('css/template.css'), array('gavern-font-awesome'));
	   wp_enqueue_style('gavern-wp', gavern_file_uri('css/wp.css'), array('gavern-template'));
	   wp_enqueue_style('gavern-stuff', gavern_file_uri('css/stuff.css'), array('gavern-wp'));
	   wp_enqueue_style('gavern-wpextensions', gavern_file_uri('css/wp.extensions.css'), array('gavern-stuff'));
	   wp_enqueue_style('gavern-extensions', gavern_file_uri('css/extensions.css'), array('gavern-wpextensions'));
	?>
	<!--[if IE 9]>
	<link rel="stylesheet" href="<?php echo gavern_file_uri('css/ie9.css'); ?>" />
	<![endif]-->
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="<?php echo gavern_file_uri('css/ie8.css'); ?>" />
	<![endif]-->
	
	<?php if(is_singular() && get_option('thread_comments' )) wp_enqueue_script( 'comment-reply' ); ?>
	
	<?php do_action('gavernwp_ie_scripts'); ?>
	
	<?php gk_head_shortcodes(); ?>
	
	<?php 
	    gk_load('responsive_css'); 
	    
	    if(get_option($tpl->name . "_overridecss_state", 'Y') == 'Y') {
	      wp_enqueue_style('gavern-override', gavern_file_uri('css/override.css'), array('gavern-style'));
	    }
	?>
	
	<?php
	    if(get_option($tpl->name . '_prefixfree_state', 'N') == 'Y') {
	      wp_enqueue_script('gavern-prefixfree', gavern_file_uri('js/prefixfree.js'));
	    } 
	?>
	
	<?php gk_head_style_css(); ?>
	<?php gk_head_style_pages(); ?>
	
	<?php gk_thickbox_load(); ?>
	<?php wp_head(); ?>
	
	<?php do_action('gavernwp_fonts'); ?>
	<?php gk_head_config(); ?>
	<?php wp_enqueue_script("jquery"); ?>
	
	<?php
	    wp_enqueue_script('gavern-scripts', gavern_file_uri('js/gk.scripts.js'), array('jquery'), false, true);
	    wp_enqueue_script('gavern-menu', gavern_file_uri('js/gk.menu.js'), array('jquery', 'gavern-scripts'), false, true);
	?>
	
	<?php do_action('gavernwp_head'); ?>
	
	<?php 
		echo stripslashes(
			htmlspecialchars_decode(
				str_replace( '&#039;', "'", get_option($tpl->name . '_head_code', ''))
			)
		); 
	?>
	
	<!--[if lte IE 9]>
	<script src="<?php echo gavern_file_uri('js/ie9.js'); ?>"></script>
	<script src="<?php echo gavern_file_uri('js/selectivizr.js'); ?>"></script>
	<![endif]-->
        
        <!-- added 
        <link rel="stylesheet" href="//cdn.jsdelivr.net/emojione/1.3.0/assets/css/emojione.min.css" />
        <script src="//cdn.jsdelivr.net/emojione/1.3.0/lib/js/emojione.min.js"></script>
        <script type='text/javascript' src = '<?php echo BP_PLUGIN_URL . 'jQuery-slimScroll-1.1.0/jquery.slimscroll.min.js' ?>'></script>
        -->
        <link rel='stylesheet' href = '<?php echo BP_PLUGIN_URL . 'emojione/assets/css/emojione.min.css' ?>' type='text/css'/>
        <script type='text/javascript' src = '<?php echo BP_PLUGIN_URL . 'emojione/lib/js/emojione.min.js' ?>'></script>
</head>
<body <?php do_action('gavernwp_body_attributes'); ?> <?php body_class(); ?>>	
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=502873843121201&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<link rel='stylesheet' href = '<?php echo BP_PLUGIN_URL . 'admin-bar-notification.css' ?>' type='text/css'/>
<script type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
<script type='text/javascript' src='<?php echo home_url();?>/wp-includes/js/jquery/jquery-ui.min.js'></script>
<script type='text/javascript' src = '<?php echo BP_PLUGIN_URL . 'jQuery-slimScroll-1.1.0/jquery.slimscroll.min.js' ?>'></script>

<script type="text/javascript">
jQuery( document ).ready( function() {
    jQuery('#wp-admin-bar-bp-notifications-read-default li').each(function(){
        var readID = this.id;
        var pos = readID.lastIndexOf('-');
        readID = readID.slice(pos+1);
        var prompt = jQuery('#wp-admin-bar-notification-all-'+readID);
        prompt.css("background-color","#4D4D4D");
    });

    var tempTag = jQuery('ul#wp-admin-bar-bp-notifications-tab-default .ab-sub-wrapper ul');
    tempTag.slimScroll({
        height: '420px'
    });
    
});
</script>
<div id="gk-bg" class="<?php echo(wp_is_mobile())?'mobile':'' ?>">
	<div id="gk-content-wrapper" class="gk-page">	
	
		<div id="gk-header">
		
			<div id="gk-top">
			
			<div id="gk-logo"><a href=" <?php echo site_url(); ?> "><img src="<?php bloginfo('template_directory'); ?>/images/Final logo.png" style="width:132px"></a></div>
				<?php if(gk_show_menu('mainmenu') && get_option($tpl->name . '_menu_type', 'overlay') == 'classic') : ?>
					<?php
				$login_user_link = ( is_user_logged_in() )?'<option value="'.bp_loggedin_user_domain().'">'.bp_core_get_user_displayname( bp_loggedin_user_id() ).'</option>':'';
				 gavern_menu('mainmenu', 'main-menu-mobile', array('walker' => new GKMenuWalkerMobile(), 'items_wrap' => '<i class="icon-reorder"></i><select onchange="window.location.href=this.value;">'.$login_user_link.'%3$s</select>', 'container' => 'div')); ?>
				<?php endif; ?>
				
					<!--<?php if((get_option($tpl->name . '_register_link', 'Y') == 'Y' && !is_user_logged_in()) || get_option($tpl->name . '_login_link', 'Y') == 'Y') : ?>
				<div id="gk-user-area">
					<?php if(get_option($tpl->name . '_login_link', 'Y') == 'Y') : ?>
					<a href="<?php echo get_option($tpl->name . '_login_url', 'wp-login.php?action=login'); ?>" id="gk-login"><?php (!is_user_logged_in()) ? _e('Log in', GKTPLNAME) : _e('Logout', GKTPLNAME); ?></a>
					<?php endif; ?>
				</div>
				<?php endif; ?>-->
				<div id="gk-user-area">
				<?php
                 if ( is_user_logged_in() ) {
	               echo '<a href="'.wp_logout_url( home_url() ).'">Logout</a>';
                       if (wp_is_mobile()){
                            echo '<div id="gk-user-chat" class="iflychat-icon-chat"></div>';
                            echo '<div id="gk-user-notification" class="gk-user-notification"></div>';
                        }
                 } else {
	              echo '<a href="'.wp_login_url().'">Log in</a>';
                       }
                          ?>
				</div>
				<?php if(get_option($tpl->name . "_branding_logo_type", 'css') != 'none') : ?>
				<a href="<?php echo home_url(); ?>" class="<?php echo get_option($tpl->name . "_branding_logo_type", 'css'); ?>Logo"><?php gk_blog_logo(); ?></a>
				<?php endif; ?>
				
				<?php if(gk_show_menu('mainmenu')) : ?>
					<?php gavern_menu('mainmenu', 'gk-main-menu', array('walker' => new GKMenuWalker())); ?>
				<?php endif; ?>
			
			
			</div>
			<?php if(gk_show_breadcrumbs()) : ?>
			<div id="gk-breadcrumb-area">
				<?php gk_breadcrumbs_output(); ?>
			</div>
			<?php endif; ?>
			
			<?php if(gk_is_active_sidebar('header')) : ?>
			<div id="gk-header-mod">
				<?php gk_dynamic_sidebar('header'); ?>
			</div>
			<?php endif; ?>
		</div>
		
		<?php if(gk_is_active_sidebar('header_bottom')) : ?>
		<div id="gk-header-bottom">
			<div class="widget-area">
				<?php gk_dynamic_sidebar('header_bottom'); ?>
			</div>
		</div>
		<?php endif; ?>
