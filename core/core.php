<?php

	$wp_action = function () {
		if ( wp_basename( get_page_template_slug() ) === PLUGIN_ACRONYM . '-template' ) {
			add_action( 'wp_enqueue_scripts',
				function () {
					wp_enqueue_style(
						'bootstrap',
						'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css'
					); // bootstrap.
					wp_enqueue_script(
						PLUGIN_ACRONYM . '-js',
						plugins_url(
							'../js/scripts.js',
							__FILE__
						), // js script path.
						array(
							'jquery', // JQuery core loaded first.
						),
						'1.0',
						true
					);
					$variables = array(
						'plugin_acronym' => PLUGIN_ACRONYM,
						'ajax_url'       => admin_url( 'admin-ajax.php' ),
						// ajax solver.
						'current_page'   => 1,
						// need for indicate and re-set number of current page for "load more"-pagination
					);
					wp_localize_script(
						PLUGIN_ACRONYM . '-js',
						'obj',
						$variables
					);// AJAX-url to frontage into 'obj' object.
					// it can be create nonce with wp_create_nonce() and add like variable in search_plugin object.
					// https://wordpress.stackexchange.com/questions/231797/what-is-nonce-and-how-to-use-it-with-ajax-in-wordpress.
				}
			);
		}
	};


	$plugins_loaded_action = function () {

		add_filter( 'theme_page_templates',
			function ( $templates ) { // add template to dropdown lists of templates.
				$templates[ PLUGIN_ACRONYM . '-template' ] = __(
					'Type Review Search Template ' . PLUGIN_ACRONYM,
					PLUGIN_TEXT_DOMAIN
				);

				return $templates;
			}
		); // set to template list.
		add_filter( 'template_include',
			function ( $template
			) { // override standard template for page if {PLUGIN_ACRONYM} template selected by user - proper decision!
				return ( ( basename( get_page_template_slug() ) === PLUGIN_ACRONYM . '-template' ) ? plugin_dir_path( __FILE__ ) . '../template/template.php' : $template );
			}
			, 1 );
		$text_domain_dir = dirname( plugin_basename( __FILE__ ) ) . '../lang/'; // localization.
		load_plugin_textdomain( PLUGIN_TEXT_DOMAIN, false, $text_domain_dir );
	};


	$deactivation_hook_action = function () {
		unload_textdomain( PLUGIN_TEXT_DOMAIN );
		delete_option( PLUGIN_ACRONYM . '_options' );// delete from db.
		unregister_setting(
			PLUGIN_ACRONYM . '_options',
			PLUGIN_ACRONYM . '_options',
		);
		unregister_post_type( PLUGIN_CONTENT_TYPE );// delete custom content type.
	};


	$activation_hook_action = function () {
		// TODO some activation actions.
	};

	$plugin_action_links_action = function ( $plugin_actions, $plugin_file ): array {
		$new_actions      = array();
		$options_page_url = esc_url( admin_url( 'options-general.php?page=type-search-page' ) );
		$path_parts       = pathinfo( $plugin_file );
		$base             = $path_parts['filename'];

		if ( $base . '/' . $base . '.php' === $plugin_file ) {
			$new_actions['cl_settings'] =
				'<a href="' . $options_page_url . '">' . __( 'Settings', PLUGIN_TEXT_DOMAIN ) . '</a>';
		}

		return array_merge( $new_actions, $plugin_actions );
	};