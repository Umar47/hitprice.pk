# Single Product Page Redesign — Session Handoff Document
**Last updated:** 2026-05-11  
**Context limit hit after:** Phase 10 complete  
**Next task:** Phase 11 — Reviews image upload backend (Phase 7 renamed)  

---

## QUICK START FOR NEXT SESSION

1. Read this file top to bottom (5 min)
2. Confirm `template-hooks.php` is intact
3. Resume at **Phase 11** (reviews image upload)
4. All decisions are already made — no need to re-ask

---

## COMPLETED PHASES

### ✅ Phase 1 — Global Settings Admin Page

**Files created/modified:**

| File | Action |
|---|---|
| `wp-content/plugins/hitprice-helper/hitprice-helper.php` | Added `require_once` for `global-settings.php` inside `is_admin()` block |
| `wp-content/plugins/hitprice-helper/inc/admin/global-settings.php` | Created — full admin page |
| `wp-content/plugins/hitprice-helper/assets/css/admin-global-settings.css` | Created |
| `wp-content/plugins/hitprice-helper/assets/js/admin-global-settings.js` | Created — WP media frame upload |

**Storage:** Single `wp_options` row `hp_global_settings` (serialized array).

**Key functions (all in `global-settings.php`):**
```php
hp_get_global_setting( $key, $default = '' )   // dot-notation, static cached
hp_get_global_badge( $badge_key )              // returns ['image_url','label','description']
hp_get_badge_keys()                            // returns all 10 badge slot keys
```

**Badge keys:** `pta_approved`, `genuine`, `best_price`, `weekly_deals`, `fast_delivery`, `secure_packaging`, `easy_returns`, `safe_payments`, `customer_support`, `satisfaction`

**Save nonce:** `hp_global_settings_save`, capability: `manage_options`

---

### ✅ Phase 2 — New ACF Fields

**File modified:** `wp-content/plugins/hitprice-helper/inc/acf/product-fields.php`

New field group `group_hp_product_page_settings` (menu_order: 5) with:

| Field key | Field name | Type |
|---|---|---|
| `field_hp_pta_approved` | `hp_pta_approved` | true_false (UI toggle) |
| `field_hp_key_highlights_content` | `hp_key_highlights_content` | wysiwyg (basic toolbar) |
| `field_hp_key_highlights_image` | `hp_key_highlights_image` | image (return_format: array) |
| `field_hp_overview_specs` | `hp_overview_specs` | repeater (max 8, table layout) |
| → `field_hp_os_icon` | `icon` | image sub-field |
| → `field_hp_os_title` | `title` | text sub-field |
| → `field_hp_os_value` | `value` | text sub-field |

**Helper functions added to `wp-content/plugins/hitprice-helper/inc/product/product-data.php`:**
```php
hp_is_pta_approved( $product_id )             // bool
hp_get_key_highlights_content( $product_id )  // string (wp_kses_post)
hp_get_key_highlights_image( $product_id )    // array|null
hp_get_overview_specs( $product_id )          // array (max 8 rows)
hp_get_sale_banner_data( $product )           // array{label,valid_until}|null
```

---

### ✅ Phase 3 — Hero + Summary Restructure

**CSS critical file rewritten:** `wp-content/themes/astra-child/assets/css/product-hero-critical.css`

**New template parts created:**

| File | Purpose |
|---|---|
| `template-parts/product/gallery-badges.php` | PTA badge (top-left, conditional) + Best Price badge (top-right, always) |
| `template-parts/product/gallery-trust-strip.php` | 4-icon strip below gallery: genuine, secure_packaging, fast_delivery, easy_returns |
| `template-parts/product/stock-viewers.php` | Accepts `modifier` arg ('mobile'/'desktop') for CSS show/hide |
| `template-parts/product/summary-meta.php` | Brand link \| star rating \| review count link \| SKU |
| `template-parts/product/sale-banner.php` | SVG flame icon + label + "Price valid till [Day]!" |
| `template-parts/product/trust-badges.php` | 4 slots: PTA (conditional), genuine, best_price, weekly_deals |
| `template-parts/product/payment-methods.php` | 3-col: Cash on Delivery, Open Parcel, 7-Day Check Warranty |
| `template-parts/product/delivery-estimate.php` | In-stock only; shows today+2 to today+4 date range |

