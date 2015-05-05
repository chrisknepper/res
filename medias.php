<?php
/*
Plugin Name: Res
Plugin URI: http://github.com/chrisknepper/res/
Description: A customizable portfolio post type for WordPress
Author: Chris Knepper
Version: 0.1
Author URI: http://chrisknepper.com/
*/
global $mediasClasses;
$mediasClasses = array('portfolioPostType','repeatableCustomMeta','metabox');

function importClasses() {
	//Loop through each class name in the above array and create a new instance of that class
	//Should correspond to a PHP file in the classes folder, and be in the "medias" namespace
	global $mediasClasses;
	foreach($mediasClasses as $class) {
		$namespacedClass = 'medias\\' . $class;
		$dir = '/classes/';
		$filename = dirname(__FILE__) . $dir . $class . '.php';

		if (!class_exists($namespacedClass)) {
			require($filename); //bring 'em out bring 'em out
		}

		$r = new ReflectionClass($namespacedClass); //PHP is magic
		$r->newInstanceArgs(); //Equivalent to "new Classname();"
	}
}

importClasses();

add_action('wp_ajax_mediasGroupOrder', 'saveMediasGroupOrder');
function saveMediasGroupOrder() {
	$postID = $_POST['postID'];
	$field_groups = get_post_meta($postID, 'item_field_groups', true);
	$response = $_POST['orderArray'];
	$newOrder = array();
	foreach ($response as $key => $value) { //Recreate the existing meta array with the new order
		$newOrder[$key] = $field_groups[$key];
		$newOrder[$key]['label'] = $value;
	}
	update_post_meta($postID, 'item_field_groups', $newOrder); //Save it
    die($response);
}

add_action('wp_ajax_mediasGetNewMetabox', 'mediasGetNewMetabox');
function mediasGetNewMetabox() {
	//We should also add the new meta group to postmeta and then call saveMediasGroupOrder
	$postID = $_POST['postID'];
	$field_groups = get_post_meta($postID, 'item_field_groups', true);
	$key = 'medias_' . (time() + rand(1, 10)); //Any better random methods than this?
	$newGroup = generateNewFieldGroup($_POST['type']); //Should get desired group type from $_POST here
	$field_groups[$key] = $newGroup;
	update_post_meta($postID, 'item_field_groups', $field_groups);
	$output = array();
	$outputLabels = array();
	//error_log('adding custom metaboxes to post with ID of ' . $postID);
	include('classes/metaboxes/meta_box.php');
	foreach ($newGroup['inputs'] as $input) {
		$outputLabels[] = $input['label'];
		ob_start();
		custom_meta_box_field($input);
		$output[] = ob_get_contents();
		ob_end_clean();
	}
	$return = array(
		'id' => $key,
		'outerLabel' => $newGroup['label'],
		'innerLabels' => $outputLabels,
		'table' => $output
		);
	die(json_encode($return));
}

add_action('wp_ajax_doingItWell', 'doingItWell');
function doingItWell() {
	$response = dealWithImageUpload($_POST['imageField'], intval($_POST['imageWidth']), intval($_POST['imageHeight']), true, intval($_POST['postID']));
    die($response);
}

