<?php
//var_dump("1250");
/**
 * BuddyPress - Users familys
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); ?>

		<?php if ( !bp_is_current_action( 'requests' ) ) : ?>

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
	 locate_template( array( 'members/single/familys/requests.php' ), true );

else :
	do_action( 'bp_before_member_familys_content' ); ?>

	<div class="members familys">
<?php  //var_dump("1250"); ?>
		<?php locate_template( array( 'members/members-loop.php' ), true ); ?>
<?php  //var_dump("1252"); ?>
	</div><!-- .members.familys -->
<?php  //var_dump("1253"); ?>
	<?php do_action( 'bp_after_member_familys_content' ); ?>
<?php  //var_dump("1254"); ?>
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
