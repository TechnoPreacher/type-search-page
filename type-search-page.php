<?php
/**
 * Plugin Name: Type Search Page
 * Description: Add page template with Review post type
 * Version: 1.1
 * Text Domain: tsp_domain
 * Domain Path: /lang/
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */


// plugin identification constant.
define( 'PLUGIN_ACRONYM', 'tsp' ); // must be unique!
define( 'PLUGIN_TEXT_DOMAIN', 'tsp_domain' ); // text domain
define( 'PLUGIN_CONTENT_TYPE', 'review' );

require_once __DIR__ . '/core/core.php';// deafault actions & hooks.
require_once __DIR__ . '/ajax/ajax.php';// ajax routine.
require_once __DIR__ . '/inc/options.php';// option page.
require_once __DIR__ . '/inc/helpers.php';// add some functions.
require_once __DIR__ . '/content-type/review-content-type.php';// add custom content type.

global $wp_action,
	   $plugins_loaded_action,
	   $deactivation_hook_action,
	   $activation_hook_action,
	   $ajax_action,
	   $plugin_action_links_action,

		// custom content type.
	   $manage_events_posts_columns,
	   $manage_events_posts_custom_column,
	   $add_meta_boxes,
	   $create_tax,
	   $create_type;

register_activation_hook( __FILE__, $activation_hook_action );
register_deactivation_hook( __FILE__, $deactivation_hook_action );// remove plugin actions.

add_action( 'wp', $wp_action );
add_action( 'plugins_loaded', $plugins_loaded_action );
add_action( 'wp_ajax_' . PLUGIN_ACRONYM, $ajax_action );// AJAX for registered users.
add_action( 'wp_ajax_nopriv_' . PLUGIN_ACRONYM, $ajax_action );// AJAX for unregistered users.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), $plugin_action_links_action, 10,
	2 );// set 'settings' link to plugin's row on plugin page.

add_action( 'add_meta_boxes', $add_meta_boxes, 1 );//кастомные поля
add_action( 'init', $create_tax );// categories.
add_action( 'init', $create_type );// custom content type.
add_action( 'save_post', 'my_extra_fields_update', 0 ); // включаем обновление полей при сохранении

// custom content type.
add_filter( 'manage_'.PLUGIN_CONTENT_TYPE.'_posts_columns', $manage_events_posts_columns );
add_action( 'manage_'.PLUGIN_CONTENT_TYPE.'_posts_custom_column', $manage_events_posts_custom_column );
