<?php

$create_tax = function () {
	$tax_labels = array(
		'name'          => __( ucfirst( PLUGIN_ACRONYM ) . ' ' . ucfirst( PLUGIN_CONTENT_TYPE ) . '\'s Category',
			PLUGIN_TEXT_DOMAIN ),
		'singular_name' => __( 'Type', PLUGIN_TEXT_DOMAIN ),
		'search_items'  => __( 'Search', PLUGIN_TEXT_DOMAIN ),
		'all_items'     => __( 'All', PLUGIN_TEXT_DOMAIN ),
		'parent_item'   => __( 'Parent', PLUGIN_TEXT_DOMAIN ),

		'parent_item_colon' => __( 'Parent', PLUGIN_TEXT_DOMAIN ),
		'edit_item'         => __( 'Edit', PLUGIN_TEXT_DOMAIN ),
		'update_item'       => __( 'Update', PLUGIN_TEXT_DOMAIN ),
		'add_new_item'      => __( 'Add new', PLUGIN_TEXT_DOMAIN ),
		'new_item_name'     => __( 'New', PLUGIN_TEXT_DOMAIN ),
		'menu_name'         => __( 'Categories', PLUGIN_TEXT_DOMAIN ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $tax_labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,

		'rewrite'           => array( 'slug' => 'type' ),
	);
	register_taxonomy( PLUGIN_CONTENT_TYPE . '_cat', array( PLUGIN_CONTENT_TYPE ), $args );
};

$create_type = function () {
	$type_labels = array(
		'name'               => __( PLUGIN_ACRONYM . ' ' . PLUGIN_CONTENT_TYPE, PLUGIN_TEXT_DOMAIN ),
		'singular_name'      => __( PLUGIN_ACRONYM . ' ' . PLUGIN_CONTENT_TYPE, PLUGIN_TEXT_DOMAIN ),
		'menu_name'          => __( ucfirst( PLUGIN_ACRONYM ) . ' ' . ucfirst( PLUGIN_CONTENT_TYPE ),
			PLUGIN_TEXT_DOMAIN ),
		'name_admin_bar'     => __( PLUGIN_ACRONYM . ' ' . PLUGIN_CONTENT_TYPE, PLUGIN_TEXT_DOMAIN ),
		'add_new'            => __( 'Add' . ' ' . ucfirst( PLUGIN_CONTENT_TYPE ), PLUGIN_TEXT_DOMAIN ),
		'add_new_item'       => __( 'Add new ' . ' ' . ucfirst( PLUGIN_CONTENT_TYPE ), PLUGIN_TEXT_DOMAIN ),
		'new_item'           => __( 'New' . ' ' . ucfirst( PLUGIN_CONTENT_TYPE ), PLUGIN_TEXT_DOMAIN ),
		'edit_item'          => __( 'Edit' . ' ' . PLUGIN_CONTENT_TYPE, PLUGIN_TEXT_DOMAIN ),
		'view_item'          => __( 'View', PLUGIN_TEXT_DOMAIN ),
		'all_items'          => __( 'All', PLUGIN_TEXT_DOMAIN ),
		'search_items'       => __( 'Search', PLUGIN_TEXT_DOMAIN ),
		'parent_item_colon'  => __( 'Parent', PLUGIN_TEXT_DOMAIN ),
		'not_found'          => __( 'Not found', PLUGIN_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'Trash is empty', PLUGIN_TEXT_DOMAIN ),
	);
	$supports    = array(
		'title', // post title.
		'editor', // post content.
		//	'author', // post author.
		'thumbnail', // featured images.
		//	'excerpt', // post excerpt.
		//'custom-fields', // custom fields.
		//	'comments', // post comments.
		//	'revisions', // post revisions.
		//	'post-formats', // post formats.
		'status', // CUSTOM.
		'eventdate', // CUSTOM.
	);

	$args = array(
		'labels'              => $type_labels,
		'supports'            => $supports,
		'public'              => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_nav_menus'   => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-admin-appearance',
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'has_archive'         => true,
		'rewrite'             => array( 'slug' => PLUGIN_CONTENT_TYPE ),
		'query_var'           => true
	);
	register_post_type( PLUGIN_CONTENT_TYPE, $args );// register.

	flush_rewrite_rules();// restore CNC settings.
};


// подключаем функцию активации мета блока (add_meta_boxes) - нужно для вывода кастомных полей на странице "добавить" в админке.
$add_meta_boxes = function () {
	add_meta_box( 'extra_fields', __( PLUGIN_CONTENT_TYPE . ' fields', PLUGIN_TEXT_DOMAIN ), 'extra_fields_box_func',
		PLUGIN_CONTENT_TYPE, 'normal', 'high' );
};

function extra_fields_box_func( $post )// код блока (внешний вид на странице добавления события в админке).
{
	?>
    <div style="width:100%;height:100%;border:2px solid greenyellow;">

        <p> <?php _e( 'Статус ивента: ', PLUGIN_TEXT_DOMAIN );
			$mark_v = get_post_meta( $post->ID, 'status', 1 ); ?>
            <label>
                <input type="radio" name="extra[status]"
                       value="open" <?php checked( $mark_v, 'open' ); ?> /> open
            </label>
            <label>
                <input type="radio" name="extra[status]"
                       value="closed" <?php checked( $mark_v, 'closed' ); ?> />closed
            </label>
        </p>

        <p> <?php _e( 'Дата ивента: ', PLUGIN_TEXT_DOMAIN );
			$eventDate = get_post_meta( $post->ID, 'eventdate', 1 ); ?>
            <input type='date' name="extra[eventdate]"
                   value="<?= $eventDate ?>"/>
        </p>

        <input type="hidden" name="extra_fields_nonce"
               value="<?php echo wp_create_nonce( __FILE__ ); ?>"/>
    </div>
	<?php
}

function my_extra_fields_update( $post_id )//Сохрание маета-данных, при сохранении поста.
{
	// базовая проверка.
	if (
		empty( $_POST['extra'] )
		|| ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ )
		|| wp_is_post_autosave( $post_id )
		|| wp_is_post_revision( $post_id )
	) {
		return false;
	}
	// Все ОК! Теперь, нужно сохранить/удалить данные.
	$_POST['extra'] = array_map( 'sanitize_text_field', $_POST['extra'] ); // чистим все данные от пробелов по краям.
	foreach ( $_POST['extra'] as $key => $value ) {
		if ( empty( $value ) ) {
			delete_post_meta( $post_id, $key ); // удаляем поле если значение пустое.
			continue;
		}
		update_post_meta( $post_id, $key, $value ); // add_post_meta() работает автоматически.
	}

	return $post_id;
}

$manage_events_posts_columns = function ( $columns ) {// вывод значений мета-полей в общем списке в админке.
	$my_columns = [
		'status'    => __( 'status_', PLUGIN_TEXT_DOMAIN ),
		'eventdate' => __( 'date_', PLUGIN_TEXT_DOMAIN ),
	];
	array_pop( $columns );// удаляю дату создания записи о событии, для меня важнее метадата самого события.

	return $columns + $my_columns;
};

$manage_events_posts_custom_column = function ( $column_name
) {// Выводим контент (значение) для каждой из зарегистрированных колонок $column_name в списке событий в админке.
	$custom_fields = get_post_custom();

	if ( ! isset( $custom_fields[ $column_name ] ) ) {
		return;
	}

	$my_custom_field = $custom_fields[ $column_name ];

	$color = ''; // color.
	if ( $my_custom_field[0] == 'open' ) {
		$color = 'green';
	};
	if ( $my_custom_field[0] == 'closed' ) {
		$color = 'red';
	};
	echo( "  <p style=\"color:$color;\"> $my_custom_field[0] </p>" );
};

