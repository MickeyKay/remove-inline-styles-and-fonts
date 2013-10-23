<?php

/**
 * Plugin Name: Remove Inline Styles & Fonts
 * Plugin URI:  http://demo.mightyminnow.com/plugins/remove-inline-styles-fonts
 * Description: Removes user-generated inline style attributes and font tags.
 * Version:     1.0
 * Author:      MIGHTYminnow
 * Author URI:  http://mightyminnow.com
 * License:     GPLv2+
 * Text Domain: risf
 * Domain Path: /languages/
 * 
 * Coded By: Mickey Kay
 */

// Definitions
define ( 'RISF_PLUGIN_NAME', 'Remove Inline Styles & Fonts' );

// Required files
require_once plugin_dir_path( __FILE__ ) . 'lib/admin/admin.php';
require_once plugin_dir_path( __FILE__ ) . 'lib/HTMLPurifier.auto.php';

/**
 * Removes inline style attributes and font tags
 * 
 * @param  string $content post content from the_content
 * @return string $content
 *
 * Modified from: http://gomakethings.com/removing-wordpress-funk/
 */
function clean_post_content( $content ) {

    global $post;

    if ( !$post )
        return;

    // Whether this post type is included in the plugin settings
    $include_post_type = ( 1 == get_option( 'risf-post-type-' . get_post_type() ) ) ? true : false;
    
    // If the post meta value is set to exclude
    $risf_exclude = ( 1 == get_post_meta( $post->ID, '_risf_exclude', true ) ) ? true : false;
    
    // Overide $risf_exclude if the post has just been edited
    $exclude_meta = isset( $_POST['_risf_exclude'] ) ? $_POST['_risf_exclude'] : '';
    if ( 1 == $exclude_meta )
        $risf_exclude = true;
    elseif ( 'empty' == $exclude_meta )
        $risf_exclude = false;
 
    // Only do something if:
    //  1. the current post type is checked in user settings, AND
    //  2. the metabox to exclude this specific post/page isn't checked
    if ( $include_post_type && !$risf_exclude ) {

        /**
         * Parsing Method 1: HTML Purifier
         */
        if ( 'htmlpurify' == get_option( 'risf-parsing-method' ) ) {

            $config = HTMLPurifier_Config::createDefault();

            // Style attributes
            if( 1 == get_option( 'risf-style-attributes' ) )
                $config->set( 'CSS.AllowedProperties', array() );
                
            // Remove <font> tags via regex until we figure out how to do it with HTML Purifier :)
            if ( 1 == get_option( 'risf-font-tags' ) )
                $config->set( 'HTML.ForbiddenElements', array( 'font' ) );

            if( 1 == get_option( 'risf-empty-span-elements' ) )
               $config->set( 'AutoFormat.RemoveSpansWithoutAttributes', true );

            $purifier = new HTMLPurifier( $config );
            $content = $purifier->purify( stripslashes( $content ) );

        }

        /**
         * Parsing Method 2: Regex
         */
        if ( 'regex' == get_option( 'risf-parsing-method' ) ) {

            // Remove style attribute
            if ( 1 == get_option( 'risf-style-attributes' ) )
                $content = preg_replace( '/\s*style\s*=\s*[\"\'][^\"|\']*[\"\']/', '', stripslashes( $content ) );

            // Remove <font> tag
            if ( 1 == get_option( 'risf-font-tags' ) ) {
                $content = preg_replace( '/\s*<\s*font[^<]*>/', '', stripslashes( $content ) );
                $content = preg_replace( '/\s*<\/\s*font[^<]*>/', '', stripslashes( $content ) );
            }
            
            // Remove <span> tags that are empty
            if ( 1 == get_option( 'risf-empty-span-elements' ) ) {
                $content = preg_replace( '/\s*<\s*span\s*>\s*<\/\s*span\s*>/', '', stripslashes( $content ) );
            }

        }

    }
    
    return $content;
    
}


// Removal Method 1: Filter Output
if ( 'output' == get_option( 'risf-removal-method' ) )
    add_filter( 'the_content', 'clean_post_content', get_option( 'risf-filter-priority' ) );

// Removal Method 2: Filter Content
if ( 'content' == get_option( 'risf-removal-method' ) )
    add_filter( 'content_save_pre', 'clean_post_content', get_option( 'risf-filter-priority' ), 1 );