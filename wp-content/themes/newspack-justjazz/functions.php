<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;



// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array(  ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10 );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'chld_thm_cfg_parent','newspack-style','newspack-print-style','newspack-woocommerce-style' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 20 );

// END ENQUEUE PARENT ACTION

/* Custom image sizes 

add_image_size ('video-thumbnail', 1280, 720,TRUE);

/* Display custom image sizes 
add_filter ( 'image_size_names_choose','justjazz_custom_image_sizes' );
function justjazz_custom_image_sizes ( $sizes ) {
return array_merge ( $sizes, array (
'video-thumbnail' => __( 'Cropped' ),
) );
}
*/



// use priority 11 to hook into after_setup_theme AFTER the parent theme
 add_action('after_setup_theme', 'reset_parent_setup', 11);

function reset_parent_setup() 
{
    // Override the image sizes
	add_image_size( 'newspack-featured-image', 1280, 720, true );
	add_image_size( 'newspack-archive-image', 800, 600, true );
	add_image_size( 'newspack-footer-logo', 400, 9999, true );

}		
		
