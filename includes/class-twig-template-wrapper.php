<?php

/**
 * Twig Template Wrapper
 *
 * This class lets you "wrap" the Word Press template so you can use
 * a master layout file and include the template inside it.
 */
class Twig_Template_Wrapper {

	/**
	 * Stores path to the main template file
	 * 
	 * @var string
	 */
	static $wp_template_path;

	/**
	 * Stores base name of the template file eg. 'page' for page.php etc.
	 * 
	 * @var string
	 */
	static $wp_template;

	/**
	 * Intercept the template being rendered and "wrap" our layout file around it.
	 * Eg. we will try to load layout file in order: _layout-page-about.php -> _layout-page.php -> _layout.php
	 * and function "get_twig_template()" will contain the template originally to be rendered without the file extension.
	 * 
	 * @param  string $template The template about to be rendered
	 * 
	 * @return string           The layout template to render
	 */
	static function wrap( $template ) {
		
		self::$wp_template_path = $template;

		self::$wp_template = basename( $template, '.php' );

		$templates = array();

		if ( 'index' != self::$wp_template ) {

			// create an array with the 'template parts' ( page, about, etc. in page-about.php )
			$template_parts = explode( '-', self::$wp_template );

			// create an array with equally many templates as template parts
			foreach ( $template_parts as $ignore_me ) {
				$templates[] = '_layout-' . implode( '-', $template_parts ) . '.php';
				array_pop( $template_parts );
			}

			$templates[] = '_layout.php';
			$templates[] = basename( $template );
		} else {
			
			$templates = array( '_layout.php', basename( $template ) );
		}

		return locate_template( $templates );
	}

	/**
	 * Get the path to the Word Press template to use
	 * 
	 * @return string Path to the Word Press template to use
	 */
	public static function get_wp_template_path() {
	
		return self::$wp_template_path;
	}

	/**
	 * Get the Word Press template to use
	 * 
	 * @return string Word Press template to use
	 */
	public static function get_wp_template() {
	
		return self::$wp_template;
	}

}

add_filter( 'template_include', array( 'Twig_Template_Wrapper', 'wrap' ), 99);

/**
 * Get the path to the Word Press template to use
 * 
 * @return string Path to the Word Press template to use
 */
function get_twig_template_path() {

	return Twig_Template_Wrapper::get_wp_template_path();
}

/**
 * Get the Word Press template to use
 * 
 * @return string Word Press template to use
 */
function get_twig_template() {
	
	return Twig_Template_Wrapper::get_wp_template();
}
