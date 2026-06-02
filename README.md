# Qaiyo Testimonials

> WordPress plugin for displaying customer testimonials in **12 modern layouts** with a visual Display Builder, Schema.org rich snippets, and bundled translations for 5 languages.

[![WordPress 5.8+](https://img.shields.io/badge/WordPress-5.8%2B-21759b.svg)](https://wordpress.org/)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![Version](https://img.shields.io/badge/version-1.0.0-6c5ce7.svg)](#)

This repository hosts the **free** Qaiyo Testimonials plugin (V1–V6 layouts and the core feature set).
The optional [Qaiyo Testimonials Pro](https://qaiyo-plugins.com/qaiyo-testimonials-pro/) add-on unlocks
six additional layouts (V7–V12), star ratings, a front-end submission form, video testimonials and more.

- **Website:** [qaiyo-plugins.com](https://qaiyo-plugins.com)
- **Support:** info@qaiyo-plugins.com
- **Issues:** [GitHub Issues](../../issues)

---

## Why this plugin

Most WordPress testimonial plugins ship 2–4 layouts and lock everything else behind a paid tier.
Qaiyo Testimonials ships **six full layouts free** — with the visual builder, the Schema.org markup,
and the multilingual support that competitors usually charge for — and uses the Pro add-on only for
genuinely advanced features.

The plugin is designed to be:

- **Fast** — no external dependencies, no bloated UI library, vanilla JS for the slider.
- **SEO-ready** — Schema.org Review microdata + JSON-LD `ItemList` out of the box.
- **Accessible** — semantic HTML (`<figure>`, `<blockquote>`, `<figcaption>`), proper landmarks,
  `prefers-reduced-motion` respected.
- **Developer-friendly** — a stable hook/filter API for extensions (the Pro add-on uses it).
- **Translation-ready** — five languages bundled (`.po` + `.mo`), Polylang/WPML/TranslatePress
  compatible, locale-variant fallback (e.g. `de_AT → de_DE`).

---

## Features

### Layouts (V1–V6, free)

| Version | Description |
|---|---|
| **V1 — Horizontal rows** | 1 or 2 rows scrolling left/right, optional alternating direction, fading edges |
| **V2 — Vertical columns** | 2 or 3 columns scrolling up/down, fading edges |
| **V3 — Photo carousel** | Single-row carousel with circular avatars and a handwritten name signature |
| **V4 — Bento grid** | Asymmetric static grid with a per-testimonial highlight box |
| **V5 — Avatar switcher** | Clickable photo grid (max 29) with an auto-rotating featured panel |
| **V6 — Photo hero card** | Large image on top, dark content section with a quote marker below — 1 to 4 columns |

Six more layouts (V7–V12) are previewed inside the admin as locked teasers and unlocked
by [Qaiyo Testimonials Pro](https://qaiyo-plugins.com/qaiyo-testimonials-pro/).

### Display Builder

A visual wizard CPT for assembling reusable display configurations:

- Version picker with high-fidelity SVG wireframe previews for every layout.
- 4 quote-mark styles (teardrop "66", bold block, calligraphic comma, speech-bubble outline).
- Category selector, per-version options (rows, columns, direction, speed, fade).
- Per-display design overrides (colors, borders, padding, gap, font sizes).
- Generates a stable `[qaiyo_testimonials display="ID"]` shortcode.

### Content & data

- Custom post type `qaiyo_testimonial` with the `qt_category` taxonomy.
- Photo upload via WordPress Media Library (JPG / PNG / WebP, max 1 MB) with MIME validation.
- Per-testimonial fields: name, photo, position, company, optional heading, quote, card colors.
- **Uninstall data choice** — on plugin deletion you decide whether to keep your testimonials in
  the database or erase everything.

### SEO & accessibility

- Schema.org Review microdata on every card (`itemscope`, `itemtype`, `itemprop`).
- JSON-LD `ItemList` of `Review` objects in the rendered output — picked up by Google Rich Results
  and AI search engines (ChatGPT, Perplexity, Claude).
- Semantic HTML: `<figure>`, `<blockquote>`, `<figcaption>`.
- Responsive on every layout (mobile breakpoint at 768px).
- `prefers-reduced-motion` honored — sliders and rotations pause automatically.

### Internationalization

Bundled translations: **English, Hungarian, German, French, Spanish.**
Locale-variant fallback (`de_AT`, `de_CH` → `de_DE`; `fr_CA`, `fr_BE` → `fr_FR`; etc.).
Compatible with Polylang, WPML, TranslatePress and Loco Translate.

### Admin UX

- **Qaiyo brand menu group** — a Crocoblock-style chip separator labelled *"QAIYO PLUGINOK"*
  wraps the Qaiyo plugin menus in the WP admin sidebar (coordinates across multiple Qaiyo plugins
  via shared globals).
- Settings page with default appearance, slider behavior, a wireframe gallery overview of all
  twelve layouts, and a shortcode reference table.

---

## Installation

### From a ZIP file

1. Download the latest [release ZIP](../../releases) or build one locally (see *Development*).
2. In WordPress: **Plugins → Add New → Upload Plugin** → choose the ZIP → install → activate.
3. Add testimonials under **Testimonials** in the admin menu.
4. Create a display under **Testimonials → Displays** or use the raw shortcode below.

### From source (developers)

```bash
git clone https://github.com/qaiyo/qaiyo-testimonials.git
cd qaiyo-testimonials
# Symlink or copy the folder into wp-content/plugins/
ln -s "$(pwd)" /path/to/wordpress/wp-content/plugins/qaiyo-testimonials
```

**Requirements:** WordPress 5.8+, PHP 7.4+.

---

## Usage

### Recommended: a saved Display

Create a Display under **Testimonials → Displays**, configure the layout, then use:

```
[qaiyo_testimonials display="42"]
```

The Display's stored configuration provides every default; any attribute you also pass on the
shortcode overrides it for that one instance.

### Raw shortcode attributes

```
[qaiyo_testimonials version="v1" category="clients" speed="40"]
```

Common attributes:

| Attribute | Values | Used by |
|---|---|---|
| `version` | `v1` – `v6` (free), `v7` – `v12` (Pro) | All |
| `display` | Display post ID | All — overrides everything else except attributes you also pass explicitly |
| `category` | Category slug | All |
| `ids` | `1,2,3` | All |
| `limit` | `-1`, `10`, … | All |
| `quote_style` | `1`, `2`, `3`, `4` | All except V5 |
| `rows` | `1`, `2` | V1 |
| `columns` | `1` – `4` | V2, V6 |
| `direction` | `left`, `right`, `up`, `down` | V1, V2, V3 |
| `alt_direction` | `0`, `1` | V1, V2 |
| `speed` | `5` – `600` (sec) | V1, V2, V3, V5 |
| `fade_edges` | `0`, `1` | V1, V2, V3 |
| `bg_color`, `text_color`, `meta_color`, `quote_color`, `name_color`, `border_color` | `#hex` | All except V5 |
| `padding`, `border_width`, `border_radius`, `gap`, `heading_size`, `text_size` | px | All except V5 |

A complete attribute reference is rendered on the Settings page inside the WordPress admin.

---

## Developer API

A stable set of hooks lets you extend the plugin without modifying core. The Pro add-on uses
nothing but these — your own extensions can do the same.

### Filters

| Filter | Purpose |
|---|---|
| `qt_layouts_catalog` | Add/modify entries in the layout catalog (label + description for each version). |
| `qt_supported_versions` | Versions the shortcode will actually render. |
| `qt_unlocked_versions` | Versions shown as selectable (not locked) in the Display Builder. |
| `qt_upgrade_url` | Override the "Unlock in Pro" URL. |
| `qt_shortcode_defaults` | Adjust shortcode attribute defaults. |
| `qt_shortcode_atts` | Final attribute mutation after defaults + user input + Display config. |
| `qt_query_args` | Modify the `WP_Query` args used to fetch testimonials. |
| `qt_query_items` | Modify the loaded item collection. |
| `qt_item_data` | Attach extra fields per item (rating, video URL, source, etc.). |
| `qt_quote_styles` | Register additional quote-mark style numbers. |
| `qt_quote_style_svg` | Provide the SVG for a given quote-mark style. |
| `qt_wireframe_svg` | Provide the wireframe SVG for a given version. |
| `qt_card_html` | Inject extra markup into every rendered card (badges, stars, source icons). |
| `qt_pre_render_html` | Short-circuit the renderer — used by Pro for V7–V12. |
| `qt_render_html` | Final HTML transformation. |
| `qt_schema_jsonld` | Extend the JSON-LD payload (e.g. `AggregateRating`). |
| `qt_settings_defaults` | Register additional Settings page option keys. |

### Actions

| Action | Fires |
|---|---|
| `qt_after_render` | After every shortcode render (analytics / tracking). |
| `qt_after_save_testimonial` | After a testimonial's meta is saved (post-nonce, post-cap). |
| `qt_after_save_display` | After a Display configuration is saved. |
| `qt_register_meta_boxes` | After core meta boxes are registered — add your own here. |

### Public API methods

The `Qt_Shortcode::render_v1()` … `render_v6()` methods are `public static` — Pro layouts and other
extensions can call them directly or reuse them as a base. `Qt_Shortcode::style_vars()`,
`Qt_Shortcode::customer_block()` and `Qt_Shortcode::uid()` are also public for the same reason.

### Example: add a "Verified" badge to every card

```php
add_filter( 'qt_card_html', function ( $html, $item, $version, $atts ) {
    if ( get_post_meta( $item['id'], '_my_verified', true ) ) {
        $badge = '<span class="my-verified-badge">✓ Verified</span>';
        $html  = str_replace( '<figcaption', $badge . '<figcaption', $html );
    }
    return $html;
}, 10, 4 );
```

---

## Translations

The plugin ships with five languages in `/languages/`:

```
qaiyo-testimonials.pot
qaiyo-testimonials-hu_HU.po + .mo
qaiyo-testimonials-de_DE.po + .mo
qaiyo-testimonials-fr_FR.po + .mo
qaiyo-testimonials-es_ES.po + .mo
```

The `.po` / `.mo` / `.pot` files are generated by a single Python script so we don't need
`msgfmt` on every build machine:

```bash
cd languages/
python3 build-translations.py
```

The script reads the source strings and the translation tables defined at its top and writes
all `.po`, `.mo` and the `.pot` template. To add a new language, extend the `TRANSLATIONS` dict
in the script and re-run it.

The plugin uses `load_textdomain()` directly (not `load_plugin_textdomain()`) to avoid the
WordPress.org Plugin Check warning about discouraged functions.

---

## Standards & security

The codebase follows the WordPress Coding Standards and the WordPress.org Plugin Check rules:

- Class prefix `Qt_` (matches the plugin slug).
- `$_POST` data is always sanitized with `wp_unslash()` before being passed to a sanitizer.
- Every PHP file has an `ABSPATH` guard.
- Every save handler verifies a nonce and a capability.
- Every dynamic output uses `esc_html`, `esc_attr`, `esc_url` or `wp_json_encode`.
- No raw SQL — only WP APIs (`get_posts`, `register_post_type`, `update_post_meta`, …).
- No `eval`, `extract`, `create_function`.

---

## Development

### Repository layout

```
qaiyo-testimonials.php          Main plugin file (header, constants, bootstrap)
includes/                       PHP classes
  class-qt-brand-menu.php       "QAIYO PLUGINOK" admin separator chip
  class-qt-catalog.php          Single source of truth for all 12 layouts
  class-qt-cpt.php              Custom post type + taxonomy
  class-qt-display.php          Display Builder (qt_display CPT)
  class-qt-i18n.php             Locale loading with variant fallback
  class-qt-meta.php             Testimonial meta boxes
  class-qt-settings.php         Settings page
  class-qt-shortcode.php        [qaiyo_testimonials] renderer
  class-qt-svg.php              Inline SVG library (wireframes, quote marks)
assets/
  css/admin.css                 Admin UI
  css/frontend.css              Frontend layouts
  js/admin.js                   Media Library picker
  js/display-builder.js         Version picker UX
  js/frontend.js                Vanilla JS slider + V5 switcher
  fonts/caveat-500.woff2        Bundled handwriting font
languages/
  build-translations.py         .po / .mo / .pot generator
  qaiyo-testimonials.pot
  qaiyo-testimonials-{hu_HU,de_DE,fr_FR,es_ES}.{po,mo}
readme.txt                      WordPress.org readme
uninstall.php                   Optional full data wipe on plugin deletion
```

### Building a release ZIP

```bash
cd ..
rm -f qaiyo-testimonials.zip
zip -rq qaiyo-testimonials.zip qaiyo-testimonials -x "*.DS_Store"
```

### Asset cache busting

The admin enqueue helper `qt_asset_ver()` (in `qaiyo-testimonials.php`) returns
`QT_VERSION + '.' + filemtime` for each asset, so CSS/JS changes are picked up without a manual
version bump.

---

## Contributing

Bug reports and pull requests are welcome via [GitHub Issues](../../issues).

Please follow the existing coding style (tabs for indentation, WPCS-compliant, JSDoc/PHPDoc on
public methods) and add an entry to the `readme.txt` changelog under the next version.

---

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) or <https://www.gnu.org/licenses/gpl-2.0.html>.

The bundled Caveat font (`assets/fonts/caveat-500.woff2`) is by Pablo Impallari and licensed
under the [SIL Open Font License 1.1](https://scripts.sil.org/OFL).

---

## Credits

Made by **[Qaiyo by PixelDesigns](https://qaiyo-plugins.com)**.
Part of the Qaiyo plugin family — a set of WordPress plugins that share a brand, a design
system, and a coordinated admin experience.

Contact: info@qaiyo-plugins.com
