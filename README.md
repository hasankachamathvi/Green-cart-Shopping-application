# Shopping Cart Application

## Requirements

- PHP 8.0+
- MySQL 5.7+ (or MariaDB)
- Apache (XAMPP/WAMP recommended on Windows)

## Quick Start (Windows + XAMPP)

1. Copy this project folder into `C:/xampp/htdocs/`.
2. Start `Apache` and `MySQL` from XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Import schema file: `database/shopping_cart.sql`.
5. Import sample data file: `database/seed_data.sql`.
6. Ensure DB config in `config/db.php` is correct:
	- host: `localhost`
	- user: `root`
	- password: `` (empty by default in XAMPP)
	- database: `shopping_cart_db`

## Quick Start (Windows + WAMP)

1. Copy this project folder into `C:/wamp64/www/`.
2. Start WAMP and wait for green icon (Apache + MySQL running).
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Import `database/shopping_cart.sql` and then `database/seed_data.sql`.
5. Ensure DB config in `config/db.php` matches your WAMP MySQL credentials.

## Run

Open in browser:

`http://localhost/Shopping-cart-application/`

The root file redirects to home page.

## Login/Register

- Register: `http://localhost/Shopping-cart-application/auth/register.php`
- Login: `http://localhost/Shopping-cart-application/auth/login.php`

## Main Pages

- Home: `http://localhost/Shopping-cart-application/pages/index.php`
- Products + Cart: `http://localhost/Shopping-cart-application/pages/products.php`
- Contact / Feedback: `http://localhost/Shopping-cart-application/pages/contact.php`
- Checkout / Payment: `http://localhost/Shopping-cart-application/pages/checkout.php`

## Admin

- Admin URL: `http://localhost/Shopping-cart-application/admin/dashboard.php`
- Default admin credentials:
	- username: `admin`
	- password: `admin123`

Admin panel includes:

- Product add/edit/delete
- Category management
- Customer feedback handling
- Payment and order tracking

## Notes

- Social login files are placeholders.
- The sample data script is idempotent for included categories/products.