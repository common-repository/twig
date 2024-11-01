<?php

/**
 * The Post object contains all necessary parts of a post and
 * presents them in an easy, rady formated manner.
 */
class Post {

	/**
	 * An instance of this class
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * The current post object
	 *
	 * @var object
	 */
	private $post;

	/**
	 * This is all the attributes we want to share publicly
	 *
	 * @var array
	 */
	private $available_attributes;

	/**
	 * Constructor
	 *
	 * If not given an id, we try to get it from the loop
	 * 
	 * @param int $post_id The post id
	 */
	public function __construct( $post_id = null ) {

		/** iterate and setup the post */
		the_post();

		/** try to get a post id if none is given */
		if ( is_null( $post_id ) && get_the_id() ) {

			$post_id = get_the_id();
		}

		/** setup the post components */
		if ( !is_null( $post_id ) ) {

			$this->set_available_attributes(array(
				'id' 						=> $post_id,
				'type' 						=> $this->get_type( $post_id ),
				'author'					=> $this->get_author( $post_id ),
				'title' 					=> $this->get_title( $post_id ),
				'excerpt'					=> $this->get_excerpt( $post_id ),
				'content' 					=> $this->get_content( $post_id ),
				'thumbnail'					=> $this->get_thumbnail( $post_id ),
				'meta' 						=> $this->get_meta( $post_id ),
				'category' 					=> $this->get_category( $post_id ),
				'classes'					=> $this->get_classes( '', $post_id ),
				'tags'						=> $this->get_tags( $post_id ),
				'permalink' 				=> $this->get_permalink( $post_id ),
				'published' 				=> $this->get_published( $post_id ),
				'modified' 					=> $this->get_modified( $post_id ),
				'published_date' 			=> $this->get_published_date( $post_id ),
				'published_date_human' 		=> $this->get_published_date_human( $post_id ),
				'modified_date' 			=> $this->get_modified_date( $post_id ),
				'modified_date_human' 		=> $this->get_modified_date_human( $post_id ),
				'published_time' 			=> $this->get_published_time( $post_id ),
				'published_time_human' 		=> $this->get_published_time_human( $post_id ),
				'modified_time' 			=> $this->get_modified_time( $post_id ),
				'modified_time_human' 		=> $this->get_modified_time_human( $post_id )
			));
			
			// set the instance to this so we can access this instance by Post::current()
			self::$instance = $this;
		}
	}

	/**
	 * Returns the current post (current instance of Post)
	 * 
	 * @return object The current instance of Post
	 */
	public static function current() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * This magic isset funtion checks to see if a key in the available_attributes array
	 * is set
	 *
	 * @param string $key The name of the attribute to check for
	 *
	 * @return bool
	 */
	public function __isset( $key ) {

		return isset( $this->available_attributes[ $key ] );
	}

	/**
	 * This magic get function returns items from the available_attributes array
	 * so we can controll which attributes we want to share.
	 * 
	 * @param string $key The name of the attribute to retrieve
	 * 
	 * @return mixed The value of the given key
	 */
	public function __get( $key ) {

		if ( isset( $this->available_attributes[ $key ] ) ) {

			return $this->available_attributes[ $key ];
		}
	}

	/**
	 * Here we set the attributes we wish to be public
	 *
	 * @param array The key/values to set
	 */
	private function set_available_attributes( $attributes ) {

		foreach( (array) $attributes as $key => $value ) {

			$this->available_attributes[ $key ] = $value;
		}
	}

	/**
	 * Get the post type
	 * 
	 * @param int $post_id The post id
	 * 
	 * @return string The post type
	 */
	private function get_type( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_post_type( $post_id ) : get_post_type();
	}

