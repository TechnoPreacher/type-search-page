<?php
/**
 * Template Name: type search page template
 * Template Post Type: page
 */

?>

<?php get_header(); ?>
<?php the_content(); ?>

<?php wp_nonce_field( PLUGIN_ACRONYM.'_nonce' ); ?>

<!-- another way is to create nonce with script localization. -->

<div class="input-group  p-4">

    <div class="input-group-prepend">
        <label class="input-group-text" for="search_list">Options</label>
    </div>
    <?php

	$options = get_option( 'csp_options' );
	$list = $options['filter_list'];
	$list=explode("\r\n",$list);
	?>
    <select class="custom-select" id="search_list">
        <option selected><?php _e('select...','search') ?></option>
		<?php foreach ($list as $key_list=>$value_list): ?>
            <option value="<?=$key_list?>"><?=$value_list?></option>
		<?php endforeach; ?>
    </select>

    <div class="input-group-prepend ml-5">
        <label class="input-group-text" for="search_query"><?php esc_attr_e( 'Search', 'search' ); ?></label>
    </div>
    <input type="text" class="form-control" id="search_query">

    <button class="ml-5" type="button" id="find_button"><?php esc_html_e( 'Find', 'search' ); ?></button>

</div>

<section id="search_result">

</section>

<?php get_footer(); ?>
