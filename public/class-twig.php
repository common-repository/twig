<?php

/**
 * Twig template engine for Word Press
 *
 * This plugin lets you use the Twig template engine in your Word Press themes.
 * Just specify a path to your Twig installation and you're good to go!
 *
 * It even includes a very usefull template wrapper that lets you use a layout file to
 * keep your coding DRY.
 */
class Twig {
    
    /**
     * Plugin version
     * 
     * @since 1.0.0
     * 
     * @var string
     */
    protected $version = '1.0.1';
    
    /**
     * Object singleton instance
     * 
     * @since 1.0.0
     * 
     * @var object
     */
    protected static $instance = null;
    
    /**
     * Unique identifier
     * 
     * @since 1.0.0
     * 
     * @var string
     */
    protected $plugin_slug = 'twig';
    
    /**
     * Twig environment object
     * 
     * @since 1.0.0
     * 
     * @var object
     */
    protected static $twig_environment = null;

    /**
     * Twig environment options
     *
     * @since 1.0.0
     * 
     * @var array
     */
    protected static $twig_environment_args = array();
    
    /**
     * Path to twig templates
     * 
     * @since 1.0.0
     * 
     * @var string/array
     */
    protected static $template_path = null;

    /**
     * Path to twig cache
     *
     * @since 1.0.0
     *
     * @var string
     */
    protected static $cache_path = null;
    
    /**
     * The template Word Press is rendering
     * 
     * @since 1.0.0
     * 
     * @var string
     */
    public static $wp_template;

    /**
     * This plugins options
     *
     * @since 1.0.0
     *
     * @var string/array
     */
    private static $options;
    
    /**
     * Setup Twig
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    private function __construct() {

        /** get plugin options */
        self::$options = wp_parse_args( get_option( $this->get_option_name() ), $this->get_default_settings() );

        /** if Twig is not found/installed, try to include path from options */
        if ( !class_exists( 'Twig_Autoloader' ) ) {

            if ( isset( self::$options['twig_path'] ) && !empty( self::$options['twig_path'] ) ) {

                // try to include twig installation path from admin options
                if ( is_readable( $twig_path = self::absolute_path( self::$options['twig_path'] ) ) ) {

                    include_once $twig_path;
                } else {

                    twig_yield_error( 'Could not include ' . self::absolute_path( self::$options['twig_path'] ) );
                }
            }
        }

