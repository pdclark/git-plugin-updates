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
// Don't crash sites running very old versions of PHP and WordPress.
add_action( 'admin_notices', 'gpu_quick_exit' );

function gpu_quick_exit() {
	global $wp_version;
	if ( version_compare( $wp_version, '3.2', '<' ) && version_compare( PHP_VERSION, '5.2.4', '<' ) ) {
		echo '<div class="error"><p>' . __( 'You don\'t have the resources to use this plugin.' , GPU_PLUGIN_SLUG ) . '</div>';
		deactivate_plugins( plugin_basename( __FILE__ ) );
		return;
	}
}

/**
 * Used for localization text-domain, which must match wp.org slug.
 * Used for wp-admin settings page slug.
 * 
 * @var string Slug of the plugin on wordpress.org.
 */
if ( !defined( 'GPU_PLUGIN_SLUG') )
	define( 'GPU_PLUGIN_SLUG', 'git-plugin-updates' );

/**
 * Used for error messages.
 * Used for settings page title.
 * 
 * @var string Nice name of the plugin.
 */
if ( !defined( 'GPU_PLUGIN_NAME') )
	define( 'GPU_PLUGIN_NAME', __( 'Git Plugin Updates', GPU_PLUGIN_SLUG ) );

/**
 * @var string Absolute path to this file.
 */
if ( !defined( 'GPU_PLUGIN_FILE') )
	define( 'GPU_PLUGIN_FILE', __FILE__ );

/**
 * @var string Absolute path to the root plugin directory
 */
if ( !defined( 'GPU_PLUGIN_DIR' ) )
	define( 'GPU_PLUGIN_DIR', dirname( __FILE__ ) );

// Be cautious, since this class might be included by multiple plugins.
if ( !function_exists( 'storm_git_plugin_updates_init' ) && !class_exists( 'GPU_Controller' ) && is_admin() )
	add_action( 'plugins_loaded', 'storm_git_plugin_updates_init' );

/**
 * Load plugin dependencies and instantiate the plugin.
 * Checks PHP version. Deactivates plugin and links to instructions if running PHP 4.
 */
function storm_git_plugin_updates_init() {

	require_once dirname( __FILE__ ) . '/includes/class-controller.php';
	require_once dirname( __FILE__ ) . '/includes/class-updater.php';
	require_once dirname( __FILE__ ) . '/includes/class-updater-github.php';
	require_once dirname( __FILE__ ) . '/includes/class-updater-bitbucket.php';

	GPU_Controller::get_instance();

}
