<?php

/**
 * Plugin Name: Twig
 * Plugin URI: http://github.com/martin-pettersson/WP_Twig
 * Description: Use the Twig template engine in your themes.
 * Version: 1.0.2
 * Author: Martin Pettersson
 */

// exit if called directly
if ( !defined( 'ABSPATH' ) ) {
	die();
}

// include twig helper functions
include_once plugin_dir_path( __FILE__ ) . 'includes/twig-functions.php';

/**
 * Public facing functionality
 */
include_once plugin_dir_path( __FILE__ ) . 'public/class-twig.php';
add_action( 'plugins_loaded', array( 'Twig', 'get_instance' ) );

/**
 * Plugin admin view
 */
if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'admin/class-twig-admin.php';
	add_action( 'plugins_loaded', array( 'Twig_Admin', 'get_instance' ) );
}
