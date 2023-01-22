<?php

$ajax_action = function () {
	$number = 4;// elements on page.
	$column = 4;//number of column for formating output.
	check_ajax_referer( PLUGIN_ACRONYM . '_nonce', 'security' );// return 403 if not pass nonce verification.
	$search_list  = 0;
	$cat_name     = '';
	$url          = '';
	$paged        = '';
	$search_query = '';
	// get category slug by user selected from index.
	if ( isset( $_POST['search_list'] ) ) {
		$search_list = (int) sanitize_text_field( wp_unslash( $_POST['search_list'] ?? '0' ) );// page pagination number!
		if ( $search_list > 0 ) {
			$options  = get_option( PLUGIN_ACRONYM . '_options' );// get all slugs from options.
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
	$args  = array(
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
		$s .= '<div class="col text-center bg-light p-3 m-2">' .
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
				$s .= '<div class="col text-center bg-light p-3 m-2">' .
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
		$s .= '<div class="bg-light text-center p-3 m-2">' . $links . '</div>';
	}
	$s    .= '</section>';
	$data = array(
		'search_query' => $search_query,
		'search_list'  => $search_list,
		'html'         => $s,
	);
	wp_reset_postdata();
	wp_send_json_success( $data );
	die();
};