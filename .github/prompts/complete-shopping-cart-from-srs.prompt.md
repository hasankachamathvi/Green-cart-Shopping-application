---
description: "Implement unfinished shopping-cart SRS features and always output a phpMyAdmin-ready SQL at database/shopping_cart.sql"
name: "Complete Shopping Cart From SRS"
argument-hint: "Paste missing features, constraints, or extra requirements"
agent: "agent"
---
You are implementing and finishing a PHP shopping cart application from the SRS below.

Primary goal:
- Complete unfinished features in the existing codebase.
- Keep changes minimal and production-safe.
- Produce or update a SQL import file that can be uploaded in phpMyAdmin.

Hard defaults (do not ask unless user explicitly overrides):
- Canonical SQL file is always `database/shopping_cart.sql`.
- Checkout includes order summary now, plus placeholder payment-gateway schema only (no live gateway integration).
- Scope is workspace-first (shared project files), not user-profile prompt behavior.

Project context (fixed):
- Stack: PHP, MySQL, HTML/CSS/JS
- Core modules: auth (Google/Facebook/Passkey), products, categories, cart, checkout summary, admin CRUD
- Existing database folder: [database](../../database)

SRS summary to enforce:
1. User Registration and Login
- Social login (Google/Facebook) and Passkey login for users.
- Separate admin login.

2. Category and Product Browsing
- Categories: Vegetables, Fruits, Cakes, Biscuits, and extensible categories.
- Product card: image, name, price, description.

3. Shopping Cart
- Add item, update quantity, remove item.
- Dynamic total price updates.

4. Checkout
- Show order summary before payment.
- Include placeholder payment data model support for future integration.

5. Admin Features
- Manage products and categories (CRUD).

Non-functional:
- Secure auth/data handling.
- Responsive UI.
- Good loading and navigation.
- Basic error handling and reliability safeguards.

When running this prompt, follow this execution workflow:
1. Scan project files and map implemented vs missing requirements.
2. Implement missing parts directly in the codebase.
3. Validate key flows (auth, browse, cart update, checkout summary, admin CRUD).
4. Create or update SQL schema/seed file for import at `database/shopping_cart.sql`.
5. Report exactly what was completed and what remains (if anything).

Implementation rules:
- Prefer updating existing files over introducing new architecture.
- Keep API and UI behavior backward-compatible where possible.
- Add basic input validation and server-side checks where missing.
- If a requirement cannot be completed, explain blocker and provide the smallest viable fallback.

Required output format:
1. Completed items
- Bullet list mapped to SRS requirements.

2. Code changes
- File-by-file summary with purpose.

3. Database file for phpMyAdmin
- Provide exact path and filename.
- Required output file: `database/shopping_cart.sql`
- Do not switch filenames unless user explicitly requests it.

4. SQL contents checklist
- Include table creation order with foreign keys handled safely.
- Include essential seed data for categories and sample products.
- Include payment placeholder table(s) (e.g., payments/transactions status fields) without external gateway coupling.
- Ensure import works on a fresh phpMyAdmin database.

5. Import instructions
- Short steps for phpMyAdmin import.

6. Acceptance check
- Confirm all core SRS items are either completed or listed under remaining gaps.
- Confirm the SQL file path is exactly `database/shopping_cart.sql`.

7. Remaining gaps
- Only if anything is still pending, with blockers.

User-provided extra requirements:
$ARGUMENTS
