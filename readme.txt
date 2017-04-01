=== Disable Emojis & Disable Embeds for WordPress Performance & SpeedUp ===
Contributors: pigeonhut, Jody Nesbitt, optimisation.io
Tags: Disable Emoji, Disable Embeds, Disable Gravatars, Remove Querystrings, Reduce HTTP Requests, WooCommerce disable, Close comments, force pagination
Requires at least: 4.6
Tested up to: 4.7.3
Stable tag: 1.1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reduce HTTP requests - Disable Emojis, Disable Gravatars, Disable Embeds and Remove Querystrings. Added support to disable pingbacks, disable trackbacks, close comments after 28 days, Added the ability to force pagingation after 20 posts,
Disable WooCommerce scripts and CSS on non WooCommerce Pages, Disable RSS, Disable XML-RPC, Disable Autosave, Remove Windows Live Writer tag, Remove Shortlink Tag, Remove WP API from header and
 many more features to help speed and SEO gains.

== Description ==
Planned features if there is enough support/usage for the plugin: Offload Google and FontAwesome fonts to our MaxCDN account or re-write rules to load from yours.

Disabling Emojis does not disable emoticons, it disables the support for Emojis added since WP 4.2 and removes 1 HTTP request.<br>

Disabling Embeds  - script that auto formats pasted content in the visual editor, eg videos, etc. Big issue with this script is it loads on every
single page. You can still use the default embed code from YouTube, Twitter etc to included content.

Remove Query Strings: If you look at the waterfall view of your page load, you will see your query strings end in something like ver=1.12.4.
These are called query strings and help determine the version of the script. The problem with query strings like these is that it isn’t very efficient for caching purposes and sometimes prevents caching those assets altogether.  If you are using a CDN already, you can ignore this.

Disabling Gravatars is completely optional, advise, if you don't use them, disable as it gets rid of one more useless HTTP request.

General Performance improvements: Added support for : disable ping/trackbacks, close comments after 28 days, force pagingation after 20 posts, Disable WooCommerce scripts and CSS on non WooCommerce Pages.

<b>Have an idea ?</b><br>
<a href="https://github.com/hosting-io/wp-disable">Public repo on GitHub</a> if you would like to contribute or have any ideas to add.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->WP Disable screen to configure the plugin


== Frequently Asked Questions ==

= I would like to contribute/I have an idea =

<a href="https://github.com/hosting-io/wp-disable">Public repo on GitHub</a> if you would like to contribute or have any ideas to add.

= Do I still need caching ? =

Yes, Cache Enabler is very light weight and will give you massive improvements

= Do I still need a CDN ? =

Yes, WarpCache is our recommended choice for the ultimate in flexibility and performance.

== Screenshots ==
1. Full site, 16 HTTP requests, 0.5MB
2. What's Possible with some hard work (Clean install)
3. Pingdom Report
4. Fast Hosting Servers make a difference to overall performance
5. Because Speed Matters (tm)


== Changelog ==
= 1.1.7 =
Mainly Visual changes & prep for caching integration

= 1.1.6 =
Remove Query string activation crash fixed

= 1.1.5 =
Fixed bug on saving tabs from 1.1.4

= 1.1.4 =
Added a tabbed navigation for easier usability.

= 1.1.3 =
Added support for: Disable RSS, XML-RPC, Autosave, RSD, Windows Live Writer tag, Shortlink Tag, WP API from header which in a lot of sites can shave over 1 second on page load.

= 1.1.2 =
Added support for disable ping/trackbacks
Added the ability to close comments after 28 days
Added the ability to force pagingation after 20 posts
Added Disable WooCommerce scripts and CSS on non WooCommerce Pages

= 1.0 =
* Initial commit

== Upgrade Notice ==

= 1.0 =
Nothing here yet
