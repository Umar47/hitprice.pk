# Hit Price AI Memory

## Project Goal
Build a fast, clean, modern WooCommerce store focused on sales conversion.

## Stack
- WordPress
- WooCommerce
- Astra
- Astra child theme
- Elementor

## Design Direction
- Inspired by Verizon-style premium simplicity
- Inspired by Walmart-style practical ecommerce usability
- Original design only, no cloning

## Colors
- Header / top menu: #0053e2
- Main background: #f6f6f6

## Typography
- Body + UI: Inter
- Headings only: Poppins

## Product Categories
- Mobile phones
- Mobile accessories
- Electronics
- AC
- TV
- Washing machines
- Kitchen appliances
- Home appliances
- Computers
- Related accessories and add-ons

## Priorities
- Speed
- Clean UI
- Mobile-first UX
- Sales conversion
- Easy product discovery

## Completed Work
- Read `hit_price_ai.md` and `hitprice_rules.md`
- Created Astra child theme at `wp-content/themes/astra-child`
- Structured child theme using separate WordPress files instead of placing header markup directly in `functions.php`
- Added:
  - `inc/theme-setup.php`
  - `inc/template-hooks.php`
  - `inc/template-functions.php`
  - `template-parts/header/*`
  - `template-parts/footer/*`
- Added a lightweight responsive header scaffold with separate sections for:
  - header top area
  - branding
  - product search box
  - header actions
  - main navigation
- Removed header menu fallback logic because the navigation menu is assigned from WordPress admin
- Switched branding to Astra native site identity/logo rendering instead of custom branding markup
- Replaced custom header action buttons with Astra/WooCommerce-style dynamic cart trigger
- Added footer scaffolding with separate sections for:
  - footer main area
  - copyright area
