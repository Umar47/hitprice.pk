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

### 5. Global Security & Engineering Standard (MANDATORY)

These rules apply to ALL systems: WordPress plugins, APIs, automation scripts, cron jobs, AI integrations, frontend/backend code. Security is a FIRST-CLASS requirement, not optional.

#### 5.1 Zero Trust Principle
- Treat ALL input as untrusted: user input, admin input, API responses
- Never assume safety
- Always validate and sanitize

#### 5.2 Input / Output Security
- Sanitize ALL inputs:
  - `sanitize_text_field()`, `sanitize_email()`, `intval()`, `floatval()`
  - `wp_kses_post()` for controlled HTML only
- Escape ALL outputs:
  - `esc_html()`, `esc_attr()`, `esc_url()`

#### 5.3 Authentication & Authorization
- Enforce capability checks: `current_user_can()`
- Apply least privilege: no unnecessary admin-level access

#### 5.4 CSRF & Request Protection
- Use nonces in ALL forms/actions:
  - `wp_create_nonce()`, `wp_verify_nonce()`

#### 5.5 Database Security
- Use `$wpdb->prepare()` ALWAYS
- Avoid raw SQL queries
- Prefer WordPress APIs when possible

#### 5.6 Secret Management
- NEVER hardcode API keys, tokens, or credentials
- Store securely in `wp-config.php` or environment variables

#### 5.7 API & External Call Security
- Validate all API responses
- Handle failures gracefully
- Add timeouts, retries, and rate limiting

#### 5.8 File & Access Security
- Prevent direct file access:
  ```php
  if ( ! defined( 'ABSPATH' ) ) exit;
  ```
- Restrict file permissions

#### 5.9 Error Handling
- Never expose stack traces, API keys, or internal paths
- Log errors securely

#### 5.10 XSS / Injection Prevention
- Escape output strictly
- Sanitize stored data
- Validate before DB insert

#### 5.11 Automation & AI Security
- Validate AI output before saving
- Strip unsafe HTML/scripts
- Limit generation frequency and prevent API abuse

#### 5.12 Logging & Auditing
- Track who generated content and when actions occurred
- Maintain logs for debugging

#### 5.13 Performance + Security Balance
- Avoid heavy queries
- Cache where needed
- Prevent abuse via rate limits

#### 5.14 Mandatory Security Review
- For EVERY feature or code block, include a security review:
  - Identify risks
  - Explain mitigation
  - Suggest improvements

#### 5.15 Hard Rule
- If any implementation is insecure, exposing data, or lacking validation:
  1. Stop
  2. Fix it
  3. Re-evaluate
- Security is part of the architecture, not a patch

---

### 6. Typography Rules
- Use `Inter` for body text and UI elements
- Use `Poppins` for headings only
