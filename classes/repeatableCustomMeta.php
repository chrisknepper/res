<?php

namespace medias;

//This class adds the UI and database-interaction which allows an arbitrary number of multiple meta fields, per post.
//It is the hero our plugin needs.

class repeatableCustomMeta {

	public $portfolioFields = array();

	public function __construct() {
		include('metaboxes/meta_box.php');
		$post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'];
		//Need a way to only do this on portfolio editing screen

			if(isset($_GET['post'])) {
				$post_id = $_GET['post'];
			}
			if(isset($_POST['post_ID'])) {
				$post_id = $_POST['post_ID'];
			}

			$field_groups = empty($post_id) ? array() : get_post_meta($post_id, 'item_field_groups', true);
			global $groupsToRender;
			//It's sort of stored in the DB now, but we need to be able to add them from the UI
			$defaultGroups = array(
				'medias_' . (time() + rand(1, 10)) => array(
					'label' => 'Main Slideshow',
					'kind' => 'maingallery',
					'inputs' => array(
						'images' => array(
							'label' => 'Gallery',
						    'desc'  => 'A big slideshow, suitable for prominent display.',
						    'id'    => 'gallery' . time(),
						    'type'  => 'repeatable',
						    'sanitizer' => array(
						        'image' => 'sanitize_file_name'
						    ),
						    'repeatable_fields' => array(
						        'image' => array(
						            'label' => 'Image',
						            'id' => 'image',
						            'type' => 'image'
						        )
						    )
						)
					)
				)
			);

			if(empty($field_groups)) {
				$groupsToRender = $defaultGroups;
				//Can't call update_post_meta when the plugin loads since $post isn't set yet
				//Wait until admin_head hook
				add_action('admin_head', array($this, 'updatePostMetaGroups'));
			}
			else {
				$groupsToRender = $field_groups;
			}

			foreach ($groupsToRender as $id => $group) {
				new \Custom_Add_Meta_Box($id, $group['label'], $group['inputs'], 'portfolio');
			}

			//Do these on every page load.
			add_filter('post_type_link', array($this, 'addTaxonomyToPermalinks'), 1, 2 );
			add_action('add_meta_boxes', array($this, 'addPortfolioFields'));
			add_action('admin_head', array($this,'addDashboardJs'), 100);

	}

	public function updatePostMetaGroups() {
		global $groupsToRender;
		global $post;
		update_post_meta($post->ID, 'item_field_groups', $groupsToRender);
	}

	//Link the JS we need to make Dashboard front-end stuff work correctly.
	public function addDashboardJs() {
		wp_enqueue_script('dropzone-js', plugins_url('dropzone.min.js', __FILE__));
		wp_enqueue_script('dashboard-js', plugins_url('dashboard.js', __FILE__), array('jquery')); //Register jQuery as a dependency of dashboard.js
		wp_enqueue_style('dashboard-css', plugins_url('dashboard.css', __FILE__));
	}

	//Properly display and link correct URLs in the Dashboard for items which use the "type" taxonomy.
	public function addTaxonomyToPermalinks($post_link, $id = 0) {
		$post = get_post($id);
		if ( is_object( $post ) && $post->post_type == 'portfolio' ){
			$terms = wp_get_object_terms( $post->ID, 'type' );
			if( $terms ){
				return str_replace( '%type%' , $terms[0]->slug , $post_link );
			}
		}
		return $post_link;
	}

	//Add our metaboxes to the editing interface.
	public function addPortfolioFields() {
		remove_meta_box( //Get rid of WP's default "Set Featured Image" metabox since we are using our own interface to set it
			'postimagediv',
			'portfolio',
			'side'
			);
		add_meta_box(
			'addmetaboxbutton',
			'Add Group',
			array($this,'renderAddMetabox'),
			'portfolio',
			'side'
			);
	}

	public function renderAddMetabox() { ?>
		<a href="#" class="button addGroupButton">Add Group</a>
		<div class="addGroupPicker">
			<div class="mediasGroupType" data-type="mainarea">
				<div style="width: 32px; height: 32px; background: #ccc;"></div>
				<span>Main Area</span>
			</div>
			<div class="mediasGroupType" data-type="gallery">
				<div style="width: 32px; height: 32px; background: #ccc;"></div>
				<span>Gallery</span>
			</div>
			<div class="mediasGroupType" data-type="mediumimagewithtext">
				<div style="width: 32px; height: 32px; background: #ccc;"></div>
				<span>Medium Image with Text</span>
			</div>
			<div class="mediasGroupType" data-type="largeimagewithtext">
				<div style="width: 32px; height: 32px; background: #ccc;"></div>
				<span>Large Image with Text</span>
			</div>
			<div class="mediasGroupType" data-type="mediumtext">
				<div style="width: 32px; height: 32px; background: #ccc;"></div>
				<span>Medium Text</span>
			</div>
		</div>
		<?php
	}
}
