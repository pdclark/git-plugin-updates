<?php
/**
 * Controller for the plugin.
 * 
 * @author Paul Clark <http://pdclark.com>
 */
class GPU_Controller {

	/**
	 * @var GPU_Controller Instance of this class.
	 */
	private static $instance = false;

	/**
	 * @var string Key for plugin options in wp_options table
	 */
	const OPTION_KEY = GPU_SLUG;

	/**
	 * @var int How often should transients be updated, in seconds.
	 */
	protected $update_interval;

	/**
	 * @var array Options from wp_options
	 */
	protected $options;

	/**
	 * @var GPU_Admin Admin object
	 */
	protected $admin;
	
	/**
	 * Don't use this. Use ::get_instance() instead.
	 */
	public function __construct() {
		if ( !self::$instance ) {
			$message = '<code>' . __CLASS__ . '</code> is a singleton.<br/> Please get an instantiate it with <code>' . __CLASS__ . '::get_instance();</code>';
			wp_die( $message );
		}       
	}

	/**
	 * If a variable is accessed from outside the class,
	 * return a value from method get_$var()
	 * 
	 * For example, $inbox->unread_count returns $inbox->get_unread_count()
	 * 
	 * @return pretty-much-anything
	 */
	public function __get( $var ) {
		$method = 'get_' . $var;

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}else {
			return $this->$var;
		}
	}
	
	public static function get_instance() {
		if ( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = true;
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}
	
	/**
	 * Initial setup. Called by get_instance.
	 */
	protected function init() {

		$this->options = get_site_option( self::OPTION_KEY );

		// Filter allows search results to be updated more or less frequently.
		// Default is 60 minutes
		$this->update_interval = apply_filters( 'gpu_update_interval', 60*60 );

	}

	public function get_option( $key ) {
		if ( isset( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
		}else {
			return false;
		}
	}

	/**
	 * Load HTML template from views directory.
	 * Contents of $args are turned into variables for use in the template.
	 * 
	 * For example, $args = array( 'foo' => 'bar' );
	 *   becomes variable $foo with value 'bar'
	 */
	public static function get_template( $file, $args = array() ) {
		extract( $args );

		include GPU_PLUGIN_DIR . "/views/$file.php";

	}

	/**
	 * Log data to FireBug using FirePHP
	 * 
	 * @link http://getfirebug.com/
	 * @link http://www.firephp.org/
	 * @return void
	 */
	public function log( $variable, $label='' ) {
		if ( class_exists('FB') && defined('WP_DEBUG') && WP_DEBUG ) {
			FB::log( $variable, $label );
		}
	}

}
