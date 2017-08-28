=== Reduce HTTP Requests, Disable Emojis & Disable Embeds, Speedup WooCommerce ===
Contributors: pigeonhut, Jody Nesbitt, optimisation.io
Tags: Disable Emoji, Disable Embeds, Disable Gravatars, Remove Querystrings, Reduce HTTP Requests, speedup WooCommerce, Close comments
Requires at least: 4.5
Tested up to: 4.8
Stable tag: 1.4.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reduce HTTP requests - Disable Emojis, Disable Gravatars, Disable Embeds and Remove Querystrings. SpeedUp WooCommerce, Added support to disable pingbacks, disable trackbacks, close comments after 28 days, Added the ability to force pagingation after 20 posts,
Disable WooCommerce scripts and CSS on non WooCommerce Pages, Disable RSS, Disable XML-RPC, Disable Autosave, Remove Windows Live Writer tag, Remove Shortlink Tag, Remove WP API from header and
 many more features to help speed and SEO gains.

== Description ==
<strong>Reduce HTTP requests</strong> - Disable Emojis, Disable Gravatars, Disable Embeds and Remove Querystrings. SpeedUp WooCommerce, Added support to disable pingbacks, disable trackbacks, close comments after 28 days, Added the ability to force pagingation after 20 posts,
Disable WooCommerce scripts and CSS on non WooCommerce Pages, Disable RSS, Disable XML-RPC, Disable Autosave, Remove Windows Live Writer tag, Remove Shortlink Tag, Remove WP API from header and
 many more features to help speed and SEO gains.  Now includes <strong>Disable Comments, Heartbeat Control, Selective Disable</strong>

 <strong>**NEW Features:**</strong>
 Better Stats on Dashboard
 Disable loading dashicons on front end if admin bar disabled
 Disable Author Pages

Disabling Emojis does not disable emoticons, it disables the support for Emojis added since WP 4.2 and removes 1 HTTP request.<br>

Disabling Embeds  - script that auto formats pasted content in the visual editor, eg videos, etc. Big issue with this script is it loads on every
single page. You can still use the default embed code from YouTube, Twitter etc to included content.

Remove Query Strings: If you look at the waterfall view of your page load, you will see your query strings end in something like ver=1.12.4.
These are called query strings and help determine the version of the script. The problem with query strings like these is that it isnâ€™t very efficient for caching purposes and sometimes prevents caching those assets altogether.  If you are using a CDN already, you can ignore this.

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
= 1.4.4 =
* Added ability to stop (disable) admin notices from showing
* removed the stats sub menu item, so everything is now at the top level
* "local-ga.js" file was created on activation, changed the way this works so it will work now independant of when adding the GA code

= 1.4.3 =
More dashboard visual tweaks.
No new features, but this is a stepping stone.

= 1.4.2 =
* General tidy up on dashboard

= 1.4.1 =
* removed third party errors out of our dashboard to the top of the page where they belong
* cleaned out redundant data in GA cache file

= 1.4.0 =
* New Dashboard Design (Work in progress)
* Added Average load time of pages to stats
* Remove Comments navigation when comments disabled
* Added the ability to block referrer spam (using Piwik Database)
* Updated Import/Export settings to now include settings for Image Compression and Cache plugins (if active)
* General code improvements

= 1.3.22 =
Added ability to Import and Export settings between sites
Removed Visual Banner Ads from inside plugin
Fix - Keep Active Tab State after saving
Please note *** if you use either our cache or image compression plugin, they both need to be updated to take full advantage of these changes***

= 1.3.21 =
Further cleanup to Navigation
Fixed Pagination
Improvements based on Settings --> Discussion core features
<strong>**NEW Features:**</strong>
Better Stats on Dashboard
Disable loading dashicons on front end if admin bar disabled
Disable Author Pages
One click addon for Cache, CDN paths and Image Optimisation

= 1.3.20 =
Added Dashboard to show stats (work in progress) - aim is to get rid of the sidebar ads
Moving towards a Modular version to enable a cleaner panel for users.
Improved function checks for is WooCommerce active
Improved Settings layout
Added better analysis to help us improve.

= 1.3.12 =
Fix to Some features not showing on existing installs
Updated menu icon
tidied up navigation
Removed Admin bar navigation

= 1.3.11 =
Added support for Heartbeat (please remove any other heartbeat plugins)
Fixed sidebar navigation
Added a top navigation for easy access (to support upcoming features)
Fixed Remove RSD error
General code tidy up and removed unused functions

= 1.3.1 =
Cleaned up navigation (moved to Optimisation.io in WP menu)

= 1.3.0 =
Added default option to Google Analytics to make it more clear when its active
Option to Remove password strength meter js on non woo pages
Added option to completely disable comments
Improved folder/file structure
Added option to disable feed, replaced Disable RSS
Added option to remove spam comments
Option to combine and async Google Fonts and Font awesome fonts for better performance
Removed old versions from WP Repo for better WP Compliance
