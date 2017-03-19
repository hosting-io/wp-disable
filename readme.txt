=== Plugin Name ===
Contributors: pigeonhut, Jody Nesbitt, optimisation.io
Donate link: ***
Tags: Disable Emoji, Disable Embeds, Disable Gravatars, Remove Querystrings, HTTP Requests, WooCommerce disable, Close comments, force pagination
Requires at least: 4.6
Tested up to: 4.7.3
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Quickly and easily Disable Emojis, Disable Gravatars, Disable Embeds and Remove Querystrings.
Added support for disable ping/trackbacks, Added the ability to close comments after 28 days, Added the ability to force pagingation after 20 posts,
Added Disable WooCommerce scripts and CSS on non WooCommerce Pages

== Description ==

Having used disable items in the past, it made sense to put everything in one place, so its a single plugin with a few options instead of 4 plugins.

Planned features if there is enough support/usage for the plugin: Offload Google fonts and Fontawesome to our MaxCDN account or re-writing rules to
load Google and MaxCDN from your (our?) own CDN.

<b>Emojis</b><br>
Disabling Emojis does not disable emoticons, it disables the support for Emojis added since WP 4.2, it also gets rid of 1 HTTP request helping speed up your site.

<b>Embeds</b><br>
Disabling Embeds This is a script that auto formats pasted content in the visual editor, such as videos, tweets, etc. However, this is not really needed.
A big issue with this script is that it loads on every single page, whether it is being used or not. You can still use the default embed code from YouTube
and Twitter to included content, even when this script is disabled. And we get rid of another HTTP Request

<b>Remove Query Strings</b>
If you look at your source or a waterfall view of your page load, you will see your query strings end in something like ver=1.12.4 or vers=3.4.1. These are
called query strings and help determine the version of the script. The problem with query strings like these is that it isn’t very efficient for caching
purposes and sometimes prevents caching those assets altogether.  If you are using a CDN already, you can ignore this OPTION_KEY

<b>Gravatars</b><br>
Disabling Gravatars is completely optional, advise, if you dont use them, disable as it gets rid of one more useless HTTP request.

<b>General Performance improvements</b>
Added support for : disable ping/trackbacks, close comments after 28 days, force pagingation after 20 posts, Disable WooCommerce scripts and CSS on non WooCommerce Pages

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->WP Disable screen to configure the plugin


== Frequently Asked Questions ==

= Do I still need caching ? =

Yes, Cache Enabler is very light weight and will give you massive improvements

= Do I still need a CDN ? =

Yes, Max or KeyCDN are our recommended choices.

== Screenshots ==


== Changelog ==
= 1.1.2
Added support for disable ping/trackbacks
Added the ability to close comments after 28 days
Added the ability to force pagingation after 20 posts
Added Disable WooCommerce scripts and CSS on non WooCommerce Pages
= 1.0 =
* Initial commit

== Upgrade Notice ==

= 1.0 =
Nothing here yet
