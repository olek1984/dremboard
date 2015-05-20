<?php
/**
 * BuddyPress - Users Plugins Template
 *
 * 3rd-party plugins should use this template to easily add template
 * support to their plugins for the members component.
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */
 $is_mobile = wp_is_mobile();
?>

		<?php do_action( 'bp_before_member_plugin_template' ); ?>

		<?php if ( ! bp_is_current_component_core() ) : ?>

		<div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="subnav">
		    <?php if( $is_mobile ): ?>
			<ul class="has_sub">
				<li class="has_sub"><a onclick="javascript:;">
					<?php bp_get_selected_options_nav_name(); ?>
				</a></li>
			</ul>
			<?php endif; ?>
			<ul class="sub">
				<?php bp_get_options_nav(); ?>

				<?php do_action( 'bp_member_plugin_options_nav' ); ?>
			</ul>
		</div><!-- .item-list-tabs -->

		<?php endif; ?>

		<h3><?php do_action( 'bp_template_title' ); ?></h3>

		<?php do_action( 'bp_template_content' ); ?>

		<?php do_action( 'bp_after_member_plugin_template' ); ?>
