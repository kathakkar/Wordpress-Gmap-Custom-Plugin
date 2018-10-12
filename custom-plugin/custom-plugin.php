<?php
/**
* @package CustomPlugin
*/


/*
Plugin Name: Custom Plugin
Plugin URI: http://customplugin.com/plugin
Description: My Custom Plugin.
Version: 1.0.0
Author: Khushbu Thakkar
Author URI: http://customplugin.com/
License: GPLv2 or later
Text Domain: custom-plugin

*/

ini_set("display_errors",0);
defined('ABSPATH') or die("Hey, You cant access this files..!");

class CustomPlugin
{

	function __construct()
	{
		add_action('admin_enqueue_scripts',array($this,'enqueue'));
		add_action('wp_enqueue_scripts',array($this,'enqueue'));
		add_action('init',array($this,'custom_post_type'));
	}
	
	function activate()
	{
		//genrate CPT
		$this->custom_post_type();
		//flush rewrite rules
		flush_rewrite_rules();
	}
	
	function deactivate()
	{
		//flush rewrite rules
		flush_rewrite_rules();
	}
	
	function enqueue()
	{
		//enqueue all our scripts
		global $post;
		wp_enqueue_script( 'google-maps-native', "http://maps.google.com/maps/api/js?sensor=false&ver=1");
		wp_enqueue_style("gmaps-meta-box",plugins_url('/assets/style.css',__FILE__));
		wp_enqueue_script("gmaps-meta-box",plugins_url('/assets/maps.js',__FILE__));
		
		$helper = array(
    		'lat' => get_post_meta($post->ID,'lat',true),
    		'lng' => get_post_meta($post->ID,'lng',true),
			'zoomlevel' => get_post_meta($post->ID,'zoomlevel',true)
		);
		wp_localize_script('gmaps-meta-box','helper',$helper);	
	}
	
	function custom_post_type()
	{
		//Post type genration
		register_post_type('location',['public'=>true,'label'=>'Locations','supports' => array('title')]);
	}
	
	function add_embed_gmaps_meta_box() 
	{
		//Gmap metabox genration
		add_meta_box(
			'gmaps_embed_meta_box', // $id
			'Post Embed Google Maps(Drag marker to change location)', // $title
			'show_embed_gmaps_meta_box', // $callback
			'location', // $posttype
			'normal', // $context
			'high'); // $priority
					
			// Show Metabox Contents(callback function)
			function show_embed_gmaps_meta_box() {
						global $post;  
						$lat = get_post_meta($post->ID, 'lat', true);  
						$lng = get_post_meta($post->ID, 'lng', true); 
						$zoomlevel = get_post_meta($post->ID, 'zoomlevel', true); 
						$nonce = wp_create_nonce(basename(__FILE__));
					?>
					<div class="maparea" id="map-canvas"></div>
					
					<input type="hidden" name="glat" id="latitude" value="<?php echo $lat; ?>">
					<input type="hidden" name="glng" id="longitude" value="<?php echo $lng; ?>">
					<input type="hidden" name="gzoom" id="zoom" value="<?php echo $zoomlevel; ?>">
					<input type="hidden" name="custom_meta_box_nonce" value="<?php echo $nonce; ?>"> 
					<br /> 
					<b>Copy short code to use in Post Or Page</b> <br />
					<input type="text" value="[location id=<?php echo $post->ID ?>]" readonly name="shortcode" />
					<?php
			}
	}
	
	
	// Save Metaboxes.
	function save_embed_gmap($post_id) 
	{   
		
			// verify nonce
			if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__)))
				return $post_id;
				
			// check autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return $post_id;
				
			// check permissions
			if ('location' == $_POST['post_type']) {
				if (!current_user_can('edit_page', $post_id))
					return $post_id;
				} elseif (!current_user_can('edit_post', $post_id)) {
					return $post_id;
			}  
			
			 $oldlat = get_post_meta($post_id, "lat", true);
			
			$newlat = $_POST["glat"]; 
		
			if ($newlat != $oldlat) {
				update_post_meta($post_id, "lat", $newlat);
			} 
			$oldlng = get_post_meta($post_id, "lng", true);
			
			$newlng = $_POST["glng"]; 
			if ($newlng != $oldlng) {
				update_post_meta($post_id, "lng", $newlng);
			} 
			
			$oldzoom = get_post_meta($post_id, "zoomlevel", true);
			
			$newlng = $_POST["gzoom"]; 
			if ($newlng != $oldlng) {
				update_post_meta($post_id, "zoomlevel", $newlng);
			} 
		}
		function create_shortcode_for_location($atts)
		{
			//Shortcode genration for plugin
				$loop=new wp_Query(
					array(
						'post_type'=>'location',
						'orderby'=>'title'
					)
				);
				if($loop->have_posts())
				{
					$meta=get_post_meta($atts['id']);
					$lat=get_post_meta($atts['id'],'lat',true);
					$lng=get_post_meta($atts['id'],'lng',true);
					$output='<div class="maparea" id="map-canvas"></div>';
					$output.='<input type="hidden" name="glat" id="latitude" value="'.$lat.'">';
					$output.='<input type="hidden" name="glat" id="longitude" value="'.$lng.'">';	
				}
				return $output;
		}

}
//End of the class

//Object creation of class
if (class_exists('CustomPlugin')){
	$customPlugin=new CustomPlugin();	
}

//activation
register_activation_hook(__FILE__,array($customPlugin,'activate'));

//deactivation
register_activation_hook(__FILE__,array($customPlugin,'deactivate'));

//add custom gmap metabox
add_action('add_meta_boxes', array($customPlugin,'add_embed_gmaps_meta_box'));

//save custom metabox data
if(isset($_POST['custom_meta_box_nonce']))
{
	add_action('save_post', array($customPlugin,'save_embed_gmap'));
}

//shortcode genration of plugin
add_shortcode('location',array($customPlugin,'create_shortcode_for_location'));
?>