**Hook priorities (in `woocommerce_single_product_summary`):**

| Priority | Callback | Notes |
|---|---|---|
| 3 | `hp_render_stock_viewers_desktop` | Hidden on mobile via CSS |
| 5 | `woocommerce_template_single_title` | WC default kept |
| 8 | `hp_render_summary_meta` | Brand \| rating \| SKU |
| 10 | `woocommerce_template_single_price` | WC default kept |
| 22 | `hp_render_tax_note` | "Inclusive of all taxes" |
| 25 | `hp_render_sale_banner` | Conditional on `$product->is_on_sale()` |
| 28 | `hp_render_summary_trust_badges` | 4 slots |
| 30 | `woocommerce_template_single_add_to_cart` | WC default kept |
| After ATC | `hp_render_buy_now_button` | Via `woocommerce_after_add_to_cart_button` hook |
| 55 | `hp_render_payment_methods` | 3-col grid |
| 60 | `hp_render_delivery_estimate` | In-stock only |

**Removed from summary:** rating (10), excerpt (20), meta (40), sharing (50)

**Gallery wrapper:**
```php
// Before WC gallery (priority 15): opens .hp-gallery-outer + renders gallery-badges
// After WC gallery (priority 25): closes .hp-gallery-outer + renders gallery-trust-strip
// Mobile viewers (priority 14): above gallery, hidden on desktop via CSS
```

**JS additions in `single-product.js`:**
- `initViewers()`: seeded LCG random (seed = productId×1000 + year×400 + month×31 + day)
- `initQtyButtons()`: wraps native `.qty` input in `.hp-qty-wrap`, injects − / + buttons
- `initBuyNow()` + `doAddToCart()`: fetch GET to WC cart endpoint → redirect to checkout

---

### ✅ Phase 4 — Why Buy Strip + Key Highlights

**Template parts created:**

| File | Purpose |
|---|---|
| `template-parts/product/why-buy.php` | "Why buy from HitPrice.pk?" 4-col → mobile vertical list |
| `template-parts/product/key-highlights.php` | 2-col: WYSIWYG left + infographic right; mobile: image hidden, overview specs surface shown |

**Hook priorities (in `woocommerce_after_single_product_summary`):**

| Priority | Callback |
|---|---|
| 1 | `hp_render_product_layout_close` |
| 5 | `hp_render_why_buy_section` |
| 10 | `hp_render_key_highlights` |

**Mobile overview specs surface:** Rendered inside `key-highlights.php` as `.hp-overview-specs-surface`. CSS: `display:none` on desktop, `display:flex` on ≤900px. No double DB call.

---

### ✅ Phase 5 — Related Products Slider

**File rewritten:** `template-parts/product/compare.php`
- Now renders `.hp-related` section, heading "You may also like"
- Calls `hp_get_compare_products($id, 8)` (limit raised from 4 to 8)
- Horizontal scroll track (`[data-hp-slider="related"]`) with prev/next arrow buttons
- Cards: 220px wide (180px mobile), `scroll-snap-align: start`, hidden scrollbar

**Hook:** `hp_render_product_compare` at priority 15 (function already existed in `template-functions.php`)

**JS:** `initRelatedSlider()` — scrolls by one card width, dims arrows at scroll boundaries

---

### ✅ Phase 6 — Tab System

**Template parts created:**

| File | Purpose |
|---|---|
| `template-parts/product/tabs.php` | Tab nav + 5 panels, accessible (`role="tablist"`, `aria-selected`, `aria-controls`, `hidden` attr) |
| `template-parts/product/tab-overview.php` | WC short description (left) + overview specs repeater (right) |
| `template-parts/product/tab-specifications.php` | Renders `hp_detail_specs` flexible content (key_value_table / text_block / media_block) |
| `template-parts/product/tab-reviews.php` | Calls `woocommerce_template_single_reviews()` |
| `template-parts/product/tab-qa.php` | Stub — icon + "Q&A coming soon" + contact link |
| `template-parts/product/tab-shipping.php` | Reads `hp_get_global_setting('shipping_policy')` |

**Hooks added to `template-hooks.php`:**
- `remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10)` — suppresses WC tab wrapper
- `add_action('woocommerce_after_single_product_summary', 'hp_render_product_tabs', 3)` — custom tabs at priority 3
- `add_filter('woocommerce_product_tabs', 'hp_suppress_wc_product_tabs', 99)` — returns empty array

