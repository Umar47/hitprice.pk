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

## Next Tasks
- Activate the Astra child theme if not already active
- Refine header design and spacing to match the premium ecommerce direction
- Build the detailed footer design
- Assign/create WordPress menus for header and footer
- Build homepage structure
- Create category layout
- Optimize product page design
- Review performance

## Important Decisions
- Theme-related presentation work goes in the child theme
- Heavy or reusable logic belongs in `hitprice-helper`, not the theme
- Header and footer must be split into separate template files following WordPress standards
- Prefer native WordPress and WooCommerce features before custom solutions
- Ask for approval before every future file create/update/delete action
- Use local theme fonts: Inter for body/UI and Poppins for headings only
- Use Astra built-in branding/logo functions where possible instead of rebuilding logo output
- Use Astra/WooCommerce native cart patterns and cart fragments for header cart behavior

## Files Changed
- `wp-content/themes/astra-child/style.css`
- `wp-content/themes/astra-child/functions.php`
- `wp-content/themes/astra-child/inc/theme-setup.php`
- `wp-content/themes/astra-child/inc/template-hooks.php`
- `wp-content/themes/astra-child/inc/template-functions.php`
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
- `wp-content/themes/astra-child/assets/js/header.js`
- `hitprice_rules.md`
- `hit_price_ai.md`

## Resume Notes
- Current header/footer is scaffold-level and not final polished design
- Header menu now depends on the assigned WordPress menu in admin
- Search is product-focused using native WordPress search with `post_type=product`
- Local fonts are available under `assets/fonts/Inter` and `assets/fonts/Poppins`
- Logo/site identity should be managed from `Appearance > Customize > Site Identity`
- Header cart should show live count and use Astra cart drawer behavior where available
- Keep future presentation work in separate child-theme template parts, not inline in `functions.php`