function generateNewFieldGroup($type = 'mainarea') {
	$randID = time();
	switch($type) {
		case 'mainarea':
			return array(
				'label' => 'Main Information',
				'kind' => $type,
				'inputs' => array(
					'client' => array(
						'label'	=> 'Client',
						'desc'	=> 'Who was this work for?',
						'id'	=> 'client' . time(),
						'type'	=> 'text'
					),
					'project' => array(
						'label'	=> 'Project',
						'desc'	=> 'Was this part of a larger project?',
						'id'	=> 'project' . time(),
						'type'	=> 'text'
					),
					'role' => array(
						'label'	=> 'Role',
						'desc'	=> 'What was your title on this project?',
						'id'	=> 'role' . time(),
						'type'	=> 'text'
					),
					'links' => array(
						'label' => 'Links',
					    'desc'  => 'Links to the project.',
					    'id'    => 'links' . time(),
					    'type'  => 'repeatable',
					    'sanitizer' => array(
					        'title' => 'sanitize_text_field',
					        'url' => 'sanitize_text_field'
					    ),
					    'repeatable_fields' => array(
					        'title' => array(
					            'label' => 'Title',
					            'id' => 'title',
					            'type' => 'text'
					        ),
					        'url' => array(
					            'label' => 'URL',
					            'id' => 'url',
					            'type' => 'text'
					        ),
					    )
					)
				)
			);
		break;
		case 'gallery':
			return array(
				'label' => 'Gallery',
				'kind' => $type,
				'inputs' => array(
					'images' => array(
						'label' => 'Gallery',
					    'desc'  => 'A gallery of images.',
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
			);
		break;
		case 'mediumimagewithtext':
			return array(
				'label' => 'Medium Image with Text',
				'kind' => $type,
				'inputs' => array(
					'image' => array(
						'label'	=> 'Image',
						'desc'	=> 'An image.',
						'id'	=> 'image' . time(),
						'type'	=> 'image'
					),
					'text' => array(
						'label'	=> 'Text',
						'desc'	=> 'Text near the image.',
						'id'	=> 'text' . time(),
						'type'	=> 'textarea'
					)
				)
			);
		break;
		case 'largeimagewithtext':
			return array(
				'label' => 'Large Image with Text',
				'kind' => $type,
				'inputs' => array(
					'image' => array(
						'label'	=> 'Image',
						'desc'	=> 'An image.',
						'id'	=> 'image' . time(),
						'type'	=> 'image'
					),
					'text' => array(
						'label'	=> 'Text',
						'desc'	=> 'Text near the image.',
						'id'	=> 'text' . time(),
						'type'	=> 'textarea'
					)
				)
			);
		break;
		case 'mediumtext':
			return array(
				'label' => 'Simply Text',
				'kind' => $type,
				'inputs' => array(
					'text' => array(
						'label'	=> 'Text',
						'desc'	=> 'Text by itself.',
						'id'	=> 'text' . time(),
						'type'	=> 'textarea'
					)
				)
			);
		break;
	}
}

function dealWithImageUpload($field, $width = 500, $height = 500, $crop = true, $postID = 0) {
	$response = array();
	$response['success'] = false;
	if (!function_exists('wp_handle_upload')) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	}
	if (!fileExists('image')) {
		$response['reason'] = 'No file was uploaded.';
	}
	elseif(!fileSmallEnough('image', 1024*1024) || !fileIsImage('image')) {
		$response['reason'] = 'The file wasn\'t small enough or wasn\'t an image.';
	}
	elseif($resizedImage = resizeImage('image', $width, $height, $crop)) {
		if($newImage = transferToMediaLibrary($resizedImage)) {
			$img = wp_get_attachment_image_src($newImage, 'admin');
			$response['success'] = true;
			$response['image'] = $img[0];
			$response['imageID'] = $newImage;
			if($postID === 0) {
				update_option($field, $newImage);
			}
			else {
				$old = get_post_meta($postID, $field, true);
				error_log(print_r($_POST, true));
				if(is_array($old)) {
					$new = $old;
					$new[intval($_POST['metaRow'])] = array('image' => $newImage, 'caption' => $_POST['caption']);
				}
				else {
					$new = array(array('image' => $newImage, 'caption' => $_POST['caption']));
				}
				update_post_meta($postID, $field, $new, $old);
			}
		}
	}
	return json_encode($response);
}

//Save image (after we resize/manipulate it in other ways) to the WP Media Library
function transferToMediaLibrary($upload) {
	$fileRef = array('name' => $upload['name'], 'tmp_name' => $upload['path']);
	$id = media_handle_sideload($fileRef, 0); //media_handle_upload $_FILES only works with $_FILES indices which we can't use since we gotta resize first
	if(file_exists($fileRef['tmp_name'])) {
		unlink($fileRef['tmp_name']);
	}
	return $id;
}

function resizeImage($file, $width = 220, $height = 220, $crop = true) {
	$path = $_FILES[$file]['tmp_name'];
	$filename = array('name' => $_FILES[$file]['name']);
	$editor = wp_get_image_editor($path);
	if (!is_wp_error($editor)) {
		if($width > 0 && $height > 0) {
			$editor->resize($width, $height, $crop);
		}
		$image = $editor->save($path, $_FILES[$file]['type']);
		if(!is_wp_error($image)) {
			return array_merge($image, $filename);
		}
	}
	return false;
}

function fileExists($file) {
	if (!empty($_FILES) &&
		isset($_FILES[$file]) &&
		$_FILES[$file]['error'] !== UPLOAD_ERR_NO_FILE) {
			return true;
		}
		return false;
}

function fileSmallEnough($file, $size) {
	if($_FILES[$file]['size'] < $size && $_FILES[$file]['error'] === UPLOAD_ERR_OK) {
		return true;
	}
	return false;
}

function fileIsImage($file) {
	if(exif_imagetype($_FILES[$file]['tmp_name']) !== false) { //Make sure proper image headers are there (no fake files!)
		$type = $_FILES[$file]['type'];
		if(($type === 'image/jpeg') || ($type === 'image/png') || ($type === 'image/gif')) { //Old-school way to check, still good to do
			return true;
		}
	}
	return false;
}
