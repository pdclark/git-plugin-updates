Git Plugin Updates for WordPress

This plugin can be installed on its own, or included in your own plugins or themes. It scans all installed plugins for Git repository addresses in the Plugin Headers, then enables automatic updates from those repositories.

Usage instructions
===========

* The plugin can be either be activated in WordPress, or `plugin.php` can be included in your own plugin using `require_once 'git-plugin-updates/plugin.php';`.
* Either way, the plugin will activate Github updates for every plugin with a Git or Bitbucket repository in its header:

	<pre>
	/*
	Plugin Name: Plugin Example
	Plugin URI: https://github.com/brainstormmedia/git-plugin-updates
	Git URI: https://github.com/brainstormmedia/git-plugin-updates
	*/
	</pre>

Either `Plugin URI` or `Git URI` can be set to your repository address. You don't need both.

For private repos, you can use the URI format `https://username:password@github.com/brainstormmedia/git-plugin-updates`.

Changelog
===========

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

Credits
===========

This plugin is written and maintained by [Paul Clark](http://pdclark.com "pdclark").

It was forked from [WordPress Github Plugin Updater](https://github.com/jkudish/WordPress-GitHub-Plugin-Updater) by [Joachim Kudish](http://jkudish.com "Joachim Kudish").

It has been updated with some methods from [Github Updater](https://github.com/afragen/github-updater) by [Andy Fragen](https://github.com/afragen "Andy Fragen, Codepress").

License
===========

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to:

Free Software Foundation, Inc.
51 Franklin Street, Fifth Floor,
Boston, MA
02110-1301, USA.