<?php
/**
 * BuddyPress Widgets
 *
 * @package BuddyPress
 * @subpackage familys
 * @since BuddyPress (1.9.0)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the familys widget.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_familys_register_widgets() {
	if ( ! bp_is_active( 'familys' ) ) {
		return;
	}
//var_dump("1070");
	add_action( 'widgets_init', create_function( '', 'return register_widget("BP_Core_familys_Widget");' ) );
}
//!!!
//add_action( 'bp_register_widgets', 'bp_familys_register_widgets' );
add_action( 'widgets_init', create_function( '', 'return register_widget("BP_Core_familys_Widget");' ) );
//var_dump("1071");
/*** MEMBER familys WIDGET *****************/

/**
 * The User familys widget class.
 *
 * @since BuddyPress (1.9.0)
 */

class BP_Core_familys_Widget extends WP_Widget {

	/**
	 * Class constructor.
	 */
	function __construct() {
		$widget_ops = array(
			'description' => __( 'A dynamic list of recently active, popular, and newest familys of the displayed member.  Widget is only shown when viewing a member profile.', 'buddypress' ),
			'classname' => 'widget_bp_core_familys_widget buddypress widget',
		);
		//parent::__construct( false, $name = _x( '(BuddyPress) familys', 'widget name', 'buddypress' ), $widget_ops );
//var_dump("1060");
		// Set up the widget
		parent::WP_Widget(
			false,
			__( '(BuddyPress) familys', 'bp-familys' ),
			$widget_ops
		);
	}

	/**
	 * Display the widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance The widget settings, as saved by the user.
	 */
	function widget( $args, $instance ) {
		//var_dump("1055");
		extract( $args );

		if ( ! bp_displayed_user_id() ) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'bp_core_widget_familys-js', BP_PLUGIN_URL . "bp-familys/js/widget-familys{$min}.js", array( 'jquery' ), bp_get_version() );

		$user_id = bp_displayed_user_id();
		$link = trailingslashit( bp_displayed_user_domain() . bp_get_familys_slug() );
		$instance['title'] = sprintf( __( '%s&#8217;s familys', 'buddypress' ), bp_get_displayed_user_fullname() );

		if ( empty( $instance['family_default'] ) ) {
			$instance['family_default'] = 'active';
		}

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		$title = $instance['link_title'] ? '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a>' : esc_html( $title );

		echo $before_title . $title . $after_title;

		$members_args = array(
			'user_id'         => absint( $user_id ),
			'type'            => sanitize_text_field( $instance['family_default'] ),
			'max'             => absint( $instance['max_familys'] ),
			'populate_extras' => 1,
		);

		?>

		<?php if ( bp_has_members( $members_args ) ) : ?>
			<div class="item-options" id="familys-list-options">
				<a href="<?php bp_members_directory_permalink(); ?>" id="newest-familys" <?php if ( $instance['family_default'] == 'newest' ) : ?>class="selected"<?php endif; ?>><?php _e( 'Newest', 'buddypress' ) ?></a>
				| <a href="<?php bp_members_directory_permalink(); ?>" id="recently-active-familys" <?php if ( $instance['family_default'] == 'active' ) : ?>class="selected"<?php endif; ?>><?php _e( 'Active', 'buddypress' ) ?></a>
				| <a href="<?php bp_members_directory_permalink(); ?>" id="popular-familys" <?php if ( $instance['family_default'] == 'popular' ) : ?>class="selected"<?php endif; ?>><?php _e( 'Popular', 'buddypress' ) ?></a>
			</div>

			<ul id="familys-list" class="item-list">
				<?php while ( bp_members() ) : bp_the_member(); ?>
					<li class="vcard">
						<div class="item-avatar">
							<a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_avatar() ?></a>
						</div>