- Added child theme CSS and JS for the header/footer scaffold
- Updated rules to enforce approval before every file write and to clarify theme vs plugin responsibility
- Added local font direction: Inter for body/UI and Poppins for headings only
- Fixed broken `.hitprice-menu` child menu presentation in the desktop header with custom submenu CSS
- Shifted desktop submenu styling toward a T-Mobile-inspired pattern using a white dropdown panel, stronger spacing, darker submenu text, and clearer hover feedback
- Built version 1 of a custom homepage in the Astra child theme using a dedicated page template file
- Added homepage sections as separate template parts for hero, categories, promo grid, featured products, and trust messaging
- Added homepage-only CSS to keep the design isolated and lightweight
- Kept homepage content mostly static for now, with WooCommerce products/categories used only where native output improves conversion and maintainability
- Deferred ACF integration until after the static homepage structure and visual direction are approved
- Converted the homepage to a selectable WordPress page template so it can be assigned from the page editor side panel instead of relying on automatic front-page routing
- Corrected the homepage template filename to `template-homepage.php` so WordPress no longer hijacks the real site front page through reserved `front-page.php` behavior
- Expanded the homepage mockup with richer placeholder copy, more descriptive cards, and stronger section density so the design can be judged more realistically
- Downloaded a small set of temporary homepage images into the child theme and wired them into the hero and promo sections as mockup assets
- Adjusted the homepage structure toward Verizon-style section patterns by introducing a stronger tile grid, image-led preview tiles, and tighter category tilettes while keeping the implementation original and lightweight
- Reworked those Verizon-inspired homepage sections again with a closer structural match: one dominant offer tile, two smaller supporting promo tiles, simpler preview cards, and more compact category quick links
- Built version 1 of a custom WooCommerce shop/category archive using theme template overrides rather than static pages
- Added a custom archive layout inspired by Verizon-style category pages and Samsung-style product cards while preserving the site-wide custom header and footer
- Added archive-only CSS and a custom WooCommerce loop card template for a more premium product browsing experience
- Added a Verizon-style left filter rail pattern to the shop archive using a custom child-theme widget area and a lightweight custom filter widget manageable from `wp-admin/widgets.php`
- Replaced the simple filter widget approach with a dedicated faceted archive filter rail that is closer to the Verizon-style sidebar pattern, including grouped sections, checkbox controls, sorting persistence, and mobile drawer behavior
- Refined the archive filter rail again toward the Verizon screenshot pattern by flattening the sidebar styling, simplifying group presentation, adding a top promo/toggle row, and reducing decorative card treatment
- Tightened the shop archive composition by reducing dead space, improving sidebar/grid proportions, moving the mobile filter trigger into the archive column, and making the product card layout more compact
- Updated the shop filters so all controls use consistent checkbox visuals and product results can refresh automatically via AJAX instead of relying on an apply button
- Removed inherited Astra yellow button styling from the filter group toggle controls so the archive sidebar matches the intended neutral filter rail design
- Increased filter checkbox and label sizing in the shop sidebar to improve readability and make the controls feel closer to the reference layout
- Increased filter section heading size to `20px` and expanded spacing between filter groups to `40px` for a more open sidebar layout
- Added WooCommerce fragment support for the custom header cart badge so the cart count updates correctly after add-to-cart actions and remains visible in the child-theme header
- Switched the visible cart bubble to a dedicated custom badge anchored on the cart icon so the count is no longer dependent on Astra count positioning quirks
- Confirmed the live frontend cart markup is Astra-rendered, then added the visible cart count bubble directly against Astra's `data-cart-total` cart icon output so the badge can appear on the real header markup
- Fixed a WooCommerce archive fatal error by removing an incompatible `wc_get_catalog_ordering_args()` call from the custom sorting UI and replacing it with a compatibility-safe ordering setup
- Created a new `hitprice-helper` plugin scaffold and moved homepage ACF field registration plus homepage product-block helper logic into the plugin to follow the theme-vs-plugin rules
- Added an ACF flexible-content based homepage builder for the `Hit Price Homepage` page template so sections can be reordered and managed from wp-admin
- Reworked the homepage template to render dynamic flexible sections when configured, while keeping the existing static homepage partials as a safe fallback until ACF content is entered
- Added reusable flexible homepage rendering partials in the child theme for hero, featured categories, product blocks, promo banners, USP items, preview tiles, campaign tiles, and trust content
- Extended homepage styling to support the new reusable ACF-driven banner and USP layouts without loading extra assets globally
- Built a Verizon-style single product page using hooks only (no template overrides)
- Added product data helpers in the plugin: `hp_get_compare_products()` with category → cross-sell → latest fallback chain, transient cached 12hr
- Added ACF field groups for product feature cards (repeater, max 3) and detail specs (flexible content, max 10 layouts with text_block, key_value_table, media_block)
- Registered single product hooks via `hitprice_register_product_hooks()` on `wp` action, guarded by `is_product()`
- Restructured product hero as a 2-column CSS grid with sticky gallery and summary column
- Added custom summary sections: add-ons UI, trade-in block, payment options (with 12-month installment calculation)
- Added after-summary sections: compare grid (4-col), feature cards (3-col from ACF), accordions (replaces tab UI using `<details>/<summary>`), detail specs (from ACF flexible content)
- Replaced WooCommerce tabs with accordion UI by capturing tab data via filter and suppressing default rendering
- Added variation swatch overlay: reads native selects, renders visual swatches/color circles, sets native select value on click so WooCommerce handles everything; native dropdowns remain visible if JS fails
- Added sticky bottom bar: shows on scroll past hero, syncs price/stock via `found_variation`/`reset_data` jQuery events, mobile-optimized with full-width layout
- Critical CSS (<5KB) inlined in `<head>` via `wp_head` action; sections CSS deferred via `media="print" onload` pattern
- Removed `woocommerce_template_single_meta` and `woocommerce_template_single_sharing` from summary
- Removed default related products and upsells from after-summary area

