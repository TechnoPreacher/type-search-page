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
//$options = get_option( 'tsp_options' );
//$list    = $options['pagination_type'];
//$list=explode("\r\n",$list);


?>


<div class="row ml-2">


    <label class="input-group-text" for="cat">cat</label>

<?php
$list = wp_dropdown_categories( [
	'taxonomy'   => PLUGIN_CONTENT_TYPE . '_cat',
	'multiple'   => true,
	//'selected'   => '1, 2', // selected terms…
	'hide_empty' => false,
    'echo'=>false,
    'class'=>'',
    'name'=>PLUGIN_CONTENT_TYPE.'_cat',
	//'value_field'       => 'term_id'
] );

$list=str_replace('<select','<select multiple class="col mr-3" size="1" ',$list);
//$list=str_replace('class=\'postform\'','',$list);


echo $list;

?>

    <label class="input-group-text" for="begin_date">From:</label>
    <input class="mr-1" type="date" id="begin_date" name="begin_date">

    <label class="input-group-text" for="end_date">To:</label>
    <input class="mr-3" type="date" id="end_date" name="end_date">

    <label class="input-group-text" for="search_query"><?php esc_attr_e( 'Query:', 'search' ); ?></label>
    <input class="col mr-3" type="text"  id="search_query">

    <button class="col" type="button" id="find_button"><?php esc_html_e( 'Find', 'search' ); ?></button>

</div>


<section id="search_result">
</section>

<?php

//query_posts(array(
  // 'post_type' => PLUGIN_CONTENT_TYPE,
//));


  get_footer();
