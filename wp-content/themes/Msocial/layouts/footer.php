<?php 
	
	/**
	 *
	 * Template footer
	 *
	 **/
	
	// create an access to the template main object
	global $tpl;
	
	// disable direct access to the file	
	defined('GAVERN_WP') or die('Access denied');
	
?>

		<footer id="gk-footer">
			<div class="gk-page">			
				<?php gavern_menu('footermenu', 'gk-footer-menu'); ?>
				
				<div class="gk-copyrights">
					<?php echo str_replace('\\', '', htmlspecialchars_decode(get_option($tpl->name . '_template_footer_content', ''))); ?>
				</div>
				
				<?php if(get_option($tpl->name . '_template_footer_logo', 'Y') == 'Y') : ?>
				<img src="<?php echo gavern_file_uri('images/gavernwp.png'); ?>" class="gk-framework-logo" alt="GavernWP" />
				<?php endif; ?>
			</div>
		</footer>
		
		<?php if(get_option($tpl->name . '_styleswitcher_state', 'Y') == 'Y') : ?>
		<div id="gk-style-area">
			<?php for($i = 0; $i < count($tpl->styles); $i++) : ?>
			<div class="gk-style-switcher-<?php echo $tpl->styles[$i]; ?>">
				<?php 
					$j = 1;
					foreach($tpl->style_colors[$tpl->styles[$i]] as $stylename => $link) : 
				?> 
				<a href="#<?php echo $link; ?>" id="gk-color<?php echo $j++; ?>"><?php echo $stylename; ?></a>
				<?php endforeach; ?>
			</div>
			<?php endfor; ?>
		</div>
		<?php endif; ?>
		
	</div> <!-- #gk-content-wrapper -->
</div> <!-- #gk-bg -->

<?php gk_load('login'); ?>

<?php gk_load('social'); ?>

<?php do_action('gavernwp_footer'); ?>

<?php 
	echo stripslashes(
		htmlspecialchars_decode(
			str_replace( '&#039;', "'", get_option($tpl->name . '_footer_code', ''))
		)
	); 
?>
<!--
<script src="http://code.jquery.com/jquery-2.1.1.js" type="text/javascript"></script>
-->
	<script type="text/javascript">
		var ar = jQuery("#wp-admin-bar-my-account-family a").html();
//		alert(ar);
		jQuery("#wp-admin-bar-my-account-family a").text("Families");
	</script>
<?php wp_footer(); ?>

<?php do_action('gavernwp_ga_code'); ?>

</body>
</html>