## Homepage v2 Rebuild (2026-04-16)
- Dropped ACF flexible-content builder entirely; replaced with fixed 6-section layout matching the new Figma mockup
- Replaced `hitprice-helper/inc/acf/homepage-fields.php` with a single tabbed ACF group (Hero Slider, Trust Strip, Hot Deals, Latest Phones, Shop By Category, Why Buy From Us) under the key `group_hp_homepage`
- Rewrote `hitprice-helper/inc/homepage/homepage-data.php` with dedicated sanitized accessors: `hp_get_hero_slides`, `hp_get_trust_strip_items`, `hp_get_hot_deals_data`, `hp_get_latest_phones_data`, `hp_get_shop_categories_data`, `hp_get_why_buy_data`, plus shared `hp_get_product_slider_data( $prefix )`
- Rewrote `template-homepage.php` to render 6 sections in fixed order; removed legacy static fallback chain
- Created new template parts: `hero-slider.php`, `product-slider.php` (reusable, args-driven for both Hot Deals & Latest Phones), `shop-categories.php`, `why-buy.php`; rewrote `trust-strip.php` as image-only horizontal strip
- Deleted obsolete partials: `hero.php`, `categories.php`, `promo-grid.php`, `preview-tiles.php`, `featured-products.php`, and entire `template-parts/home/flexible/` folder (8 files)
- Rewrote `assets/css/front-page.css` from scratch — mobile-first, ~500 lines, new token system under `.hp-home` scope, shared `.hp-slider` base for hero + product sliders
- Added `assets/js/home-sliders.js` — vanilla JS (no dependencies), scroll-snap based sliders with arrows + dots, keyboard support, debounced resize handling, per-slider state tracking
- Hero slider: one slide per view, dot count = slide count, full-bleed background image with left-aligned content overlay and primary/secondary CTAs
- Product slider: 1/2/4 cards per view at 320/640/900+ breakpoints, dots computed from `scrollWidth / clientWidth`, arrows disable at edges
- Updated `inc/theme-setup.php` to enqueue `home-sliders.js` only on the homepage template (same guard as the stylesheet)

## Bulk Specs Importer (2026-04-25)
- Added an "Add Bulk" admin tool to speed up product spec data entry without changing the existing ACF structure or frontend rendering
- Button is injected next to ACF "Add Section" on the `field_hp_detail_specs` flexible content field on product edit screens only
- Opens a modal with a textarea for pasting competitor spec HTML; parsing runs entirely client-side (no server endpoint)
- Each `.p-spec-table` block in the pasted HTML becomes a new `key_value_table` layout: `<h6>` populates the section heading, `<dt>/<dd>` pairs populate the inner rows repeater (label/value)
- Uses ACF's official JS API (`acf.getField`, `field.add()`) — no ACF core files modified, no template overrides
- Reuses the auto-created blank first row in each new section so there are no orphan empty rows
- Dedupes by lowercased label within each pasted section, trims values, skips empty `<dt>`, handles missing `<dd>` gracefully
- Shows imported section/row counts in the modal status line; ESC, backdrop click, X, and Cancel all close the modal
- Assets enqueue only on `post.php`/`post-new.php` for the `product` post type, with capability gate (`edit_products`/`edit_posts`); cache-busted via `filemtime`
- Used `__()` (not `esc_html__()`) for localized strings since jQuery `.text()` already escapes — fixes literal `&amp;` rendering for "Parse & Insert"
- Removed the `'max' => 10` cap from `field_hp_detail_specs` flexible content so admins can add unlimited spec sections per product

## Next Tasks
- Activate the Astra child theme if not already active
- Create/assign the "Hit Price Homepage" page via Pages → Add New → Template → Hit Price Homepage
- Populate ACF tabs (Hero Slider, Trust Strip, Hot Deals, Latest Phones, Shop By Category, Why Buy) with real content
- Refine header design and spacing to match the premium ecommerce direction
- Build the detailed footer design
- Assign/create WordPress menus for header and footer
- Create category layout
- Visual QA on single product page — test variable products, swatches, sticky bar, accordions, responsive layout
- Review performance

