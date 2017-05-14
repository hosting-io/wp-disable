=== Reduce HTTP Requests, Disable Emojis & Disable Embeds, Speedup WooCommerce ===
Contributors: pigeonhut, Jody Nesbitt, optimisation.io
Tags: Disable Emoji, Disable Embeds, Disable Gravatars, Remove Querystrings, Reduce HTTP Requests, speedup WooCommerce, Close comments
Requires at least: 4.6
Tested up to: 4.7.4
Stable tag: 1.2.27
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reduce HTTP requests - Disable Emojis, Disable Gravatars, Disable Embeds and Remove Querystrings. SpeedUp WooCommerce, Added support to disable pingbacks, disable trackbacks, close comments after 28 days, Added the ability to force pagingation after 20 posts,
Disable WooCommerce scripts and CSS on non WooCommerce Pages, Disable RSS, Disable XML-RPC, Disable Autosave, Remove Windows Live Writer tag, Remove Shortlink Tag, Remove WP API from header and
 many more features to help speed and SEO gains.

== Description ==
<strong>Reduce HTTP requests</strong> - Disable Emojis, Disable Gravatars, Disable Embeds and Remove Querystrings. SpeedUp WooCommerce, Added support to disable pingbacks, disable trackbacks, close comments after 28 days, Added the ability to force pagingation after 20 posts,
Disable WooCommerce scripts and CSS on non WooCommerce Pages, Disable RSS, Disable XML-RPC, Disable Autosave, Remove Windows Live Writer tag, Remove Shortlink Tag, Remove WP API from header and
 many more features to help speed and SEO gains.

<strong>Coming soon - Disable Comments, Heartbeat Control, Selective Disable</strong>

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

Yes, We have just release a <a href="https://wordpress.org/plugins/cache-performance/">WordPress Caching plugin</a> which is really easy to setup and includes a built in CD-rewrite rule system.<br>

= What about Minification, do I still need it? =

Yes, you absolutely do, and none come close to the awesome <a href="https://en-gb.wordpress.org/plugins/autoptimize/"> Autoptimize</a> by Frank Goossens.

= Do I still need a CDN ? =

Yes, WarpCache is our recommended choice for the ultimate in flexibility and performance. <br>
We will soon be adding a free CDN for css/js for all users that is integrated with just an "on/off" switch in the plugin and no setup.

= What about my Image Compression =

You can try our <a href="https://wordpress.org/plugins/wp-image-compression/">Free Image Compression plugin</a> which has really good compression ratios with little to no loss of image quality.

== Screenshots ==
1. Full site, 16 HTTP requests, 0.5MB
2. What's Possible with some hard work (Clean install)
3. Pingdom Report
4. Fast Hosting Servers make a difference to overall performance
5. Because Speed Matters (tm)


== Changelog ==
= 1.2.26 =
* Improved visuals
* Cleaner Code, fixed issue that breaks access on some installs

= 1.2.25 =
* Bug fix breaking css on woo checkout pages

= 1.2.24 =
* Added ability to remove Ajax calls for WooCommerce on home page
= 1.2.23 =
* Fixed bug in GA Local

= 1.2.22 =
* Minor text clean up

= 1.2.21 =
* Fixed Google Maps bug where maps not showing

= 1.2.2 =
* Fix for GA bug where it disables tracking completely
* IMPORTANT! please ensure you Disable any other GA tracking you have active
* Moved Navigation under Tools to free up the sidebar
* Minor edits on WooCommerce cleanup logic


= 1.2.1 =
* Fix GA options not saving

= 1.2.0 =
* <strong>Major update:</strong>
* Added the ability to Cache Google Analytics scripts
* Added the ability to remove Google Maps calls if your theme has it embedded but you don't want or use it.
* Small bug fixes and general tidy up
* Added links to our Caching Plugin and Image Compression

= 1.1.9 =
Fixed bug in querystrings not disabling fully

= 1.1.8 =
Small bug fix where we were showing other plugin notifications inside ours

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
