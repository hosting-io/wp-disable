# WP Disable — Modernization TODO

Tracking the work to bring **WP Disable** (currently v1.5.14, last touched ~2018, "Tested up to 4.9")
up to compliance with modern WordPress (targeting **WP 6.x → 7.0**) and supported PHP (**7.4+ / 8.x**),
plus fixing functional bugs, security issues, and removing dead code.

**Target release:** `2.0.0` (this is a major modernization, hence the major bump).

**Decisions** (confirmed with owner 2026-06-03):
- "WP7 compliant" = run clean on the current WP 6.x line and forward-compatible with the WP 7.0 major.
- Minimum PHP raised to **7.4**; code must also run clean on **PHP 8.1/8.2** (no deprecation notices).
- Plugin slug / canonical text domain becomes **`wp-disable`**.
- **GA "local offload" feature: REMOVE entirely** (see Phase 4).

Legend: `[ ]` todo · `[~]` in progress · `[x]` done · 🔴 critical · 🟠 high · 🟡 medium · ⚪ low/cleanup

---

## Phase 0 — Tooling & baseline
- [x] ⚪ Local PHP lint path — **blocked on this box** (no php/docker/sudo/composer). Worked around: `scripts/ship-wp-disable.sh` runs `php -l` when php is present and skips gracefully otherwise; the real executing lint path is CI (below). Install `php-cli` on the LAN server to lint locally.
- [x] 🟡 Add WPCS config — `phpcs.xml.dist` (WordPress + PHPCompatibilityWP, testVersion 7.4-, text-domain + prefix properties).
- [x] 🟡 Add CI lint path — `.github/workflows/lint.yml`: `php -l` matrix (7.4/8.1/8.2) + phpcs (non-blocking until phases 2–5 land).
- [ ] ⚪ Record a clean "before" baseline (current behavior) so we can confirm no regressions. _(Deferred — needs a running WP install.)_

## Phase 1 — Remove dead / duplicate code (do first; shrinks the surface)
- [ ] 🟠 Delete legacy duplicate class tree `lib/WpPerformance/` (`Admin.php`, `View.php`) — not loaded anywhere; it's the pre-1.5 version (renders via `tools.php`, references `WpPerformance_View::render`).
- [ ] 🟠 Remove `lib/class-wpperformance-view.php` + its `require_once` in `wpperformance.php` — `WpPerformance_View` is only referenced by the dead `lib/WpPerformance/` code.
- [ ] 🟠 Delete orphan duplicate view `views/admin_settings.php` and stale `views/admin-settings.php` (only the dead admin class used them; active UI is `addon_settings()` + `optimisationio-*` views). Verify no active reference before deleting.
- [ ] 🟠 Delete orphan duplicate `includes/update_local_ga.php` (underscore) — active file is `includes/update-local-ga.php` (hyphen).
- [ ] ⚪ Remove empty stub methods `reset_saved_google_fonts_request()` / `reset_saved_font_awesome_requests()` in `lib/class-wpperformance.php`.

