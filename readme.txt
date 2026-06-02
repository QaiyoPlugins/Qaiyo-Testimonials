=== Qaiyo Testimonials ===
Contributors: pixeldesigns
Tags: testimonials, reviews, slider, bento, carousel
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display customer testimonials in six built-in layouts with a visual Display Builder, Schema.org rich snippets, and five bundled languages.

== Description ==

Qaiyo Testimonials provides a flexible custom post type with categorisation and six built-in frontend layouts, each fully configurable through a visual Display Builder, shortcode attributes and a settings page.

= Free layouts =

* **V1** — One or two rows, horizontal scroll, optional alternating direction, fading edges.
* **V2** — Two or three vertical columns scrolling up/down, optional alternating direction per column.
* **V3** — Single-row carousel with a circular customer avatar on top of each card.
* **V4** — Bento grid layout that adapts to the testimonial count, with per-item highlight box & custom color.
* **V5** — Avatar grid (max 29) where clicking a face reveals the matching testimonial below; auto-rotates.
* **V6** — Photo hero card: large image on top, dark content section with a quote marker below.

= More layouts with Pro =

Six additional layouts (V7–V12) — pastel tilted cards, split-panel hero, masonry wall, featured + grid, spotlight + list, and an editorial big-quote — are previewed inside the admin and unlocked by the optional **Qaiyo Testimonials Pro** add-on, which also adds star ratings, a front-end submission form, video testimonials and more.

= Per-testimonial fields =

Customer name (title), photo (JPG/PNG/WebP, max 1 MB), position, company, optional heading, quote/description, plus card-color controls (used by V4 highlight and V7 background).

= Shortcode =

`[qaiyo_testimonials version="v1" category="clients" speed="40"]`

See the Settings page for every available attribute.

= Multilingual =

Ships with translations for English, Hungarian, German, French and Spanish, with locale variant fallback (e.g. de_AT → de_DE). Compatible with Polylang, WPML, TranslatePress and Loco Translate.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu.
3. Add testimonials under Testimonials in the admin menu.
4. Place the shortcode `[qaiyo_testimonials version="v1"]` on any page.

== Frequently Asked Questions ==

= Can I change colors per shortcode instance? =
Yes — every appearance setting on the Settings page can be overridden via shortcode attributes.

= Does V5 support more than 29 testimonials? =
No, V5 is limited to the first 29 to keep the avatar grid manageable. Use V1/V2/V3 for larger sets.

== Changelog ==

= 1.0.0 =
First public release.

**Six built-in layouts**

* V1 — Horizontal rows: 1 or 2 rows scrolling left/right, optional alternating direction, fading edges.
* V2 — Vertical columns: 2 or 3 columns scrolling up/down, optional alternating direction per column, fading edges.
* V3 — Photo carousel: single row with circular avatars and a handwritten name signature.
* V4 — Bento grid: asymmetric static grid with a per-testimonial highlight box (custom background + text color).
* V5 — Avatar switcher: clickable photo grid (max 29), auto-rotating featured quote panel.
* V6 — Photo hero card: large image on top, dark content section with a quote-circle marker. 1-4 columns.

Six more layouts (V7-V12: pastel tilted, split-panel hero, masonry wall, featured + grid, spotlight + list, editorial big quote) are previewed in the admin and unlocked by the optional Qaiyo Testimonials Pro add-on.

**Content & data**

* Custom post type with categories.
* Photo upload via WordPress Media Library (JPG/PNG/WebP, max 1 MB) with MIME validation.
* Per-testimonial fields: name, photo, position, company, optional heading, quote/description, card-color controls.
* Uninstall data choice: on plugin deletion you decide whether to keep your testimonials in the database or erase everything.

**Display Builder**

* Visual wizard for assembling reusable display configurations.
* Version picker with high-fidelity SVG wireframes for all twelve layouts (V7-V12 shown as locked Pro previews).
* 4 quote-mark styles (teardrop / bold block / calligraphic comma / speech-bubble outline).
* Category selector, per-version options (rows/columns/direction/speed/fade), per-display design overrides.
* Generates a stable [qaiyo_testimonials display="ID"] shortcode.

**SEO & accessibility**

* Schema.org Review microdata + JSON-LD ItemList for Google Rich Results and AI search engines.
* Semantic HTML (figure / blockquote / figcaption) with proper itemprop attributes.
* Responsive on every layout; prefers-reduced-motion respected.

**Internationalization**

* Bundled translations: English, Hungarian, German, French, Spanish, with locale-variant fallback.
* Polylang, WPML, TranslatePress and Loco Translate compatible.

**Developer API**

* Stable hook/filter surface (qt_shortcode_atts, qt_query_args, qt_item_data, qt_card_html, qt_render_html, qt_schema_jsonld, qt_supported_versions, qt_unlocked_versions, qt_after_save_testimonial, and more) so add-ons can extend the plugin without touching core.
