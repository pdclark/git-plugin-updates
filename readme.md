# Usage instructions

This plugin will activate updates for every plugin with a Git or Bitbucket repository in its header:

	/*
	Plugin Name: Plugin Example
	Plugin URI: https://github.com/brainstormmedia/git-plugin-updates
	Git URI: https://github.com/brainstormmedia/git-plugin-updates
	*/

Either `Plugin URI` or `Git URI` can be set to your repository address. You don't need both.

For private repos, you can use the URI format:

	https://username:password@bitbucket.org/brainstormmedia/git-plugin-updates

### Using as a library in your own plugins

Ideally, Git Plugin Updates runs as a stand-alone plugin. However, if you would like to bundle it as a package in your own plugins to make sure updates over Git are enabled by default, you may do so by moving `git-plugin-updates` into your plugin directory, then activating updates with this code:

	add_action( 'plugins_loaded', 'myplugin_git_updater' );

	function myplugin_git_updater() {
		if ( is_admin() && !class_exists( 'GPU_Controller' ) ) {
			require_once dirname( __FILE__ ) . '/git-plugin-updates/git-plugin-updates.php';
			add_action( 'plugins_loaded', 'GPU_Controller::get_instance', 20 );
		}
	}

This method allows your plugin to update over Git, and if Git Plugin Updates is installed as a plugin later, only the stand-alone-plugin copy will load.

# Changelog

### 2.0.1

* New: Updater ran as plugin overrides and prevents load of additional updaters included as libraries.
* New: Cleaner readme code examples.
* New: Ignore `Plugin URI` header by default to avoid conflicts with wordpress.org. Override with `add_filter( 'gpu_use_plugin_uri_header' '__return_true' );`
* Fix: Don't use variables for text-domains. See [Internationalization: You're probably doing it wrong](http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/).
* Minor: Code cleanup. Simplify plugin load. Remove unused `log` and `__get` methods. Remove variable github and bitbucket hosts. Move constants into `GPU_Controller`. Reorder pre-load checks in order of liklihood.

### 2.0
* Rewrite to support Github as well as Bitbucket
* Updates enabled on plugins by including a Git repository address in the Plugin Header under `Plugin URI` or `Git URI`.
* Enable private repositories with `URI` format `https://username:password@repo_address`.
* Get remote version number from plugin header.

### 1.4
* Minor fixes from [@sc0ttkclark](https://github.com/sc0ttkclark)'s use in Pods Framework
* Added readme file into config

### 1.3
* Fixed all php notices
* Fixed minor bugs
* Added an example plugin that's used as a test
* Minor documentation/readme adjustments

### 1.2
* Added phpDoc and minor syntax/readability adjusments, props [@franz-josef-kaiser](https://github.com/franz-josef-kaiser), [@GaryJones](https://github.com/GaryJones)
* Added a die to prevent direct access, props [@franz-josef-kaiser](https://github.com/franz-josef-kaiser)

### 1.0.3
* Fixed sslverify issue, props [@pmichael](https://github.com/pmichael)

### 1.0.2
* Fixed potential timeout

### 1.0.1
* Fixed potential fatal error with wp_error

### 1.0
* Initial Public Release


# Credits

This plugin is written and maintained by [Paul Clark](http://pdclark.com "pdclark").

It was forked from [WordPress Github Plugin Updater](https://github.com/jkudish/WordPress-GitHub-Plugin-Updater) by [Joachim Kudish](http://jkudish.com "Joachim Kudish").

It has been updated with methods from [Github Updater](https://github.com/afragen/github-updater) by [Andy Fragen](https://github.com/afragen "Andy Fragen, Codepress") and [@GaryJones](https://github.com/garyjones).
