<?php
/**
 * Plugin Name: Type Search Page
 * Description: Add page template with Review post type
 * Version: 1.1
 * Text Domain: search
 * Domain Path: /lang/
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// plugin identification constant.
define( 'PLUGIN_ACRONYM', 'tsp' );

require_once __DIR__ . '/includes/options.php';// option page.
require_once __DIR__ . '/includes/helpers.php';// add some functions.

add_action( 'wp', function () {
	if ( wp_basename( get_page_template_slug() ) === 'tsp-template' ) {
		add_action( 'wp_enqueue_scripts',
			function () {
				wp_enqueue_style(
					'bootstrap',
					'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css'
				); // bootstrap.
				wp_enqueue_script(
					'search-js',
					plugins_url(
						'/js/type-search-page-scripts.js',
						__FILE__
					), // js script path.
					array(
						'jquery', // JQuery core loaded first.
					),
					'1.0',
					true
				);
				$variables = array(
					'plugin_acronym' => PLUGIN_ACRONYM,
					'ajax_url'       => admin_url( 'admin-ajax.php' ), // ajax solver.
				);
				wp_localize_script(
					'search-js',
					'custom_search_page',
					$variables
				);// AJAX-url to frontage into custom_search_page object.
				// it can be create nonce with wp_create_nonce() and add like variable in search_plugin object.
				// https://wordpress.stackexchange.com/questions/231797/what-is-nonce-and-how-to-use-it-with-ajax-in-wordpress.
			}
		);
	}
} );

register_activation_hook( __FILE__, function () {
	// TODO some activation actions.
} );

register_deactivation_hook( __FILE__, function () {
	unload_textdomain( 'search' );
	delete_option( 'tsp_options' );// delete from db.
	unregister_setting(
		'tsp_options',
		'tsp_options',
	);
} );// remove plugin actions.

add_action( 'plugins_loaded', function () {
	add_filter( 'theme_page_templates',
		function ( $templates ) { // add template to dropdown lists of templates.
			$templates['tsp-template'] = __(
				'Type Review Search Template (tsp)',
				'search'
			); // plugin_dir_path( __FILE__ ) . 'search-plugin-template.php'.

			return $templates;
		}
	); // set to template list.
	add_filter( 'template_include',
		function ( $template
		) { // override standard template for page if tsp_template selected by user - proper decision!
			return ( ( basename( get_page_template_slug() ) === 'tsp-template' ) ? plugin_dir_path( __FILE__ ) . 'type-search-page-template.php' : $template );
		}
		, 1 );
	$text_domain_dir = dirname( plugin_basename( __FILE__ ) ) . '/lang/'; // localization.
	load_plugin_textdomain( 'search', false, $text_domain_dir );
} );

$ajax_routine = function () {

	$number = 2;// elements on page.
	$column = 3;//number of column for formating output.

	check_ajax_referer( 'tsp_nonce', 'security' );// return 403 if not pass nonce verification.

	$search_list  = 0;
	$cat_name     = '';
	$url          = '';
	$paged        = '';
	$search_query = '';

	// get category slug by user selected from index.
	if ( isset( $_POST['search_list'] ) ) {
		$search_list = (int) sanitize_text_field( wp_unslash( $_POST['search_list'] ?? '0' ) );// page pagination number!
		if ( $search_list > 0 ) {
			$options  = get_option( 'tsp_options' );// get all slugs from options.
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

};

add_action( 'wp_ajax_tsp', $ajax_routine );// AJAX for registered users.
add_action( 'wp_ajax_nopriv_tsp', $ajax_routine );// AJAX for unregistered users.

// http:{host}/wp/wp-admin/options-general.php?page=custom-search-page.
//http://w.local/wp/wp-admin/options-general.php?page=type-search-page

add_filter( 'plugin_action_links', // set 'settings' link to plugin's row on plugin page.
	function ( $plugin_actions, $plugin_file ): array {
		$new_actions      = array();
		$options_page_url = esc_url( admin_url( 'options-general.php?page=type-search-page' ) );
		if ( 'type-search-page/type-search-page.php' === $plugin_file ) {
			$new_actions['cl_settings'] =
				'<a href="' . $options_page_url . '">' . __( 'Settings', 'search' ) . '</a>';
		}

		return array_merge( $new_actions, $plugin_actions );
	},
	10,
	2
);
