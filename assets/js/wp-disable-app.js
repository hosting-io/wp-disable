/* ============================================================================
   WP Disable — Folium UI admin app.

   Ported from the canvas design bundle (Folium ui - Standalone.html) and wired
   to the REAL WP Disable option schema (wpperformance_rev3a_settings, 44 keys).
   The renderer keeps the canvas chrome (tabs, dashboard metrics, search) but
   every row maps to a real option key. Save/Reset post to the plugin's own
   ajax handlers (see class-optimisationio-dashboard.php → persist_settings()),
   so persistence reuses the exact, live-tested sanitisation — not a reimpl.

   Mounts into the shared Folium #wpd panel via window.Folium.registerApp.
   Real settings + post types + capability flags arrive as window.WPDisableData
   (wp_localize_script). Vanilla; depends on window.FL (folium-ui.js) for icons.
   ============================================================================ */
(function () {
  'use strict';
  const WPD = (window.WPD = window.WPD || {});
  const $ = (s, r) => (r || document).querySelector(s);
  const ICON = (n) => (window.FL && window.FL.icon ? window.FL.icon(n) : '');

  /* ---- data model: real WP Disable settings ------------------------------- *
   * item kinds:
   *   (default) toggle  — boolean, real option key in `id`. `field` is shown
   *                       when `on` and is a dependent text/select control with
   *                       its own real `key`.
   *   control:'select'  — a standalone <select> row (no toggle), real key in id.
   * reqs/kb are illustrative per-optimisation weights for the dashboard meter.  */
  WPD.SECTIONS = [
    { id: 'dashboard', label: 'Dashboard', icon: 'gauge', kind: 'dashboard' },

    { id: 'requests', label: 'Requests', icon: 'broom', kind: 'settings',
      eyebrow: '01 — REQUESTS', title: 'Trim front-end requests',
      lead: 'Strip the markup, scripts, and meta WordPress injects by default. Each toggle removes weight from every front-end page load.',
      groups: [
        { name: 'Head & meta', items: [
          { id: 'disable_emoji', title: 'Disable emojis', desc: 'Removes the emoji detection script and inline CSS from <code>wp_head</code>.', reqs: 1, kb: 14 },
          { id: 'disable_embeds', title: 'Disable embeds', desc: 'Removes oEmbed discovery and <code>wp-embed.min.js</code>.', reqs: 1, kb: 8 },
          { id: 'disable_google_maps', title: 'Disable Google Maps', desc: 'Strips Google Maps scripts from the front end.', reqs: 1, kb: 0,
            field: { type: 'text', key: 'exclude_from_disable_google_maps', label: 'Exclude post / page IDs', placeholder: 'e.g. 14, 88' } },
        ]},
        { name: 'Scripts & assets', items: [
          { id: 'remove_querystrings', title: 'Remove query strings', desc: 'Strips <code>?ver=</code> from static asset URLs for better edge caching.', reqs: 0, kb: 3 },
          { id: 'remove_jquery_migrate', title: 'Remove jQuery Migrate', desc: 'Dequeues <code>jquery-migrate.min.js</code> on the front end.', reqs: 1, kb: 11 },
          { id: 'lazy_load_google_fonts', title: 'Async Google Fonts', desc: 'Minimise requests and load <strong>Google Fonts</strong> asynchronously.', reqs: 1, kb: 0 },
          { id: 'lazy_load_font_awesome', title: 'Async Font Awesome', desc: 'Minimise requests and load <strong>Font Awesome</strong> asynchronously.', reqs: 1, kb: 0 },
          { id: 'disable_wordpress_password_meter', title: 'Disable password-strength meter', desc: 'Dequeues <code>zxcvbn</code> on pages that don’t need it.', reqs: 1, kb: 0 },
          { id: 'disable_front_dashicons_when_disabled_toolbar', title: 'Disable front-end Dashicons', desc: 'Removes Dashicons CSS for visitors when the admin toolbar is hidden.', reqs: 1, kb: 46 },
          { id: 'dns_prefetch', title: 'DNS prefetch', desc: 'Adds resource hints for the origins you list.', reqs: 0, kb: 0,
            field: { type: 'textarea', key: 'dns_prefetch_host_list', label: 'Prefetch host list', placeholder: 'One domain per line' } },
          { id: 'disable_referral_spam', title: 'Disable referral spam', desc: 'Blocks known referral-spam domains.', reqs: 0, kb: 0 },
        ]},
      ]
    },

    { id: 'tags', label: 'Tags', icon: 'code', kind: 'settings',
      eyebrow: '02 — META TAGS', title: 'Remove header tags',
      lead: 'Drop the discovery and generator tags WordPress prints into the document head. The underlying endpoints stay active.',
      groups: [
        { name: 'Discovery & generator', items: [
          { id: 'remove_wordpress_generator_tag', title: 'Remove version generator', desc: 'Hides the WordPress version <code>generator</code> meta tag.', reqs: 0, kb: 1 },
          { id: 'remove_shortlink_tag', title: 'Remove shortlink', desc: 'Drops the <code>rel=shortlink</code> header and link tag.', reqs: 0, kb: 1 },
          { id: 'remove_wordpress_api_from_header', title: 'Remove REST API link', desc: 'Removes the REST API discovery <code>link</code> tag. The API stays active.', reqs: 0, kb: 1 },
          { id: 'remove_rsd', title: 'Remove RSD', desc: 'Removes the Really Simple Discovery <code>link</code> tag.', reqs: 0, kb: 1 },
          { id: 'remove_windows_live_writer', title: 'Remove Windows Live Writer', desc: 'Removes the WLW manifest <code>link</code> tag.', reqs: 0, kb: 1 },
        ]},
      ]
    },

    { id: 'comments', label: 'Comments', icon: 'comment', kind: 'settings',
      eyebrow: '03 — COMMENTS', title: 'Comments, pings & Gravatars',
      lead: 'Turn off discussion features and the third-party Gravatar requests they pull in.',
      groups: [
        { name: 'Comments', items: [
          { id: 'disable_all_comments', title: 'Disable all comments', desc: 'Turns comments off across the whole site.', reqs: 0, kb: 0 },
          { id: 'disable_comments_on_certain_post_types', title: 'Disable comments on certain post types', desc: 'Pick the post types that should never accept comments.', reqs: 0, kb: 0,
            field: { type: 'posttypes', key: 'disable_comments_on_post_types', label: 'Post types' } },
          { id: 'close_comments', title: 'Close comments after 28 days', desc: 'Automatically closes discussion on posts older than 28 days.', reqs: 0, kb: 0 },
          { id: 'paginate_comments', title: 'Paginate comments at 20', desc: 'Breaks long comment threads into pages of 20.', reqs: 0, kb: 0 },
          { id: 'remove_comments_links', title: 'Remove links from comments', desc: 'Strips hyperlinks out of comment text.', reqs: 0, kb: 0 },
          { id: 'default_ping_status', title: 'Disable pingbacks & trackbacks', desc: 'Stops WordPress accepting and sending pings.', reqs: 0, kb: 0 },
        ]},
        { name: 'Gravatars & privacy', items: [
          { id: 'disable_gravatars', title: 'Disable Gravatars', desc: 'Removes the third-party Gravatar avatar requests.', reqs: 1, kb: 0 },
          { id: 'disable_author_pages', title: 'Disable author pages', desc: 'Redirects author archive URLs away from public listing.', reqs: 0, kb: 0 },
          { id: 'spam_comments_cleaner', title: 'Spam comments cleaner', desc: 'Schedule automatic deletion of spam comments.', reqs: 0, kb: 0,
            field: { type: 'select', key: 'delete_spam_comments', label: 'Delete spam',
              options: [['hourly','Once Hourly'],['twicedaily','Twice Daily'],['daily','Once Daily'],['weekly','Once Weekly'],['twicemonthly','Twice Monthly'],['monthly','Once Monthly']] } },
        ]},
      ]
    },

    { id: 'feeds', label: 'Feeds & APIs', icon: 'feed', kind: 'settings',
      eyebrow: '04 — FEEDS & APIS', title: 'Feeds & remote endpoints',
      lead: 'Disable the legacy syndication and remote-publishing endpoints most sites never use.',
      groups: [
        { name: 'Feeds & APIs', items: [
          { id: 'disable_rss', title: 'Disable RSS feeds', desc: 'Turns off the site’s feed endpoints.', reqs: 0, kb: 0, warn: 'Breaks podcast & email feeds.',
            field: { type: 'select', key: 'disabled_feed_behaviour', label: 'When disabled',
              options: [['redirect','Redirect to HTML content'],['404_error','Issue a 404 error']] } },
          { id: 'disable_xmlrpc', title: 'Disable XML-RPC', desc: 'Blocks the legacy remote-publishing endpoint.', reqs: 0, kb: 0, warn: 'Jetpack & the WP mobile app use this.' },
        ]},
      ]
    },

    { id: 'admin', label: 'Admin', icon: 'tools', kind: 'settings',
      eyebrow: '05 — ADMIN & DATABASE', title: 'Admin, revisions & Heartbeat',
      lead: 'Tame the background activity and database growth that the admin area generates.',
      groups: [
        { name: 'Database', items: [
          { id: 'disable_revisions', control: 'select', title: 'Post revisions', desc: 'Cap how many revisions WordPress keeps per post.',
            options: [['default','WordPress default'],['0','0 (disable)'],['1','1'],['2','2'],['3','3'],['4','4'],['5','5'],['10','10'],['15','15'],['20','20'],['25','25'],['30','30']] },
          { id: 'disable_autosave', title: 'Disable autosave', desc: 'Stops the editor from autosaving drafts.', reqs: 0, kb: 0 },
          { id: 'disable_admin_notices', title: 'Disable admin notices', desc: 'Hides the nag notices plugins print across wp-admin.', reqs: 0, kb: 0 },
        ]},
        { name: 'Heartbeat', items: [
          { id: 'heartbeat_frequency', control: 'select', title: 'Heartbeat frequency', desc: 'How often the admin-ajax Heartbeat polls.',
            options: [['default','WordPress default'],['15','15 seconds'],['20','20 seconds'],['25','25 seconds'],['30','30 seconds'],['35','35 seconds'],['40','40 seconds'],['45','45 seconds'],['50','50 seconds'],['55','55 seconds'],['60','60 seconds']] },
          { id: 'heartbeat_location', control: 'select', title: 'Heartbeat locations', desc: 'Where the Heartbeat API is allowed to run.',
            options: [['default','WordPress default'],['disable_everywhere','Disable everywhere'],['disable_on_dashboard_page','Disable on dashboard page'],['allow_only_on_post_edit_pages','Allow only on post-edit pages']] },
        ]},
      ]
    },

    { id: 'woocommerce', label: 'WooCommerce', icon: 'cart', kind: 'settings', requires: 'woo',
      eyebrow: '06 — WOOCOMMERCE', title: 'WooCommerce assets',
      lead: 'Stop WooCommerce loading its scripts and styles on pages that aren’t part of the store.',
      groups: [
        { name: 'WooCommerce', items: [
          { id: 'disable_woocommerce_non_pages', title: 'Scripts & CSS off non-Woo pages', desc: 'Dequeues WooCommerce assets where the store isn’t shown.', reqs: 3, kb: 120 },
          { id: 'disable_woocommerce_cart_fragments', title: 'Defer cart fragments', desc: 'Stops the cart-fragments ajax call running on every page.', reqs: 1, kb: 0 },
          { id: 'disable_woocommerce_reviews', title: 'Disable reviews', desc: 'Turns off WooCommerce product reviews.', reqs: 0, kb: 0 },
          { id: 'disable_woocommerce_password_meter', title: 'Disable Woo password meter', desc: 'Dequeues the WooCommerce password-strength meter.', reqs: 1, kb: 0 },
        ]},
      ]
    },

    { id: 'seo', label: 'SEO', icon: 'star', kind: 'settings', requires: 'seo',
      eyebrow: '07 — SEO', title: 'Yoast SEO tidy-ups',
      lead: 'Small Yoast SEO clean-ups for sites running the plugin.',
      groups: [
        { name: 'Yoast SEO', items: [
          { id: 'remove_yoast_comment', title: 'Remove Yoast HTML comment', desc: 'Strips the Yoast SEO comment block from the head.', reqs: 0, kb: 1 },
          { id: 'remove_yoast_breadcrumbs_duplicates', title: 'De-dupe breadcrumb names', desc: 'Removes duplicate names in Yoast breadcrumbs.', reqs: 0, kb: 0 },
        ]},
      ]
    },

    { id: 'tools', label: 'Tools', icon: 'download', kind: 'tools' },
  ];

  /* ---- state -------------------------------------------------------------- */
  const state = (WPD.state = {
    section: 'dashboard',
    layout: 'tabs', rowStyle: 'table', dashboard: 'metrics', accent: 'green',
    search: '', dirty: false, saved: 0,
    flags: { woo: true, seo: true },   // overridden by WPDisableData at boot
    postTypes: [],                     // [{name,label}] from WPDisableData
    ptChecked: {},                     // { postTypeName: bool }
  });
  const wpdActive = () => !window.__activePlugin || window.__activePlugin === 'wp-disable';

  /* ---- section visibility (woo/seo are conditional) ----------------------- */
  function sections() {
    return WPD.SECTIONS.filter(s => !s.requires || state.flags[s.requires]);
  }

  /* ---- derived metrics ---------------------------------------------------- */
  function allItems() {
    const out = [];
    sections().forEach(s => (s.groups || []).forEach(g => g.items.forEach(i => out.push(i))));
    return out;
  }
  function toggleItems() { return allItems().filter(i => i.control !== 'select'); }
  function totals() {
    let reqs = 0, kb = 0, active = 0, total = 0;
    toggleItems().forEach(i => { total++; if (i.on) { active++; reqs += i.reqs || 0; kb += i.kb || 0; } });
    const score = total ? Math.min(99, 62 + Math.round((active / total) * 34)) : 70;
    return { reqs, kb, active, total, score };
  }
  function sectionCounts(sec) {
    let on = 0, n = 0;
    (sec.groups || []).forEach(g => g.items.forEach(i => { if (i.control === 'select') return; n++; if (i.on) on++; }));
    return { on, n };
  }

  /* ---- small builders ----------------------------------------------------- */
  const sw = (item) =>
    `<label class="fl-switch"><input type="checkbox" data-toggle="${item.id}" ${item.on ? 'checked' : ''} /><span class="fl-track"></span><span class="fl-thumb"></span></label>`;

  const statusPill = (on) => on
    ? `<span class="fl-pill fl-pill--good"><span class="fl-dot"></span> On</span>`
    : `<span class="fl-pill"><span class="fl-dot"></span> Off</span>`;

  function selectHTML(key, value, options, extra) {
    return `<select class="fl-select" data-select="${key}" style="width:auto;min-width:130px"${extra || ''}>` +
      options.map(([v, l]) => `<option value="${v}" ${String(v) === String(value) ? 'selected' : ''}>${l}</option>`).join('') +
      `</select>`;
  }

  function fieldHTML(item) {
    const f = item.field; if (!f) return '';
    let ctrl = '';
    if (f.type === 'select') {
      ctrl = selectHTML(f.key, f.value, f.options);
    } else if (f.type === 'posttypes') {
      ctrl = `<div class="wpd-pt-grid">` + state.postTypes.map(pt =>
        `<label class="wpd-pt"><input type="checkbox" data-pt="${pt.name}" ${state.ptChecked[pt.name] ? 'checked' : ''}/> ${pt.label}</label>`
      ).join('') + `</div>`;
    } else if (f.type === 'textarea') {
      ctrl = `<textarea class="fl-textarea fl-input--mono" data-text="${f.key}" placeholder="${f.placeholder || ''}" style="min-height:64px">${f.value || ''}</textarea>`;
    } else {
      ctrl = `<input class="fl-input fl-input--mono" data-text="${f.key}" value="${f.value || ''}" placeholder="${f.placeholder || ''}" />`;
    }
    return `<div class="wpd-field"><span class="fl-label">${f.label}</span>${ctrl}</div>`;
  }

  const warnNote = (item) => item.warn
    ? `<span class="wpd-warn"><span class="fl-i" data-ic="warn"></span>${item.warn}</span>` : '';

  /* ---- row renderers (table layout) --------------------------------------- */
  function rowTableTR(item) {
    const search = (item.title + ' ' + item.desc).toLowerCase();
    if (item.control === 'select') {
      return `<tr data-rowfor="${item.id}" data-search="${search}">
        <td class="fl-td-name">${item.title}</td>
        <td><span class="fl-row-desc">${item.desc}</span></td>
        <td colspan="2" class="fl-td-right">${selectHTML(item.id, item.value, item.options)}</td>
      </tr>`;
    }
    const sub = (item.on && item.field)
      ? `<tr class="wpd-subrow" data-subfor="${item.id}"><td colspan="4">${fieldHTML(item)}${warnNote(item)}</td></tr>`
      : '';
    return `<tr data-rowfor="${item.id}" data-search="${search}">
        <td class="fl-td-name">${item.title}</td>
        <td><span class="fl-row-desc">${item.desc}</span></td>
        <td>${statusPill(item.on)}</td>
        <td class="fl-td-right">${sw(item)}</td>
      </tr>${sub}`;
  }

  function groupHTML(g) {
    return `<div class="wpd-group">
      <div class="wpd-group-head"><span class="fl-eyebrow">${g.name}</span><span class="fl-meta">${g.items.length}</span></div>
      <div class="fl-card" style="overflow:hidden">
        <table class="fl-table"><thead><tr><th>Setting</th><th>Description</th><th>Status</th><th class="fl-td-right">Toggle</th></tr></thead>
        <tbody>${g.items.map(rowTableTR).join('')}</tbody></table>
      </div></div>`;
  }

  /* ---- section renderers -------------------------------------------------- */
  function settingsSection(sec) {
    const c = sectionCounts(sec);
    return `<div class="wpd-section" data-section="${sec.id}" data-screen-label="${sec.label}">
      <div class="wpd-section-head">
        <div class="fl-stack" style="gap:7px">
          <span class="fl-eyebrow"><span class="fl-num">${sec.eyebrow.split(' — ')[0]}</span> — ${sec.eyebrow.split(' — ')[1]}</span>
          <h2 class="fl-h1" style="font-size:24px">${sec.title}</h2>
          <p class="fl-lead" style="max-width:640px">${sec.lead}</p>
        </div>
        <div class="wpd-section-meta">
          <div class="fl-stack" style="align-items:flex-end;gap:6px">
            <span class="fl-metric-value" style="font-size:24px">${c.on}<span class="fl-unit">/ ${c.n}</span></span>
            <span class="fl-meta">ACTIVE IN SECTION</span>
          </div>
        </div>
      </div>
      ${sec.groups.map(groupHTML).join('')}
    </div>`;
  }

  function dashboardSection() {
    const t = totals();
    const wide = sections().filter(s => s.kind === 'settings');
    const header = `
      <div class="wpd-metrics">
        <div class="fl-metric">
          <div class="fl-metric-top"><span class="fl-metric-label">Requests removed</span><span class="fl-pill fl-pill--good"><span class="fl-dot"></span> Good</span></div>
          <div class="fl-metric-value is-good" data-metric="reqs">${t.reqs}</div>
          <div class="fl-metric-foot">per front-end page load</div>
        </div>
        <div class="fl-metric">
          <div class="fl-metric-top"><span class="fl-metric-label">Page weight saved</span></div>
          <div class="fl-metric-value"><span data-metric="kb">${t.kb}</span><span class="fl-unit">KB</span></div>
          <div class="fl-meter"><span data-metric="weightpct" style="width:${Math.min(100, t.kb / 4)}%"></span></div>
        </div>
        <div class="fl-metric">
          <div class="fl-metric-top"><span class="fl-metric-label">Active optimisations</span></div>
          <div class="fl-metric-value"><span data-metric="active">${t.active}</span><span class="fl-unit">/ ${t.total}</span></div>
          <div class="fl-metric-foot"><span class="fl-trend up">▲</span> tuned for speed</div>
        </div>
        <div class="fl-metric" style="flex-direction:row;align-items:center;gap:16px">
          <div class="fl-ring" data-metric="ring" style="--_v:${t.score}"><b data-metric="score">${t.score}</b></div>
          <div class="fl-stack" style="gap:3px">
            <span class="fl-metric-label">Perf score</span>
            <span style="font-size:12px;color:var(--fl-ink-2)">Lighthouse · mobile</span>
          </div>
        </div>
      </div>`;

    const breakdown = `
      <div class="wpd-group" style="margin-top:8px">
        <div class="wpd-group-head"><span class="fl-eyebrow">BY SECTION</span><span class="fl-meta">jump in</span></div>
        <div class="fl-card"><div class="fl-rows">
          ${wide.map(s => { const c = sectionCounts(s); return `
            <a class="fl-row wpd-jump" data-jump="${s.id}" style="cursor:pointer;text-decoration:none">
              <div class="fl-row-main"><div class="fl-row-title"><span class="fl-i" data-ic="${s.icon}" style="color:var(--fl-accent);width:15px;height:15px"></span> ${s.title || s.label}</div><div class="fl-row-desc">${(s.lead || '').slice(0, 78)}…</div></div>
              <div class="fl-row-ctrl"><span class="fl-pill ${c.on ? 'fl-pill--good' : ''}"><span class="fl-dot"></span> ${c.on}/${c.n} on</span><span class="fl-i" data-ic="chevron" style="color:var(--fl-ink-3);width:15px;height:15px"></span></div>
            </a>`; }).join('')}
        </div></div>
      </div>`;

    return `<div class="wpd-section" data-section="dashboard" data-screen-label="Dashboard">
      <div class="wpd-section-head" style="border:0;padding-bottom:8px">
        <div class="fl-stack" style="gap:7px">
          <span class="fl-eyebrow"><span class="fl-num">00</span> — OVERVIEW</span>
          <h2 class="fl-h1" style="font-size:24px">Dashboard</h2>
          <p class="fl-lead">A live read on what WP Disable is removing right now.</p>
        </div>
        <div class="wpd-section-meta"><span class="fl-meta">UPDATED LIVE</span></div>
      </div>
      ${header}
      ${breakdown}
    </div>`;
  }

  function toolsSection() {
    const cfg = JSON.stringify(exportConfig(), null, 2);
    return `<div class="wpd-section" data-section="tools" data-screen-label="Tools">
      <div class="wpd-section-head">
        <div class="fl-stack" style="gap:7px">
          <span class="fl-eyebrow"><span class="fl-num">08</span> — TOOLS</span>
          <h2 class="fl-h1" style="font-size:24px">Import, export &amp; reset</h2>
          <p class="fl-lead" style="max-width:640px">Move a tuned configuration between sites, or roll everything back to defaults.</p>
        </div>
      </div>
      <div class="wpd-tools-grid">
        <div class="fl-card">
          <div class="fl-card-head"><div class="fl-card-title"><span class="fl-eyebrow"><span class="fl-i" data-ic="download" style="width:13px;height:13px"></span> EXPORT</span></div><button class="fl-btn fl-btn--sm" data-act="copy"><span class="fl-i" data-ic="code"></span> Copy</button></div>
          <div class="fl-card-pad">
            <p class="fl-row-desc" style="margin:0 0 10px">Current settings as portable JSON.</p>
            <textarea class="fl-textarea" id="wpd-export" readonly style="min-height:150px">${cfg}</textarea>
          </div>
        </div>
        <div class="fl-card">
          <div class="fl-card-head"><div class="fl-card-title"><span class="fl-eyebrow"><span class="fl-i" data-ic="upload" style="width:13px;height:13px"></span> IMPORT</span></div></div>
          <div class="fl-card-pad">
            <p class="fl-row-desc" style="margin:0 0 10px">Paste a configuration and apply it.</p>
            <textarea class="fl-textarea" id="wpd-import" placeholder="Paste WP Disable JSON…" style="min-height:150px"></textarea>
            <div class="wpd-actions" style="margin-top:12px"><button class="fl-btn fl-btn--primary fl-btn--sm" data-act="import"><span class="fl-i" data-ic="upload"></span> Apply import</button><span class="fl-hint" id="wpd-import-msg"></span></div>
          </div>
        </div>
      </div>
      <div class="fl-banner fl-banner--warn" style="margin-top:16px">
        <span class="fl-i" data-ic="warn" style="color:var(--fl-warn)"></span>
        <div class="fl-banner-body"><div class="fl-banner-title">Reset all settings</div><div class="fl-banner-desc">Restores every option to its WP Disable default. This cannot be undone.</div></div>
        <button class="fl-btn fl-btn--danger fl-btn--sm" data-act="reset"><span class="fl-i" data-ic="refresh"></span> Reset to defaults</button>
      </div>
    </div>`;
  }

  function exportConfig() {
    const o = {};
    allItems().forEach(i => {
      if (i.control === 'select') { o[i.id] = i.value; return; }
      o[i.id] = i.field ? { on: !!i.on, value: i.field.value } : !!i.on;
    });
    if (state.flags.woo || state.postTypes.length) o.disable_comments_on_post_types = Object.assign({}, state.ptChecked);
    return { plugin: 'wp-disable', version: '2.1.0', settings: o };
  }

  /* ---- nav / tabs --------------------------------------------------------- */
  function tabsHTML() {
    return sections().map(s => {
      const c = s.kind === 'settings' ? sectionCounts(s) : null;
      return `<button class="fl-tab" data-nav="${s.id}" aria-selected="${state.section === s.id}">${s.label}${c ? ` <span class="fl-count">${c.on}</span>` : ''}</button>`;
    }).join('');
  }

  function renderSectionBody(sec) {
    if (sec.kind === 'dashboard') return dashboardSection();
    if (sec.kind === 'tools') return toolsSection();
    return settingsSection(sec);
  }

  /* ---- main render -------------------------------------------------------- */
  function render() {
    const root = $('#wpd'); if (!root) return;
    root.setAttribute('data-layout', 'tabs');
    root.setAttribute('data-rowstyle', 'table');
    root.setAttribute('data-accent', state.accent);

    const nav = $('#wpd-nav'); if (nav) nav.hidden = true;
    const tabs = $('#wpd-tabsbar'); if (tabs) { tabs.hidden = false; tabs.innerHTML = `<div class="fl-tabs">${tabsHTML()}</div>`; }
    const main = $('#wpd-main');
    let sec = sections().find(s => s.id === state.section);
    if (!sec) { state.section = 'dashboard'; sec = sections()[0]; }
    if (main) main.innerHTML = renderSectionBody(sec);
    paintIcons(root);
    applySearch();
    updateBar();
  }

  function paintIcons(root) {
    (root || document).querySelectorAll('[data-ic]').forEach(el => { el.innerHTML = ICON(el.getAttribute('data-ic')); });
  }

  /* ---- live updates ------------------------------------------------------- */
  function refreshMetrics() {
    const t = totals();
    document.querySelectorAll('[data-metric="reqs"]').forEach(e => e.textContent = t.reqs);
    document.querySelectorAll('[data-metric="kb"]').forEach(e => e.textContent = t.kb);
    document.querySelectorAll('[data-metric="active"]').forEach(e => e.textContent = t.active);
    document.querySelectorAll('[data-metric="score"]').forEach(e => e.textContent = t.score);
    document.querySelectorAll('[data-metric="ring"]').forEach(e => e.style.setProperty('--_v', t.score));
    document.querySelectorAll('[data-metric="weightpct"]').forEach(e => e.style.width = Math.min(100, t.kb / 4) + '%');
    document.querySelectorAll('[data-nav]').forEach(a => {
      const s = sections().find(x => x.id === a.getAttribute('data-nav'));
      if (s && s.kind === 'settings') {
        const c = sectionCounts(s);
        const cnt = a.querySelector('.fl-count'); if (cnt) cnt.textContent = c.on;
      }
    });
  }

  function updateBar() {
    const t = totals();
    const ac = $('#wpd-active-count'); if (ac) ac.textContent = t.active;
    const d = $('#wpd-dirty'); if (d) d.hidden = !state.dirty;
    const save = $('#wpd-save'); if (save) save.disabled = !state.dirty;
    const note = $('#wpd-saved-note'); if (note) note.hidden = state.dirty || !state.saved;
  }

  function markDirty() { state.dirty = true; updateBar(); }

  function setToggle(id, on) {
    const it = toggleItems().find(i => i.id === id); if (!it) return;
    it.on = on;
    if (it.field) { render(); } // dependent field appears/disappears
    else {
      document.querySelectorAll(`[data-rowfor="${id}"]`).forEach(row => {
        const pill = row.querySelector('.fl-pill');
        if (pill) { pill.className = 'fl-pill' + (on ? ' fl-pill--good' : ''); pill.innerHTML = `<span class="fl-dot"></span> ${on ? 'On' : 'Off'}`; }
      });
    }
    refreshMetrics();
    markDirty();
  }

  /* ---- search ------------------------------------------------------------- */
  function applySearch() {
    const q = state.search.trim().toLowerCase();
    document.querySelectorAll('[data-search]').forEach(el => {
      const hide = q && !el.getAttribute('data-search').includes(q);
      el.hidden = hide;
      // keep a dependent subrow in step with its parent
      const sub = el.nextElementSibling;
      if (sub && sub.classList && sub.classList.contains('wpd-subrow')) sub.hidden = hide;
    });
    document.querySelectorAll('.wpd-group').forEach(g => {
      const rows = g.querySelectorAll('[data-search]');
      if (rows.length) { const any = [...rows].some(r => !r.hidden); g.hidden = q && !any; }
    });
  }

  /* ---- navigation --------------------------------------------------------- */
  function goTo(id) { state.section = id; render(); const m = $('#wpd-main'); if (m) m.scrollTop = 0; }

  /* ---- events (delegated; gated to active plugin) ------------------------- */
  let wired = false;
  function wire() {
    if (wired) return; wired = true;
    const root = $('#wpd'); if (!root) return;

    root.addEventListener('change', (e) => {
      if (!wpdActive()) return;
      const t = e.target;
      if (t.matches('[data-toggle]')) { setToggle(t.getAttribute('data-toggle'), t.checked); return; }
      if (t.matches('[data-select]')) {
        const key = t.getAttribute('data-select');
        const std = allItems().find(i => i.id === key && i.control === 'select');
        if (std) { std.value = t.value; }
        else { const it = allItems().find(i => i.field && i.field.key === key); if (it) it.field.value = t.value; }
        markDirty(); return;
      }
      if (t.matches('[data-pt]')) { state.ptChecked[t.getAttribute('data-pt')] = t.checked; markDirty(); return; }
    });
    root.addEventListener('input', (e) => {
      if (!wpdActive()) return;
      if (e.target.matches('[data-text]')) {
        const key = e.target.getAttribute('data-text');
        const it = allItems().find(i => i.field && i.field.key === key);
        if (it) { it.field.value = e.target.value; markDirty(); }
      }
    });
    root.addEventListener('click', (e) => {
      if (!wpdActive()) return;
      const nav = e.target.closest('[data-nav]'); if (nav) { goTo(nav.getAttribute('data-nav')); return; }
      const jump = e.target.closest('[data-jump]'); if (jump) { goTo(jump.getAttribute('data-jump')); return; }
      const act = e.target.closest('[data-act]'); if (act) { doAct(act.getAttribute('data-act')); return; }
    });
  }

  function doAct(a) {
    if (!wpdActive()) return;
    if (a === 'copy') {
      const ta = $('#wpd-export'); if (ta) { ta.select(); try { navigator.clipboard.writeText(ta.value); } catch (e) {} }
      toast('Configuration copied to clipboard');
    } else if (a === 'import') {
      const msg = $('#wpd-import-msg');
      try {
        const data = JSON.parse($('#wpd-import').value);
        const s = data.settings || data;
        allItems().forEach(i => {
          if (s[i.id] === undefined) return;
          const v = s[i.id];
          if (i.control === 'select') { i.value = String(v); return; }
          if (typeof v === 'object' && v) { i.on = !!v.on; if (i.field && v.value !== undefined) i.field.value = v.value; }
          else i.on = !!v;
        });
        if (s.disable_comments_on_post_types && typeof s.disable_comments_on_post_types === 'object') {
          state.ptChecked = {}; Object.keys(s.disable_comments_on_post_types).forEach(k => { state.ptChecked[k] = !!s.disable_comments_on_post_types[k]; });
        }
        markDirty(); render(); toast('Import applied — review and save');
      } catch (err) { if (msg) { msg.textContent = 'Invalid JSON.'; msg.style.color = 'var(--fl-bad)'; } }
    } else if (a === 'reset') {
      WPD.resetAll();
    }
  }

  /* ---- save / reset (overridden by the boot shim with real ajax) ---------- */
  WPD.save = function () { state.dirty = false; state.saved = Date.now(); updateBar(); toast('Settings saved'); };
  WPD.resetAll = function () { toast('Reset to defaults'); };
  WPD.filter = function (str) { state.search = str || ''; applySearch(); };

  let toastTimer;
  function toast(msg) {
    const t = $('#wpd-toast'); if (!t) return;
    const title = t.querySelector('.fl-banner-title'); if (title) title.textContent = msg; else t.textContent = msg;
    t.hidden = false; t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.hidden = true, 220); }, 2600);
  }
  WPD.toast = toast;

  /* ---- mount -------------------------------------------------------------- */
  WPD.mount = function () { render(); wire(); };
  WPD.render = render;
  WPD.markClean = function () { state.dirty = false; state.saved = Date.now(); updateBar(); };
})();

