# Changelog (development tracker)

Running, developer-facing record of the 2.0.0 modernization effort.
User-facing release notes live in [`readme.txt`](readme.txt) / [`changelog.txt`](changelog.txt); this file
tracks every concrete change (with file references) as the work in [`TODO.md`](TODO.md) is done.

Format: keep entries newest-first under the unreleased heading; move to a versioned heading on release.
Use the prefixes **Added / Changed / Fixed / Security / Removed / Deprecated**.

---

## [Unreleased] — targeting 2.0.0

### Added
- `TODO.md` — full modernization task list (phases 0–6).
- `CHANGELOG.md` — this development tracker.
- **Phase 0 — tooling:** `phpcs.xml.dist` (WPCS + PHPCompatibilityWP, PHP 7.4+ target) and `.github/workflows/lint.yml` (CI `php -l` matrix on 7.4/8.1/8.2 + non-blocking phpcs). Local lint is blocked on the dev box (no php), so CI is the executing lint path.
- `scripts/ship-wp-disable.sh` (local-only, gitignored) — deploy/lint/zip helper.

### Changed
- `wpperformance.php`: dropped the `require_once` for the deleted `class-wpperformance-view.php`.

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
