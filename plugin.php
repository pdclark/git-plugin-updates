<?php
/*
Plugin Name: WP Github Plugin Updater
Plugin URI: https://github.com/brainstormmedia/git-updater
Description: Update plugins hosted on <a href="http://github.com" target="_blank">Github</a> and <a href="http://bitbucket.org" target="_blank">Bitbucket</a>. Search and install plugins from Github using <a href="https://github.com/brainstormmedia/github-plugin-search/" target="_blank">Github Plugin Search</a>.
Version: 0.1
Real Version: 1.0
Author: Brainstorm Media
Author URI: http://brainstormmedia.com/
License: GPLv2
*/

/**
 * NOTE: Version number above is 0.1 to test updates.
 * 
 * Forked from WordPress Github Plugin Updater by Joachin Kudish
 * @link https://github.com/jkudish/WordPress-GitHub-Plugin-Updater
 * @link http://jkudish.com
 */

define( 'WP_GITHUB_FORCE_UPDATE', WP_DEBUG );

include_once dirname(__FILE__).'/updater.php';