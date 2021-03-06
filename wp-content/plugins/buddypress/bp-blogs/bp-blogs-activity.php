<?php

/**
 * BuddyPress Blogs Activity.
 *
 * @package BuddyPress
 * @subpackage BlogsActivity
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register activity actions for the blogs component.
 *
 * @since BuddyPress (1.0.0)
 *
 * @global object $bp The BuddyPress global settings object.
 *
 * @return bool|null Returns false if activity component is not active.
 */
function bp_blogs_register_activity_actions() {
	global $bp;

	// Bail if activity is not active
	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	if ( is_multisite() ) {
		bp_activity_set_action( $bp->blogs->id, 'new_blog', __( 'New site created',        'buddypress' ) );
	}

	bp_activity_set_action( $bp->blogs->id, 'new_blog_post',    __( 'New post published',      'buddypress' ) );
	bp_activity_set_action( $bp->blogs->id, 'new_blog_comment', __( 'New post comment posted', 'buddypress' ) );

	do_action( 'bp_blogs_register_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'bp_blogs_register_activity_actions' );

/**
 * Record blog-related activity to the activity stream.
 *
 * @since BuddyPress (1.0.0)
 *
 * @see bp_activity_add() for description of parameters.
 * @global object $bp The BuddyPress global settings object.
 *
 * @param array $args {
 *     See {@link bp_activity_add()} for complete description of arguments.
 *     The arguments listed here have different default values from
 *     bp_activity_add().
 *     @type string $component Default: 'blogs'.
 * }
 * @return int|bool On success, returns the activity ID. False on failure.
 */
function bp_blogs_record_activity( $args = '' ) {
	global $bp;

	// Bail if activity is not active
	if ( ! bp_is_active( 'activity' ) )
		return false;

	$defaults = array(
		'user_id'           => bp_loggedin_user_id(),
		'action'            => '',
		'content'           => '',
		'primary_link'      => '',
		'component'         => $bp->blogs->id,
		'type'              => false,
		'item_id'           => false,
		'secondary_item_id' => false,
		'recorded_time'     => bp_core_current_time(),
		'hide_sitewide'     => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// Remove large images and replace them with just one image thumbnail
 	if ( !empty( $content ) )
		$content = bp_activity_thumbnail_content_images( $content, $primary_link, $r );

	if ( !empty( $action ) )
		$action = apply_filters( 'bp_blogs_record_activity_action', $action );

	if ( !empty( $content ) )
		$content = apply_filters( 'bp_blogs_record_activity_content', bp_create_excerpt( $content ), $content );

	// Check for an existing entry and update if one exists.
	$id = bp_activity_get_activity_id( array(
		'user_id'           => $user_id,
		'component'         => $component,
		'type'              => $type,
		'item_id'           => $item_id,
		'secondary_item_id' => $secondary_item_id
	) );

	return bp_activity_add( array( 'id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
}

/**
 * Delete a blog-related activity stream item.
 *
 * @since BuddyPress (1.0.0)
 *
 * @see bp_activity_delete() for description of parameters.
 * @global object $bp The BuddyPress global settings object.
 *
 * @param array $args {
 *     See {@link bp_activity_delete()} for complete description of arguments.
 *     The arguments listed here have different default values from
 *     bp_activity_add().
 *     @type string $component Default: 'blogs'.
 * }
 * @return bool True on success, false on failure.
 */
function bp_blogs_delete_activity( $args = true ) {
	global $bp;

	// Bail if activity is not active
	if ( ! bp_is_active( 'activity' ) )
		return false;

	$defaults = array(
		'item_id'           => false,
		'component'         => $bp->blogs->id,
		'type'              => false,
		'user_id'           => false,
		'secondary_item_id' => false
	);

	$params = wp_parse_args( $args, $defaults );
	extract( $params, EXTR_SKIP );

	bp_activity_delete_by_item_id( array(
		'item_id'           => $item_id,
		'component'         => $component,
		'type'              => $type,
		'user_id'           => $user_id,
		'secondary_item_id' => $secondary_item_id
	) );
}