## Important Decisions
- Homepage v2 is a fixed 6-section layout (hero slider, trust strip, hot deals, latest phones, shop by category, why buy from us) — no flexible-content builder
- Slider implementation is a single reusable vanilla JS module (`home-sliders.js`) using CSS scroll-snap + native overflow scrolling; no external libraries
- Hero slider gets arrows + dots; product sliders get arrows + dots (dots auto-computed from scroll pages)
- Product slider is one `product-slider.php` partial consumed by both Hot Deals and Latest Phones — passed args differ, template reuses
- Trust strip is image-only (text inside image) per brand requirement; optional link URL per item
- Shop by Category uses a custom ACF repeater (max 4 cards) with background image, not a taxonomy selector
- Homepage CSS is scoped under `.hp-home` with local CSS variables to avoid collisions with Astra or header/footer styles
- Theme-related presentation work goes in the child theme
- Heavy or reusable logic belongs in `hitprice-helper`, not the theme
- Header and footer must be split into separate template files following WordPress standards
- Prefer native WordPress and WooCommerce features before custom solutions
- Ask for approval before every future file create/update/delete action
- Use local theme fonts: Inter for body/UI and Poppins for headings only
- Use Astra built-in branding/logo functions where possible instead of rebuilding logo output
- Use Astra/WooCommerce native cart patterns and cart fragments for header cart behavior
- Desktop submenu design should stay presentation-only in child theme CSS unless a native WordPress limitation requires a markup adjustment
- Homepage CSS should load only when the `Hit Price Homepage` page template is selected via `template-homepage.php`
- Single product page uses hooks only — no `single-product.php` or `content-single-product.php` template overrides
- Variation swatch JS must fall back gracefully: native dropdowns hidden only via JS-added `.hp-has-swatches` class
- Product critical CSS inlined in head, sections CSS deferred via print/onload pattern
- ACF feature cards limited to max 3, detail specs flexible content limited to max 10 layouts

## Files Changed
- `wp-content/themes/astra-child/style.css`
- `wp-content/themes/astra-child/functions.php`
- `wp-content/themes/astra-child/inc/theme-setup.php`
- `wp-content/themes/astra-child/inc/template-hooks.php`
- `wp-content/themes/astra-child/inc/template-functions.php`
- `wp-content/themes/astra-child/woocommerce/archive-product.php`
- `wp-content/themes/astra-child/woocommerce/content-product.php`
- `wp-content/themes/astra-child/template-parts/header/header.php`
- `wp-content/themes/astra-child/template-parts/header/top-bar.php`
- `wp-content/themes/astra-child/template-parts/header/branding.php`
- `wp-content/themes/astra-child/template-parts/header/search.php`
- `wp-content/themes/astra-child/template-parts/header/actions.php`
- `wp-content/themes/astra-child/template-parts/header/navigation.php`
- `wp-content/themes/astra-child/template-parts/footer/footer.php`
- `wp-content/themes/astra-child/template-parts/footer/footer-widgets.php`
- `wp-content/themes/astra-child/template-parts/footer/copyright.php`
- `wp-content/themes/astra-child/assets/css/header-footer.css`
- `wp-content/themes/astra-child/assets/css/front-page.css`
- `wp-content/themes/astra-child/assets/css/shop-archive.css`
- `wp-content/themes/astra-child/assets/js/shop-archive.js`
- `wp-content/themes/astra-child/assets/images/home/hero-phone.jpg`
- `wp-content/themes/astra-child/assets/images/home/promo-tv.jpg`
- `wp-content/themes/astra-child/assets/images/home/promo-appliance.jpg`
- `wp-content/themes/astra-child/assets/images/home/category-phone.jpg`
- `wp-content/themes/astra-child/assets/js/header.js`
- `wp-content/themes/astra-child/template-homepage.php`
- `wp-content/themes/astra-child/template-parts/home/hero-slider.php` (new v2)
- `wp-content/themes/astra-child/template-parts/home/product-slider.php` (new v2, reusable)
- `wp-content/themes/astra-child/template-parts/home/shop-categories.php` (new v2)
- `wp-content/themes/astra-child/template-parts/home/why-buy.php` (new v2)
- `wp-content/themes/astra-child/template-parts/home/trust-strip.php` (rewritten v2)
- `wp-content/themes/astra-child/assets/js/home-sliders.js` (new v2)
- `wp-content/plugins/hitprice-helper/hitprice-helper.php`
- `wp-content/plugins/hitprice-helper/inc/acf/homepage-fields.php`
- `wp-content/plugins/hitprice-helper/inc/homepage/homepage-data.php`
- `wp-content/plugins/hitprice-helper/inc/product/product-data.php`
- `wp-content/plugins/hitprice-helper/inc/acf/product-fields.php`
- `wp-content/plugins/hitprice-helper/inc/admin/bulk-specs-importer.php` (new — bulk specs importer enqueue)
- `wp-content/plugins/hitprice-helper/assets/js/admin-bulk-specs.js` (new — modal + parser + ACF JS API insertion)
- `wp-content/plugins/hitprice-helper/assets/css/admin-bulk-specs.css` (new — modal styles)
- `wp-content/plugins/hitprice-helper/hitprice-helper.php` (admin-only require for bulk specs importer)
- `wp-content/themes/astra-child/template-parts/product/compare.php`
- `wp-content/themes/astra-child/template-parts/product/features.php`
- `wp-content/themes/astra-child/template-parts/product/accordions.php`
- `wp-content/themes/astra-child/template-parts/product/detail-specs.php`
- `wp-content/themes/astra-child/template-parts/product/sticky-bar.php`
- `wp-content/themes/astra-child/template-parts/product/addons.php`
- `wp-content/themes/astra-child/template-parts/product/tradein.php`
- `wp-content/themes/astra-child/template-parts/product/payment-options.php`
- `wp-content/themes/astra-child/assets/css/product-hero-critical.css`
- `wp-content/themes/astra-child/assets/css/product-sections.css`
- `wp-content/themes/astra-child/assets/js/single-product.js`
- `hitprice_rules.md`
- `hit_price_ai.md`

