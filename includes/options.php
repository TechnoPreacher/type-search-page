<?php

add_action( 'admin_menu', 'tsp_add_settings_page' );
add_action( 'admin_init', 'tsp_register_settings' );

function tsp_add_settings_page() {
	add_options_page(
		__( 'TSP Settings', 'search' ),
		__( 'type search page', 'search' ),
		'manage_options',
		'type-search-page',
		'tsp_settings_page'
	);
}

function tsp_settings_page() {
	echo( '<h1>' . esc_html( 'Type search page settings', 'search' ) . '</h1>' );
	?>
    <form action="options.php" method="post"> <!--must be options.php.-->
		<?php
		settings_fields( 'tsp_options' );
		do_settings_sections( 'tsp' );
		?>
        <input name="submit" class="button button-primary" type="submit"
               value="<?php esc_html_e( 'Save', 'search' ); ?>"/>
    </form>
	<?php
}

function tsp_register_settings() {
	register_setting(
		'tsp_options',
		'tsp_options',
		'tsp_validate'
	);

	add_settings_section(
		'tsp_settings',
		'',
		'tsp_section_text',
		'tsp'
	);

	add_settings_field(
		'find_slug',
		__( 'Post type slug', 'search' ) . ' ("post" ' . __( 'if empty', 'search' ) . ')',
		'tsp_find_slug',
		'tsp',
		'tsp_settings'
	);




}


function tsp_validate( $input ) {

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

function tsp_section_text() {
	esc_attr_e( 'Make some customize for search options', 'search' );
}

function tsp_find_slug() {
	$options = get_option( 'tsp_options' );
	if ( isset( $options['find_slug'] ) ) {
		echo "<input id='find_slug' name='tsp_options[find_slug]' type='text' value='" . esc_attr( $options['find_slug'] ) . "' />";
	} else {
		echo "<input id='find_slug' name='tsp_options[find_slug]' type='text' value='post'/>";
	}
}

function tsp_results_on_page() {
	$options = get_option( 'tsp_options' );
	if ( isset( $options['results_on_page'] ) ) {
		echo "<input id='results_on_page' name='tsp_options[results_on_page]' type='text' value='" . esc_attr( $options['results_on_page'] ) . "' />";
	} else {
		echo "<input id='results_on_page' name='tsp_options[results_on_page]' type='text' value='5'/>";
	}
}




function tsp_filter_list() {
	$options = get_option( 'tsp_options' );



	echo( "<textarea id='filter_list' name='tsp_options[filter_list]' rows='4' cols='19'>" );
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