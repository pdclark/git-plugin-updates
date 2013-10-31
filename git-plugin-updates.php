<?php
/*
Plugin Name: Git Plugin Updates
Plugin URI: https://github.com/brainstormmedia/git-plugin-updates
Description: Update plugins hosted on <a href="http://github.com" target="_blank">Github</a> and <a href="http://bitbucket.org" target="_blank">Bitbucket</a>. Search and install plugins from Github using <a href="https://github.com/brainstormmedia/github-plugin-search/" target="_blank">Github Plugin Search</a>.
Version: 2.0
Author: Brainstorm Media
Author URI: http://brainstormmedia.com/
License: GPLv2
*/

/**
 * ## TESTING
 * Change version number above to 0.1 to test updates.
 */

/**
 * Verify that update library not already included by another plugin.
 * Verify that we're running WordPress 3.2 (which enforces PHP 5.2.4).
 * Verify we're in wp-admin -- plugin doesn't need to load in front-end.
 */
if (
	is_admin()
	&& !class_exists( 'GPU_Controller' )
	&& version_compare( $wp_version, '3.2', '>=' )
) :	

	/**
	 * Load plugin dependencies and instantiate the plugin.
	 */
	function gpu_git_plugin_updates_init() {

		require_once dirname( __FILE__ ) . '/includes/class-controller.php';
		require_once dirname( __FILE__ ) . '/includes/class-updater.php';
		require_once dirname( __FILE__ ) . '/includes/class-updater-github.php';
		require_once dirname( __FILE__ ) . '/includes/class-updater-bitbucket.php';

		GPU_Controller::get_instance( array(
			'plugin_name'     => 'Git Plugin Updates',
			'plugin_slug'     => 'git-plugin-updates',
			'option_key'      => 'git-plugin-updates',
			'plugin_file'     => __FILE__,
			'update_interval' => apply_filters( 'gpu_update_interval', 60*60 ),
		) );

	}

	add_action( 'plugins_loaded', 'gpu_git_plugin_updates_init' );

endif;