# Featherweight (ex WP Disable) — TODO

> **STATUS — 2026-06-08.** The 2.0.0 modernization below (Phases 0–6) **shipped**
> (live through 2.0.2) and is verified against the current code. The admin was then
> reskinned onto the shared **Folium UI** frame (**2.1.0**, shipped). The plugin was
> **rebranded to Featherweight** with a pre-Folium dead-code purge (**2.2.0**, on
> `master`, not yet tagged). Phases 0–6 are kept here `[x]` as a record. The only
> genuinely-open work is the **competitive feature roadmap** and the **suite
> diagnostics** concept at the bottom — those did **not** land in 2.2.0 (which became
> a rebrand + cleanup release), so re-target them to **2.3.0**.

Original tracker: bring **WP Disable** (was v1.5.14, ~2018, "Tested up to 4.9") up to
modern WordPress (**WP 6.x → 7.0**) and supported PHP (**7.4+ / 8.x**), fixing
functional bugs, security issues, and dead code.

**Decisions** (confirmed with owner 2026-06-03):
- "WP7 compliant" = run clean on the current WP 6.x line and forward-compatible with WP 7.0.
- Minimum PHP raised to **7.4**; clean on **PHP 8.1/8.2** (no deprecations).
- Plugin slug / canonical text domain is **`wp-disable`** (kept forever; only the brand changed).
- **GA "local offload" feature: REMOVED entirely** (Phase 4).

Legend: `[ ]` todo · `[~]` in progress · `[x]` done · 🔴 critical · 🟠 high · 🟡 medium · ⚪ low/cleanup

---

## Done since 2.1.0 — Featherweight rebrand + dead-code purge (2.2.0)
- [x] 🟠 Rebrand to **Featherweight** (Plugin Name header, readme title/description,
  Folium `register_plugin` name + new feather icon, vendored catalog entry). Slug,
  text domain, and main file stay `wp-disable` per wp.org rules.
- [x] 🟡 Bump vendored **Folium UI 1.0.0 → 1.0.1** so the rebranded catalog
  deterministically wins the newest-wins negotiation (re-vendored into Featherweight
  + Sitewise).
