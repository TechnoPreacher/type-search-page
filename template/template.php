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


<?php
$now_date = date( "Y-m-d" );
$now_date = "";
?>

<?php
//$options = get_option( 'tsp_options' );
//$list    = $options['pagination_type'];
//$list=explode("\r\n",$list);
?>





<?php


$list = wp_dropdown_categories( [
	'taxonomy'   => 'category',
	'multiple'   => true,
	'selected'   => '1, 2', // selected terms…
	'hide_empty' => false,
] );

$list=str_replace('<select','<select multiple ',$list);
echo $list;
?>



    <label class="input-group-text" for="search_list">Options</label>

    <select class="custom-select mr-5" id="search_list">
        <option selected><?php _e( 'select...', 'search' ) ?></option>
		<?php //foreach ($list as $key_list=>$value_list):
		?>
        <option value="<?= $list ?>"><?= $list ?></option>
		<?php //endforeach;
		?>
    </select>


    <label class="input-group-text" for="begin_date">From:</label>
    <input class="mr-2" type="date" id="begin_date" name="begin_date" value="<?= $now_date ?>">

    <label class="input-group-text" for="end_date">To:</label>
    <input class="mr-5" type="date" id="end_date" name="end_date" value="<?= $now_date ?>">


    <div class="input-group-prepend ml-5">
        <label class="input-group-text" for="search_query"><?php esc_attr_e( 'Search', 'search' ); ?></label>
    </div>
    <input type="text" class="form-control" id="search_query">
    <button class="ml-5" type="button" id="find_button"><?php esc_html_e( 'Find', 'search' ); ?></button>



<section id="search_result">
</section>

<?php

query_posts(array(
   'post_type' => PLUGIN_CONTENT_TYPE,
)); ?>
<?php
while (have_posts()) : the_post(); ?>
<h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
<p><?php the_excerpt(); ?></p>

<?php endwhile;
get_footer(); ?>
