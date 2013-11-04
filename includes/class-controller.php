<?php
/**
 * The main plugin wrapper
 * Sets up hooks, manages options, loads templates, instantiates other classes.
 * 
 * @author Paul Clark <http://pdclark.com>
 */
class GPU_Controller {

	/**
	 * @var GPU_Controller Instance of this class.
	 */
	private static $instance = false;

	/**
	 * Used for error messages.
	 * Used for settings page title.
	 * 
	 * @var string Nice name of the plugin.
	 */
	const PLUGIN_NAME = 'Git Plugin Updates';

	/**
	 * Used for wp-admin settings page slug.
	 * 
	 * @var string Slug of the plugin on wordpress.org.
	 */
	const PLUGIN_SLUG = 'git-plugin-updates';

	/**
	 * @var string Key for plugin options in wp_options table
	 */
	const OPTION_KEY = 'git-plugin-updates';

	/**
	 * @var int How often should transients be updated, in seconds.
	 */
	public static $update_interval;

	/**
	 * @var array Options from wp_options
	 */
	protected $options;

	/**
	 * @var GPU_Admin Admin object
	 */
	protected $admin;

	/**
	 * @var array Installed plugins that list a Git URI.
	 */
	var $plugins = array();
	
	/**
	 * Don't use this. Use ::get_instance() instead.
	 */
	public function __construct() {
		if ( !self::$instance ) {
			$message = '<code>' . __CLASS__ . '</code> is a singleton.<br/> Please get an instantiate it with <code>' . __CLASS__ . '::get_instance();</code>';
			wp_die( $message );
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
		$this->clear_cache_if_debugging();

		// Filter allows search results to be updated more or less frequently.
		// Default is 60 minutes
		GPU_Controller::$update_interval = apply_filters( 'gpu_update_interval', 60*60 );

		// Add Git URI: to valid plugin headers
		add_filter( 'extra_plugin_headers', array($this, 'extra_plugin_headers') );

		// Check for plugin updates
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins' ) );

		// Build Git plugin list
		add_action( 'admin_init', array($this, 'load_plugins'), 20 );

		// Plugin details screen
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 9000, 3 );

		// Cleanup and activate plugins after update
		add_filter( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 3 );

		add_filter( 'http_request_args', array( $this, 'disable_git_ssl_verify' ), 10, 2 );

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

		include dirname( dirname( __FILE__ ) ) . "/views/$file.php";

	}

