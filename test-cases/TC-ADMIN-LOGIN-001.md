# Test Case: TC-ADMIN-LOGIN-001

## Title
Admin login with valid default credentials

## Module
Admin Authentication

## Priority
High

## Preconditions
1. WAMP/XAMPP server is running.
2. Database is connected successfully.
3. Default admin account exists (`admin` / `admin123`).
4. User is logged out from admin session.

## Test Data
- Username: `admin`
- Password: `admin123`

## Steps
1. Open browser.
2. Navigate to `http://localhost/Shopping-cart-application/admin/dashboard.php`.
3. Enter username `admin`.
4. Enter password `admin123`.
5. Click `Sign In`.

## Expected Result
1. Login succeeds.
2. User is redirected to admin dashboard.
3. Dashboard shows welcome text for admin.
4. Sidebar menu is visible with items: Dashboard, Users, Orders, Payments, Feedback, Products, Categories.

## Actual Result
- Pending execution.

## Status
- Not Run
