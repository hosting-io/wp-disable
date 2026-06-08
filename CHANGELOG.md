# Changelog (development tracker)

Running, developer-facing record of the modernization (2.0.0), the Folium UI
reskin (2.1.0), and the Featherweight rebrand + dead-code purge (2.2.0).
User-facing release notes live in [`readme.txt`](readme.txt); this file
tracks every concrete change (with file references) as the work in [`TODO.md`](TODO.md) is done.

Format: keep entries newest-first under the unreleased heading; move to a versioned heading on release.
Use the prefixes **Added / Changed / Fixed / Security / Removed / Deprecated**.

---

## [2.2.0] — 2026-06-08 — Featherweight rebrand + dead-code purge

### Changed
- **Rebranded WP Disable → Featherweight** (Folium Studio suite). Cosmetic only — the
  slug, text domain, and main file all stay `wp-disable` per wp.org's permanent-slug
  rules. `Plugin Name` header, `readme.txt` title/description (+ "formerly WP Disable"),
  the Folium `register_plugin` name, and the vendored catalog entry now say
  Featherweight; new feather icon (`images/featherweight-icon.png`). Version 2.1.0 → 2.2.0.
- **Vendored Folium UI 1.0.0 → 1.0.1** (loader version stamp + `Folium_UI::VERSION` +
  `VERSION` file) so the rebranded catalog deterministically wins the newest-wins
  negotiation; re-vendored into Featherweight **and** Sitewise. (Fixes the switcher /
  overview cards still reading "WP Disable" when Sitewise's older copy won the tie.)
- GitHub repo links in `readme.txt` → `FoliumStudio/featherweight` (repo renamed; slug stays).
- `readme.txt` FAQ: removed the stale caching / CDN / image-compression cross-sells;
  rewrote the minification answer ("outside this plugin's remit"); noted **Rank Math
  support coming soon**.

### Added
- wp.org listing assets in `.wordpress-org/` — `icon-128x128`/`icon-256x256` and
  `banner-772x250`/`banner-1544x500` (published to SVN `/assets` by the deploy action,
  excluded from the plugin zip via `.distignore`).

### Removed
- **The entire pre-Folium dead admin layer** (distinct from Phase 1's pre-1.5 cleanup;
  ~6.4k lines, none reachable once Folium UI owns the screen):
  - `views/` (all 3), legacy `css/` (6) and `js/` (6 — kept the live `js/css-lazy-load.js`
    used by the async Google Fonts / Font Awesome feature), the stale optimisation.io
    `images/`, and the orphaned `class-optimisationio-stats-and-addons` +
    `class-optimisationio-upgrader-skin` classes.
  - Slimmed `class-optimisationio-dashboard` 893 → 148 lines (live Folium bridge only:
    register, enqueue, app_data, save/reset ajax, legacy-slug redirect).
  - Dropped the dead `addon_settings()` PHP form from `class-wpperformance-admin`
    934 → 501 lines (the only caller of `checkbox_component`); kept `persist_settings`.
  - `uninstall.php`: dropped the dashboard-class dependency, inlined the legacy
    add-on download-link transient cleanup.

## [2.1.0] — 2026-06-07 — Folium UI reskin

### Added
- Adopted the shared, vendored **Folium UI** design framework (`lib/folium-ui/`,
  newest-wins loader). The plugin now opens inside the single "Folium" admin menu with
  a redesigned tabbed settings screen (dashboard + live optimisation count + instant
  search) instead of its own top-level page.
- `assets/js/wp-disable-app.js` + `assets/css/wp-disable-app.css` — the JS app that
  renders the settings screen against the real 44-key option schema.
- `Optimisationio_Dashboard`: `register_plugin` (Folium nesting), `folium_ui_enqueue`
  hook, `wp_disable_app_save` / `wp_disable_app_reset` ajax, and a redirect from the
  retired top-level slug to the in-frame Folium page.

### Changed
- Settings now save over ajax through the **same** `WpPerformance_Admin::persist_settings()`
  validation as the legacy form — no change to what gets stored.

## [2.0.2] — 2026-06-05

### Security
- Block direct access to all plugin PHP files (`defined( 'ABSPATH'/'WPINC' ) || exit`).

### Changed
- The SEO tab now appears only when a supported SEO plugin (Yoast SEO) is active;
  fixed a settings-message typo (props @JeroenSormani). Refreshed the plugin description.

## [2.0.1] — 2026-06-05

### Changed
- Housekeeping: corrected the `Contributors` field to a valid WordPress.org username
  and shortened the short description to meet the 150-character directory limit.

## [2.0.0] — 2026-06-05 — modernization

### Added
- `TODO.md` — full modernization task list (phases 0–6).
- `CHANGELOG.md` — this development tracker.
- **Phase 0 — tooling:** `phpcs.xml.dist` (WPCS + PHPCompatibilityWP, PHP 7.4+ target) and `.github/workflows/lint.yml` (CI `php -l` matrix on 7.4/8.1/8.2 + non-blocking phpcs). Local lint is blocked on the dev box (no php), so CI is the executing lint path.
- `scripts/ship-wp-disable.sh` (local-only, gitignored) — deploy/lint/zip helper.
- **Release tooling:** `.github/workflows/deploy.yml` (tag-triggered deploy to WordPress.org SVN via `10up/action-wordpress-plugin-deploy`, with a dry-run mode and a version/Stable-tag guard) and `.distignore` (keeps dev files out of the SVN/zip). Requires `SVN_USERNAME` / `SVN_PASSWORD` GitHub secrets.

### Changed
- `wpperformance.php`: dropped the `require_once` for the deleted `class-wpperformance-view.php`.
- **Phase 5 — WP7 / modern compliance:**
  - **Text domain unified to `wp-disable`** across all 150+ `__()/_e()/esc_*` calls (was a mix of `optimisationio` / `wpperformance` / `wpper`, none of which matched the slug, so translations never loaded). `TEXT_DOMAIN` const + `load_plugin_textdomain` updated. Slugs, nonces, and `Optimisationio_*` class names deliberately left unchanged.
  - **Plugin header** (`wpperformance.php`): `Version` → `2.0.0`; added `Requires at least: 6.4`, `Requires PHP: 7.4`, `License`/`License URI`, `Text Domain: wp-disable`, `Domain Path: /lang`; refreshed copyright to 2017–2026.
  - **Version floors** (`class-wpperformance.php`): `MIN_PHP_VERSION` 5.2.4 → 7.4; `MIN_WP_VERSION` 4.3 → 6.4. (`OPTION_KEY` left as-is to preserve existing users' saved settings.)
  - **`readme.txt`**: `Requires at least` → 6.4, `Tested up to` → 6.8 (verify against live latest in Phase 6), added `Requires PHP: 7.4`, `Stable tag` → 2.0.0, and a 2.0.0 changelog entry.
- **Trademark compliance (WordPress.org guidelines):**
  - Renamed the public plugin title (readme `=== ... ===`) from the keyword-stuffed `Reduce HTTP Requests, … Speedup WooCommerce` to **`WP Disable`** — removes the third-party "WooCommerce" trademark and keyword stuffing; matches the plugin header name. ("WP" is not WordPress-trademarked, so the name/slug are compliant.)
  - Removed "WooCommerce" (+ stuffed terms) from `readme.txt` Tags.
  - Corrected the trademark casing "Wordpress" → "WordPress" in the two visible admin labels and a doc comment. (Internal option keys like `remove_wordpress_*` left unchanged to preserve saved settings.)

### Fixed
- **Phase 2 — functional bugs:**
  - **Spam-comment cleaner now actually deletes.** `delete_spam_comments()` used `IN ( %s )` with an imploded string, so `$wpdb->prepare` quoted the whole id list as one value and deleted nothing. Now builds one `%d` placeholder per id (`class-wpperformance.php`).
  - **Comment links no longer blank out comments.** `disable_comments_content_links()` is a `comment_text` filter but `echo`'d and returned null. Now `return`s the stripped content (`class-wpperformance.php`).
  - **Saved-request stats no longer crossed.** `update_saved_google_fonts_request()` / `update_saved_font_awesome_requests()` each wrote to the *other* option key. Swapped to their correct keys (`class-wpperformance.php`).
  - **`check_spam_comments_delete()`** was `static` yet branched on `isset($this)` / `$this->...` (dead branch, PHP 8 error). Removed; reads options directly + casts the flag (`class-wpperformance.php`).
  - **`get_plugin_name()`** called `get_plugin_data(__FILE__)` on the class file (wrong file) and the function may be unloaded. Now guards the include and points at the main plugin file with a safe fallback (`class-wpperformance.php`).
  - **jQuery Migrate removal no longer downgrades core jQuery.** Old code re-registered `jquery` pinned to `1.12.4`, breaking modern WP (jQuery 3.x). Now just removes `jquery-migrate` from the `jquery` handle's deps (`class-wpperformance-admin.php`).
  - **Heartbeat handlers** read `heartbeat_location` / `heartbeat_frequency` without `isset` (PHP 8 warnings). Added guards (`class-wpperformance-admin.php`).
  - Renamed typo method `redirect_athor_pages` → `redirect_author_pages` (+ its hook).

### Security
- **Phase 3 — hardening:**
  - **Output escaping:** DNS-prefetch `<link>` href now `esc_url()`'d (`class-wpperformance.php`); admin settings echoes for `exclude_from_disable_google_maps` (`esc_attr`) and `dns_prefetch_host_list` (`esc_textarea`) (`class-wpperformance-admin.php`).
  - **Input sanitization:** save handler now `wp_unslash( $_POST )` once, then sanitizes every free-text/array field — `dns_prefetch_host_list` (`sanitize_textarea_field`), `disable_comments_on_post_types` (`array_map('intval')`), `heartbeat_frequency`/`heartbeat_location`/`delete_spam_comments`/`disable_revisions` (`sanitize_text_field`), `exclude_from_disable_google_maps`, and the nonce value (`class-wpperformance-admin.php`).
  - **Superglobals:** `$_SERVER['HTTP_HOST']`/`['REQUEST_URI']` (feed-redirect) and `$_SERVER['HTTP_REFERER']` (referral-spam) now `isset`-guarded + `wp_unslash` + `sanitize_text_field`/`esc_url_raw` (`class-wpperformance.php`).
  - **Safe redirects:** all three `wp_redirect()` calls → `wp_safe_redirect()` (`class-wpperformance.php`).
  - **Misc:** `parse_url()` → `wp_parse_url()`; `get_template_part(404)` → `get_template_part('404')`; `date()` → `wp_date()` (timezone-correct + `esc_html`); referral-spam blocklist fetch switched to HTTPS with a 5s timeout (`class-wpperformance.php` / `-admin.php`).
  - **Verified (no change needed):** the shared optimisationio dashboard/stats AJAX handlers (addon install/activate/deactivate, settings import/export) are already nonce-gated; the import-export view's `$_GET['export']` is a presence-only UI toggle (no state change). Deeper hardening of that shared framework (cap checks, `wp_unslash`) is noted for later.

### Removed
- **Phase 4 — obsolete Universal Analytics "local GA" offload (entire feature):** Google sunset UA in July 2023, so this code was dead. Removed:
  - `includes/update-local-ga.php` (raw `fsockopen` fetch of `analytics.js`) and the tracked `cache/local-ga.js` artifact.
  - `wpperformance_add_ga_header_script()`, `wpperformance_update_local_ga_script()`, the `update_local_ga` cron schedule/hook, and GA cron logic in activate/deactivate (`wpperformance.php`).
  - `WpPerformance::add_ga_header_script()`, `caos_remove_wp_cron()`, and the `wpperformance_ds_tracking_id` transient (`class-wpperformance.php`).
  - GA save-handling block + the `offload_google_analytics_settings()` form + GA default-option keys (`class-wpperformance-admin.php`).
  - The "Offload Google Analytics" sidebar tab in both the dashboard and stats-addons views.
  - **Migration:** new `maybe_upgrade()` (schema `DB_VERSION` 2) runs once on load to clear the leftover `update_local_ga` cron, delete the GA transient, strip the 8 `ds_*`/`caos_*` keys from saved settings, and unlink the cached UA file. `uninstall.php` also clears these.
- **Phase 1 — dead/duplicate code:**
  - `lib/WpPerformance/Admin.php` + `lib/WpPerformance/View.php` — entire pre-1.5 duplicate class tree (never loaded).
  - `lib/class-wpperformance-view.php` — `WpPerformance_View`, only referenced by the dead code above.
  - `views/admin-settings.php` + `views/admin_settings.php` — orphan precursor views (active form is rendered inline by `WpPerformance_Admin::addon_settings()`).
  - `includes/update_local_ga.php` — orphan underscore duplicate of `includes/update-local-ga.php`.
  - Empty stub methods `reset_saved_google_fonts_request()` / `reset_saved_font_awesome_requests()` in `class-wpperformance.php`.
  - Stale tracked `lib/.DS_Store` (already gitignored).

---

## Audit baseline (2026-06-03) — starting state, no code changed yet

State as found: **v1.5.14**, header "Tested up to 4.9", `MIN_PHP_VERSION 5.2.4`.

Issues catalogued during the initial audit (see `TODO.md` for the actionable list):

**Dead / duplicate code**
- `lib/WpPerformance/{Admin,View}.php` — entire pre-1.5 duplicate, not loaded.
- `lib/class-wpperformance-view.php` — loaded but only used by the dead code above.
- `views/admin_settings.php`, `views/admin-settings.php` — orphan legacy views.
- `includes/update_local_ga.php` — orphan duplicate of `includes/update-local-ga.php`.
- Empty stub methods `reset_saved_google_fonts_request()` / `reset_saved_font_awesome_requests()`.

**Functional bugs**
- Swapped saved-request option keys (Google Fonts ↔ Font Awesome) — `class-wpperformance.php:340-354`.
- `comment_text` filter `echo`s instead of `return`s — `class-wpperformance.php:696-699`.
- Broken `$wpdb->prepare` `IN ( %s )` on spam-comment delete — `class-wpperformance.php:82-83`.
- `static` method branching on `$this` — `class-wpperformance.php:510-516`.
- `get_plugin_data(__FILE__)` on wrong file / unavailable on front end — `class-wpperformance.php:161-164`.
- jQuery Migrate removal re-pins jQuery to 1.12.4 (breaks modern WP) — `class-wpperformance-admin.php:337-345`.
- Heartbeat handlers read array keys without `isset` — `class-wpperformance-admin.php:358-383`.
- Typo `redirect_athor_pages`.

**Security**
- Inline GA `<script>` built from settings with weak escaping — `wpperformance.php:119-137`.
- Unescaped DNS-prefetch `href` output — `class-wpperformance.php:617`.
- Unescaped settings echoes in admin UI — `class-wpperformance-admin.php:511,650,671`.
- Unsanitized `$_SERVER` superglobals — `class-wpperformance.php:767,889`.
- Raw `$_POST` stored without `wp_unslash`/sanitization — `class-wpperformance-admin.php:397-501`.
- `wp_redirect()` instead of `wp_safe_redirect()` — `class-wpperformance.php:585,752,771`.
- Plain-HTTP third-party fetch `http://wielo.co/referrer-spam.php` — `class-wpperformance.php:928`.

**Obsolete feature**
- "Local GA" offload caches Universal Analytics `analytics.js` (UA sunset Jul 2023) via raw `fsockopen` — `includes/update-local-ga.php`, `wpperformance.php`.

**Compliance**
- Text-domain mismatch (`wpperformance` vs `optimisationio` vs `wpper`).
- Header missing `Requires PHP` / `Text Domain` / `Domain Path`; stale copyright; `readme.txt` "Tested up to 4.9".
- `MIN_PHP_VERSION`/`MIN_WP_VERSION` far below modern floors.
