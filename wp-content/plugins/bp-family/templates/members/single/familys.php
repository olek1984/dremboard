<?php
//var_dump("1250");
/**
 * BuddyPress - Users familys
 *
 * @package BuddyPress
 * @subpackage bp-default
 */
$is_mobile = wp_is_mobile();
?>

<div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="subnav"  role="navigation">
    <?php if( $is_mobile ): ?>
	<ul class="has_sub">
		<li class="has_sub"><a onclick="javascript:;">
			<?php bp_get_selected_options_nav_name(); ?>
		</a></li>
	</ul>
	<?php endif; ?>
	<ul class="sub">
		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); ?>
<?php
global $bp;
$a == bp_current_action();
//var_dump("1301");
//var_dump($a);
?>
		<?php if ( !bp_is_current_action( 'requests' ) ) : ?>
<?php
$a == bp_current_action();
//var_dump("123456");
//var_dump($a);
?>
			<li id="members-order-select" class="last filter">

				<label for="members-familys"><?php _e( 'Order By:', 'buddypress' ); ?></label>
				<select id="members-familys">
					<option value="active"><?php _e( 'Last Active', 'buddypress' ); ?></option>
					<option value="newest"><?php _e( 'Newest Registered', 'buddypress' ); ?></option>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'buddypress' ); ?></option>

					<?php do_action( 'bp_member_blog_order_options' ); ?>

				</select>
			</li>

		<?php endif; ?>

	</ul>
</div>


<?php

if ( bp_is_current_action( 'requests' ) ) :
	 //!!! locate_template( array( 'members/single/familys/requests.php' ), true );
	 bp_core_load_template( apply_filters( 'familys_template_my_familys', 'members/single/familys/requests' ) );

else :
	do_action( 'bp_before_member_familys_content' ); ?>

	<div class="members familys">
<?php // var_dump("1250"); ?>
		<?php 
		//!!! locate_template( array( 'members/members-loop.php' ), true ); 
		bp_core_load_template( apply_filters( 'familys_template_my_familys', 'members/members-loop' ) );
		?>
<?php // var_dump("1252"); ?>
	</div><!-- .members.familys -->
<?php  // var_dump("1253"); ?>
	<?php do_action( 'bp_after_member_familys_content' ); ?>
<?php  // var_dump("1254"); ?>
<?php endif; ?>
<?php 
//var_dump("1127");
				do_action( 'bp_after_member_body' ); ?>

			</div><!-- #item-body -->
<?php //var_dump("1128"); ?>
			<?php do_action( 'bp_after_member_home_content' ); ?>
<?php //var_dump("1129"); ?>
		</div><!-- .padder -->
	</div><!-- #content -->
<?php //var_dump("1130"); ?>
<?php get_sidebar( 'buddypress' ); ?>
<?php //var_dump("1131"); ?>
<?php get_footer( 'buddypress' ); ?>
<?php //var_dump("1132"); ?>
