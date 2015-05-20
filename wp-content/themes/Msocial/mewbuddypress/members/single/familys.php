<?php
//var_dump("1090");
/**
 * BuddyPress - Users familys
 *
 * @package BuddyPress
 * @subpackage bp-legacy
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
switch ( bp_current_action() ) :

	// Home/My familys
	case 'my-familys' :
		do_action( 'bp_before_member_familys_content' ); ?>

		<div class="members familys">

			<?php bp_get_template_part( 'members/members-loop' ) ?>

		</div><!-- .members.familys -->

		<?php do_action( 'bp_after_member_familys_content' );
		break;

	case 'requests' :
		bp_get_template_part( 'members/single/familys/requests' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
