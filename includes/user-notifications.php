<?php
/**
 * User notification functions
 *
 * @package Achievements
 * @subpackage UserNotifications
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Sends a notification to a user when they unlock an achievement.
 *
 * @param object $achievement_obj The Achievement object to send a notification for.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param object $progress_obj The Progress object's ID.
 * @since 3.0
 */
function dpa_send_notification( $achievement_obj, $user_id, $progress_id ) {
	// Let other plugins easily bypass sending notifications.
	if ( ! apply_filters( 'dpa_send_notification', true, $achievement_obj, $user_id, $progress_id ) )
		return;

	// Create a notification for this user/achievement.
	dpa_new_notification( $user_id, $achievement_obj->ID );
}

/**
 * Add a new notification for the specified user
 *
 * @param int $user_id int Optional. The ID for the user.
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @since 3.0
 */
function dpa_new_notification( $user_id = 0, $post_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// Default to current post
	if ( empty( $post_id ) && is_single() )
		$post_id = get_the_ID();

	// No user or post ID to check
	if ( empty( $user_id ) || empty( $post_id ) )
		return;

	// Run an action for third-party plugins before we add the notification
	do_action( 'dpa_new_notification', $user_id, $post_id );

	// Get current notifications; the array is keyed by the achievement (post) ID.
	$notifications = dpa_get_user_notifications( $user_id );

	// Add the new notification - value is the timestamp in GMT
	$notifications[$post_id] = current_time( 'mysql', true );
	dpa_update_user_notifications( $notifications, $user_id );
}

/**
 * Clears any notifications for the specified user for the specified achievement.
 *
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @param int $user_id int Optional. The ID for the user.
 * @since 3.0
 */
function dpa_clear_notification( $post_id = 0, $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// Default to current post
	if ( empty( $post_id ) && is_single() )
		$post_id = get_the_ID();

	// No user or post ID to check
	if ( empty( $user_id ) || empty( $post_id ) )
		return;

	// The notifications array is keyed by the achievement (post) ID.
	$notifications = dpa_get_user_notifications( $user_id );

	// Is there a notification to clear?
	if ( ! isset( $notifications[$post_id] ) )
		return;

	// Run an action for third-party plugins before we clear the notification
	do_action( 'dpa_clear_notification', $post_id, $user_id );

	// Clear the notification
	unset( $notifications[$post_id] );
	dpa_update_user_notifications( $notifications, $user_id );
}