**Tab IDs (for URL hash):** `overview`, `specifications`, `reviews`, `qa`, `shipping`
**Hash format:** `#hp-tab-{id}` (e.g. `#hp-tab-reviews`)

**JS:** `initTabs()` — click switching, `aria-selected` + `hidden` attribute management, URL hash on load with smooth scroll, active tab scrolled into view on mobile

**Section order in `woocommerce_after_single_product_summary`:**

| Priority | Callback |
|---|---|
| 1 | `hp_render_product_layout_close` |
| 3 | `hp_render_product_tabs` ← NEW |
| 5 | `hp_render_why_buy_section` |
| 10 | `hp_render_key_highlights` |
| 15 | `hp_render_product_compare` |

---

---

### ✅ Phase 10 — UI Redesign & Visual Refinement (This Session)

**Overview specs — ACF icon field:**
- `field_hp_os_icon` changed from `image` to `text` (Font Awesome class string)
- Placeholder: `fa-solid fa-mobile`
- Font Awesome 6.5.2 enqueued via CDN on `is_product()` pages only (`theme-setup.php`)

**Overview tab (`tab-overview.php`):**
- Added `<h2 class="hp-overview__heading">About this item</h2>` before description
- Icon rendered as `<i class="fa-class">` instead of `<img>`
- Spec order changed: title first, value second

**Key Highlights (`key-highlights.php` + CSS):**
- `ul li` vertical stack with green circle-SVG checkmarks (same pattern as all other sections)
- Right image wrap: `display:flex; align-items:center; padding:10px 20px`
- Middle separator via `border-left` on last grid child
- `align-items: center` on grid (was `stretch`)

**Why Choose section (`why-choose.php` + CSS):**
- Compact container: white bg, `border: 1px solid #e5e7eb`, `border-radius: 14px`, `padding: 20px 24px`
- Heading: `14px bold`; description: `13px #6b7280`
- `ul` horizontal flex-wrap row with green circle-SVG checkmarks

**Bottom Trust Strip — full rebuild:**
- New helper: `hp_get_product_trust_strip_items()` in `product-data.php` (named to avoid conflict with `hp_get_trust_strip_items()` in homepage-data.php)
- New admin settings group "Single Product Page – Bottom Trust Strip" in `global-settings.php` — 4 fixed rows, each with Icon Class / Title / Subtitle text inputs; saves to `hp_global_settings['trust_strip']`
- Template `trust-strip-bottom.php` rewritten: uses Font Awesome `<i>` icons, reads from new helper
- CSS redesign: white bg, border, border-radius, 4-col grid with `border-right` dividers, icon `30px #1e3a6e`, 2-col on tablet, subtitle hidden on mobile

**Related slider (`compare.php`):**
- "View details" CTA changed to "Buy Now"
- `border-radius` on `.hp-related__cta` updated to `14px`

**Global layout alignment (all sections matched to consistent max-widths):**

| Section | max-width |
|---|---|
| `.single-product .woocommerce-breadcrumb` | 1250px |
| `.hp-product-layout` | 1278px |
| `.hp-why-buy` | 1278px |
| `.hp-key-highlights` | 1250px |
| `.hp-tabs` | 1250px |
| `.hp-why-choose` | 1248px |
| `.hp-related` | 1248px |
| `.hp-trust-strip-bottom` | 1248px |

**Overview tab 40/60 split + separator:**
- `grid-template-columns: 40% 60%`; left column `border-right: 1px solid #e5e7eb`
- Right specs: `border/background` removed (rendered inside shared container)
- On mobile (≤900px): stacks, right border → bottom border

**Spec card typography swap:**
- `.hp-overview__spec-title`: `13px bold #111827` (prominent — rendered first)
- `.hp-overview__spec-value`: `12px 500 #4b5563 capitalize` (secondary — rendered second)

**Consistent green checkmark pattern used in ALL sections:**
```css
li::before {
  content: '';
  flex-shrink: 0;
  width: 18px; height: 18px;
  background-color: #22c55e;
  border-radius: 50%;
  background-image: url("data:image/svg+xml, ...SVG checkmark...");
}
```

---

## PENDING PHASES

### ✅ Phase 11 — Reviews Image Upload Backend