- [x] 🟡 wp.org listing assets in `.wordpress-org/` (icon 128/256, banner 772/1544).
- [x] 🟠 **Remove the pre-Folium dead layer** (distinct from Phase 1's pre-1.5 cleanup):
  `views/`, legacy `css/`+`js/`, stale optimisation.io images, the orphaned
  `class-optimisationio-stats-and-addons` + `class-optimisationio-upgrader-skin`
  classes; slimmed the dashboard class 893→148 (live Folium bridge only) and dropped
  the dead `addon_settings()` form 934→501 (kept `persist_settings`). Kept the live
  `js/css-lazy-load.js`. ~6.4k lines deleted; `php -l` clean on the test box.
- [x] ⚪ readme FAQ scrub: removed the stale caching / CDN / image-compression
  cross-sells; rewrote the minification answer; noted **Rank Math support coming soon**.
- [ ] 🟡 Tag **`v2.2.0`** to ship Featherweight to wp.org (after the owner's visual pass).

## Phase 0 — Tooling & baseline
- [x] ⚪ Local PHP lint path — worked around: `scripts/ship-wp-disable.sh` runs `php -l` when present; the executing lint path is CI. (Can also lint on the LAN/test box, as done 2026-06-08.)
- [x] 🟡 WPCS config — `phpcs.xml.dist` (WordPress + PHPCompatibilityWP, testVersion 7.4-, text-domain + prefix properties).
- [x] 🟡 CI lint path — `.github/workflows/lint.yml`: `php -l` matrix (7.4/8.1/8.2) + phpcs.
- [ ] ⚪ Record a clean "before" baseline. _(Deferred — needs a running WP install; the plugin has since shipped, so this is moot.)_

## Phase 1 — Remove dead / duplicate code _(verified gone 2026-06-08)_
- [x] 🟠 Deleted legacy duplicate class tree `lib/WpPerformance/`.
- [x] 🟠 Removed `lib/class-wpperformance-view.php` + its `require_once`.
- [x] 🟠 Deleted orphan views `views/admin_settings.php` / `views/admin-settings.php`.
- [x] 🟠 Deleted orphan duplicate `includes/update_local_ga.php` (underscore).
- [x] ⚪ Removed empty stub methods `reset_saved_google_fonts_request()` / `reset_saved_font_awesome_requests()`.

## Phase 2 — Functional bug fixes _(verified in code 2026-06-08)_
- [x] 🔴 **Swapped option keys** for the saved Google Fonts / Font Awesome request counters — corrected.
- [x] 🔴 **`comment_text` filter returned nothing** — `disable_comments_content_links()` now `return`s the stripped content instead of `echo`.
- [x] 🔴 **Broken `$wpdb->prepare`** in spam-comment delete — rebuilt with one `%d` placeholder per id.
- [x] 🟠 **Static/`$this` confusion** in `check_spam_comments_delete()` — now purely static (`$reschedule` param).
- [x] 🟠 **`get_plugin_name()`** — uses the main plugin file + guards the `get_plugin_data` include.
- [x] 🟠 **jQuery Migrate removal** — drops `jquery-migrate` from the meta-handle deps without re-pinning core jQuery (no more 1.12.4 downgrade).
- [x] 🟡 Hardened `heartbeat_stop()` / `heartbeat_frequency()` against missing array keys.
- [x] 🟡 Renamed typo method `redirect_athor_pages` → `redirect_author_pages`.

## Phase 3 — Security hardening _(verified in code 2026-06-08)_
- [x] 🔴 Output escaping in the inline GA `<script>` — moot, the GA feature was removed (Phase 4).
- [x] 🟠 **Unescaped DNS-prefetch output** — `check_dns_prefetch()` now uses `esc_url()`.
- [x] 🟠 **Unescaped settings echoes** in admin UI — wrapped in `esc_attr()` / `esc_textarea()`.
- [x] 🟠 **Unsanitized superglobals** — `$_SERVER['REQUEST_URI']` / `['HTTP_REFERER']` now `esc_url_raw( wp_unslash() )`.
- [x] 🟠 **Raw `$_POST` in save handler** — `persist_settings()` unslashes + field-sanitizes (incl. array values).
- [x] 🟡 Replaced `wp_redirect()` with `wp_safe_redirect()` (zero raw `wp_redirect(` remain).
- [x] 🟡 Use `date_i18n()`/`wp_date()` for the "next spam delete" display.
- [x] 🟡 Reviewed the external `wielo.co/referrer-spam.php` dependency in the referral-spam blocker.

## Phase 4 — Remove the obsolete Google Analytics "local offload" feature _(verified gone 2026-06-08)_
- [x] 🔴 Deleted `includes/update-local-ga.php` and the `cache/local-ga.js` artifact + `cache/` dir.
- [x] 🔴 Removed GA glue from `wpperformance.php` (script writer, cron, header script).
- [x] 🔴 Removed GA from `class-wpperformance.php` (header script, cron removal, GA option keys).
- [x] 🔴 Removed GA save-handling + the offload settings form + GA fields.
- [x] 🟠 Upgrade/uninstall migration: unschedule leftover `update_local_ga` cron + delete orphaned GA options/transients (still present as cleanup-only code, by design).
- [x] 🟡 Scrubbed GA copy/links from `readme.txt`.

## Phase 5 — WP7 / modern-WP compliance _(verified 2026-06-08)_
- [x] 🟠 **Text domain** unified to **`wp-disable`** across all `__()/_e()/esc_*_e()` (no stray `optimisationio`/`wpperformance`/`wpper` domains remain).
- [x] 🟠 Plugin header: `Requires at least`, `Requires PHP`, `License`, `Text Domain`, `Domain Path`, refreshed copyright.
- [x] 🟠 `readme.txt`: `Requires at least` 6.x, `Tested up to` current, `Requires PHP: 7.4`, `Stable tag`, changelog.
- [x] 🟠 Raised `MIN_PHP_VERSION` / `MIN_WP_VERSION` to realistic floors.
- [x] 🟡 Audited menu + `current_user_can` + nonce coverage on every save path.
- [x] 🟡 Confirmed no use of functions removed/deprecated through WP 6.x→7.0.

## Phase 5 follow-up (deferred — needs tooling not on this box)
- [ ] 🟡 Regenerate translations for `wp-disable`: `wp i18n make-pot . lang/wp-disable.pot`; remove the stale `lang/wpperformance*.{pot,mo}`. Not blocking — wp.org serves translations by slug.
- [ ] ⚪ Confirm `readme.txt` "Tested up to" matches the current live WordPress release at submission time.

## Phase 6 — Verify
- [x] 🟠 `php -l` every file — clean (re-confirmed on the test box 2026-06-08).
- [x] 🟠 phpcs against WPCS — runs in CI.
- [x] 🟠 Manual smoke test — shipped and stable across 2.0.0–2.1.0 in production.
- [x] 🟡 WooCommerce-conditional paths (Woo active / inactive).
- [x] 🟡 Settings migration from a v1.5.14 options row.

---

## Release to WordPress.org
WP.org uses SVN; deployment is automated via `.github/workflows/deploy.yml`
(`10up/action-wordpress-plugin-deploy`) — push a `vX.Y.Z` tag.
- [x] GitHub repo secrets `SVN_USERNAME` + `SVN_PASSWORD` (committer on the `wp-disable` slug).
- [x] Merge `modernize-2.0` → `master`.
- [x] Shipped `v2.0.0`, `v2.0.1`, `v2.0.2`, `v2.1.0`.
- [ ] Regenerate `lang/wp-disable.pot`; drop stale `lang/wpperformance*` (Phase 5 follow-up).
- [ ] **Tag `v2.2.0`** (Featherweight) after the owner's visual pass.

---

## Competitive feature roadmap (re-targeted to 2.3.0)
Feature-parity gaps found by auditing the big single-purpose plugins Featherweight
overlaps with. Identity is **performance/cleanup**, so the bias is: fill the cheap,
on-brand header/script/endpoint cleanups; treat management-style features (network
admin UIs, per-role rules, wizards) as out of scope unless we deliberately
reposition. Each item below is a new real option key → a `persist_settings()` line →
a row in the matching section of `wp-disable-app.js`. _(Originally targeted 2.2.0;
that release became the Featherweight rebrand + cleanup, so these move to 2.3.0.)_

### vs `disable-comments` (1M+ installs) — audited 2026-06-07
Engine is already strong: "Disable all comments" removes the Comments admin menu
page, deregisters `comment-reply.js` on singular pages, drops the comment-feed
link + meta-widget RSS link, and empties the comments template. Gaps:
- [ ] 🟠 **Remove the `X-Pingback` HTTP header** (`wp_headers` filter). We disable
  pings via `default_ping_status` but the header still ships. → **Tags** section.
- [ ] 🟠 **Finish the admin comment hiding** when "Disable all comments" is on:
  remove the Admin Bar **Comments** node, the dashboard **Recent Comments /
  Activity** widget, and hide the **Discussion** settings page. Today we only
  `remove_menu_page('edit-comments.php')`. → makes the existing toggle feel complete.
- [ ] 🟡 **Redirect comment-feed requests to the parent post** — we remove the feed
  *link* but `/comments/feed/` still resolves; disable-comments redirects/404s it.
- [ ] ⚪ **Standalone `comment-reply.js` dequeue toggle** — currently only auto-removed
  when comments are off on singular pages; could be its own opt-in perf toggle.
- [ ] ⛔ Out of scope (management, not perf): multisite network-wide UI, per-user-role
  exclusions, setup wizard, WP-CLI, "enable certain comment types", "show existing
  comments", bulk "delete comments by type".

### vs `disable-wp-rest-api` (30k+ installs) — audited 2026-06-07
Mostly already covered: `remove_wordpress_api_from_header` strips the
`<link rel="https://api.w.org/">` from the HTML head. Missed:
- [ ] 🟠 **Remove the REST `Link:` HTTP response header** (`remove_action(
  'template_redirect', 'rest_output_link_header', 11 )`) — the head `<link>` and the
  HTTP header are two different hooks; we only do the first. Cheap, on-brand. Fold
  into the existing `remove_wordpress_api_from_header` toggle (extend it) rather than
  add a new key. Also consider `remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' )`.
- [ ] 🟡 **Restrict REST API to authenticated users** (optional policy: logged-in /
  none / blocked). disable-wp-rest-api's headline feature; the canvas demo already
  mocked a "Restrict REST API" toggle + policy select. Hardening, not perf — ship
  only if we want Featherweight to play in the hardening space too. → **Feeds & APIs**.

---

## Suite-level direction: AI-actionable diagnostics (not another passive monitor)
**Scope note:** this is bigger than a Featherweight toggle — it's a Folium *suite*
concept and may graduate to its own plugin and/or a suite-level doc. Captured here
because it grew out of the competitive audit and Featherweight already owns the
cron/Heartbeat surface (heartbeat freq/location, the weekly/monthly schedules,
the spam-cleaner cron) so it's the natural first data source.

### Reference: `wp-crontrol` (300k+ installs, 4.5★) — audited 2026-06-07
The bar for cron tooling. Shows every scheduled event with hook, args, schedule,
recurrence, next-run, **and the callback that runs**; lets you edit / delete /
pause / resume / run-now / add events (incl. PHP + URL events) and manage custom
schedules. Crucially it also **detects problems**: events with *no registered
callback* ("None" action), *missed / overdue* events, and *WP-Cron spawn failures*
— "show you a helpful warning message if it detects any problems with your cron
system." That problem-detection is the part worth having; the raw event editor is
table stakes.

### The thesis — diagnostics that *fix*, not diagnostics that *display*
Query Monitor / wp-crontrol surface the truth and stop there — the user still has
to know what to do with "this event has no callback" or "this query took 800ms".
Because the Folium suite is getting an **AI bot** (see Sitewise direction), we can
close that loop: **ingest the diagnostic signal → the bot explains it in plain
terms → the bot offers/executes the actual fix** (disable the orphaned event,
dequeue the slow asset, raise the Heartbeat interval, etc.). The product is not the
dashboard — it's the *resolution*. That's the differentiator vs every existing
monitor, and it's why we'd build it intentionally for AI consumption from day one
(structured, explained, fixable signals) rather than bolting a chat box onto a
classic QM table.

### Decided architecture (owner, 2026-06-07) — signals live local, fixing is central
Hybrid, not either/or:
- **Each suite plugin owns and emits its own diagnostic signals.** Featherweight keeps
  the **cron/Heartbeat** data here (it owns that surface); Cache emits cache/TTFB
  signals; Sitewise emits crawl/corpus signals; etc. The data stays where it's
  generated.
- **A standalone Folium "Query Monitor" plugin is the aggregator-that-fixes.** It
  consumes the signals published by every suite plugin **plus** does the typical QM
  job natively (slow queries, hook/callback timing, HTTP API calls, PHP notices,
  rewrite/conditionals, enqueue bloat…). One pane over the whole suite + WP itself.
- **Its mandate is resolution, not display — "it lives to fix, not show."** Same
  Folium UI frame, wired to the suite AI bot: ingest signal → explain plainly →
  offer/execute the fix. The classic QM table is the fallback view, not the point.

### Integration seam to build
- [ ] 🟡 Define a lightweight **suite signal contract** — how any Folium plugin
  publishes a diagnostic (id, severity, plain-language summary, structured payload,
  and an optional *fix action* the bot/QM can invoke). The QM plugin subscribes;
  the bot consumes. Featherweight's cron/Heartbeat checks are the first producer.
- [ ] 🟡 Featherweight: expose its cron/Heartbeat health as signals on that contract
  (orphaned events, overdue events, WP-Cron spawn failure, Heartbeat hammering).
- [ ] Prereq: the suite AI bot (Sitewise grounded-chat work) must exist and expose an
  ingestion/answer path before the QM plugin's "fix" loop is buildable; the standalone
  QM plugin + native diagnostics can start independently as the signal aggregator.

---

_See [CHANGELOG.md](CHANGELOG.md) for the running record of what's actually been changed._
