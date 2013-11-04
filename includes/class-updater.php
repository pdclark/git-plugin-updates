<?php
/**
 * Definition of required Updater class structure.
 * Methods shared by child updaters.
 */
abstract class GPU_Updater {

	protected $name;
	protected $slug;
	protected $folder_name;
	protected $key;
	protected $host;
	protected $username;
	protected $password;
	protected $owner;
	protected $repository;
	protected $version;
	protected $author;
	protected $homepage;
	protected $requires;
	protected $tested;

	protected $local_version;
	protected $remote_version;
	protected $default_branch;
	protected $remote_info;
	protected $last_updated;
	protected $zip_url;
	protected $sections;


	/**
	 * @var array Settings for the Github request. Filter with ghu_http_request_args.
	 */
	var $git_request_args = array(
		'timeout' => 5,
		'sslverify' => false,
	);

	public function __construct( $args ){

		global $wp_version;
		
		$defaults = array(
			'name'        => $args['Name'],
			'slug'        => $args['slug'],
			'folder_name' => dirname( $args['slug'] ),
			'key'         => dirname( $args['slug'] ),
			'version'     => $args['Version'],
			'author'      => $args['Author'],
			'homepage'    => $args['PluginURI'],
			'requires'    => $wp_version,
			'tested'      => $wp_version,
		);

		$args = wp_parse_args( $args, $defaults );

		foreach( $args as $key => $value ) {
			$this->$key = $value;
		}

		$this->set_repo_info( $args );

		add_filter( 'http_request_args', array( $this, 'maybe_authenticate_zip_url' ), 10, 2 );

		add_filter( 'gpu_ssl_disabled_urls', array( $this, 'ssl_disabled_urls' ) );

	}

	/**
	 * If a protected variable is accessed from outside the class,
	 * return a value from method $this->get_$var() or $this->$var
	 * 
	 * For example, $this->unread_count returns $this->get_unread_count()
	 * 
	 * @return $this->get_$var()|$this->$var
	 */
	public function __get( $var ) {
		$method = 'get_' . $var;

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}else {
			return $this->$var;
		}
	}

	/**
	 * Should this class be used as the updater the passed plugin?
	 * Static so we can access this method without instantiating the object.
	 * 
	 * @see GPU_Controller::get_plugin_updater_object()
	 * @param $plugin array Metadata for a plugin
	 */
	public static function updates_this_plugin( $plugin ) {
		
		/**
		 * static:: calls a static class in the extending class.
		 * @see http://stackoverflow.com/questions/2859633/why-cant-you-call-abstract-functions-from-abstract-classes-in-php
		 */

		$uri = static::parse_plugin_uri( $plugin );

		if ( in_array( $uri['host'], static::$valid_domain_names ) ) {
			return true;
		}
		return false;

	}

	/**
	 * @param  $plugin array Plugin metadata
	 * @return array         Parsed plugin URI
	 */
	public static function parse_plugin_uri( $plugin ) {

		if ( !empty( $plugin['Git URI'] ) ) {
			$url = parse_url( $plugin['Git URI'] );
		}elseif ( apply_filters( 'gpu_use_plugin_uri_header', false ) ) {
			$url = parse_url( $plugin['PluginURI'] );
		}

		return $url;
	}

	public function ssl_disabled_urls( $urls ) {
		
		if ( !empty( $this->homepage ) ) {
			$urls[] = $this->homepage;
		}
		$urls[] = $this->get_api_url( '/repos/:owner/:repo' );

		return $urls;
	}

	/**
	 * Retrieves the local version from the file header of the plugin
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 * 
	 * @return string|boolean Version of installed plugin, false if not determined.
	 */
	protected function get_local_version() {
		$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->slug );

		if ( ! empty( $data['Version'] ) )
			return $data['Version'];

		return false;
	}

	/**
	 * Retrieve the remote version from the file header of the plugin
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 * 
	 * @return string|boolean Version of remote plugin, false if not determined.
	 */
	protected function get_remote_version() {
		$response = $this->get_remote_info();
		if ( false === $response ) {
			return false;
		}

		// Todo: Handle this switch in the sub classes
		if ( isset( $response->encoding ) && 'base64' == $response->encoding ) {
			// Github
			$content = base64_decode( $response->content );
		}else {
			// Bitbucket
			$content = $response;
		}

		preg_match( '/^[ \t\/*#@]*Version\:\s*(.*)$/im', $content, $matches );

		if ( ! empty( $matches[1] ) )
			return $matches[1];

		return false;
	}

	/**
	 * Parse the remote info to find what the default branch is.
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 *
	 * @return string Default branch name.
	 */
	protected function get_default_branch() {
		// If we've had to call this default branch method, we know that a branch header has not been provided. As such
		// the remote info was retrieved without a ?ref=... query argument.
		$response = $this->get_remote_info();

		// If we can't contact API, then assume a sensible default in case the non-API part of GitHub is working.
		if ( false === $response ) {
			return 'master';
		}

		// Assuming we've got some remote info, parse the 'url' field to get the last bit of the ref query string
		$components = parse_url( $response->url, PHP_URL_QUERY );
		parse_str( $components );
		return $ref;
	}

	/**
	 * Get the last updated date from remote repo
	 */
	protected function get_last_updated() {
		return false;
	}

	/**
	 * Get plugin details section for plugin details iframe
	 *
	 * @return array Sections array for wp-admin/plugin-install.php::install_plugin_information()
	 */
	protected function get_sections() {
		$readme = $this->get_remote_info( 'readme' );

		if ( false === $readme ) {
			return array();
		}

		$readme = base64_decode( $readme->content );

		// Maybe TODO: parse readme textile into sections.
		// Also, maybe not, because $readme could be markdown.
		return array(
			'description' => '<pre>' . $readme . '</pre>',
			// 'installation' => $readme,
			// 'changelog' => $readme,
		);
	}

	/**
	 * Set repo host, owner, username, password, and repository from URI
	 */
	protected function set_repo_info( $plugin ) {

		// parse_plugin_uri() defined in GPU_Updater
		$uri  = self::parse_plugin_uri( $plugin );
		$path = explode('/', $uri['path'] );

		$this->host       = $uri['host'];
		$this->username   = str_replace( '%40', '@', $uri['user'] );
		$this->password   = $uri['pass'];
		$this->owner      = $path[1];
		$this->repository = $path[2];

	}

	/**
	 * Disable SSL only for Git repo URLs
	 *
	 * @return array $args http_request_args
	 */
	public function maybe_authenticate_zip_url( $args, $url ) {

		if ( $url == $this->get_zip_url() ) {
			$args = $this->maybe_authenticate_http( $args );
		}

		return $args;
	}

	abstract protected function api( $url );

	abstract protected function get_api_url( $endpoint );

	abstract protected function get_remote_info();
	
	abstract protected function get_zip_url();

	abstract protected function maybe_authenticate_http( $args );

}