        /** if Twig is available then load it up */
        if ( class_exists( 'Twig_Autoloader' ) ) {

            Twig_Autoloader::register();

            self::set_template_path( self::$options['template_path'] );
        
            /** get the template Word Press is rendering for use later ( self::View() ) */
            add_action( 'template_include', function( $template ) {

                Twig::$wp_template = basename( $template, '.php' );
                
                return $template;
            });

            /** include the template wrapper if enabled */
            if ( self::$options['template_wrapper'] ) {

                include_once plugin_dir_path( __FILE__ ) . '../includes/class-twig-template-wrapper.php';
            }

            /** include the Post for easy access to WordPress posts */
            include_once plugin_dir_path( __FILE__ ) . '../includes/class-post.php';

            self::invoke();

        } else {

            twig_yield_error( 'Twig Autoloader not found!' );
        }
    }
    
    /**
     * Return class instance
     * 
     * @since 1.0.0
     * 
     * @return object A single instance of the class
     */
    public static function get_instance() {
        
        // singleton
        if ( is_null( self::$instance ) ) {

            self::$instance = new self;
        }
        
        return self::$instance;
    }

    /**
     * Returns this plugins slug
     *
     * @since 1.0.0
     *
     * @return string This plugins slug
     */
    public function get_plugin_slug() {

        return $this->plugin_slug;
    }

    /**
     * Returns this plugins option name
     *
     * @since  1.0.0
     *
     * @return string/array This plugins options
     */
    public function get_option_name() {

        return $this->plugin_slug . '-options';
    }

    /**
     * Return the plugins options
     *
     * @since 1.0.1
     *
     * @return array
     */
    public function get_options() {

        return self::$options;
    }

    /**
     * The plugins default settings
     *
     * @since  1.0.2
     *
     * @return array
     */
    public function get_default_settings() {

        return array(
            'twig_path' => '',
            'template_path' => get_stylesheet_directory() . '/twig',
            'template_wrapper' => false,
            'use_cache' => false,
            'cache_dir' => get_stylesheet_directory() . '/twig/cache'
        );
    }
    
    /**
     * renders the twig view
     * 
     * @since 1.0.0
     * 
     * @param array $args An array of arguments
     * 
     * @return string/void Returns rendered template as a string or void if template echoes
     */
    public static function View( $args = array() ) {

        // parse with defaults
        $args = wp_parse_args( $args, array(
            'template'          => null,
            'context'           => array(),
            'template_path'     => null,
            'echo'              => true
        ));
        
        // if template_path is given, add new path/paths to path array and invoke
        if ( !is_null( $args['template_path'] ) && !empty( $args['template_path'] ) ) {

            $invoke = false;
            
            foreach ( (array) $args['template_path'] as $path ) {

                if ( is_null( self::$template_path ) ) {

                    $invoke = true;
                    self::$template_path = $path;
                } else if ( is_array( self::$template_path ) && !in_array( $path, self::$template_path ) ) { // only add new paths

                    $invoke = true;
                    self::$template_path[] = $path;
                } else if ( self::$template_path != $path ) {

                    $invoke = true;
                    self::$template_path = array( self::$template_path, $path );
                }
            }
            
            if ( $invoke ) {

                self::invoke();
            }
        }
        
        if ( is_null( self::$twig_environment ) ) {

            self::invoke();
        }
        
        // find twig template to use
        $template_names = array();

        $template_to_find = isset ( $args['template'] ) ? $args['template'] : self::$wp_template;

        // create an array with the 'template parts' ( page, about, etc. in page-about.php )
        $template_parts = explode( '-', $template_to_find );

        // create an array with equally many templates as template parts
        foreach ( $template_parts as $ignore_me ) {
            $template_names[] = implode( '-', $template_parts ) . '.twig';
            array_pop( $template_parts );
        }

        foreach ( (array) self::$template_path as $path ) {

            foreach ( $template_names as $template_name ) {

                $templates[] = str_replace( get_template_directory() . '/', '', $path ) . '/' . $template_name;
            }
        }

        $template_to_use = locate_template( $templates );

        /** strip the path to Twig's template path from the template */
        foreach ( (array) self::$template_path as $path ) {

            if ( substr( $template_to_use, 0, strlen( $path ) ) === $path ) {

                $template_to_use = substr( $template_to_use, ( strlen( $path ) + 1 ) ); // +1 for the last '/'
            }
        }

        if ( $args['echo'] ) {
            echo self::$twig_environment->render( $template_to_use, $args['context'] );
        }

        return self::$twig_environment->render( $template_to_use, $args['context'] );
    }
    
    /**
     * Sets the path to twig templates
     * 
     * If path is allready set, the new path will be added
     * in the array.
     * 
     * @since 1.0.0
     * 
     * @param string/array Path/paths to twig templates
     * 
     * @return void
     */
    public static function set_template_path( $path ) {

        if ( !is_null( self::$template_path ) ) {

            self::$template_path = array_merge( (array) self::$template_path, (array) self::absolute_path( $path ) );
            self::invoke();
        } else {

            self::$template_path = self::absolute_path( $path );
            self::invoke();
        }
    }

    /**
     * Make sure the path is absolute by prepending the path
     * with ABSPATH if not allready present
     *
     * @param string $path The path to be absolute
     * 
     * @return string
     */
    public static function absolute_path( $path ) {

        /** make sure the path is absolute */
        if ( substr( $path, 0, strlen( ABSPATH ) ) != ABSPATH ) {

            $path = ABSPATH . $path;
        }

        return $path;
    }

    /**
     * Create the template paths if they don't allready exist
     * 
     * @param string|array $paths The template path|paths to create
     * 
     * @return void
     */
    public static function create_template_paths( $paths ) {

        foreach ( (array) $paths as $path ) {

            if ( !is_dir( self::absolute_path( $path ) ) ) {

                mkdir( self::absolute_path( $path ), 0777, true );
            }
        }
    }
    
    /**
     * Sets up the twig variable with present paths and various functions/tags/filters/extensions
     *
     * @since 1.0.0
     * 
     * @return void
     */
    private static function invoke() {

        self::create_template_paths( self::$template_path );
    
        $twig_loader = new Twig_Loader_Filesystem( self::$template_path );

        // set twig environment args
        self::$twig_environment_args['cache'] = self::$options['use_cache'] ? self::absolute_path( self::$options['cache_dir'] ) : false;
        
        self::$twig_environment = new Twig_Environment( $twig_loader, self::$twig_environment_args );
        
        /**
         * Use any php_function
         *
         * Limited to one function argument for now
         * 
         * @param string Function name
         * @param string/array Function arguments
         *
         * @return mixed Function return
         */
        self::$twig_environment->addFunction( new Twig_SimpleFunction( 'php_function', function( $function, $args = '' ) {

            return call_user_func( $function, $args );
        }));

        /**
         * Count words in text
         * 
         * @param string The text to count in
         * 
         * @return int Returns word count
         */
        self::$twig_environment->addFunction( new Twig_SimpleFunction( 'word_count', function( $string ) {

            return sizeof( preg_split('/( |\n)/i', strip_tags($string)) ) - 1;
        }));
    }
}
