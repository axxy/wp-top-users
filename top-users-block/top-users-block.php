<?php
/**
 * Plugin Name:       Top Users Block
 * Description:       Display top users based on total order value.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       top-users-block
 *
 * @package Ahmedyahya
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function ahmedyahya_top_users_block_block_init() {
	register_block_type( __DIR__ . '/build/top-users-block' );
}
add_action( 'init', 'ahmedyahya_top_users_block_block_init' );

/**
 * Register REST API endpoint for fetching top users.
 */
function ahmedyahya_register_top_users_api() {
    register_rest_route( 'ahmedyahya/v1', '/top-users', array(
        'methods' => 'GET',
        'callback' => 'ahmedyahya_get_top_users',
        'permission_callback' => '__return_true'
    ) );
}
add_action( 'rest_api_init', 'ahmedyahya_register_top_users_api' );

/**
 * Callback function to get top users.
 */
function ahmedyahya_get_top_users( $request ) {
    $limit = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : 5;
	$limit = max(1, min($limit, 20)); // max of 20 users and min of 1 user

    $args = array(
        'meta_key' => 'total_order_value',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'number' => $limit
    );

    $user_query = new WP_User_Query( $args );
    $top_users = array();

    if ( ! empty( $user_query->results ) ) {
        foreach ( $user_query->results as $user ) {
            $top_users[] = array(
                'id' => $user->ID,
                'name' => $user->display_name,
                'total_order_value' => floatval( get_user_meta( $user->ID, 'total_order_value', true ) )
            );
        }
    }

    return rest_ensure_response( $top_users );
}