**Goal:** Logged-in users can attach up to 3 images when submitting a WC product review.

**Backend (in plugin — `hitprice-helper`):**
- New file: `inc/product/review-images.php`
- Require it in `hitprice-helper.php` (unconditionally — not inside `is_admin()`)
- Hook: `comment_post` — fires after comment saved
  - Check `comment_type === 'review'` and user is logged in
  - Handle `$_FILES['hp_review_images']` (multiple)
  - Max 3 images; allowed MIME: `image/jpeg`, `image/png`, `image/webp`; max 5MB each
  - Use `wp_handle_upload()` + `wp_insert_attachment()` to add to media library
  - Store attachment IDs in comment meta: `update_comment_meta($comment_id, 'hp_review_images', $ids)`
  - Nonce: `hp_review_image_nonce` verified before processing
- Helper function: `hp_get_review_images($comment_id)` → array of attachment URLs

**Frontend (in theme):**
- Hook into `comment_form_top` or filter `woocommerce_product_review_comment_form_args`
- Add `<input type="file" name="hp_review_images[]" accept="image/jpeg,image/png,image/webp" multiple>` — only shown to logged-in users
- Add nonce field: `wp_nonce_field('hp_review_image_nonce', 'hp_review_nonce')`
- JS preview: show thumbnail previews before submit (in `single-product.js`)
- CSS: image upload zone styling + preview thumbnails
- Review card rendering: call `hp_get_review_images()` inside `tab-reviews.php` for each comment and render `<img>` thumbnails

**Important:** The WC comment form is output via `woocommerce_template_single_reviews()` which is already called in `tab-reviews.php`. The file input must be injected into that form.

---

### ✅ Phase 8 — "Why Choose [Product]?" + Bottom Trust Strip

**8A — "Why Choose [Product]?" section:**
- Hook: `woocommerce_after_single_product_summary` priority 20 (between tabs at 15 and compare at... wait — compare is at 15, so use priority 18 or reorder)
- Actually the current order is: 3 (tabs), 5 (why buy), 10 (key highlights), 15 (compare)
- "Why Choose" should go AFTER compare. Use priority 20.
- Data: WC long description (`$product->get_description()`)
- Template: `template-parts/product/why-choose.php`
- Return early if long description is empty
- Layout: heading "Why Choose [product name]?" + `wp_kses_post()` output of long description

**8B — Bottom Trust Strip:**
- Hook: `woocommerce_after_single_product_summary` priority 25
- Template: `template-parts/product/trust-strip-bottom.php`
- Data: 4 global badges: `safe_payments`, `easy_returns`, `customer_support`, `satisfaction`
- Layout: 4-col grid desktop, 2-col mobile
- Mobile: description text hidden (`display:none`)

---

### ✅ Phase 9 — CSS Polish + Mobile QA

**Tasks:**
1. Full mobile pass at 320px, 375px, 390px, 414px, 768px breakpoints
2. Sticky tab bar top offset — must account for any fixed header height (check Astra header height in child theme, likely 60-80px; set `top: var(--hp-header-h, 60px)` on `.hp-tabs__nav-wrap`)
3. Sticky gallery column — check `position: sticky` works on long summary columns (may need `align-self: start` on gallery outer)
4. Review star rating breakdown bar chart (CSS only — calculate percentages from comment meta counts)
5. "Buy Now" button — ensure full-width on mobile (`width: 100%`)
6. Tab panel for Reviews: remove WC's default `#reviews` padding/margin conflicts
7. Print stylesheet: hide sticky bar, nav wrap
8. Verify `product-hero-critical.css` is still inlined in `<head>` (check `functions.php` enqueue logic)
9. Performance: confirm `product-sections.css` is loaded deferred (print/onload trick)

---

## KEY FILE LOCATIONS

### Theme (Astra Child)
```
wp-content/themes/astra-child/
├── assets/
│   ├── css/
│   │   ├── product-hero-critical.css   ← inlined in <head>
│   │   └── product-sections.css        ← deferred load
│   └── js/
│       └── single-product.js
├── inc/
│   ├── template-hooks.php              ← ALL hook registrations live here
│   └── template-functions.php         ← render functions for older sections
└── template-parts/product/
    ├── tabs.php
    ├── tab-overview.php
    ├── tab-specifications.php
    ├── tab-reviews.php
    ├── tab-qa.php
    ├── tab-shipping.php
    ├── compare.php                     ← now the related slider (renamed in code only)
    ├── why-buy.php
    ├── key-highlights.php
    ├── gallery-badges.php
    ├── gallery-trust-strip.php
    ├── stock-viewers.php
    ├── summary-meta.php
    ├── sale-banner.php
    ├── trust-badges.php
    ├── payment-methods.php
    └── delivery-estimate.php
```

