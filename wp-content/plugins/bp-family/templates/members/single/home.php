<?php
//var_dump("1113000");
/**
 * BuddyPress - Users Home
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

get_header( 'buddypress' ); ?>

	<div id="content">
	<?php
	
	//!!!	<div class="padder">
	//!!! <div class="buddypress">
?>
			<div id="buddypress">
			<?php do_action( 'bp_before_member_home_content' ); 
				$is_mobile = wp_is_mobile();
			?>

			<div id="item-header" role="complementary">
<?php //var_dump("1120"); ?>
				<?php 
				//locate_template( array( 'members/single/member-header.php' ), true ); 
				//bp_core_load_template( apply_filters( 'familys_template_my_familys', 'members/single/member-header' ) );
				//!!! begin head
				?>
<?php
//var_dump("1114");
/**
 * BuddyPress - Users Header
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<?php do_action( 'bp_before_member_header' ); ?>

<div id="item-header-avatar">
	<a href="<?php bp_displayed_user_link(); ?>">

		<?php bp_displayed_user_avatar( 'type=full' ); ?>

	</a>
</div><!-- #item-header-avatar -->

<div id="item-header-content">

	<h2>
		<a href="<?php bp_displayed_user_link(); ?>"><?php bp_displayed_user_fullname(); ?></a>
	</h2>

	<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
		<span class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></span>
	<?php endif; ?>

	<span class="activity"><?php bp_last_activity( bp_displayed_user_id() ); ?></span>

	<?php do_action( 'bp_before_member_header_meta' ); ?>

	<div id="item-meta">

		<?php if ( bp_is_active( 'activity' ) ) : ?>

			<div id="latest-update">

				<?php bp_activity_latest_update( bp_displayed_user_id() ); ?>

			</div>

		<?php endif; ?>

		<div id="item-buttons">

			<?php do_action( 'bp_member_header_actions' ); ?>

		</div><!-- #item-buttons -->

		<?php
		/***
		 * If you'd like to show specific profile fields here use:
		 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
		 */
		 do_action( 'bp_profile_header_meta' );

		 ?>

	</div><!-- #item-meta -->

</div><!-- #item-header-content -->

<?php do_action( 'bp_after_member_header' ); ?>

<?php do_action( 'template_notices' ); ?>

<?php //!!!end ?>
<?php //var_dump("1121"); ?>
			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="object-nav" role="navigation">
					<?php if( $is_mobile ): ?>
					<ul class="has_sub">
						<li class="has_sub"><a onclick="javascript:;">
							<?php bp_get_selected_user_nav_name(); ?>
						</a></li>
					</ul>
					<?php endif; ?>
					<ul class="sub">
<?php //var_dump("1122"); ?>
						<?php bp_get_displayed_user_nav(); ?>
<?php //var_dump("1123"); ?>
						<?php do_action( 'bp_member_options_nav' ); ?>
<?php //var_dump("1124"); ?>
					</ul>
				</div>
			</div><!-- #item-nav -->
<?php //var_dump("1125"); ?>
			<div id="item-body">

				<?php do_action( 'bp_before_member_body' );
//var_dump("1126");
				if ( bp_is_user_activity() || !bp_current_component() ) :
					locate_template( array( 'members/single/activity.php'  ), true );

				 elseif ( bp_is_user_blogs() ) :
					locate_template( array( 'members/single/blogs.php'     ), true );

				elseif ( bp_is_user_friends() ) :
					locate_template( array( 'members/single/friends.php'   ), true );

				elseif ( bp_is_user_familys() ) :
				bp_core_load_template( apply_filters( 'familys_template_my_familys', 'members/single/familys' ) );
					//locate_template( array( 'members/single/familys.php'   ), true );
					
				elseif ( bp_is_user_groups() ) :
					locate_template( array( 'members/single/groups.php'    ), true );

				elseif ( bp_is_user_messages() ) :
					locate_template( array( 'members/single/messages.php'  ), true );

				elseif ( bp_is_user_profile() ) :
					locate_template( array( 'members/single/profile.php'   ), true );

				elseif ( bp_is_user_forums() ) :
					locate_template( array( 'members/single/forums.php'    ), true );

				elseif ( bp_is_user_settings() ) :
					locate_template( array( 'members/single/settings.php'  ), true );

				elseif ( bp_is_user_notifications() ) :
					locate_template( array( 'members/single/notifications.php' ), true );

				// If nothing sticks, load a generic template
				else :
					locate_template( array( 'members/single/plugins.php'   ), true );

				endif;
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
