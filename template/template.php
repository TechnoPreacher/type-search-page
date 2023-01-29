<?php
	/**
	 * Template Name: type search page template
	 * Template Post Type: page
	 */
?>

<?php get_header(); ?>
<?php the_content(); ?>
<?php wp_nonce_field( PLUGIN_ACRONYM . '_nonce' ); ?>
    <!-- another way is to create nonce with script localization. -->

    <div class="row ml-2">
        <label class="input-group-text" for="cat">cat</label>
		<?php
			$list = wp_dropdown_categories( [
				'taxonomy'   => PLUGIN_CONTENT_TYPE . '_cat',
				'multiple'   => true,
				'hide_empty' => false,
				'echo'       => false,
				'class'      => '',
				'name'       => PLUGIN_CONTENT_TYPE . '_cat',
			] );
			$list = str_replace( '<select', '<select multiple class="col mr-3" size="1" ', $list );// multiply
			echo $list;
		?>

        <label class="input-group-text" for="begin_date">From:</label>
        <input class="mr-1" type="date" id="begin_date" name="begin_date">

        <label class="input-group-text" for="end_date">To:</label>
        <input class="mr-3" type="date" id="end_date" name="end_date">

        <label class="input-group-text" for="search_query"><?php esc_attr_e( 'Query:', 'search' ); ?></label>
        <input class="col mr-3" type="text" id="search_query">

        <button class="col mr-3" type="button" id="find_button"><?php esc_html_e( 'Find', 'search' ); ?></button>

    </div>

    <section id="search_result">
    </section>

<?php
	$options         = get_option( PLUGIN_ACRONYM . '_options' );// get all slugs from options.
	$pagination_type = $options['pagination_type'] ?? '';
	if ( 'more' == $pagination_type ):
		?>
        <div class="row ml-2">
            <button class="col m-3" type="button" id="load_more"><?php esc_html_e( 'Load more', 'search' ); ?></button>
        </div>
	<?php endif;

	get_footer();
