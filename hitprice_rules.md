# Hit Price AI Rules (MANDATORY)

## Instruction Priority
- These rules OVERRIDE any default AI behavior.
- If any conflict occurs → follow these rules.

---

## Environment
- This project is currently in DEVELOPMENT environment.
- Performance optimizations should still be applied, but debugging tools/logging can be enabled.
- WP_DEBUG may be enabled during development but must not reach production.
- Temporary test code is allowed ONLY if removed after validation.
- Debugging allowed, but no sloppy code.
- Production-level performance mindset must still be maintained.

---

## Core Principles
1. Performance first
2. Conversion second
3. Simplicity third

---

## Architecture Rules
- All custom code MUST go into:
  → hitprice-helper plugin
- No business logic inside theme files.
- Code must be modular and reusable.
- Use proper prefix: hp_ or hitprice_

---

## Performance Rules (STRICT)
- Load assets ONLY where needed
- No global scripts unless critical
- No heavy libraries unless justified
- Prefer vanilla JS over jQuery
- Minimize DOM size
- No unnecessary API/DB calls

---

## Database Rules
- Avoid repeated queries
- Use transients for expensive operations
- Optimize WP_Query usage
- No unoptimized loops with queries

---

## Code Quality Rules
- No unused code
- No redundant logic
- No large files
- Each function must have a clear purpose
- Reusable and clean structure required

---

## WordPress Standards
- Follow WP coding standards
- Sanitize + escape everything
- Use hooks properly
- No direct DB queries unless necessary

---

## WooCommerce Rules
- Use hooks instead of template overrides
- Keep checkout fast and minimal
- Avoid heavy plugins

---

## Accessibility
- Semantic HTML required
- Keyboard accessible UI
- Proper labels and ARIA where needed

---

## Error Policy
- ZERO PHP warnings
- ZERO JS console errors
- Defensive coding required

---

## Elementor Rules
- Use only for layout
- Avoid nested complexity
- Reuse templates

---

## Media Rules
- Optimized images only
- Use modern formats
- Lazy load everything possible

---

## Documentation Rule (MANDATORY)
- Always update:
  hit_price_ai.md

Include:
- What was done
- Why
- Files changed
- Next steps

---

## Final Rule
If code does NOT improve:
- performance
- conversion
- or maintainability

→ DO NOT IMPLEMENT IT.
## Additional Execution Rules (STRICT)

### 1. File Creation / Modification Approval
- BEFORE creating, editing, or deleting ANY file:
  - You MUST ask for confirmation.
  - Clearly specify:
    - File path
    - Purpose of change
    - Type of change (create / update / delete)
- DO NOT proceed without explicit approval.

---

### 2. Theme vs Plugin Responsibility

#### Child Theme (STRICT)
- All theme-related work MUST go into the child theme.
- Includes:
  - UI adjustments
  - Template overrides (only if absolutely necessary)
  - Styling (CSS)
  - Minor front-end behavior

#### Helper Plugin (STRICT)
- All heavy logic MUST go into helper plugin (`hitprice-helper`), including:
  - Business logic
  - Performance optimizations
  - Custom queries
  - API integrations
  - Background processing
  - Any logic not directly tied to presentation

- If logic is reusable or not UI-specific, it belongs in plugin, not theme.

---

### 3. WordPress Native First Approach
- ALWAYS prefer WordPress built-in features before creating custom solutions.

Priority order:
1. WordPress core functions
2. WooCommerce native hooks/features
3. Existing APIs (WP_Query, REST API, etc.)
4. Extend existing functionality
5. Custom code (ONLY if no native solution exists)

- DO NOT reinvent existing WordPress functionality.

---

### 4. ACF Usage Policy
- If functionality requires structured/custom data:
  - You MAY suggest using ACF (Advanced Custom Fields)
- BUT:
  - Explain WHY ACF is needed before implementation
  - Get approval before using it
- Do NOT introduce ACF for simple use cases.

---

### 5. Security Standards (MANDATORY)
- ALWAYS:
  - Sanitize input (`sanitize_text_field`, etc.)
  - Escape output (`esc_html`, `esc_attr`, etc.)
  - Use nonces for forms and actions
  - Validate all external data

- NEVER:
  - Trust user input
  - Expose sensitive data
  - Allow direct file access

- Use:
  ```php
  if ( ! defined( 'ABSPATH' ) ) exit;
  ```

---

### 6. Typography Rules
- Use `Inter` for body text and UI elements
- Use `Poppins` for headings only