## Phase 2 — Functional bug fixes
- [ ] 🔴 **Swapped option keys**: `update_saved_google_fonts_request()` writes to `_combined_font_awesome_requests_number` and `update_saved_font_awesome_requests()` writes to `_combined_google_fonts_requests_number` (`class-wpperformance.php:340-354`). The two saved-request counters are crossed. Swap them.
- [ ] 🔴 **`comment_text` filter returns nothing**: `disable_comments_content_links()` does `echo $content;` instead of `return $content;` (`class-wpperformance.php:696-699`) — strips/duplicates comment bodies. Return instead of echo.
- [ ] 🔴 **Broken `$wpdb->prepare`**: spam-comment delete uses `comment_id IN ( %s )` with an imploded ID string (`class-wpperformance.php:82-83`) — `%s` quotes the whole list as one value, so nothing is deleted. Rebuild with one `%d` placeholder per id (or use the already-`intval`'d list safely).
- [ ] 🟠 **Static/`$this` confusion**: `check_spam_comments_delete()` is `static` yet branches on `isset($this)` / calls `$this->get_settings_values()` (`class-wpperformance.php:510-516`) — dead branch + error on PHP 8. Make it purely static.
- [ ] 🟠 **`get_plugin_name()`**: calls `get_plugin_data(__FILE__)` with the *class* file path, and `get_plugin_data()` isn't loaded on the front end (`class-wpperformance.php:161-164`). Use the main plugin file + guard the include.
- [ ] 🟠 **jQuery Migrate removal is broken on modern WP**: `remove_jquery_migrate()` re-registers jQuery pinned to `1.12.4` (`class-wpperformance-admin.php:337-345`). WP 5.6+ ships jQuery 3.x; this downgrades/breaks sites. Rework to only dequeue `jquery-migrate` without re-pinning core jQuery.
- [ ] 🟡 Harden `heartbeat_stop()` / `heartbeat_frequency()` against missing array keys (`class-wpperformance-admin.php:358-383`) — add `isset()` guards (PHP 8 warnings).
- [ ] 🟡 Rename typo method `redirect_athor_pages` → `redirect_author_pages` (update the `add_action` reference too).

## Phase 3 — Security hardening
- [ ] 🔴 **Output escaping in inline GA `<script>`**: `wpperformance_add_ga_header_script()` concatenates settings into a raw `<script>` block with only `esc_attr` (`wpperformance.php:119-137`). Escape with `esc_js`, and prefer `wp_print_inline_script_tag` / `wp_add_inline_script`.
- [ ] 🟠 **Unescaped DNS-prefetch output**: `check_dns_prefetch()` echoes host into `href` without escaping (`class-wpperformance.php:617`). Use `esc_url()`.
- [ ] 🟠 **Unescaped settings echoes in admin UI**: `ds_tracking_id` (`admin.php:511`), `exclude_from_disable_google_maps` (`:650`), `dns_prefetch_host_list` (`:671`) printed raw. Wrap in `esc_attr()` / `esc_textarea()`.
- [ ] 🟠 **Unsanitized superglobals**: `$_SERVER['HTTP_HOST']` / `['REQUEST_URI']` (`class-wpperformance.php:767`), `$_SERVER['HTTP_REFERER']` (`:889`). Sanitize + `wp_unslash`.
- [ ] 🟠 **Raw `$_POST` in save handler**: `$post_req = $_POST` then stored without `wp_unslash`/per-field sanitization for `dns_prefetch_host_list`, `heartbeat_*`, `disable_comments_on_post_types[]`, `disable_revisions`, `delete_spam_comments` (`class-wpperformance-admin.php:397-501`). Add `wp_unslash` + field-appropriate sanitizers; sanitize array values in `disable_comments_on_post_types`.
- [ ] 🟡 Replace `wp_redirect()` with `wp_safe_redirect()` (`class-wpperformance.php:585,752,771`).
- [ ] 🟡 Use `date_i18n()`/`wp_date()` instead of `date()` for the "next spam delete" display (`class-wpperformance-admin.php:969`).
- [ ] 🟡 Review external HTTP dependency `http://wielo.co/referrer-spam.php` in the referral-spam blocker (`class-wpperformance.php:928`) — third-party, plain HTTP, likely dead. Remove the feature or replace the source + move to HTTPS.

## Phase 4 — Remove the obsolete Google Analytics "local offload" feature
**Decision: REMOVE entirely** (UA sunset Jul 2023; out of scope for a disabler plugin).
- [ ] 🔴 Delete `includes/update-local-ga.php` and the `cache/local-ga.js` artifact + `cache/` dir if otherwise empty.
- [ ] 🔴 Remove GA glue from `wpperformance.php`: `wpperformance_update_local_ga_script()`, the `update_local_ga` cron schedule/clear in activate/deactivate, `wpperformance_add_ga_header_script()`, and the `disable_google_maps` GA-adjacent bits stay (maps is separate).
- [ ] 🔴 Remove GA from `class-wpperformance.php`: `add_ga_header_script()`, `caos_remove_wp_cron()`, the `wpperformance_ds_tracking_id` transient, GA option keys.
- [ ] 🔴 Remove GA save-handling + the `offload_google_analytics_settings()` form and GA fields from `class-wpperformance-admin.php` and the dashboard view.
- [ ] 🟠 On upgrade, unschedule any leftover `update_local_ga` cron event and delete orphaned GA options/transients (migration routine).
- [ ] 🟡 Scrub GA copy/links from `readme.txt` description + FAQ.

## Phase 5 — WP7 / modern-WP compliance
- [ ] 🟠 **Text-domain mismatch**: header declares `wpperformance`, but most UI strings use `optimisationio` (and the dead code uses `wpper`). Standardize on **`wp-disable`** across all `__()/_e()/esc_*_e()` and `load_plugin_textdomain`; regenerate `.pot`.
- [ ] 🟠 Update plugin header: add `Requires at least`, `Requires PHP`, `License`, `Text Domain`, `Domain Path`; bump `Version` to `2.0.0`; refresh stale `Copyright (C) 2017`.
- [ ] 🟠 Update `readme.txt`: `Requires at least` → 6.x, `Tested up to` → current, add `Requires PHP: 7.4`, bump `Stable tag`, add 2.0.0 changelog entry.
- [ ] 🟠 Raise `MIN_PHP_VERSION` (5.2.4 → 7.4) and `MIN_WP_VERSION` (4.3 → realistic floor) in `class-wpperformance.php`.
- [ ] 🟡 Audit all `add_management_page`/menu + `current_user_can` + nonce coverage on every form submit path post-cleanup.
- [ ] 🟡 Confirm no use of functions removed/deprecated through WP 6.x→7.0 (e.g. legacy widget/`create_function`, `wp_make_content_images_responsive`, etc.) — grep after refactor.

## Phase 6 — Verify
- [ ] 🟠 `php -l` every file (PHP 8.2) — zero parse errors.
- [ ] 🟠 phpcs against WPCS — triage remaining warnings.
- [ ] 🟠 Manual smoke test on a clean WP install: activate, toggle each setting group, save, deactivate, uninstall — no PHP warnings in debug.log.
- [ ] 🟡 Test WooCommerce-conditional paths with Woo active and inactive.
- [ ] 🟡 Confirm settings migration from a v1.5.14 options row doesn't lose data.

---

_See [CHANGELOG.md](CHANGELOG.md) for the running record of what's actually been changed._
