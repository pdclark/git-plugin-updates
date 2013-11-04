<?php
/**
 * Manage updates for plugins hosted on Bitbucket.
 */
class GPU_Updater_Bitbucket extends GPU_Updater {

	/**
	 * Static so we can access these values without instantiating the object.
	 * 
	 * @var array Git URI domain names this updater should be applied to.
	 */
	public static $valid_domain_names = array(
		'bitbucket.org',
		'www.bitbucket.org',
	);
	
	public function __construct( $args ){
		parent::__construct( $args );

		// Todo: Properly set branch from plugin header
		$this->branch = 'master';
	}

	public function set_repo_info( $plugin ) {
		parent::set_repo_info( $plugin );

		// Remove .git extension
		$this->repository = str_replace( '.git', '', $this->repository );
	}

	/**
	 * Read the remote plugin file.
	 *
	 * Uses a transient to limit the calls to the API.
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 * 
	 * @param string $type plugin|readme
	 */
	protected function get_remote_info( $type = 'plugin' ) {
		if ( 'plugin' == $type ){
			$type = 'raw/' . $this->branch . '/' . basename( $this->slug );
		}

		// Transients fail if key is longer than 45 characters
		$transient_key = 'gpu-' . md5( $type );

		$remote = get_site_transient( $transient_key );

		if ( false === $remote ) {
			$remote = $this->api( '/repositories/:owner/:repo/' . $type );

			if ( $remote ) {
				set_site_transient( $transient_key, $remote, GPU_Controller::$update_interval );
			}
		}
		return $remote;
	}

	/**
	 * Call the Bitbucket API and return a json decoded body.
	 *
	 * @author Andy Fragen, Codepress
	 * @link   https://github.com/afragen/github-updater
	 * 
	 * @see    http://developer.github.com/v3/
	 * @param  string $url
	 * @return boolean|object
	 */
	protected function api( $url ) {

		$request_args = $this->maybe_authenticate_http( $this->git_request_args );
		$response = wp_remote_get( $this->get_api_url( $url ), $request_args );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != '200' ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$json = json_decode( wp_remote_retrieve_body( $body ) );

		if ( null === $json ) {
			return $body;
		}

		return $json;
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

		// if ( ! empty( $this->access_token ) )
		// 	$endpoint = add_query_arg( 'access_token', $this->access_token, $endpoint );

		// If a branch has been given, only check that for the remote info.
		// If it's not been given, Bitbucket will use the Default branch.
		// if ( ! empty( $this->branch ) )
		// 	$endpoint = add_query_arg( 'ref', $this->branch, $endpoint );

		return 'https://api.bitbucket.org/1.0' . $endpoint;
	}

	protected function get_zip_url() {

		return 'https://bitbucket.org/' . $this->owner . '/' . $this->repository .
		       '/get/' . $this->branch . '.zip';

	}

	/**
	 * Get plugin details section for plugin details iframe
	 *
	 * @return array Sections array for wp-admin/plugin-install.php::install_plugin_information()
	 */
	protected function get_sections() {
		return array(
			'description' => '<pre>Plugin details not yet supported for Bitbucket repositories.</pre>',
		);
	}

	public function maybe_authenticate_http( $args ) {
		if ( !empty( $this->access_token ) ) {
			return $args;
		}

		$username = apply_filters( 'gpu_username_bitbucket', $this->username );
		$password = apply_filters( 'gpu_password_bitbucket', $this->password );

		if ( $username && $password ) {
			$args['headers']['Authorization'] = 'Basic ' . base64_encode( "$username:$password" );
		}

		return $args;
	}

}