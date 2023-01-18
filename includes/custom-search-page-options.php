<?php

add_action( 'admin_menu', 'csp_add_settings_page' );
add_action( 'admin_init', 'csp_register_settings' );

function csp_add_settings_page() {
	add_options_page(
		__( 'CSP Settings', 'search' ),
		__( 'Custom search page', 'search' ),
		'manage_options',
		'custom-search-page',
		'csp_settings_page'
	);
}

function csp_settings_page() {
	echo( '<h2>' . esc_html_e( 'Search settings', 'search' ) . '</h2>' );
	?>
    <form action="options.php" method="post"> <!--must be options.php.-->
		<?php
		settings_fields( 'csp_options' );
		do_settings_sections( 'search_plugin' );
		?>
        <input name="submit" class="button button-primary" type="submit"
               value="<?php esc_html_e( 'Save', 'search' ); ?>"/>
    </form>
	<?php
}

function csp_register_settings() {
	register_setting(
		'csp_options',
		'csp_options',
		'csp_validate'
	);

	add_settings_section(
		'settings',
		'',
		'csp_section_text',
		'search_plugin'
	);

	add_settings_field(
		'find_slug',
		__( 'Post type slug', 'search' ) . ' ("post" ' . __( 'if empty', 'search' ) . ')',
		'csp_find_slug',
		'search_plugin',
		'settings'
	);

	add_settings_field(
		'results_on_page',
		__( 'Results on page', 'search' ) . ' (5 ' . __( 'if empty', 'search' ) . ')',
		'csp_results_on_page',
		'search_plugin',
		'settings'
	);

	add_settings_field(
		'filter_list',
		__( 'Filter list', 'search' ) . __( ' (use enter key for separate values)', 'search' ),
		'csp_filter_list',
		'search_plugin',
		'settings'
	);
}


function csp_validate( $input ) {

	if ( empty( $input['find_slug'] ) ) {
		$newinput['find_slug'] = 'post';
	} else {
		$newinput['find_slug'] = sanitize_text_field( trim( $input['find_slug'] ) );
	}

	if ( empty( $input['results_on_page'] ) ) {
		$newinput['results_on_page'] = '5';
	} else {
		$newinput['results_on_page'] = sanitize_text_field( trim( $input['results_on_page'] ) );
	}

	if ( empty( $input['filter_list'] ) ) {
		$newinput['filter_list'] = '';
	} else {
		$newinput['filter_list'] = sanitize_textarea_field( $input['filter_list'] );//) ) );
	}

	return $newinput;
}

function csp_section_text() {
	esc_attr_e( 'Make some customize for search options', 'search' );
}

function csp_find_slug() {
	$options = get_option( 'csp_options' );
	if ( isset( $options['find_slug'] ) ) {
		echo "<input id='find_slug' name='csp_options[find_slug]' type='text' value='" . esc_attr( $options['find_slug'] ) . "' />";
	} else {
		echo "<input id='find_slug' name='csp_options[find_slug]' type='text' value='post'/>";
	}
}

function csp_results_on_page() {
	$options = get_option( 'csp_options' );
	if ( isset( $options['results_on_page'] ) ) {
		echo "<input id='results_on_page' name='csp_options[results_on_page]' type='text' value='" . esc_attr( $options['results_on_page'] ) . "' />";
	} else {
		echo "<input id='results_on_page' name='csp_options[results_on_page]' type='text' value='5'/>";
	}
}




function csp_filter_list() {
	$options = get_option( 'csp_options' );



	echo( "<textarea id='filter_list' name='csp_options[filter_list]' rows='4' cols='19'>" );
	if ( isset( $options['filter_list'] ) ) {
		echo esc_attr( $options['filter_list'] );
	}
	echo( "</textarea>" );


	$categories = get_terms( 'category', array(
		'orderby'    => 'count',
		'hide_empty' => 0,
	) );


    $s = 'Founded slugs:';

	foreach( $categories as $taxonomy ) {
		$s.= "<p>".urldecode($taxonomy->slug) . "</p>";
	}

	echo('<br>'. ($s) .'</br>');


}