						<div class="item">
							<div class="item-title fn"><a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_name() ?></a></div>
							<div class="item-meta">
								<span class="activity">
								<?php
									if ( 'newest' == $instance['family_default'] )
										bp_member_registered();
									if ( 'active' == $instance['family_default'] )
										bp_member_last_active();
									if ( 'popular' == $instance['family_default'] )
										bp_member_total_family_count();
								?>
								</span>
							</div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
			<?php wp_nonce_field( 'bp_core_widget_familys', '_wpnonce-familys' ); ?>
			<input type="hidden" name="familys_widget_max" id="familys_widget_max" value="<?php echo absint( $instance['max_familys'] ); ?>" />
<?php //var_dump("1050");

?>
		<?php else: ?>
<?php // var_dump("1051");

?>
			<div class="widget-error">
				<?php _e( 'Sorry, no members were found.', 'buddypress' ); ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	/**
	 * Process a widget save.
	 *
	 * @param array $new_instance The parameters saved by the user.
	 * @param array $old_instance The paramaters as previously saved to the database.
	 * @return array $instance The processed settings to save.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['max_familys']    = absint( $new_instance['max_familys'] );
		$instance['family_default'] = sanitize_text_field( $new_instance['family_default'] );
		$instance['link_title']	    = (bool) $new_instance['link_title'];

		return $instance;
	}

	/**
	 * Render the widget edit form.
	 *
	 * @param array $instance The saved widget settings.
	 */
	function form( $instance ) {
		$defaults = array(
			'max_familys' 	 => 5,
			'family_default' => 'active',
			'link_title' 	 => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$max_familys 	= $instance['max_familys'];
		$family_default = $instance['family_default'];
		$link_title	= (bool) $instance['link_title'];
		?>

		<p><label for="<?php echo $this->get_field_name( 'link_title' ) ?>"><input type="checkbox" name="<?php echo $this->get_field_name('link_title') ?>" value="1" <?php checked( $link_title ) ?> /> <?php _e( 'Link widget title to Members directory', 'buddypress' ) ?></label></p>

		<p><label for="bp-core-widget-familys-max"><?php _e( 'Max familys to show:', 'buddypress' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_familys' ); ?>" name="<?php echo $this->get_field_name( 'max_familys' ); ?>" type="text" value="<?php echo absint( $max_familys ); ?>" style="width: 30%" /></label></p>

		<p>
			<label for="bp-core-widget-familys-default"><?php _e( 'Default familys to show:', 'buddypress' ); ?>
			<select name="<?php echo $this->get_field_name( 'family_default' ) ?>">
				<option value="newest" <?php selected( $family_default, 'newest' ); ?>><?php _e( 'Newest', 'buddypress' ) ?></option>
				<option value="active" <?php selected( $family_default, 'active' );?>><?php _e( 'Active', 'buddypress' ) ?></option>
				<option value="popular"  <?php selected( $family_default, 'popular' ); ?>><?php _e( 'Popular', 'buddypress' ) ?></option>
			</select>
			</label>
		</p>

	<?php
	}
}

/** Widget AJAX ***************************************************************/

/**
 * Process AJAX pagination or filtering for the familys widget.
 *
 * @since BuddyPress (1.9.0)
 */
function bp_core_ajax_widget_familys() {

	check_ajax_referer( 'bp_core_widget_familys' );

	switch ( $_POST['filter'] ) {
		case 'newest-familys':
			$type = 'newest';
			break;

		case 'recently-active-familys':
			$type = 'active';
			break;

		case 'popular-familys':
			$type = 'popular';
			break;
	}

	$members_args = array(
		'user_id'         => bp_displayed_user_id(),
		'type'            => $type,
		'max'             => absint( $_POST['max-familys'] ),
		'populate_extras' => 1,
	);

	if ( bp_has_members( $members_args ) ) : ?>
		<?php echo '0[[SPLIT]]'; // return valid result. TODO: remove this. ?>
		<?php while ( bp_members() ) : bp_the_member(); ?>
			<li class="vcard">
				<div class="item-avatar">
					<a href="<?php bp_member_permalink() ?>"><?php bp_member_avatar() ?></a>
				</div>

				<div class="item">
					<div class="item-title fn"><a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_name() ?></a></div>
					<?php if ( 'active' == $type ) : ?>
						<div class="item-meta"><span class="activity"><?php bp_member_last_active() ?></span></div>
					<?php elseif ( 'newest' == $type ) : ?>
						<div class="item-meta"><span class="activity"><?php bp_member_registered() ?></span></div>
					<?php elseif ( bp_is_active( 'familys' ) ) : ?>
						<div class="item-meta"><span class="activity"><?php bp_member_total_family_count() ?></span></div>
					<?php endif; ?>
				</div>
			</li>
		<?php endwhile; ?>

	<?php else: ?>
		<?php echo "-1[[SPLIT]]<li>"; ?>
		<?php _e( 'There were no members found, please try another filter.', 'buddypress' ) ?>
		<?php echo "</li>"; ?>
	<?php endif;
}
add_action( 'wp_ajax_widget_familys', 'bp_core_ajax_widget_familys' );
add_action( 'wp_ajax_nopriv_widget_familys', 'bp_core_ajax_widget_familys' );
