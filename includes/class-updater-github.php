<?php

class GPU_Updater_Github extends GPU_Updater {

	/**
	 * Static so we can access these values without instantiating the object.
	 * 
	 * @var array Git URI domain names this updater should be applied to.
	 */
	public static $valid_domain_names = array(
		'github.com',
		'www.github.com',
	);
	
	public function __construct( $args ){
		parent::__construct( $args );
	}

	/**
	 * Set repo host, owner, username, password, and repository from URI
	 */
	protected function set_repo_info( $plugin ) {
		$uri  = self::parse_plugin_uri( $plugin );
		$path = explode('/', $uri['path'] );

		$this->host       = $uri['host'];
		$this->username   = $uri['user'];
		$this->password   = $uri['pass'];
		$this->owner      = $path[1];
		$this->repository = $path[2];

	}

	/**
	 * Read the remote plugin file.
	 *
	 * Uses a transient to limit the calls to the API.
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 */
	protected function get_remote_info() {
		$transient_key = GPU_Controller::OPTION_KEY . '-' . md5( $this->slug );

		$remote = get_site_transient( $transient_key );

		if ( false === $remote ) {
			$remote = $this->api( '/repos/:owner/:repo/contents/' . basename( $this->slug ) );

			if ( $remote ) {
				set_site_transient( $transient_key, $remote, GPU_Controller::$update_interval );
			}
		}
		return $remote;
	}

	/**
	 * Call the GitHub API and return a json decoded body.
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 * 
	 * @see    http://developer.github.com/v3/
	 * @param  string $url
	 * @return boolean|object
	 */
	protected function api( $url ) {
		$response = wp_remote_get( $this->get_api_url( $url ) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != '200' ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Return API url.
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 *
	 * @param string $endpoint
	 * @return string
	 */
	protected function get_api_url( $endpoint ) {
		$segments = array(
			'owner' => $this->owner,
			'repo'  => $this->repository,
		);

		/**
 		 * Add or filter the available segments that are used to replace placeholders.
		 *
		 * @since 1.5.0
		 *
		 * @param array $segments List of segments.
		 */
		$segments = apply_filters( 'gpu_api_segments', $segments );

		foreach ( $segments as $segment => $value ) {
			$endpoint = str_replace( '/:' . $segment, '/' . $value, $endpoint );
		}

		if ( ! empty( $this->access_token ) )
			$endpoint = add_query_arg( 'access_token', $this->access_token, $endpoint );

		// If a branch has been given, only check that for the remote info.
		// If it's not been given, GitHub will use the Default branch.
		if ( ! empty( $this->branch ) )
			$endpoint = add_query_arg( 'ref', $this->branch, $endpoint );

		return 'https://api.github.com' . $endpoint;
	}

	protected function get_zip_url() {

		return 'https://' . $this->host . '/' . $this->owner . '/' . $this->repo .
		       '/archive/' . $this->get_default_branch() . '.zip';

	}

	/**
	 * Get update date
	 *
	 * @since 1.0
	 * @return string $date the date
	 */
	public function get_last_updated() {
		$_date = $this->get_remote_info();
		return ( !empty($_date->updated_at) ) ? date( 'Y-m-d', strtotime( $_date->updated_at ) ) : false;
	}


	/**
	 * Get plugin description
	 *
	 * @since 1.0
	 * @return string $description the description
	 */
	public function get_description() {
		$_description = $this->get_remote_info();
		return ( !empty($_description->description) ) ? $_description->description : false;
	}

}