## Resume Notes
- Current header/footer is scaffold-level and not final polished design
- Header menu now depends on the assigned WordPress menu in admin
- Desktop submenu dropdowns are now custom-styled in `assets/css/header-footer.css`; mobile submenu items stay inline inside the mobile panel
- Homepage version 1 is now custom-coded in the child theme rather than relying on Elementor layout output
- Homepage layout is now assigned manually through the WordPress template selector using `Hit Price Homepage`
- The reserved `front-page.php` file is intentionally removed so the root homepage can use normal WordPress front page behavior
- Homepage content management via ACF is intentionally deferred until the static layout is approved
- Homepage ACF management is now scaffolded through a flexible-content field group in `hitprice-helper`, but the existing static sections still act as fallback until the plugin is active and homepage rows are configured
- Temporary downloaded images are mockup-only and should be replaced with final optimized brand-approved assets later
- Homepage structure now includes Verizon-inspired pattern translations for campaign tiles, preview cards, and category quick links without copying Verizon markup directly
- Shop and product category archives are now being customized through WooCommerce template overrides in the child theme, not static page templates
- Shop filters can now be managed through the `Hit Price Shop Filters` widget area, with a theme-provided fallback filter widget when no sidebar widgets are assigned
- Search is product-focused using native WordPress search with `post_type=product`
- Local fonts are available under `assets/fonts/Inter` and `assets/fonts/Poppins`
- Logo/site identity should be managed from `Appearance > Customize > Site Identity`
- Header cart should show live count and use Astra cart drawer behavior where available
- Keep future presentation work in separate child-theme template parts, not inline in `functions.php`
- Single product page is Phase 1 (design replication) — add-ons and trade-in blocks are static UI placeholders
- Product tab data is captured via `hp_capture_product_tabs_for_accordions()` filter at priority 98, stored in `$hp_product_tabs` global, then default tabs suppressed by returning empty array
- Sticky bar uses jQuery `found_variation`/`reset_data` events from WooCommerce variation form for price syncing
- Swatch overlay creates visual buttons from native select options and triggers native `change` event so WooCommerce handles all variation logic
- Compare products transient key pattern: `hp_compare_{product_id}_{limit}`, cleared on `save_post_product`
