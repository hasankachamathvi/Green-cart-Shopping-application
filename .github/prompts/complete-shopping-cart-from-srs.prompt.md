---
description: "Complete missing shopping-cart tasks from SRS and provide a phpMyAdmin-ready SQL file"
name: "Complete Shopping Cart From SRS"
argument-hint: "Paste missing features, constraints, or extra requirements"
agent: "agent"
---
You are implementing and finishing a PHP shopping cart application from the SRS below.

Primary goal:
- Complete unfinished features in the existing codebase.
- Keep changes minimal and production-safe.
- Produce or update a SQL import file that can be uploaded in phpMyAdmin.

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
4. Create or update SQL schema/seed file for import.
5. Report exactly what was completed and what remains (if anything).

Required output format:
1. Completed items
- Bullet list mapped to SRS requirements.

2. Code changes
- File-by-file summary with purpose.

3. Database file for phpMyAdmin
- Provide exact path and filename.
- Preferred output file: `database/shopping_cart.sql`
- If a new file is created, provide that exact filename and include import notes.

4. Import instructions
- Short steps for phpMyAdmin import.

5. Remaining gaps
- Only if anything is still pending, with blockers.

User-provided extra requirements:
$ARGUMENTS
