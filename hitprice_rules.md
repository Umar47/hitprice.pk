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