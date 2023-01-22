<?php

add_action( 'admin_menu', 'tsp_add_settings_page' );
add_action( 'admin_init', 'tsp_register_settings' );

function tsp_add_settings_page() {
	add_options_page(
		__( 'TSP Settings', PLUGIN_TEXT_DOMAIN ),
		__( 'type search page', PLUGIN_TEXT_DOMAIN ),
		'manage_options',
		'type-search-page',
		'tsp_settings_page'
	);
}

function tsp_settings_page() {
	echo( '<h1>' . esc_html( 'Type search page settings', PLUGIN_TEXT_DOMAIN ) . '</h1>' );
	?>
    <form action="options.php" method="post"> <!--must be options.php.-->
		<?php
		settings_fields( 'tsp_options' );
		do_settings_sections( 'tsp' );
		?>
        <input name="submit" class="button button-primary" type="submit"
               value="<?php esc_html_e( 'Save', PLUGIN_TEXT_DOMAIN ); ?>"/>
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
		__( 'Post type slug', PLUGIN_TEXT_DOMAIN ) . ' ("post" ' . __( 'if empty', 'search' ) . ')',
		'tsp_find_slug',
		'tsp',
		'tsp_settings'
	);

	add_settings_field(
		'pagination_type',
		__( 'Pagination type', PLUGIN_TEXT_DOMAIN ),
		'tsp_pagination_type',
		'tsp',
		'tsp_settings'
	);
}


function tsp_validate() {

	if ( empty( $_POST['contact'] ) ) {
		$newinput['find_slug'] = '1';
	} else {
		$newinput['find_slug'] = sanitize_text_field( trim( $_POST['contact'] ) );
	}

	if ( empty( $_POST['pagination'] ) ) {
		$newinput['pagination_type'] = '1';
	} else {
		$newinput['pagination_type'] = sanitize_text_field( trim( $_POST['pagination'] ) );
	}

	return $newinput;
}

function tsp_section_text() {
	esc_attr_e( 'Make some customize for search options', 'search' );
}

function tsp_pagination_type() {
	$options = get_option( 'tsp_options' );
	$digit_checked = '';
	$scroll_checked = '';
	$more_checked = '';
	if ( isset( $options['pagination_type'] ) ) {
		switch ( $options['pagination_type'] ) {
			case 'digit':
				$digit_checked = 'checked';
				break;
			case 'scroll':
				$scroll_checked = 'checked';
				break;
			case 'more':
				$more_checked = 'checked';
				break;
		}
	}
	$a = '
	<p><input type="radio" id="pag_view1" name="pagination" value="digit" '.$digit_checked.'>
    <label for="pag_view1">Digits</label></p>
    <p><input type="radio" id="pag_view2" name="pagination" value="scroll" '.$scroll_checked.'>
    <label for="pag_view2">Infinity scroll</label></p>
    </p><input type="radio" id="pag_view3" name="pagination" value="more" '.$more_checked.'>
    <label for="pag_view3">More</label></p>
    ';

	echo $a;
}

function tsp_find_slug() {
	$options = get_option( 'tsp_options' );

	$list_checked = '';
	$grid_checked = '';

	if ( isset( $options['find_slug'] ) ) {
		switch ( $options['find_slug'] ) {
			case 'list':
				$list_checked = 'checked';
				break;
			case 'grid':
				$grid_checked = 'checked';
				break;
		}
	}


	$a = '
	<p><input type="radio" id="post_view1" name="contact" value="list" '.$list_checked.'>
    <label for="post_view1">List</label></p>
    <p><input type="radio" id="post_view2" name="contact" value="grid" '.$grid_checked.'>
    <label for="post_view2">Grid</label></p>';

	//if ( isset( $options['find_slug'] ) ) {
	echo $a;//"<input id='find_slug' name='tsp_options[find_slug]' type='text' value='" . esc_attr( $options['find_slug'] ) . "' />";
	//} else {
	//	echo $a;// "<input id='find_slug' name='tsp_options[find_slug]' type='text' value='post'/>";
//	}
}
