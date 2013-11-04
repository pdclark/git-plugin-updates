<?php
/*
Plugin Name: Git Plugin Updates
Plugin URI: https://github.com/brainstormmedia/git-plugin-updates
Description: Update plugins hosted on <a href="http://github.com" target="_blank">Github</a> and <a href="http://bitbucket.org" target="_blank">Bitbucket</a>. Search and install plugins from Github using <a href="https://github.com/brainstormmedia/github-plugin-search/" target="_blank">Github Plugin Search</a>.
Version: 2.0.1
Author: Brainstorm Media
Author URI: http://brainstormmedia.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Git URI: https://github.com/brainstormmedia/git-plugin-updates
*/

/**
 * ## TESTING
 * Change version number above to 1.0 to test updates.
 */

/**
 * Verify we're in wp-admin -- plugin doesn't need to load in front-end.
 * Verify that we're running WordPress 3.2 (which enforces PHP 5.2.4).
 */
if ( is_admin() && version_compare( $GLOBALS['wp_version'], '3.2', '>=' ) ) :

	// Load plugin classes and instantiate the plugin.
	require_once dirname( __FILE__ ) . '/includes/class-controller.php';
	require_once dirname( __FILE__ ) . '/includes/class-updater.php';
	require_once dirname( __FILE__ ) . '/includes/class-updater-github.php';
	require_once dirname( __FILE__ ) . '/includes/class-updater-bitbucket.php';

	add_action( 'plugins_loaded', 'GPU_Controller::get_instance', 5 );

endif;