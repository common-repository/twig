<?php

/**
 * Twig Admin View
 *
 * Here we generate the admin view for the Twig plugin.
 */
class Twig_Admin {
	
	/**
     * Instance of this class
     * 
     * @since 1.0.0
     * 
     * @var object
     */
	protected static $instance = null;

    /**
     * The main plugin
     *
     * @since 1.0.0
     *
     * @var object The main Twig plugin object
     */
    private $plugin;

    /**
     * This plugins options
     *
     * @since 1.0.0
     *
     * @var string/array
     */
    private $options;
	
	/**
     * Constructor
     *
     * Here we get an instance of the main plugin and its options. Initialize Twig
     * and create the admin menu.
     * 
     * @since 1.0.0
     */
	private function __construct() {

        // get an instance of the main plugin
        $this->plugin = Twig::get_instance();

        // get the plugin slug from main plugin
        $this->plugin_slug = $this->plugin->get_plugin_slug();

        // get the main plugin options (shared with admin)
        $this->options = wp_parse_args( get_option( $this->plugin->get_option_name() ), $this->plugin->get_options() );

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        // Add settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // filter the saved options
        add_filter( 'pre_update_option_' . $this->plugin->get_option_name(), array( $this, 'plugin_update_option' ) );

        // Add an action link pointing to the options page.
        $plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
        add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
    }
	
	/**
     * Returns an instance of this class
     * 
     * @since 1.0.0
     * 
     * @return object A single instance of this class.
     */
	public static function get_instance() {
    	
    	if ( is_null( self::$instance ) ) {

        	self::$instance = new self;
        }
        
        return self::$instance;
    }

    /**
     * Filter the saved options
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function plugin_update_option( $options ) {

        // attempt to create paths if they don't exist
        Twig::create_template_paths( $options['template_path'] );

        /**
         * If cache is enabled attempt to create a cache directory in the first template directory
         * if it does'nt allready exist.
         */
        if ( isset( $options['use_cache'] ) && $options['use_cache'] ) {

            $cache_path = is_array( $options['template_path'] ) ? ABSPATH . $options['template_path'][0] . '/cache' : ABSPATH . $options['template_path'] . '/cache';

            // create cache path if it does not exist
            if ( !is_dir( $cache_path ) ) {

                mkdir( $cache_path, 0777, true );
            }
            
            $options['cache_dir'] = $cache_path;
        } else if ( isset( $options['cache_dir'] ) && !empty( $options['cache_dir'] ) ) { // remove cache dir when not in use

            if ( $this->remove_dir( $options['cache_dir'] ) ) {

                $options['cache_dir'] = '';
            }
        }

        return $options;
    }

    /**
     * Removes directory recursively
     * 
     * @param string $dir The directory to remove
     * 
     * @return void
     */
    private function remove_dir( $dir ) {

        if ( is_dir( $dir ) && !empty( $dir ) ) {

            $objects = scandir($dir);

            foreach ( $objects as $object ) {

                if ( $object != "." && $object != ".." ) {

                    if ( filetype( $dir . "/" . $object ) == "dir" ) $this->remove_dir( $dir . "/" . $object ); else unlink( $dir . "/" . $object );
                }
            }
            
            reset($objects);

            return rmdir($dir);
        }
    }

    /**
     * Register the administration menu for this plugin into the Word Press Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        add_submenu_page(
            'options-general.php',
            __( 'Twig Settings', $this->plugin_slug ),
            __( 'Twig', $this->plugin_slug ),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'render_admin_menu' )
        );

    }

    /**
     * Register the plugin settings
     * 
     * @since   1.0.0
     */
    public function register_settings() {

        register_setting(
            $this->plugin_slug,
            $this->plugin->get_option_name()
            // optional sanitizer function ex. array( $this, 'sanitize_input' )
        );

        // main section
        add_settings_section(
            'main_settings',
            '',
            array( $this, 'main_settings_callback' ),
            $this->plugin_slug
        );

        // twig path
        add_settings_field(
            'twig_path',
            'Twig Installation Path',
            array( $this, 'twig_path_callback' ),
            $this->plugin_slug,
            'main_settings'
        );

        // twig template path
        add_settings_field(
            'template_path',
            'Template Path',
            array( $this, 'template_path_callback' ),
            $this->plugin_slug,
            'main_settings'
        );

        // template wrapper checkbox
        add_settings_field(
            'template_wrapper',
            'Template Wrapper',
            array( $this, 'template_wrapper_callback' ),
            $this->plugin_slug,
            'main_settings'
        );

        // cache checkbox
        add_settings_field(
            'use_cache',
            'Use Cache',
            array( $this, 'use_cache_callback' ),
            $this->plugin_slug,
            'main_settings'
        );

        // cache dir
        add_settings_field(
            'cache_dir',
            '',
           array( $this, 'cache_dir_callback' ),
           $this->plugin_slug,
           'main_settings' 
        );

    }

    /**
     * Render the admin menu
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function render_admin_menu() {
        include 'views/settings.php';
    }

    /**
     * The main settings section description
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function main_settings_callback() {
        //echo '<h4>The main settings for twig</h4>';
    }

    /**
     * Twig path input option callback
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function twig_path_callback() {
        printf(
            '<code>' . ABSPATH . '</code><input id="twig_path" class="regular-text" name="%s[twig_path]" type="text" value="%s"><p class="description">Path to Twigs <b>Autoloader.php</b> or other autoload file eg. composers <b>autoload.php</b></p>',
            $this->plugin->get_option_name(),
            isset( $this->options['twig_path'] ) ? $this->options['twig_path'] : ''
        );
    }

    /**
     * Twig template path input option callback
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function template_path_callback() {
        printf(
            '<code>' . ABSPATH . '</code><input id="template_path" class="regular-text" name="%s[template_path]" type="text" value="%s"><p class="description">Path to Twig templates (defaults to: <code>' . substr( get_stylesheet_directory(), strlen( ABSPATH ) ) . '/twig</code>)</p>',
            $this->plugin->get_option_name(),
            isset( $this->options['template_path'] ) ? $this->options['template_path'] : ''
        );
    }

    /**
     * Template wrapper checkbox option callback
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function template_wrapper_callback() {
        printf(
            '<label for="template_wrapper">
                <input id="template_wrapper" type="checkbox" name="%s[template_wrapper]" value="1" %s>
                Use <code>_layout.php</code> as a global template
                <p class="description">Be aware: This could break your theme if you don\'t use a master layout file.</p>
            </label>',
            $this->plugin->get_option_name(),
            checked( 1, $this->options['template_wrapper'], false )
        );
    }

    /**
     * Use cache checkbox option callback
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function use_cache_callback() {
        printf(
            '<label for="use_cache">
                <input id="use_cache" type="checkbox" name="%s[use_cache]" value="1" %s>
                Enable cached templates for better performence
            </label>',
            $this->plugin->get_option_name(),
            checked( 1, $this->options['use_cache'], false )
        );
    }

    /**
     * Cache dir callback
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function cache_dir_callback() {
        printf(
            '<input id="cache_dir" type="hidden" name="%s[cache_dir]" value="%s">',
            $this->plugin->get_option_name(),
            $this->options['cache_dir']
        );
    }
}
