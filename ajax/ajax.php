<?php

$ajax_action = function () {
	$number = 4;// elements on page.
	check_ajax_referer( PLUGIN_ACRONYM . '_nonce', 'security' );// return 403 if not pass nonce verification.

	$options         = get_option( PLUGIN_ACRONYM . '_options' );// get all slugs from options.
	$format_of_view  = ! empty( $options['format_of_view'] ) ? $options['format_of_view'] : 'list';
	$pagination_type = ! empty( $options['pagination_type'] ) ? $options['pagination_type'] : 'digit';

	$url          = sanitize_text_field( wp_unslash( $_POST['url'] ?? '' ) );// page url (need for correct pagination base!).
	$paged        = sanitize_text_field( wp_unslash( $_POST['page_number'] ?? 1 ) );// page pagination number!
	$search_query = sanitize_text_field( wp_unslash( $_POST['search_query'] ) );// page title.

	if ( ! empty( $_POST['search_cats'] ) ) {
		$search_cats       = $_POST['search_cats'];
		$search_cats_array = array();
		foreach ( $search_cats as $cat ) {
			if ( ! empty( $cat ) ) {
				$search_cats_array[] = $cat;
			}
		}
	}

	$args = array(
		'post_type'      => PLUGIN_CONTENT_TYPE,
		'posts_per_page' => $number,
		'paged'          => $paged,
		'order'          => 'ASC', // 'DESC'.
	);

	$args['s'] = $search_query ?? '';

	if ( ! empty( $search_cats_array ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'review_cat',
				'field'    => 'term_id', // 'slug'.
				'terms'    => $search_cats_array, // array( 'cat1','cat2','cat...' ).
			),
		);
	}

	$begin_date = ! empty( $_POST['begin_date'] ) ? $_POST['begin_date'] : '';
	$end_date   = ! empty( $_POST['end_date'] ) ? $_POST['end_date'] : '';

	$after_date  = array();
	$before_date = array();

	if ( '' !== $begin_date ) {
		$begin_date_array = explode( '-', $begin_date );
		$after_date       = array( // после этой даты
			'year'  => $begin_date_array[0],
			'month' => $begin_date_array[1],
			'day'   => $begin_date_array[2],
		);
	}

	if ( '' !== $end_date ) {
		$end_date_array = explode( '-', $end_date );
		$before_date    = array( //
			'year'  => $end_date_array[0],
			'month' => $end_date_array[1],
			'day'   => $end_date_array[2],
		);
	}

	$args['date_query'] = array(
		array(
			'after'     => $after_date,
			'before'    => $before_date,
			'inclusive' => true
		)
	);

	// global $wp_query;
	$query       = new WP_Query();
	$posts       = $query->query( $args );
	$index       = 0;
	$html_output = '<section id="search_result">';

	foreach ( $posts as $post ) {
		$d     = $post->post_title;
		$link  = get_permalink( $post->ID );
		$image = get_the_post_thumbnail( $post->ID, array( 200, 200 ) );

		// grid formating.
		if ( 'grid' == $format_of_view ) {
			$index ++;
			$_row .= '<div class="col text-center bg-warning p-3 m-2">' .
			         '<p>' . $image . '</p>' .
			         '<p><a href = ' . $link . '>' . $d . '</a></p>' .
			         '</div>';
			if ( $index % 2 == 0 ) {
				$html_output .= '<div class="row">' . $_row . '</div>';
				$index       = 0;
				$_row        = '';
			}
		}

		// list formating.
		if ( 'list' == $format_of_view ) {
			$image       = get_the_post_thumbnail( $post->ID, array( 100, 100 ) );
			$content     = wp_trim_words( get_the_content( $link, false, $post->ID ), 5,
				'<a href = ' . $link . '>...</a>' );
			$html_output .= '<div class="row bg-dark p-1 m-1">' .
			                '<div class="col-auto text-center bg-warning">' . $image . '</div>' .
			                '<div class="col text-left bg-warning ml-1">' .
			                '<a href = ' . $link . '>' . $d . '</a>' .
			                '<p>' . $content . '</p>' .
			                '</div>' . '</div>';
		}
	}

	if ( '' !== $_row ) {// wrapping last odd col element as new row (if exists)
		$html_output .= '<div class="row">' . $_row . '</div>';
	}

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

// pagination.
	if ( 'digit' == $pagination_type ) {

		$add_args = array();

		// set user defined values like get-parameters in pagination's links.- think about it?

		if ( ! empty( $search_query ) ) {
			$add_args['search_query'] = $search_query;
		}

		if ( ! empty( $begin_date ) ) {
			$add_args['begin_date'] = $begin_date;
		}

		if ( ! empty( $end_date ) ) {
			$add_args['end_date'] = $end_date;
		}

		if ( ! empty( $search_cats ) ) {
			$add_args['search_cats'] = implode( ',', $search_cats_array );
		}

		$pagination_args['add_args'] = $add_args;
		// work only if set 'permalink structure' as 'Post name' in Settings/Permalinks in admin panel.
		$links = paginate_links( $pagination_args );
		$s     = '';
		if ( ! is_null( $links ) ) {
			$s .= '<div class="bg-light text-center p-3 m-2">' . $links . '</div>';
		}
		$html_output .= $s;
	}

	if ( 'more' == $pagination_type ) {
		$links = paginate_links( $pagination_args );
		$s     = '';
		//	if ( ! is_null( $links ) ) {
		//		$s .= '<div class="bg-light text-center p-3 m-2">' . $links . '</div>';
		//	}
//$s.='<input type = "text" id="num" value="22"';
	//	$s .= ' <button type="button" class="btn btn-primary" id="load_b">Load more...</button>';
		//$s.='<button onclick="location.href="http://www.example.com" type="button">www.example.com</button>';
		$html_output .= $s;
	}


	$html_output .= '</section>'; // make closing tag.

	$data = array(
		'search_query' => $search_query,
		'begin_date'   => $begin_date,
		'end_date'     => $end_date,
		'html'         => $html_output,
		'search_cats'  => implode( ',', $search_cats_array ), // make string.
	);

	wp_reset_postdata();
	wp_send_json_success( $data );
	die();

};