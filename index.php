<?php 
/*
Plugin Name: Bookmark it
Plugin URI:  https://github.com/
Description: Because simple bookmarking should be simple
Version:     1.0
Author:      Tom Woodward
Author URI:  https://bionicteaching.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// add_action('wp_enqueue_scripts', 'prefix_load_scripts');

// function prefix_load_scripts() {                           
//     $deps = array('jquery');
//     $version= '1.0'; 
//     $in_footer = true;    
//     wp_enqueue_script('prefix-main-js', plugin_dir_url( __FILE__) . 'js/prefix-main.js', $deps, $version, $in_footer); 
//     wp_enqueue_style( 'prefix-main-css', plugin_dir_url( __FILE__) . 'css/prefix-main.css');
// }



//bookmark custom post type

// Register Custom Post Type bookmark
// Post Type Key: bookmark

function bmi_create_bookmark_cpt() {

  $labels = array(
    'name' => __( 'Bookmarks', 'Post Type General Name', 'textdomain' ),
    'singular_name' => __( 'Bookmark', 'Post Type Singular Name', 'textdomain' ),
    'menu_name' => __( 'Bookmark', 'textdomain' ),
    'name_admin_bar' => __( 'Bookmark', 'textdomain' ),
    'archives' => __( 'Bookmark Archives', 'textdomain' ),
    'attributes' => __( 'Bookmark Attributes', 'textdomain' ),
    'parent_item_colon' => __( 'Bookmark:', 'textdomain' ),
    'all_items' => __( 'All Bookmarks', 'textdomain' ),
    'add_new_item' => __( 'Add New Bookmark', 'textdomain' ),
    'add_new' => __( 'Add New', 'textdomain' ),
    'new_item' => __( 'New Bookmark', 'textdomain' ),
    'edit_item' => __( 'Edit Bookmark', 'textdomain' ),
    'update_item' => __( 'Update Bookmark', 'textdomain' ),
    'view_item' => __( 'View Bookmark', 'textdomain' ),
    'view_items' => __( 'View Bookmarks', 'textdomain' ),
    'search_items' => __( 'Search Bookmarks', 'textdomain' ),
    'not_found' => __( 'Not found', 'textdomain' ),
    'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
    'featured_image' => __( 'Featured Image', 'textdomain' ),
    'set_featured_image' => __( 'Set featured image', 'textdomain' ),
    'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
    'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
    'insert_into_item' => __( 'Insert into bookmark', 'textdomain' ),
    'uploaded_to_this_item' => __( 'Uploaded to this bookmark', 'textdomain' ),
    'items_list' => __( 'Bookmark list', 'textdomain' ),
    'items_list_navigation' => __( 'Bookmark list navigation', 'textdomain' ),
    'filter_items_list' => __( 'Filter Bookmark list', 'textdomain' ),
  );
  $args = array(
    'label' => __( 'bookmark', 'textdomain' ),
    'description' => __( '', 'textdomain' ),
    'labels' => $labels,
    'menu_icon' => '',
    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields', 'thumbnail',),
    'taxonomies' => array('category', 'post_tag'),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 5,
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
    'menu_icon' => 'dashicons-universal-access-alt',
  );
  register_post_type( 'bookmark', $args );
  
  // flush rewrite rules because we changed the permalink structure
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}
add_action( 'init', 'bmi_create_bookmark_cpt', 0 );

/**
 * Plugin Name: Press-This Custom Post Type And Taxonomy Term
 * Plugin URI:  http://wordpress.stackexchange.com/a/192065/26350
 * from https://wordpress.stackexchange.com/questions/192059/save-press-this-posts-in-a-custom-post-type
 */
add_filter( 'press_this_save_post', function( $data )
{
  
  $pattern = '/Source: <em><a href=."([^"]+)"/';//regex to get source URL

   // Using preg_match to extract the URL
   if (preg_match($pattern, $data['post_content'], $matches)) {
       $url = $matches[1]; // The URL will be captured in the first capturing group
       //write_log( "Extracted URL: " . $url);
       update_post_meta( $data['ID'], 'press-it-url', $url );//write source URL to custom field 
   } else {
       //write_log("No URL found.");
   }

    //---------------------------------------------------------------
    //
    $new_cpt    = 'bookmark';              // new post type   
    //---------------------------------------------------------------

    $post_object = get_post_type_object( $new_cpt );

    // Change the post type if current user can
    if( 
           isset( $post_object->cap->create_posts ) 
        && current_user_can( $post_object->cap->create_posts ) 
    ) 
        $data['post_type']  = $new_cpt;


    return $data;

}, 999 );


//overwrite normal post link with the URL that was bookmarked

function bmi_custom_bookmark_permalink($post_link, $post) {
    // Check if the post type is 'bookmark'
    if ('bookmark' === get_post_type($post)) {
        // Retrieve the custom field 'press-this-url'
        $custom_url = get_post_meta($post->ID, 'press-it-url', true);

        // If the custom field is not empty, replace the post link with the custom URL
        if (!empty($custom_url)) {
            return esc_url($custom_url);
        }
    }

    // If the conditions are not met, return the original post link
    return $post_link;
}
add_filter('post_type_link', 'bmi_custom_bookmark_permalink', 10, 2);
//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");
