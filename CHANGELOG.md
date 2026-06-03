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

### Changed
- `wpperformance.php`: dropped the `require_once` for the deleted `class-wpperformance-view.php`.

### Fixed
- _nothing yet_

### Security
- _nothing yet_

### Removed
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
