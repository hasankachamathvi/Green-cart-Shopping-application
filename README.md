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

## Run

Open in browser:

`http://localhost/Shopping-cart-application/`

The root file redirects to products page.

## Login/Register

- Register: `http://localhost/Shopping-cart-application/auth/register.php`
- Login: `http://localhost/Shopping-cart-application/auth/login.php`

## Notes

- Some social/admin files are placeholders and currently empty.
- The sample data script is idempotent for the included categories/products.