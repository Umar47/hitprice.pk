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

## Hero Slider Mobile Image Field (2026-04-28)
- Added a per-slide optional `Background Image (Mobile)` ACF field (`field_hp_hero_slide_background_mobile`) inside the hero slides repeater; the existing desktop image is now labeled `Background Image (Desktop)`
- `hp_get_hero_slides()` normalizes the mobile image as `background_image_mobile` (or `null` when absent)
- `template-parts/home/hero-slider.php` now renders a `<picture>` element with a `<source media="(max-width: 639px)">` for the mobile image and the desktop `<img>` as fallback — preserves `alt`, `loading`, `decoding`, and `fetchpriority` so the LCP image still hints correctly per viewport
- Mobile breakpoint is `≤ 639px` to align with the existing `@media (min-width: 640px)` breakpoint in `front-page.css`
- No CSS change needed — `<picture>`/`<img>` with `object-fit: cover` works the same as before

## Homepage Slider Fixes & Autoplay Controls (2026-04-28)
- Fixed clipped/abrupt drop shadow under the hero slider: shadow now lives on `.hp-slider--hero .hp-slider__viewport` (the viewport's own outward shadow is not clipped by its own overflow) instead of the inner slide; viewport padding/margin reset to `0` for hero, border-radius moved up to the viewport, and inner `.hp-hero__slide` border-radius/shadow cleared so the rounded clip + smooth shadow render evenly on all four sides
- Bumped `.hp-hero` bottom padding from `16px` to `40px` so the shadow has clearance before the next section and the dots sit cleanly below
- Fixed autoplay-stuck bug: `template-parts/home/hero-slider.php` now pre-filters slides without a valid background image before rendering, so the dots loop and `is_single` check both reflect the actual slide count (previously a phantom dot for an image-less slide caused `(current + 1) % 1 === 0` and the slider never advanced)
- Added per-slider autoplay controls in ACF on the `Hit Price Homepage` template: each slider (Hero Slider, Hot Deals, Latest Phones) now has its own `Enable Autoplay` toggle and a conditional `Autoplay Speed (seconds)` number field (clamped 2–30s; hero default ON @ 7s, product sliders default OFF @ 5s)
- New `hp_normalize_autoplay_speed()` and `hp_get_hero_section_data()` helpers in `homepage-data.php`; `hp_get_product_slider_data()` now also returns `autoplay` + `autoplay_speed`
- Hero and product slider templates emit `data-hp-autoplay="1"` and `data-hp-autoplay-speed="<ms>"` on the slider root only when autoplay is enabled and there is more than one slide/page
- Rewrote the autoplay block in `home-sliders.js` as a generic `setupAutoplay()` that runs for any slider type (hero or products) when the data attributes are present; reads delay per-slider; restarts the timer on manual arrow/dot clicks so a click is not immediately followed by an auto-advance; safely no-ops when there is only one page

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

## Live Search Overlay (2026-05-01)
- Built a full live search system for WooCommerce products — no external search plugin, entirely custom
- **Backend (plugin — `hitprice-helper`):**
  - `inc/search/search-install.php` — creates `wp_hp_search_log` table on activation; auto-upgrades on admin load if DB version mismatches
  - `inc/search/search-query.php` — 6-pass ranked product search (exact title → starts-with → contains → SKU → tag/category → content/excerpt); transient cached 5 min; filters to visible/purchasable products via `wc_get_product()->is_visible()`; `hp_search_format_products()` returns `id`, `title`, `url`, `price` (wp_kses filtered WC HTML), `image`, `sku`; `hp_search_term_suggestions()` pulls past searches from the log matching a prefix
  - `inc/search/search-analytics.php` — `hp_log_search()` records every search (term, normalized_term, results_count, user_id, session_hash, ip_hash); `hp_log_search_click()` records product click against a log row; analytics query helpers: `hp_get_top_searches()`, `hp_get_zero_result_searches()`, `hp_get_search_volume_daily()`, `hp_get_top_clicked_products()`, `hp_get_search_summary()`; `hp_get_trending_terms_for_overlay()` returns real top-7d terms, fills remaining slots from admin-set fallback
  - `inc/search/search-rest.php` — 3 REST endpoints under `hp/v1`: `GET /search/suggest` (live suggestions), `POST /search/click` (click tracking), `GET /search/trending` (overlay empty-state terms); per-IP rate limiting (60 req/60s via transient counter); query length validation (2–100 chars); nonces not required (public endpoints)
  - `inc/admin/search-admin.php` — WP Admin menu page "Search Analytics" with 4 tabs: Overview (KPI cards + 14-day bar chart + top clicked products), Top Searches (30d), Zero-Result (30d — shows what users searched for that returned nothing), Settings (logging toggle + trending fallback terms field)
- **Frontend (child theme):**
  - `template-parts/header/search.php` — header search input + button, both tagged `data-hp-search-trigger`
  - `template-parts/search/overlay.php` — full-screen overlay shell with backdrop, back/clear/cancel buttons, overlay input, trending chips section, results section (terms chips + product list + view-all link), loading/empty/error states; rendered in `wp_footer` at priority 5
  - `assets/css/search.css` — mobile-first overlay CSS; full-screen on mobile, centered card (720px max, 80vh) on desktop ≥768px; CSS transitions on backdrop (opacity 180ms) and panel (transform+opacity 220ms); `.hp-search-is-open` class keeps `display:block` during close animation so transition plays correctly; scroll lock via `body.hp-search-open`; reduced-motion support
  - `assets/js/search.js` — vanilla JS IIFE, no jQuery; reads `window.hpSearchConfig` (REST URL + nonce from `wp_localize_script`); 160ms debounce; AbortController cancels in-flight requests on each new keystroke; client-side Map cache (40-entry LRU) avoids duplicate requests; loading indicator delayed 280ms (fast responses feel instant); `navigator.sendBeacon` for click tracking (fire-and-forget); trending prefetched on first hover/focus of header trigger; arrow-key navigation through product results; Escape to close; chip clicks fill input and search immediately; open/close animation via double-rAF + `hp-search-is-open` class pattern
- **Admin UX:** trending fallback terms managed at WP Admin → Search Analytics → Settings (comma-separated); real top-search terms take priority, fallback fills remaining slots
- **Enqueue:** search CSS + JS loaded sitewide (header trigger appears on every page); `hpSearchConfig.restUrl` = `rest_url('hp/v1/search')`, `hpSearchConfig.nonce` = `wp_create_nonce('wp_rest')`

## Review Image Upload (Phase 7 — 2026-05-05)
- Logged-in users can attach up to 3 images (JPG/PNG/WEBP, max 5 MB each) when submitting a WC product review
- **Backend (`hitprice-helper/inc/product/review-images.php` — new):**
  - `hp_handle_review_image_upload()` hooked on `comment_post`; verifies `hp_review_image_nonce` nonce, confirms user is logged-in and comment type is `review`, validates each file server-side with `finfo` MIME check + size cap, uploads via `wp_handle_upload()` + `wp_insert_attachment()` + `wp_generate_attachment_metadata()`, stores IDs in comment meta `hp_review_images`
  - `hp_get_review_images($comment_id)` returns `array{ url, full, alt }[]` for use in templates
  - Required WP includes (`file.php`, `image.php`) loaded on demand — not globally
- **Frontend hooks (`inc/template-hooks.php`):**
  - `comment_form_after_comment_field` → `hp_inject_review_image_upload()` — renders upload zone + nonce inside the WC review form (logged-in + `is_product()` guard)
  - `woocommerce_review_after_comment_text` → `hp_render_review_images_for_comment($comment)` — shows thumbnail strip below each review
- **JS (`assets/js/single-product.js` — `initReviewImagePreview()`):**
  - Sets `enctype="multipart/form-data"` on `#commentform` via JS (WP's `comment_form()` has no native enctype arg)
  - Drag-and-drop + click-to-upload; filters to allowed MIME + max size client-side
  - Renders inline `<img>` previews with individual remove buttons; rebuilds `DataTransfer` to keep native file input in sync
  - 3-image cap enforced client-side
- **CSS (`assets/css/product-sections.css`):** dashed upload zone with hover/drag state, 80×80 preview thumbnails with × remove button, review card image strip (80×80 thumbnails, open full image on click)

## Why Choose + Bottom Trust Strip (Phase 8 — 2026-05-05)
- **Bug fixed:** `hp_get_badge_keys()`, `hp_get_global_setting()`, `hp_get_global_badge()` were defined only inside `is_admin()` in `global-settings.php` — moved to `product-data.php` (loaded unconditionally) so they work on the front end; removed from `global-settings.php` with a comment noting the move; this also fixes the pre-existing `trust-badges.php` silent render failure
- **Phase 8A — Why Choose section (`template-parts/product/why-choose.php`):** reads `$product->get_description()` (WC long description); returns early if empty; heading "Why Choose [name]?"; `wp_kses_post()` output; hooked at `woocommerce_after_single_product_summary` priority 20
- **Phase 8B — Bottom trust strip (`template-parts/product/trust-strip-bottom.php`):** 4 global badge slots (`safe_payments`, `easy_returns`, `customer_support`, `satisfaction`); skips unconfigured slots; 4-col grid desktop → 2-col mobile; description text hidden on mobile; hooked at priority 25
- **CSS:** Why Choose section scoped under `.hp-why-choose` with prose typography, heading styles for nested h2/h3/h4, max-width 900px; Trust strip scoped under `.hp-trust-strip-bottom` / `.hp-tsb-item`

## CSS Polish + Mobile QA (Phase 9 — 2026-05-05)
- **Sticky offsets:** `--hp-header-h` CSS variable set by `initStickyTabOffset()` in `single-product.js`; measures `#masthead` computed position at runtime and updates on resize; `.hp-tabs__nav-wrap { top: var(--hp-header-h, 0px) }` and `.hp-gallery-outer { top: calc(var(--hp-header-h, 0px) + 24px) }` both updated to consume the variable
- **Star rating breakdown:** Added to `tab-reviews.php` above WC reviews; uses `$product->get_average_rating()`, `get_review_count()`, `get_rating_counts()` (WC-cached); renders average number + WC star HTML + 5 bar rows with PHP-computed `width` inline styles; CSS-only bar animation on load; accessible `role="img"` + `aria-label` on each bar track
- **WC reviews conflicts:** `.hp-tabs__panel #reviews` reset rules expanded — clears WC `padding`, `margin`, `commentlist` list styles, and `#respond` top margin
- **Print styles:** `.hp-sticky-bar { display:none }`, `.hp-tabs__nav-wrap { position:static }`, `.hp-review-upload { display:none }` all suppressed in `@media print`
- **Mobile pass:**
  - `product-hero-critical.css`: added `375px` breakpoint (title 17px, price 20px, trust badges tighter) and `320px` breakpoint (title 16px, price 18px, payment methods description hidden)
  - `product-sections.css`: added `375px` breakpoint (tab buttons 12px 10px padding, sections 28px padding, trust strip single-column, related cards 160px, why-choose tighter)
- **Verified:** critical CSS inline confirmed active in `theme-setup.php` (`wp_head` priority 99); sections CSS deferred via `media='print'` + `onload` confirmed in `hitprice_deferred_product_css_onload()` filter

## Live Search Completion (2026-05-13)

- Removed `is_admin()` guard from `hp_search_maybe_upgrade()` so the analytics DB table is auto-created on the first REST request — previously it only ran on wp-admin pages
- Improved `hp_search_normalize_term()` to convert hyphens and underscores to spaces before normalizing, so `samsung-s24` and `samsung s24` resolve to the same term
- Added a 7th search pass (split-word intersection): multi-word queries like "s24 ultra" now match "Samsung Galaxy S24 Ultra" by requiring each individual word to appear in the title; added `hp_search_split_words()` helper
- Extended `hp_search_format_products()` to return `category` (primary product category name via `get_category_ids()` + `get_term()`) and `in_stock` (bool) per product
- Changed REST suggest response cache header from `no-store` to `private, max-age=30` — browser reuses identical queries for 30 s, server transient cache covers 5 min
- Increased JS debounce from 160ms to 250ms to reduce request volume on fast typing
- Added `focus` event listener on header `<input>` triggers so keyboard users open the overlay without needing to click
- Added explicit `keydown` Enter handler on overlay input to save the term to recent searches before the form submits (form itself handles the redirect natively)
- Added `overlayForm` submit listener as a clean hook for recent-search persistence
- Added recent searches system (localStorage key `hp_recent_searches`, max 5 terms): rendered in idle state above trending chips; saved on successful product results; updated on Enter; cleared by "Clear" button
- Added "Clear recent" delegated handler in overlay click dispatcher
- Added `[data-hp-search-recent]` + `[data-hp-search-recent-list]` section to `overlay.php` with heading row + Clear button
- Extended `renderProducts()` to show: category label pill, out-of-stock badge, highlighted matched term in title (`<mark class="hp-search-highlight">`)
- Added `highlightTerm()` helper that highlights each word of the query inside the escaped title string using a safe regex
- Improved product card image fallback: inline SVG placeholder via CSS background instead of empty `<img>`
- Added all supporting CSS: recent section, heading-row flex, clear-recent button, chip--recent variant with clock icon, category pill, stock badge, highlight mark, image placeholder

### Files Changed
- `wp-content/plugins/hitprice-helper/inc/search/search-install.php`
- `wp-content/plugins/hitprice-helper/inc/search/search-analytics.php`
- `wp-content/plugins/hitprice-helper/inc/search/search-query.php`
- `wp-content/plugins/hitprice-helper/inc/search/search-rest.php`
- `wp-content/themes/astra-child/assets/js/search.js`
- `wp-content/themes/astra-child/assets/css/search.css`
- `wp-content/themes/astra-child/template-parts/search/overlay.php`

## Search Idle State Fix (2026-05-13)

- **Root cause fixed:** Astra's CSS reset removes the browser's native `[hidden]{display:none}` rule, causing loading/empty/error states to all show simultaneously on open. Fixed by adding `.hp-search-overlay [hidden] { display: none !important; }` scoped to the overlay.
- Added pre-rendered "Popular products" grid to overlay idle state — queries latest 6 published visible products via `wc_get_products()` in PHP at footer render time (no AJAX call, instant display).
- Featured products section (`data-hp-search-featured`) hidden whenever user starts typing (showLoading / showResults / showEmpty / showError all call `hide(featuredSec)`), restored by `showIdle()` when input is cleared.
- Added `featuredSec` element reference in search.js and wired into all view-state functions.

### Files Changed
- `wp-content/themes/astra-child/assets/css/search.css`
- `wp-content/themes/astra-child/assets/js/search.js`
- `wp-content/themes/astra-child/template-parts/search/overlay.php`

## Next Tasks
- Visual QA on live product page — test variable products, sticky nav, star breakdown, review image upload, responsive layout at all breakpoints
- Populate Global Settings badges (safe_payments, easy_returns, customer_support, satisfaction, genuine, best_price) with real icons and labels so trust strips render
- Assign WordPress menus for header and footer
- Refine header and footer design
- Build category archive layout
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
- Per-slider autoplay is opt-in via ACF (`*_autoplay_enabled`) with a per-slider speed (`*_autoplay_speed`, 2–30s); JS reads `data-hp-autoplay` / `data-hp-autoplay-speed` from the slider root rather than a hardcoded constant
- Hero shadow lives on the viewport (`.hp-slider--hero .hp-slider__viewport`), not on the inner slide, so it is not clipped by viewport `overflow-x: auto`
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
- `wp-content/plugins/hitprice-helper/hitprice-helper.php` (admin-only require for bulk specs importer + search includes)
- `wp-content/plugins/hitprice-helper/inc/search/search-install.php` (new)
- `wp-content/plugins/hitprice-helper/inc/search/search-analytics.php` (new)
- `wp-content/plugins/hitprice-helper/inc/search/search-query.php` (new)
- `wp-content/plugins/hitprice-helper/inc/search/search-rest.php` (new)
- `wp-content/plugins/hitprice-helper/inc/admin/search-admin.php` (new)
- `wp-content/themes/astra-child/template-parts/search/overlay.php` (new)
- `wp-content/themes/astra-child/template-parts/header/search.php` (updated — data-hp-search-trigger attributes)
- `wp-content/themes/astra-child/assets/css/search.css` (new)
- `wp-content/themes/astra-child/assets/js/search.js` (new)
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