	/**
	 * Clear transient caches if WP_DEBUG is enabled
	 * 
	 * @return void
	 */
	public function clear_cache_if_debugging() {
		if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			delete_site_transient( 'update_plugins' ); // WordPress update check
			delete_site_transient( GPU_Controller::OPTION_KEY . '-plugin-objects' );
		}
	}

	/**
	 * Additional headers
	 *
	 * @return array Plugin header key names
	 */
	public function extra_plugin_headers( $headers ) {
		$headers[] = 'Git URI';
		$headers[] = 'Git Branch';

		return $headers;
	}

	/**
	 * Check if an update is available from plugin Git repos
	 *
	 * @param object $transient the plugin data transient
	 * @return object $transient updated plugin data transient
	 */
	public function pre_set_site_transient_update_plugins( $transient ) {

		// If transient doesn't contain checked info, return without modification.
		if ( empty( $transient->last_checked ) && empty( $transient->checked ) ) {
			return $transient;
		}

		// Iterate over all plugins
		foreach( (array) $this->plugins as $plugin ) {

			// TODO: Move version compare to Update parent class

			// Compare remote version to local version
			$remote_is_newer = ( 1 === version_compare( $plugin->remote_version, $plugin->local_version ) );

			if ( $remote_is_newer ) {

				$response = array(
					'slug'        => $plugin->folder_name,
					'new_version' => $plugin->remote_version,
					// TODO: Fix Bitbucket homepage
					'url'         => $plugin->homepage,
					'package'     => $plugin->zip_url,
				);

				// Add update data for this plugin
				$transient->response[ $plugin->slug ] = (object) $response;

			}
		}

		return $transient;

	}

	/**
	 * Build $this->plugins, a list of Github-hosted plugins based on installed plugin headers
	 *
	 * @return void
	 */
	public function load_plugins( $plugins ) {
		$transient_key = GPU_Controller::OPTION_KEY . '-plugin-objects';

		$this->plugins = get_site_transient( $transient_key );

		if ( false !== $this->plugins ) {
			return;
		}

		global $wp_version;

		foreach ( get_plugins() as $slug => $args ) {
			$args = array_merge( array( 'slug' => $slug ), $args );
			
			$plugin = $this->get_plugin_updater_object( $args );

			if ( false === $plugin ) {
				continue;
			}

			// Using folder name as key for array_key_exists() check in $this->plugins_api()
			$this->plugins[ $plugin->key ] = $plugin;

		}

		// Refresh plugin list and Git metadata
		set_site_transient( $transient_key, $this->plugins, self::$update_interval );

	}

	/**
	 * Return appropriate repository handler based on URI
	 *
	 * @return object|bool GPU_Updater_Github|GPU_Updater_Bitbucket|GPU_Updater_Gitweb|false
	 */
	public function get_plugin_updater_object( $args ) {

		if ( GPU_Updater_Github::updates_this_plugin( $args ) ) {
			return new GPU_Updater_Github( $args );
		}

		if ( GPU_Updater_Bitbucket::updates_this_plugin( $args ) ) {
			return new GPU_Updater_Bitbucket( $args );
		}

		/*
		if ( GPU_Updater_Gitweb::updates_this_plugin( $args ) ) {
			// if ( '.git' == substr($parsed['path'], -4) ) {
			// 	return new GPU_Updater_Gitweb( array_merge( $args, $parsed ) );
			// }
			return new GPU_Updater_Gitweb( $args );
		}
		*/

		return false;
	}

	/**
	 * Disable SSL only for Git repo URLs
	 *
	 * @return array $args http_request_args
	 */
	public function disable_git_ssl_verify( $args, $url ) {

		$ssl_disabled_urls = apply_filters( 'gpu_ssl_disabled_urls', array() );

		if ( in_array( $url, $ssl_disabled_urls ) ) {
			$args['sslverify'] = false; 
		}

		// Zip URLs within the disabled URLs
		foreach ( $ssl_disabled_urls as $disabled_url ) {
			if ( false !== strpos( $url, $disabled_url ) && '.zip' == substr( $url, -4 ) ) {
				$args['sslverify'] = false; 
			}
		}

		return $args;
	}

	/**
	 * Get Plugin info
	 *
	 * @param  bool   $false    Always false
	 * @param  string $action   The API function being performed
	 * @param  object $args     Plugin arguments
	 * @return object $response The plugin info
	 */
	public function plugins_api( $false, $action, $response ) {
		if ( 'query_plugins' == $action ) {
			return $false;
		}

		// API sometimes passes full slug instead of just dirname
		// e.g., on plugin_information page
		if ( false === strpos( $response->slug, '/') ) {
			$plugin_key = $response->slug;
		}else {
			$plugin_key = explode( '/', $response->slug );
			$plugin_key = $plugin_key[0];
		}

		if ( !array_key_exists( $plugin_key, (array)$this->plugins ) ) {
			return false;
		}
		$plugin = $this->plugins[ $plugin_key ];

		$response->slug          = $plugin->slug;
		$response->plugin_name   = $plugin->name;
		$response->version       = $plugin->remote_version;
		$response->author        = $plugin->author;
		$response->homepage      = $plugin->homepage;
		$response->requires      = $plugin->requires;
		$response->tested        = $plugin->tested;
		$response->downloaded    = 0;
		$response->last_updated  = $plugin->last_updated;
		$response->download_link = $plugin->zip_url;

		$response->sections = ( 'plugin_information' == $action ) ? $plugin->sections : array();

		return $response;
	}


	/**
	 * Upgrader/Updater
	 * Move & activate the plugin, echo the update message
	 *
	 * @since 1.0
	 * @param boolean $true always true
	 * @param mixed $hook_extra not used
	 * @param array $result the result of the move
	 * @return array $result the result of the move
	 */
	public function upgrader_post_install( $true, $hook_extra, $result ) {

		global $wp_filesystem;

		$plugin_key = dirname($hook_extra['plugin']);

		if ( !array_key_exists( $plugin_key, $this->plugins ) ) {
			return $result;
		}

		$plugin = $this->plugins[ $plugin_key ];

		// Move & Activate
		$proper_destination = WP_PLUGIN_DIR . '/' . $plugin->folder_name;
		$wp_filesystem->move( $result['destination'], $proper_destination );
		$result['destination'] = $proper_destination;
		$activate = activate_plugin( WP_PLUGIN_DIR . '/' . $plugin->slug );

		// Output the update message
		$fail		= __('The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'git-plugin-updates' );
		$success	= __('Plugin reactivated successfully.', 'git-plugin-updates' );

		echo is_wp_error( $activate ) ? $fail : $success;
		return $result;

	}

}