### Plugin (hitprice-helper)
```
wp-content/plugins/hitprice-helper/
├── hitprice-helper.php                 ← main file, require_once list
├── inc/
│   ├── admin/
│   │   ├── global-settings.php        ← admin page + hp_get_global_setting() etc.
│   │   ├── bulk-specs-importer.php
│   │   └── search-admin.php
│   ├── acf/
│   │   └── product-fields.php         ← all ACF field group registrations
│   └── product/
│       ├── product-data.php            ← all hp_get_* helper functions (incl. hp_get_product_trust_strip_items)
│       └── review-images.php          ← TO CREATE in Phase 11
└── assets/
    ├── css/admin-global-settings.css
    └── js/admin-global-settings.js
```

---

## KEY PATTERNS & DECISIONS

### Template loading
```php
hitprice_get_template_part( 'template-parts/product/foo' );
hitprice_get_template_part( 'template-parts/product/stock-viewers', array( 'modifier' => 'mobile' ) );
// Args accessed inside template via $args['modifier']
```

### Global settings access
```php
hp_get_global_setting( 'shipping_policy' )          // top-level key
hp_get_global_setting( 'badges.genuine.label' )     // dot-notation (NOT used — badges use hp_get_global_badge())
hp_get_global_badge( 'genuine' )                    // returns ['image_url','label','description']
```

### Viewers seeded random (JS)
```js
// seed = productId * 1000 + year * 400 + month * 31 + day
// LCG: seed = (seed * 1664525 + 1013904223) & 0xffffffff
// count = min + (Math.abs(seed) % (max - min + 1))
```

### Buy Now button
```js
// Variable: checks variation_id hidden input — if 0, scrolls form into view
// Simple: uses data-product-id
// Both: fetch GET to wc-ajax=add_to_cart, then window.location.href = checkoutUrl
```

### URL hash tabs
```js
// Hash format: #hp-tab-overview, #hp-tab-reviews, etc.
// On load: parse hash, call activateTab(id), smooth scroll to .hp-tabs
// On tab click: history.replaceState to update URL without reload
```

### WC tab suppression
```php
// Filter returns empty array → WC skips its tab wrapper entirely
add_filter( 'woocommerce_product_tabs', 'hp_suppress_wc_product_tabs', 99 );
// Also removed the action that wraps tab output:
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
```

### CSS breakpoints
- `≤767px` — single column mobile
- `≤900px` — key highlights image hidden, overview specs surface shown
- `≤992px` — minor grid adjustments

### Overview specs: desktop vs mobile
- **Desktop**: rendered inside `tab-overview.php` as right column of Overview tab
- **Mobile**: rendered in `key-highlights.php` as `.hp-overview-specs-surface` (4-icon row above Key Highlights) — `display:none` on desktop, `display:flex` on ≤900px
- Same ACF data, no double query — CSS controls which rendering is visible

---

## ARCHITECTURE RULES (from project rules)
- All PHP logic → `hitprice-helper` plugin (never in theme functions.php)
- All UI/CSS/templates → Astra child theme
- Hooks only — no WC template file overrides
- All functions prefixed `hp_` (plugin) or `hitprice_` (theme)
- Security: nonces on all form saves, capability checks, `esc_*` on all output
- Performance: no `SELECT *`, transient cache for expensive queries, critical CSS inlined
- ACF Pro is active — use `get_field()`, not `get_post_meta()` for ACF fields

---

## WHAT STILL NEEDS DOING (checklist)

- [ ] Phase 7: `review-images.php` plugin file + form injection + JS preview + card rendering
- [ ] Phase 8A: `why-choose.php` template + hook at priority 20
- [ ] Phase 8B: `trust-strip-bottom.php` template + hook at priority 25
- [ ] Phase 9: Full CSS polish + mobile QA pass
- [ ] Update `hit_price_ai.md` with all completed work (project documentation rule)
