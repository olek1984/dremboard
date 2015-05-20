<?php
//var_dump("9113");
/**
 * BuddyPress - Users Home
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

get_header( 'buddypress' ); ?>

	<div id="content">
	<?php
		//<div class="padder">
?>
		<div class="buddypress">

			<?php do_action( 'bp_before_member_home_content' ); ?>

			<div id="item-header" role="complementary">
<?php //var_dump("1120"); ?>
				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>
<?php //var_dump("1121"); ?>
			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
					<ul>
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
				//bp_core_load_template( apply_filters( 'familys_template_my_familys', 'members/single/familys' ) );
					locate_template( array( 'members/single/familys.php'   ), true );
					
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