	/**
	 * Get the post author
	 * 
	 * @param int $post_id The post id
	 * 
	 * @return string The post author
	 */
	private function get_author( $post_id = null ) {

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the author by id
			return get_the_author();
		} else {
			return get_the_author();
		}
	}

	/**
	 * Get the post title
	 * 
	 * @param int $post_id The post id
	 * 
	 * @return string The post title
	 */
	private function get_title( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_the_title( $post_id ) : get_the_title();
	}

	/**
	 * Get the post excerpt
	 * 
	 * @param int $post_id The post id
	 * 
	 * @return string The post excerpt
	 */
	private function get_excerpt( $post_id = null ) {

		ob_start();

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the excerpt by id
			the_excerpt();
		} else {
			the_excerpt();
		}

		return ob_get_clean();
	}

	/**
	 * Get the post content
	 * 
	 * @param int $post_id The post id
	 * 
	 * @return string The post content
	 */
	private function get_content( $post_id = null ) {

		ob_start();

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the content by id
			the_content();
		} else {
			the_content();
		}

		return ob_get_clean();
	}

	/**
	 * Get the post thumbnail
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The thumbnail url
	 */
	private function get_thumbnail( $post_id = null ) {

		return (false !== wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) ) ? wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) : '';
	}

	/**
	 * Get the post meta
	 * 
	 * @param int $post_id The post id
	 * 
	 * @return string The post meta
	 */
	private function get_meta( $post_id = null ) {

		if ( !is_null( $post_id ) ) {
			return get_post_meta( $post_id );
		} else {
			ob_start();
			the_meta();
			return ob_get_clean();
		}
	}

	/**
	 * Get the category
	 *
	 * @param int $post_id The post id
	 *
	 * @return array An array of category objects
	 */
	private function get_category( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_the_category( $post_id ) : get_the_category();
	}

	/**
	 * Get the permalink
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The permalink
	 */
	private function get_permalink( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_permalink( $post_id ) : get_permalink();
	}

	/**
	 * Get the post classes
	 *
	 * @param string $class Aditional classes to add
	 * @param int $post_id The post id
	 *
	 * @return array The post classes
	 */
	public function get_classes( $class = '', $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_post_class( $class, $post_id ) : get_post_class( $class );
	}

	/**
	 * Get the post Tags
	 *
	 * @param int $post_id The post id
	 *
	 * @return array An array of tag objects
	 */
	public function get_tags( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_the_tags( $post_id ) : get_the_tags();
	}

	/**
	 * Get the full date and time of publish
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The date and time of publish
	 */
	private function get_published( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_post_time( 'c', true, $post_id ) : get_post_time( 'c', true );
	}

	/**
	 * Get the full date and time of modified
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The date and time of modified
	 */
	private function get_modified( $post_id = null ) {

		ob_start();

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the date by id
			the_modified_time('c');
		} else {
			the_modified_time('c');
		}

		return ob_get_clean();
	}

	/**
	 * Get the published date
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The published date
	 */
	private function get_published_date( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_post_time( 'Y-m-d', true, $post_id ) : get_post_time( 'Y-m-d', true );
	}

	/**
	 * Get the published date in human readable form
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The published date
	 */
	private function get_published_date_human( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_post_time( 'l j M', true, $post_id ) : get_post_time( 'l j M', true );
	}

	/**
	 * Get the modified date
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The modified date
	 */
	private function get_modified_date( $post_id = null ) {

		ob_start();

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the date by id
			the_modified_time('Y-m-d');
		} else {
			the_modified_time('Y-m-d');
		}

		return ob_get_clean();
	}

	/**
	 * Get the modified date in human readable form
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The modified date
	 */
	private function get_modified_date_human( $post_id = null ) {

		ob_start();

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the date by id
			the_modified_time('l j M');
		} else {
			the_modified_time('l j M');
		}

		return ob_get_clean();
	}

	/**
	 * Get the published time
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The published time
	 */
	private function get_published_time( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_post_time( 'H:i:s', true, $post_id ) : get_post_time( 'H:i:s', true );
	}

	/**
	 * Get the published time human
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The published time human
	 */
	private function get_published_time_human( $post_id = null ) {

		return ( !is_null( $post_id ) ) ? get_post_time( 'g a', true, $post_id ) : get_post_time( 'g a', true );
	}

	/**
	 * Get the modified time
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The modified time
	 */
	private function get_modified_time( $post_id = null ) {

		ob_start();

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the time by id
			the_modified_time('H:i:s');
		} else {
			the_modified_time('H:i:s');
		}

		return ob_get_clean();
	}

	/**
	 * Get the modified time in human readable form
	 *
	 * @param int $post_id The post id
	 *
	 * @return string The modified time
	 */
	private function get_modified_time_human( $post_id = null ) {

		ob_start();

		if ( !is_null( $post_id ) ) { // @TODO: Figure out the best way to get the time by id
			the_modified_time('g a');
		} else {
			the_modified_time('g a');
		}

		return ob_get_clean();
	}
}
