<?php
/**
 * Verify we're running PHP 5.2
 * Used only if WordPress is older than version 3.2
 */

// PHP Version Check
$php_is_outdated = version_compare( PHP_VERSION, '5.2.4', '<' );

// Only exit and warn if on admin page
$okay_to_exit = is_admin() && ( !defined('DOING_AJAX') || !DOING_AJAX );

if ( $php_is_outdated ) {
	if ( $okay_to_exit ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		deactivate_plugins( __FILE__ );
		wp_die( sprintf( __(
			'%s requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself. For information on upgrading, %ssee this article%s.', GHPS_PLUGIN_SLUG ),
			GPU_PLUGIN_NAME,
			'<a href="http://codex.wordpress.org/Switching_to_PHP5" target="_blank">',
			'</a>'
		) );
	} else {
		return;
	}
}