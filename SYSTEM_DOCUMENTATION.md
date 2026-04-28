# Shopping Cart Application - Detailed System Documentation

## 1. System Overview

### 1.1 Project Name
GreenCart Shopping Cart Application

### 1.2 Purpose
This system provides a complete web-based shopping platform where customers can browse products, manage carts, place orders, and track order history, while administrators can manage products, categories, users, payments, orders, and feedback.

### 1.3 Business Goals
- Provide a smooth online grocery and essentials ordering flow.
- Support secure and flexible authentication options.
- Give admins centralized control over catalog and operations.
- Keep UI responsive for desktop and mobile browsers.

### 1.4 Technology Stack
- Frontend: HTML5, CSS3, JavaScript (vanilla)
- Backend: PHP (mysqli)
- Database: MySQL / MariaDB
- Server: Apache (WAMP/XAMPP)
- Authentication support: Manual + Google/Facebook (demo-mode flow) + Passkey flow pages

---

## 2. System Architecture

### 2.1 High-Level Architecture
The application follows a classic PHP server-rendered architecture:
- Presentation layer: `pages/`, `auth/`, `admin/`
- API/action layer: `api/`
- Data/config layer: `config/` + MySQL schema in `database/`
- Shared layout components: `includes/`
- Static assets: `assets/css`, `assets/js`, `assets/images`

### 2.2 Request Flow
1. Browser requests a page or API endpoint.
2. PHP script loads database connection from `config/db.php`.
3. Session is checked where required.
4. Business logic executes SQL queries.
5. Response returned as HTML (pages) or JSON/redirect (APIs).

---

## 3. Project Structure and Responsibilities

- `index.php`
  - Entry point; redirects users to `pages/index.php`.

- `pages/`
  - Customer-facing UI pages (home, products, cart, checkout, profile, history, etc.).

- `auth/`
  - Registration, login, logout, account chooser, passkey and social auth flow pages.

- `admin/`
  - Admin authentication and management pages for operations.

- `api/`
  - Action endpoints for cart updates, checkout, and feedback operations.

- `config/`
  - Core configuration for DB, OAuth, email.

- `database/`
  - SQL schema and seed/sample data scripts.

- `assets/`
  - CSS, JS, and image assets.

- `includes/`
  - Shared reusable components (navbar/footer/header).

- `test-cases/`
  - Manual test case documents.

---

## 4. Functional Modules

## 4.1 Customer Module

### 4.1.1 Home and Navigation
- Home page: `pages/index.php`
- Primary navigation to products, about, contact, profile/login/register.

### 4.1.2 Product Browsing
- Product list with category filtering: `pages/products.php`
- Product detail view: `pages/product-details.php`
- Category chips and product counts.

### 4.1.3 Cart Management
- Add to cart: `api/add-to-cart.php`
- Update quantity: `api/update-cart.php`
- Remove item: `api/remove-from-cart.php`
- Fetch product/cart data: `api/fetch-products.php`
- Frontend logic: `assets/js/cart.js`

### 4.1.4 Checkout and Order Placement
- Checkout page with summary: `pages/checkout.php`
- Order creation endpoint: `api/checkout.php`
- Order success page: `pages/order-success.php`
- Order history page: `pages/order-history.php`

### 4.1.5 User Profile
- Profile and recent orders snapshot: `pages/profile.php`

### 4.1.6 Contact and Feedback
- Customer feedback form: `pages/contact.php`
- Feedback submission API: `api/submit-feedback.php`

## 4.2 Authentication Module

### 4.2.1 Manual Authentication
- Register: `auth/register.php`
- Login: `auth/login.php`
- Logout: `auth/logout.php`

### 4.2.2 Social/Passkey Flow Pages
- Google flow pages: `auth/google-login.php`, `auth/google-callback.php`
- Facebook flow pages: `auth/facebook-login.php`, `auth/facebook-callback.php`
- Account chooser page: `auth/account-chooser.php`
- Passkey pages: `auth/passkey-auth.php`, `auth/passkey-chooser.php`

Note: Social and passkey flows are currently development-friendly and should be hardened for full production identity verification.

## 4.3 Admin Module

### 4.3.1 Admin Authentication and Setup
- Admin bootstrap and schema checks: `admin/admin-auth.php`
- Admin dashboard/login page: `admin/dashboard.php`
- Shared admin sidebar and common admin styles: `admin/admin-sidebar.php`

### 4.3.2 Admin Operations
- User management: `admin/manage-users.php`
- Order management: `admin/manage-orders.php`
- Payment management: `admin/manage-payments.php`
- Feedback management: `admin/manage-feedback.php`
- Product create/list: `admin/add-product.php`
- Product edit/update: `admin/edit-product.php`
- Product delete: `admin/delete-product.php`
- Category management: `admin/manage-category.php`

---

## 5. API Endpoint Summary

- `api/add-to-cart.php`
  - Adds selected product to user cart.

- `api/update-cart.php`
  - Updates item quantity in cart.

- `api/remove-from-cart.php`
  - Removes item from cart.

- `api/fetch-products.php`
  - Returns product/cart data used by UI logic.

- `api/checkout.php`
  - Creates order, order items, payment row, clears cart, sends notifications.

- `api/submit-feedback.php`
  - Saves feedback records.

---

## 6. Database Design

### 6.1 Core Tables
- `users`
- `categories`
- `products`
- `carts`
- `cart_items`
- `orders`
- `order_items`

### 6.2 Operational/Admin Tables
- `admin_users`
- `payments`
- `feedbacks`

