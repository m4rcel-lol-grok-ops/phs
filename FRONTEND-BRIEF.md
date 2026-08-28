# Frontend build brief — pornhub.singles

> Paste this whole file to the AI you want to build the frontend. It is written
> to be self-contained: it states the product, the hard constraints, the exact
> server-side contract every template must satisfy, and the acceptance criteria.

---

## 1. Your task

Build the complete frontend for **pornhub.singles**, a self-hostable link-in-bio
platform: one public page per user holding their avatar, bio and a list of links.

Deliver server-rendered PHP templates plus a hand-written stylesheet and vanilla
JavaScript. **The PHP backend already exists and is out of scope — do not modify
controllers, models, routing, or middleware.** Your job is everything under:

```
resources/views/**      templates
public/assets/css/**    stylesheets
public/assets/js/**     scripts
```

If you believe a template genuinely cannot be built without a backend change,
say so explicitly and propose the smallest change — do not silently edit PHP.

---

## 2. The product, and one hard constraint

It is a parody of adult-site visual culture: black and orange, loud, a bit
stupid on purpose. The copy is dry and self-deprecating ("totally real
visitors", "Nothing here yet. Embarrassing.").

**The constraint, which is not negotiable:**

- This site hosts **no pornography and no sexually explicit material**. It is a
  profile service. Explicit content is banned by its content policy.
- Nothing you produce may be sexual, suggestive, or NSFW. No suggestive imagery,
  illustration, iconography, copy, or innuendo beyond the domain name itself.
- Nothing may imitate Pornhub's or Aylo's actual trademarks, logo, wordmark, or
  brand assets. The joke is a black-and-orange aesthetic and the domain — not a
  copy of anyone's logo. Every page must remain plainly an independent parody.

Treat "black background, orange accent, blocky bold type" as the whole visual
reference. Do not reproduce any real brand's mark.

---

## 3. Technical constraints

- **PHP 8.3**, server-rendered. No build step, no bundler, no npm, no compiler.
- **No frontend framework.** No React/Vue/Svelte/Alpine/jQuery/htmx.
- **No CDNs and no external requests of any kind.** Self-hosted instances run on
  private networks. Inline or vendor everything. System font stacks only — no
  Google Fonts, no icon fonts. Use Unicode glyphs or inline SVG for icons.
- **CSS**: one hand-written stylesheet using custom properties. No Tailwind, no
  preprocessor. Modern CSS is fine (nesting, `:has()`, `clamp()`, grid) as long
  as it degrades sanely.
- **JS**: vanilla, in an IIFE, loaded with `defer`. The site must remain fully
  usable with JavaScript disabled — JS is progressive enhancement only.
- Total CSS + JS should stay comfortably under ~60 KB uncompressed.

---

## 4. Template contract — follow exactly

### 4.1 Every page template

Views live at `resources/views/<name>.php` and are rendered by `view('a.b')`,
which maps `a.b` → `resources/views/a/b.php` and `extract()`s the data array
into scope. Each page buffers its body and hands it to the layout:

```php
<?php ob_start(); ?>
<section class="section">
    …page body…
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
```

`layouts/main.php` owns `<!DOCTYPE>`, `<head>`, header, footer and echoes
`$content`. **Exception:** `profile/show.php` is a standalone document with its
own `<html>` — it does not use the layout (see §7).

### 4.2 Escaping — non-negotiable

- Echo **every** dynamic value through `e()`: `<?= e($profile['bio']) ?>`.
- Never interpolate a user value into a `<style>` block or a `style=` attribute
  except through the sanitizers in §7.3. HTML escaping does **not** apply inside
  `<style>`; a raw value there is a stored-XSS hole.
- Every state-changing `<form>` must contain `<?= csrf_field() ?>`.
- All POST endpoints are listed in §5. `GET` never mutates.

### 4.3 Helpers available in every template

| Helper | Purpose |
|---|---|
| `e($s)` | HTML-escape. Use on all output. |
| `csrf_field()` | Hidden CSRF input. Required in every form. |
| `csrf_token()` | Raw token, for the `<meta name="csrf-token">` tag. |
| `flash_alerts()` | Returns pre-built HTML for pending success/error/info messages. |
| `old($key)` | Escaped previously-submitted value, to repopulate a form after a failed POST. |
| `url($path)`, `asset($path)` | Absolute site URL; asset URL with a cache-busting `?v=`. |
| `site_name()` | Configured site name — use instead of hardcoding. |
| `setting_bool($key)` | Runtime settings: `registration_enabled`, `discovery_enabled`, `maintenance_mode`. |
| `is_logged_in()`, `current_user()`, `is_admin()` | Viewer state. `current_user()` returns `null` or a user row. |
| `format_number($n)` | `12483` → `12.5K`. |
| `time_ago($datetime)` | `3d ago`. |
| `link_host($url)` | `https://www.github.com/x` → `github.com`. |
| `upload_filename($name)` | Returns the filename only if it is a valid generated upload name, else `null`. **Always** pass stored `avatar`/`banner`/`bg_image` through this before building a URL. |

Gate anything that depends on a setting, e.g. only render the "Create profile"
button when `setting_bool('registration_enabled', true)` is true.

---

## 5. Pages to build

All routes are `GET` unless marked. Views marked *(no layout)* are standalone.

### Public

| Route | View | Data in scope |
|---|---|---|
| `/` | `pages/home.php` | `$title, $description, $featured` (up to 3 profile rows), `$stats` = `['profiles','links','views']` |
| `/discover` | `pages/discover.php` | `$profiles, $total, $page, $perPage, $totalPages, $sort` (`popular\|new\|random`), `$search` (string or `null`) |
| `/features`, `/about`, `/content-policy`, `/privacy`, `/terms`, `/contact` | `pages/*.php` | `$title, $description` — static editorial content, written by you |
| `/{username}` | `profile/show.php` *(no layout)* | see §7 |
| `/login` · POST | `auth/login.php` | field name is **`identifier`** — accepts username *or* email, so it is `type="text"`, never `type="email"` |
| `/register` · POST | `auth/register.php` | fields `username, email, password, password_confirm` |
| POST `/report` | modal inside `profile/show.php` | fields `user_id, type, reason` (min 10 chars) |
| POST `/logout` | form in the layout header | logout is **POST-only**; a plain link will not work |

### Dashboard (all require login)

| Route | View | Data |
|---|---|---|
| `/dashboard` | `dashboard/index.php` | `$user, $profile, $links, $totalClicks, $viewSeries` (14 × `['day','total']`) |
| `/dashboard/profile` · POST | `dashboard/profile.php` | `$user, $profile` |
| `/dashboard/links` · POST | `dashboard/links.php` | `$user, $profile, $links` |
| `/dashboard/appearance` · POST | `dashboard/appearance.php` | `$user, $profile, $themes` |
| `/dashboard/account` · POST | `dashboard/account.php` | `$user, $profile` |

Additional POST endpoints these pages must target:
`/dashboard/avatar`, `/dashboard/avatar/delete`, `/dashboard/banner`,
`/dashboard/banner/delete`, `/dashboard/links/{id}`,
`/dashboard/links/{id}/delete`, `/dashboard/links/{id}/move` (body:
`direction=up|down`), and `/dashboard/links/reorder` (AJAX, body: `_csrf` +
`order` = JSON array of link ids, responds `{"ok":true}`).

### Admin (require an admin account)

| Route | View | Data |
|---|---|---|
| `/admin` | `admin/index.php` | `$stats, $recentUsers, $recentActions` |
| `/admin/users` · POST | `admin/users.php` | `$users, $total, $page, $perPage, $totalPages, $q, $filter` |
| `/admin/reports` · POST | `admin/reports.php` | `$reports, $status, $counts` |
| `/admin/settings` · POST | `admin/settings.php` | `$settings` |

User actions POST `user_id` + `action` ∈ `disable, enable, verify, unverify,
promote, demote, delete, reset_password, unlock`. Report actions POST
`report_id` + `action` ∈ `dismiss, review, action`, plus optional `notes` and
`disable_user`.

### Errors

`errors/404.php`, `403.php`, `405.php`, `429.php`, `500.php`,
`maintenance.php`. These are `require`d from arbitrary places (router,
middleware, the top-level exception handler), so **they must not assume any
variable is set** — give every value a fallback. Build one shared partial and
keep each file a thin wrapper. `403.php` may receive `$csrf_expired = true`,
which should produce a "your session expired, log in and retry" message rather
than an accusatory permission error.

---

## 6. Data shapes

`$user` — `id, username, email, role` (`user|admin`), `is_verified`,
`is_disabled`, `created_at`.

`$profile` — `id, user_id, display_name, bio, location, website, pronouns,
avatar, banner, theme, bg_type` (`solid|gradient|image|url`)`, bg_color,
bg_gradient, bg_image, bg_url, card_color, accent_color, text_color,
button_color, font_family, use_custom_colors, effects_enabled, effect_type,
music_url, music_title, music_artist, is_public, show_in_discover,
profile_views` — plus joined `username, is_verified, user_created_at`.

`$links[]` — `id, title, url, description, emoji, is_enabled, sort_order,
click_count`.

Nullable in practice: `display_name, bio, location, website, pronouns, avatar,
banner, music_*`, and every link field except `title` and `url`. Always render a
sensible fallback (e.g. avatar → a coloured circle with the first initial).

---

## 7. The public profile page — the important one

This is the page users actually share. It is a standalone HTML document, not
inside the site layout, and it is themed per user.

### 7.1 Content

Banner, avatar (or initial placeholder), display name + verified badge, `@handle`,
optional pronouns, bio, meta row (location · website host · joined date),
optional music player, the link list, a footer stat line, and a small actions row
with **Report profile** and a link back to the site.

If `$isOwner` is true, show a small owner bar above the card ("this is your
public profile" / "your profile is private — only you can see this") linking to
the appearance editor, and **hide the report control** — nobody reports
themselves.

### 7.2 Theming

`$theme` is pre-resolved for you by `Theme::resolve()` and contains:

```
theme      string   one of hub|midnight|terminal|corporate|degenerate|minimal
colors     array    bg, card, accent, text, button   (all #rrggbb, sanitized)
font       string   a complete, ready-to-use font-family stack
radius     string   e.g. "24px"
border     string   e.g. "rgba(255,255,255,0.08)"
background string   a ready-to-use CSS `background` shorthand value
effect     ?string  particles|gradient|glow|snow|crt|scanlines, or null
custom     bool     whether the user overrode the theme palette
```

Render these as **custom property values only**, on a single wrapper:

```php
<style>
  .profile-body {
    --p-accent: <?= $theme['colors']['accent'] ?>;
    /* …the rest… */
    background: <?= $theme['background'] ?>;
    font-family: <?= $theme['font'] ?>;
  }
</style>
<body class="profile-body theme-<?= e($theme['theme']) ?>">
```

Every rule in the profile stylesheet consumes those variables. A user value must
never become a selector or a property name. Add per-theme flourishes via the
`.theme-*` class (Terminal: dashed borders, monospace; Corporate: light and
crisp; Degenerate: loud gradients; Minimal: no shadows).

`App\Core\Theme` also offers `Theme::contrast($hex)` (returns readable `#000`/
`#fff` for a background), `Theme::rgba($hex, $alpha)`, and `Theme::isLight($hex)`
— use these so text on a user-chosen button colour stays readable.

### 7.3 Sanitizers — use these, do not roll your own

`css_color($v, $fallback)` · `css_gradient($v, $fallback)` · `css_url($v)`
(returns `null` if unsafe) · `upload_filename($v)` (returns `null` if not a
generated upload name). Anything user-supplied that reaches CSS goes through one
of these first. `$theme['background']` has already been through them.

### 7.4 Effects

Implement the six effects listed above in CSS, with JS only for spawning
particle/snow elements. All of them must be **disabled entirely** under
`@media (prefers-reduced-motion: reduce)`. They are decorative and must never
intercept pointer events — the effect layer is `position: fixed; inset: 0;
pointer-events: none;` behind the card.

---

## 8. Interaction requirements

These are the behaviours that must actually work. Several are places a previous
implementation went wrong — treat them as regression tests.

1. **Report modal.** Hidden on load; opens on click; closes on Cancel, on
   backdrop click, and on `Escape`; returns focus to the trigger. Use the
   `hidden` attribute for state and `[hidden] { display: none }` in CSS.
   **Never put an inline `style="display:flex"` on a `[hidden]` element** — the
   inline style beats the attribute and the dialog is permanently stuck open.
   Give it `role="dialog"`, `aria-modal="true"`, a label, and a focus trap.
2. **Mobile navigation.** Hamburger below ~820px, `aria-expanded` kept in sync,
   closes on `Escape`, on outside click, and on resize past the breakpoint
   (otherwise body scroll-lock leaks).
3. **Link reordering.** Drag by an explicit handle (not the whole row — text
   must stay selectable), POST the new order to `/dashboard/links/reorder`, and
   announce the result in an `aria-live` region. Each row is followed by its own
   `<details>` edit form: move the pair together, never orphan the editor.
   Also render the `▲ ▼` move buttons — they are the keyboard and no-JS path,
   and they must be disabled at the ends of the list.
4. **Flash messages.** Render `flash_alerts()` once per page. Auto-dismiss
   successes after a few seconds; **leave errors on screen** — they usually need
   to be acted on.
5. **Conditional fields.** On the appearance page, the gradient input, the
   background-URL input, the effect picker and the custom-colour block are only
   shown when their controlling `<select>`/checkbox says so. Drive this with
   data attributes, and make sure the correct state is visible on first paint.
6. **Confirmations.** Destructive actions (delete link, delete account, disable
   or delete a user) confirm first. Bind to the form's `submit`, not just the
   button's `click`, so pressing Enter in a field cannot bypass it.

---

## 9. Design direction

- **Dark by default.** A slightly warm near-black (`#0b0a09`) rather than pure
  grey, so orange never looks pasted on. Orange `#ff9900` is the single accent —
  use it for emphasis, not for large fills.
- **Type.** System font stack. Tight, heavy headings (800–900 weight, negative
  letter-spacing); comfortable 1.6–1.65 body leading. Fluid sizes with `clamp()`.
- **Depth through layering,** not heavy borders: surface → card → elevated, with
  1px hairline borders and generous radii (12–26px).
- **Motion is small and quick** (~0.15–0.25s). A 1–3px lift on hover, nothing
  that moves the page around.
- **Tokens.** Define colours, radii, shadows, easing and spacing as custom
  properties in `:root` and use them everywhere. No magic hex values in rules.
- Give the marketing pages a proper hero with a soft radial accent glow, and
  show real instance numbers (`$stats`) rather than invented ones.
- Empty states are part of the design, not an afterthought: every list needs one
  (no links, no search results, no reports), each with a heading, a line of copy,
  and the action that resolves it.

---

## 10. Accessibility & responsiveness — required, not optional

- Skip link to `#main`. One `<h1>` per page. Landmarks: `header`, `nav`,
  `main`, `footer`.
- Visible `:focus-visible` on every interactive element. Full keyboard operation
  including the modal and the reorder controls.
- Label every input. Icon-only buttons get `aria-label`. Decorative glyphs get
  `aria-hidden="true"`.
- Body text ≥ 4.5:1 contrast; check orange-on-black and, in the Corporate theme,
  dark-on-light.
- Breakpoints ~900px (dashboard sidebar → horizontal scroller) and ~820px
  (nav → drawer) and ~640px (single column). No horizontal page scroll at 320px;
  tables scroll inside their own container.
- Honour `prefers-reduced-motion` globally, not just for profile effects.

---

## 11. Deliverables

1. Every view listed in §5, plus `layouts/main.php`, `dashboard/_sidebar.php`,
   `admin/_nav.php`, and a shared error partial.
2. `public/assets/css/app.css` — site chrome, marketing pages, dashboard, admin.
3. `public/assets/css/profile.css` — the public profile page, themes, effects.
4. `public/assets/js/app.js` — nav, confirmations, reordering, conditional
   fields, copy-to-clipboard, flash dismissal.
5. `public/assets/js/profile.js` — music player, report modal, particle effects.
6. A short note listing any assumption you made and anything you deliberately
   left out.

## 12. Acceptance criteria

- [ ] Every route in §5 renders with no PHP notice, warning, or undefined-index.
- [ ] Every dynamic value is escaped; every form carries a CSRF field.
- [ ] Submitting `bg_gradient` = `red; } </style><script>alert(1)</script>` or a
      `bg_url` containing quotes or parentheses produces **no** script execution
      and no broken layout on the profile page.
- [ ] All six themes visibly change the profile page; all six effects render and
      vanish under `prefers-reduced-motion`.
- [ ] The report modal is closed on load and closes via Cancel, backdrop, and
      `Escape`.
- [ ] Reordering persists; the `▲ ▼` buttons work with JavaScript disabled.
- [ ] Every page is usable with JavaScript disabled.
- [ ] No network request leaves the origin — verify an empty external-request
      list in devtools.
- [ ] Keyboard-only pass through login → dashboard → links → appearance → a
      public profile, with focus always visible.
- [ ] Nothing sexual, explicit, or brand-imitating anywhere in the output.
