<?php

namespace medias;

//This class defines the "Portfolio" custom post type within WordPress
//We also define the "type" custom taxonomy, which is integral to the functionality of the post type

class portfolioPostType {

	public $isActivating = false;

	public function __construct() {
		//Do this when the plugin is activated
		register_activation_hook(__FILE__, function() {
			$this->isActivating = true;
		});
		//Do these on every page load
		add_action('init', array($this, 'registerPostType'), 150);
		add_action( 'init', array($this, 'registerTypeTaxonomy'), 100);
	}

	public function registerPostType() {

		$labels = array(
			'name'                => 'Portfolio',
			'singular_name'       => 'Portfolio',
			'menu_name'           => 'Portfolio',
			'parent_item_colon'   => 'Parent Portfolio:',
			'all_items'           => 'Portfolio',
			'view_item'           => 'View Item',
			'add_new_item'        => 'Add New Item',
			'add_new'             => 'New Item',
			'edit_item'           => 'Edit Item',
			'update_item'         => 'Update Item',
			'search_items'        => 'Search Portfolio Items',
			'not_found'           => 'No Items Found',
			'not_found_in_trash'  => 'No Items Found in Trash',
		);
		$rewrite = array(
			'slug'                => 'portfolio',
			'with_front'          => true,
			'pages'               => true,
			'feeds'               => true,
		);
		$args = array(
			'label'               => 'portfolio',
			'description'         => 'Portfolio information pages',
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt'),
			//'taxonomies'          => array( 'type' ),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_icon'           => 'dashicons-media-interactive',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'page',
		);

		register_post_type( 'portfolio', $args );
		register_taxonomy_for_object_type('type', 'portfolio');
		add_theme_support('post-thumbnails');
		add_theme_support('menus');
		add_image_size( 'small', 380, 315, true );
		add_image_size( 'mid', 780, 650, true );
		add_image_size( 'hero', 1180, 650, true );
		add_image_size( 'admin', 200, 200, true );
	}

	public function registerTypeTaxonomy() {

		$labels = array(
			'name'                       => _x( 'type', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Type', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Types', 'text_domain' ),
			'all_items'                  => __( 'All Types', 'text_domain' ),
			'parent_item'                => __( 'Parent Type', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent Type:', 'text_domain' ),
			'new_item_name'              => __( 'New Type Name', 'text_domain' ),
			'add_new_item'               => __( 'Add New Type', 'text_domain' ),
			'edit_item'                  => __( 'Edit Type', 'text_domain' ),
			'update_item'                => __( 'Update Type', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate types with commas', 'text_domain' ),
			'search_items'               => __( 'Search types', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove types', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used types', 'text_domain' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'rewrite'                    => array('slug' => 'portfolio')
		);

		register_taxonomy( 'type', null, $args );

		if($this->isActivating) {
			//flush_rewrite_rules();
		}
	}

}