### 6.3 Relationship Highlights
- A `user` has one cart (`carts.user_id` unique).
- A cart contains multiple `cart_items`.
- An `order` belongs to a user and has multiple `order_items`.
- Products map to categories via `products.category_id`.
- Payments track order-level transaction state.

### 6.4 Schema and Seed Files
- Base schema: `database/shopping_cart.sql`
- Seed data: `database/seed_data.sql`
- Extended sample data: `database/seed_sample_data.sql`

---

## 7. Email Notification Subsystem

### 7.1 File
- `config/email.php`

### 7.2 Email Functions
- Order confirmation email
- Order status update email
- Welcome/thank-you email on registration

### 7.3 Operational Note
Email delivery depends on host mail capability. For production use, configure real SMTP credentials and sender domain.

---

## 8. Configuration and Environment

## 8.1 Database Configuration
- File: `config/db.php`
- Current behavior:
  - Tries remote DB first (`fdb1029.awardspace.net` / `4537812_greencart`)
  - Falls back to local WAMP DB (`localhost` / `shopping_cart_db`)

This fallback allows local development continuity when remote host is unavailable.

## 8.2 OAuth Configuration
- File: `config/oauth.php`
- Replace development/demo values with production credentials.

## 8.3 Email Configuration
- File: `config/email.php`
- Update sender and SMTP settings for production.

---

## 9. UI/UX and Styling

### 9.1 Main Theme
- Primary UI style in `assets/css/style.css`

### 9.2 Admin UI
- Shared admin navigation + style block in `admin/admin-sidebar.php`
- Ensures consistent sidebar, cards, tables, and controls across admin pages.

### 9.3 Responsiveness
- Mobile-friendly layout behavior is included through CSS media queries.

---

## 10. Security Considerations

### 10.1 Implemented
- Session-based authentication.
- Password hashing and verification for manual accounts and admin.
- Prepared statements used in critical paths.
- Access guards for admin-only pages.

### 10.2 Recommended Improvements
- Add CSRF token validation on all state-changing POST actions.
- Add stricter server-side validation and centralized sanitization.
- Configure secure cookies and HTTPS-only deployment.
- Add login throttling/rate limiting for brute-force protection.

---

## 11. Deployment Guide

### 11.1 Local Deployment (WAMP)
1. Place project in `C:/wamp64/www/Shopping-cart-application`.
2. Start Apache + MySQL.
3. Import `database/shopping_cart.sql` then seed file(s).
4. Verify `config/db.php` credentials.
5. Open: `http://localhost/Shopping-cart-application/`

### 11.2 Public Hosting (AwardSpace-like)
1. Upload project files to host.
2. Import database schema and data.
3. Set remote DB credentials in `config/db.php`.
4. Configure valid domain email sender + SMTP.
5. Configure OAuth callback URLs for production domain.

---

## 12. Known Limitations

- Full payment gateway integration is not yet implemented.
- Social/passkey flows are not fully production-hardened token verification flows.
- Automated test suite is not yet established (manual test-cases are being added).
- Some legacy CSS blocks remain in `assets/css/style.css`; functional, but can be cleaned for maintainability.

---

## 13. Testing Strategy

### 13.1 Current State
- Manual validation done during development.
- Initial test case document available in `test-cases/TC-ADMIN-LOGIN-001.md`.

### 13.2 Recommended Test Packs
- Authentication (manual/social/passkey/admin)
- Product browsing and category filtering
- Cart operations and totals
- Checkout/order/payment updates
- Admin CRUD actions and access control
- Mobile responsive behavior

---

## 14. Troubleshooting Guide

### Issue: Admin page cannot open due to DB timeout
- Cause: Remote DB not reachable from local machine.
- Resolution: `config/db.php` includes local fallback. Ensure local DB exists and service is running.

### Issue: Function redeclare fatal in `config/db.php`
- Cause: Multiple includes in same request.
- Resolution: Guarded helper with `function_exists` and used `require_once` in shared admin bootstrap.

### Issue: Admin sidebar appears offset/misaligned
- Cause: Overlapping admin CSS rules.
- Resolution: Shared layout override in `admin/admin-sidebar.php`; hard refresh browser cache.

### Issue: Old style appears after fix
- Resolution: Press `Ctrl + F5` for hard refresh and clear localhost cache.

---

## 15. URLs Reference

### Customer
- Home: `http://localhost/Shopping-cart-application/`
- Products: `http://localhost/Shopping-cart-application/pages/products.php`
- Cart: `http://localhost/Shopping-cart-application/pages/cart.php`
- Checkout: `http://localhost/Shopping-cart-application/pages/checkout.php`
- Order History: `http://localhost/Shopping-cart-application/pages/order-history.php`

### Admin
- Admin dashboard/login: `http://localhost/Shopping-cart-application/admin/dashboard.php`

---

## 16. Default Admin Credentials

- Username: `admin`
- Password: `admin123`

(Optionally, alias login support for `admin@greenmart` may be enabled depending on current dashboard logic.)

---

## 17. Maintenance Notes

- Keep `D:` and `C:` project copies synchronized after each change.
- Prefer shared includes for admin to avoid style duplication.
- Keep schema migration logic in admin bootstrap small and controlled.
- Version and backup `config` credentials securely before deployment.

---

## 18. Document Metadata

- Document version: 1.0
- Last updated: 2026-04-28
- Prepared for: GreenCart Shopping Cart Application
- Audience: Developers, QA, Project Reviewers, Deployment Engineers