/* ===== boot shim: real settings + WP wiring + frame registration ===== */
(function () {
  const WPD = window.WPD; if (!WPD) return;
  const D = window.WPDisableData || {};
  const st = WPD.state;

  // capability flags + post types
  if (D.flags && typeof D.flags === 'object') Object.assign(st.flags, D.flags);
  if (Array.isArray(D.postTypes)) st.postTypes = D.postTypes;

  // hydrate the data model from the real saved option
  const S = (D.settings && typeof D.settings === 'object') ? D.settings : {};
  function hydrate() {
    WPD.SECTIONS.forEach(sec => (sec.groups || []).forEach(g => g.items.forEach(it => {
      if (it.control === 'select') {
        if (S[it.id] !== undefined && S[it.id] !== null && S[it.id] !== '') it.value = String(S[it.id]);
        else it.value = it.options[0][0];
        return;
      }
      it.on = !!Number(S[it.id]);
      if (it.field) {
        if (it.field.type === 'posttypes') {
          const sel = (S.disable_comments_on_post_types && typeof S.disable_comments_on_post_types === 'object') ? S.disable_comments_on_post_types : {};
          st.ptChecked = {};
          st.postTypes.forEach(pt => { st.ptChecked[pt.name] = !!Number(sel[pt.name]); });
        } else if (S[it.field.key] !== undefined && S[it.field.key] !== null) {
          it.field.value = S[it.field.key];
        } else {
          it.field.value = '';
        }
      }
    })));
  }
  hydrate();

  /* Build a checkbox-semantics payload using the REAL field names — toggles are
   * present only when on, exactly like the legacy form POST, so the server's
   * persist_settings() (isset-based) round-trips identically. */
  function buildPayload() {
    const out = {};
    WPD.SECTIONS.forEach(sec => {
      if (sec.requires && !st.flags[sec.requires]) return; // skip hidden woo/seo keys
      (sec.groups || []).forEach(g => g.items.forEach(it => {
        if (it.control === 'select') { out[it.id] = String(it.value); return; }
        if (it.on) out[it.id] = '1';
        if (it.field) {
          if (it.field.type === 'posttypes') {
            const m = {}; st.postTypes.forEach(pt => { if (st.ptChecked[pt.name]) m[pt.name] = '1'; });
            if (Object.keys(m).length) out[it.field.key] = m;
          } else if (it.field.value !== undefined && it.field.value !== '') {
            out[it.field.key] = it.field.value;
          }
        }
      }));
    });
    return out;
  }

  function post(action, payload, cb) {
    if (!D.ajaxUrl || !action) { cb && cb({ success: false }); return; }
    const body = new FormData();
    body.append('action', action);
    body.append('nonce', D.nonce || '');
    if (payload) body.append('data', JSON.stringify(payload));
    fetch(D.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
      .then(r => r.json()).then(j => cb && cb(j)).catch(() => cb && cb({ success: false }));
  }

  const A = D.actions || {};

  WPD.save = function () {
    post(A.save, buildPayload(), function (j) {
      WPD.markClean();
      WPD.toast(j && j.success ? 'Settings saved' : 'Save failed');
    });
  };

  WPD.resetAll = function () {
    post(A.reset, null, function (j) {
      WPD.toast(j && j.success ? 'Reset to defaults' : 'Reset failed');
      if (j && j.success) setTimeout(() => location.reload(), 600);
    });
  };

  if (window.Folium && window.Folium.registerApp) {
    window.Folium.registerApp('wp-disable', {
      mount: function () { WPD.mount(); },
      save: function () { WPD.save(); },
      reset: function () { WPD.resetAll(); },
      filter: function (s) { WPD.filter(s); }
    });
  }
})();
