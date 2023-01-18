<?php
/**
 * Plugin Name: Custom Search Page
 * Description: Add page template with AJAX-based search engine
 * Version: 1.1
 * Text Domain: search
 * Domain Path: /lang/
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

add_action( 'wp', 'add_scripts' );
add_action( 'plugins_loaded', 'csp_loaded' );// set initial conditions.
add_action( 'wp_ajax_csp', 'search_posts_query_new' );// AJAX for registered users.
add_action( 'wp_ajax_nopriv_csp', 'search_posts_query_new' );// AJAX for unregistered users.

register_activation_hook( __FILE__, 'csp_activate' );
register_deactivation_hook( __FILE__, 'csp_deactivate' );// remove plugin actions.

// set 'settings' link to plugin's row on plugin page.
add_filter( 'plugin_action_links', 'add_plugin_link', 10, 2 );

require_once __DIR__ . '/includes/custom-search-page-options.php';// option page.
require_once __DIR__ . '/includes/csp-helpers.php';// add some functions.

function add_scripts() {
	if ( wp_basename( get_page_template_slug() ) === 'csp-template' ) {
		add_action( 'wp_enqueue_scripts', 'required_scripts' );
	}
}

function required_scripts() {
	wp_enqueue_style(
		'bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css'
	); // bootstrap.
	wp_enqueue_script(
		'search-js',
		plugins_url(
			'/js/custom-search-page-scripts.js',
			__FILE__
		), // js script path.
		array(
			'jquery', // JQuery core loaded first.
		),
		'1.0',
		true
	);
	$variables = array(
		'ajax_url' => admin_url( 'admin-ajax.php' ), // ajax solver.
	);
	wp_localize_script(
		'search-js',
		'custom_search_page',
		$variables
	);// AJAX-url to frontage into custom_search_page object.
	// it can be create nonce with wp_create_nonce() and add like variable in search_plugin object.
	// https://wordpress.stackexchange.com/questions/231797/what-is-nonce-and-how-to-use-it-with-ajax-in-wordpress.
}

function csp_activate() {
	// TODO some activation actions.
}

function csp_deactivate() {
	unload_textdomain( 'search' );
	delete_option( 'csp_options' );// delete from db.
	unregister_setting(
		'csp_options',
		'csp_options',
	);
}

function csp_loaded() {
	add_filter( 'theme_page_templates', 'add_csp_template' ); // set to template list.
	add_filter( 'template_include', 'set_csp_template', 1 );
	$text_domain_dir = dirname( plugin_basename( __FILE__ ) ) . '/lang/'; // localization.
	load_plugin_textdomain( 'search', false, $text_domain_dir );
}

// add template to dropdown lists of templates.
function add_csp_template( $templates ) {
	$templates['csp-template'] = __(
		'CSP Search Template',
		'search'
	); // plugin_dir_path( __FILE__ ) . 'search-plugin-template.php'.

	return $templates;
}

// override standard template for page if csp_template selected by user - proper decision!
function set_csp_template( $template ) {
	return ( ( basename( get_page_template_slug() ) === 'csp-template' ) ? plugin_dir_path( __FILE__ ) . 'custom-search-page-template.php' : $template );
}


function search_posts_query_new() {

	$number = 9;// elements on page.
	$column = 3;//number of column for formating output.

	check_ajax_referer( 'search_plugin', 'security' );// return 403 if not pass nonce verification.

	$search_list  = 0;
	$cat_name     = '';
	$url          = '';
	$paged        = '';
	$search_query = '';

	// get category slug by user selected from index.
	if ( isset( $_POST['search_list'] ) ) {
		$search_list = (int) sanitize_text_field( wp_unslash( $_POST['search_list'] ?? '0' ) );// page pagination number!
		if ( $search_list > 0 ) {
			$options  = get_option( 'csp_options' );// get all slugs from options.
			$cats     = explode( "\r\n", $options['filter_list'] );// make array of slug.
			$cat_name = $cats[ $search_list - 1 ];// get selected value from array by income index.
		}
	}


	if ( isset( $_POST['url'] ) ) {
		$url = sanitize_text_field( wp_unslash( $_POST['url'] ?? '' ) );// page url (need for correct pagination base!).
	}

	if ( isset( $_POST['page_number'] ) ) {
		$paged = sanitize_text_field( wp_unslash( $_POST['page_number'] ?? 1 ) );// page pagination number!
	}

	if ( isset( $_POST['search_query'] ) ) {
		$search_query = sanitize_text_field( wp_unslash( $_POST['search_query'] ) );// page title.
	}

	$args = array(
		'post_type'      => 'post',
		's'              => $search_query,
		'category_name'  => $cat_name,
		'posts_per_page' => $number,
		'paged'          => $paged,
		'order'          => 'DESC',
	);

	$query = new WP_Query();
	$posts = $query->query( $args );

// make 2d array layout for page.
	$formated = array();

	for ( $i = 0; $i < $number; $i += $column ) {
		$buf = array();
		for ( $j = 0; $j < $column; $j ++ ) {
			if ( isset( $posts[ $i + $j ] ) ) {
				$buf[] = $posts[ $i + $j ];
			} else {
				$buf[] = '';
			}
		}
		$formated[] = $buf;
	}

	$s = '<section id="search_result">';
	$s .= '<div class="container">';

	if ( 0 === count( $posts ) ) {
		$s .= '<div class="col text-center bg-warning p-3 m-2">' .
			  'NOTHING' .
			  '</div>';
	} else {
		// fill data from query to output array.
		foreach ( $formated as $v ) {
			$s .= '<div class="row">';
			foreach ( $v as $vv ) {
				if ( is_object( $vv ) ) {
					$d    = $vv->post_title;
					$link = get_permalink( $vv->ID );
				} else {
					$d    = 'nothing';
					$link = '#';
				}
				$s .= '<div class="col text-center bg-warning p-3 m-2">' .
					  '<a href = ' . $link . '>' . $d . '</a>' .
					  '</div>';
			}
			$s .= '</div>';
		}
	}

	$s .= '</div>';

	// pagination.

	$pagination_args = array(
		'base'      => $url . '%_%',// take it form ajax.
		// should use link to search page nor to ajax (ajax url use if base is empty).
		'format'    => '?position=%#%',
		'total'     => $query->max_num_pages,
		'current'   => $paged,
		'show_all'  => false,
		'end_size'  => 0,
		'mid_size'  => 1,
		'prev_next' => true,
		'prev_text' => __( '« Previous' ),
		'next_text' => __( 'Next »' ),
	);

	$add_args = array();

	// set user defined query string like get-parameter.- подумать нужно ли!
	if ( ! empty( $search_query ) ) {
		$add_args['search_query'] = $search_query;
	}

	if ( ! empty( $search_list ) ) {
		$add_args['search_list'] = $search_list;
	}

	$pagination_args['add_args'] = $add_args;

	// work only if set 'permalink structure' as 'Post name' in Settings/Permalinks in admin panel.
	$links = paginate_links( $pagination_args );

	if ( ! is_null( $links ) ) {
		$s .= '<div class="bg-warning text-center p-3 m-2">' . $links . '</div>';
	}

	$s .= '</section>';

	$data = array(
		'search_query' => $search_query,
		'search_list'  => $search_list,
		'html'         => $s,
	);

	wp_reset_postdata();
	wp_send_json_success( $data );

	die();

}


	// http:{host}/wp/wp-admin/options-general.php?page=custom-search-page.

function add_plugin_link( $plugin_actions, $plugin_file ): array {
	$new_actions      = array();
	$options_page_url = esc_url( admin_url( 'options-general.php?page=custom-search-page' ) );
	if ( 'custom-search-page/custom-search-page.php' === $plugin_file ) {
		$new_actions['cl_settings'] =
			'<a href="' . $options_page_url . '">' . __( 'Settings', 'search' ) . '</a>';
	}

	return array_merge( $new_actions, $plugin_actions );
}

