# Usage instructions

The plugin can be either be activated in WordPress, or can be packaged into your own plugin using `require_once 'git-plugin-updates/git-plugin-updates.php';`.

Either way, the plugin will activate updates for every plugin with a Git or Bitbucket repository in its header:

	/*
	Plugin Name: Plugin Example
	Plugin URI: https://github.com/brainstormmedia/git-plugin-updates
	Git URI: https://github.com/brainstormmedia/git-plugin-updates
	*/

Either `Plugin URI` or `Git URI` can be set to your repository address. You don't need both.

For private repos, you can use the URI format:
`https://username:password@bitbucket.org/brainstormmedia/git-plugin-updates`

# Changelog

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

It has been updated with some methods from [Github Updater](https://github.com/afragen/github-updater) by [Andy Fragen](https://github.com/afragen "Andy Fragen, Codepress").