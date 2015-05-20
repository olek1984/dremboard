<?php 
	
	/**
	 *
	 * Template part loading the responsive CSS code
	 *
	 **/
	
	// create an access to the template main object
	global $tpl;
	global $fullwidth;
	
	// disable direct access to the file	
	defined('GAVERN_WP') or die('Access denied');
	
?>

<style type="text/css">
	.gk-page { max-width: <?php echo get_option($tpl->name . '_template_width', 980); ?>px; }
	
	.onepage #gk-bottom1 .box > h3,
	.onepage #gk-bottom1 .box > div,
	.onepage #gk-bottom2 .box > h3,
	.onepage #gk-bottom2 .box > div {
		float: none!important;
		margin: 0 auto;
		max-width: <?php echo get_option($tpl->name . '_template_width', 1040); ?>px;
	}
	<?php if(
		get_option($tpl->name . '_page_layout', 'right') != 'none' && 
		gk_is_active_sidebar('sidebar') && 
		($fullwidth != true)
	) : ?>
	#gk-mainbody-columns > aside { width: <?php echo get_option($tpl->name . '_sidebar_width', '30'); ?>%;}
	#gk-mainbody-columns > section { width: <?php echo 100 - get_option($tpl->name . '_sidebar_width', '30'); ?>%; }
	<?php else : ?>
	#gk-mainbody-columns > section { width: 100%; }
	<?php endif; ?>
	
	@media (min-width: <?php echo get_option($tpl->name . '_tablet_width', '800') + 1; ?>px) {
		#gk-mainmenu-collapse { height: auto!important; }
	}
	
	<?php if(
		get_option($tpl->name . '_homepage_columns_amount', '2') == '1' ) : ?>
		.frontpage #gk-mainbody article {
			border-right: none!important;
			padding-top: 70px!important;
			padding-left: 0!important;
			padding-right: 0!important;
			width: 100%!important;
		}
		.frontpage #gk-mainbody article:first-child { padding-top: 50px!important; }
	<?php endif; ?>
</style>

<?php
// check the dependencies for the desktop.small.css file
if(get_option($tpl->name . "_shortcodes3_state", 'Y') == 'Y') {
     wp_enqueue_style('gavern-desktop-small', gavern_file_uri('css/desktop.small.css'), array('gavern-shortcodes-template'), false, '(max-width: '. get_option($tpl->name . '_theme_width', '1150') . 'px)');
} elseif(get_option($tpl->name . "_shortcodes2_state", 'Y') == 'Y') {
     wp_enqueue_style('gavern-desktop-small', gavern_file_uri('css/desktop.small.css'), array('gavern-shortcodes-elements'), false, '(max-width: '. get_option($tpl->name . '_theme_width', '1150') . 'px)');
} elseif(get_option($tpl->name . "_shortcodes1_state", 'Y') == 'Y') {
     wp_enqueue_style('gavern-desktop-small', gavern_file_uri('css/desktop.small.css'), array('gavern-shortcodes-typography'), false, '(max-width: '. get_option($tpl->name . '_theme_width', '1150') . 'px)');
} else {
     wp_enqueue_style('gavern-desktop-small', gavern_file_uri('css/desktop.small.css'), array('gavern-extensions'), false, '(max-width: '. get_option($tpl->name . '__theme_width', '1150') . 'px)');
}

// tablet.css is always loaded after the desktop.small.css file
wp_enqueue_style('gavern-tablet', gavern_file_uri('css/tablet.css'), array('gavern-extensions'), false, '(max-width: '. get_option($tpl->name . '_tablet_width', '1030') . 'px)');

// tablet.small.css is always loaded after the tablet.css file
wp_enqueue_style('gavern-tablet-small', gavern_file_uri('css/tablet.small.css'), array('gavern-tablet'), false, '(max-width: '. get_option($tpl->name . '_small_tablet_width', '820') . 'px)');

// mobile.css is always loaded after the tablet.small.css file
wp_enqueue_style('gavern-mobile', gavern_file_uri('css/mobile.css'), array('gavern-tablet-small'), false, '(max-width: '. get_option($tpl->name . '_mobile_width', '580') . 'px)');
