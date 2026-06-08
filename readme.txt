=== Featherweight — formerly WP Disable ===
Contributors: pigeonhut
Tags: disable emoji, disable embeds, remove query strings, performance, optimization
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Speed up WordPress by disabling unused features — emojis, embeds, query strings, XML-RPC, RSS and more — for fewer requests and faster pages.

== Description ==

**Featherweight makes your site faster by switching off the features you don't use.** Every disabled item is one less HTTP request, one less script, or one less query — adding up to lighter pages and better Core Web Vitals. Pick only what you need; nothing is forced on.

> **Formerly WP Disable.** Same plugin, new name. Featherweight is part of the Folium Studio suite — your existing settings carry over untouched on update.

= Reduce requests & strip front-end bloat =
* Disable emojis (removes the emoji detection script and styles)
* Remove query strings from static assets (`?ver=...`) for cleaner caching
* Disable oEmbeds (the auto-embed script that loads site-wide)
* Disable Gravatars
* Remove jQuery Migrate on the front end
* Combine and asynchronously load Google Fonts and Font Awesome
* Add DNS-prefetch hints for external hosts
* Remove the password-strength-meter script where it isn't needed
* Drop Dashicons on the front end when the admin toolbar is hidden

= Clean up the page header =
* Remove the generator tag, shortlink, RSD, Windows Live Writer, and REST API link tags

= Comments & discussion =
* Disable comments everywhere, or selectively per post type
* Close comments on older posts and paginate long threads
* Strip links from comments
* Disable pingbacks and trackbacks
* Schedule automatic spam-comment cleanup

= Admin & performance controls =
* Limit or disable post revisions and autosave
* Control the WordPress Heartbeat (frequency and where it runs)
* Disable the REST/XML feeds (RSS), XML-RPC, author archive pages, and admin notices

= SEO helpers =
* Remove the Yoast SEO HTML comment from the head
* Remove duplicate names in Yoast breadcrumbs

= Optional WooCommerce optimisations =
When WooCommerce is active, you can stop its scripts and styles from loading on non-store pages, defer cart fragments, disable product reviews, and skip the password-strength meter on unrelated pages.

= Good to know =
* Disabling emojis does not affect normal emoticons — it only removes the extra emoji-detection script.
* Removing query strings can interfere with some CDNs that key cache on them; leave it off if unsure.
* Everything is optional and reversible — toggle a setting off and the behaviour returns.

**Have an idea or found a bug?** The plugin is developed in the open — see the [public GitHub repo](https://github.com/FoliumStudio/featherweight) to contribute or open an issue.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Open the **Folium → Featherweight** screen to configure the plugin


== Frequently Asked Questions ==

= I would like to contribute/I have an idea =

<a href="https://github.com/FoliumStudio/featherweight">Public repo on GitHub</a> if you would like to contribute or have any ideas to add.

= What about Minification, do I still need it? =

Yes — minification helps on-page performance, but that's outside the remit of this plugin. Featherweight focuses on removing the requests and bloat WordPress adds by default; pair it with a dedicated minification tool if you want that too.

== Screenshots ==
1. Plugin Interface
2. Pingdom Report
4. Fast Hosting Servers make a difference to overall performance
4. Because Speed Matters (tm)


== Changelog ==
= 2.2.1 =
* The Folium suite menu now uses a leaf icon (fitting — "Folium" is Latin for leaf).
* Sitewise now shows as "Coming soon" in the Folium overview until it's publicly released, rather than linking out.
= 2.2.0 =
* **WP Disable is now Featherweight** — a new name and look as part of the Folium Studio suite. Nothing else changes: same plugin, same slug, same settings (everything you've configured carries over automatically on update).
* New plugin icon and banner.
* Refreshed the readme and FAQ.
* Coming soon: Rank Math support, alongside the existing Yoast SEO helpers.
* Housekeeping: removed the unused pre-Folium dashboard, the old cross-plugin add-on installer, and their assets — a smaller, lighter download. No change to any optimisation feature or setting.
= 2.1.0 =
* New admin experience: WP Disable now opens inside the shared "Folium" menu with a redesigned, tabbed settings screen (dashboard, live optimisation count, instant search). All existing settings and their behaviour are unchanged.
* Settings are saved over ajax using the same validation as before — no change to what gets stored.
= 2.0.2 =
* Hardening: block direct access to all plugin PHP files.
* The SEO tab now only appears when a supported SEO plugin (Yoast SEO) is active, and fixed a settings message typo. Props @JeroenSormani.
* Refreshed the plugin description.

= 2.0.1 =
* Housekeeping: corrected the Contributors field to a valid WordPress.org username and shortened the short description to meet the 150-character directory limit.

= 2.0.0 =
* Major modernization for current WordPress (6.x) and PHP 7.4+ / 8.x.
* Removed the obsolete "local Google Analytics" offload (Universal Analytics was sunset by Google in July 2023). Existing GA settings, cron and cache are cleaned up automatically on upgrade.
* Fixed: spam-comment cleaner now deletes correctly; "remove links from comments" no longer blanks comment text; Google Fonts / Font Awesome saved-request counters corrected.
* Fixed: jQuery Migrate removal no longer downgrades core jQuery on modern WordPress.
* Security: output escaping, input sanitization, safe redirects, and sanitized server variables throughout.
* Compliance: unified text domain to "wp-disable", added Requires PHP / Requires at least headers, refreshed branding.
* Removed legacy duplicate/dead code and updated the plugin name per WordPress.org guidelines.

= 1.5.14 =
* Started on Documentation
* Added donation button - help us make this the best optimisation suite available on the repo.  Every $ donated helps.
* Added SEO Tab
* Added ability to remove Duplicate names in breadcrumbs
* Added Remove Yoast SEO comments
* Tested on Gutenberg
* Tested on WP 4.9
* Remove Dequeue from some functions
* Disabled Dashicons in Customizer
* Minor bug fixes as per support forum


= 1.5.13 =
* Added Settings link on main Installed Plugin view
* General code tidy up
* PHP 7.1 compatabile
* WP 4.8.2 tested

= 1.5.12 =
* WooCommerce bugs fixed
* Syntax error fixed
* General improvements to GA Offload (Some cases GA code may still not work, does not appear to be a fix for this, if this happens on yours, please ignore the feature)

= 1.5.11 =
* WooCommerce tab not displaying fixed

= 1.5.1 =
* More visual cleanups
* Removed all webfonts
* Minor bug fix on reporting on dashboard
* Plugin is now under 240kb

= 1.5.0 =
* Finished redesign of plugin
* All stats now in one central dashboard
* Removed sidebar navigation completely
* Remobed Freemius
* Added check for WooCommerce, so Woo related stuff only shows if Woo is installed
* Much tighter integration between the 3 optimisation plugins
* Removed old/excess files


= 1.4.5 =
* More visual fixes/general tidy up
* Added exception to Google Maps so can be enabled per page
* Minor code fixes
* Moved Google Analytics to sidebar/addons

= 1.4.4 =
* Added ability to stop (disable) admin notices from showing
* removed the stats sub menu item, so everything is now at the top level
* "local-ga.js" file was created on activation, changed the way this works so it will work now independent of when adding the GA code

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
