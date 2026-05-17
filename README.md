# MedDirect Online Medicine Shop

PHP MVC implementation of the project described in `Group_10_S.pdf`.

## Apache

Apache was failing because ports `80` and `8080` were already occupied. The XAMPP Apache HTTP port is now configured in `C:\xampp\apache\conf\httpd.conf` as:

- `Listen 8081`
- `ServerName localhost:8081`

Open the app at:

`http://localhost:8081/webtech%20project/public/index.php`

## MVC Structure

- `public/index.php` - front controller and router
- `app/controllers/` - request handling for auth, home, cart, admin, and JSON APIs
- `app/models/` - PDO models for users, categories, medicines, cart, orders, and payments
- `app/views/` - presentation templates
- `config/config.php` - database and base URL config
- `database/meddirect.sql` - shared schema and seed data

## Features

- Admin/customer registration and login with `password_hash()` / `password_verify()`
- Session-gated profile management with image upload validation
- Medicine browsing by category, liquid/solid type, vendor, and AJAX search
- Customer cart with AJAX add/update/remove
- Checkout with invoice, shipping address, payment method, order creation, and order history
- Admin dashboard, medicine CRUD, category CRUD, customer deletion, purchase requests, AJAX accept/reject, and accepted purchase history
- PDO prepared statements, CSRF tokens, server-side validation, client-side validation, and JSON endpoints

## Database Setup

Import `database/meddirect.sql` into phpMyAdmin or run it with MySQL. It creates the required shared tables:

`users`, `categories`, `medicines`, `cart`, `orders`, `order_items`, `payments`

Demo logins:

- Admin: `admin@meddirect.test`
- Admin: `manager@meddirect.test`
- Customer: `customer@meddirect.test`
- Customer: `nadia@meddirect.test`
- Customer: `samiul@meddirect.test`
- Password for every demo account: `password123`

The seed data also includes demo medicines and sample pending, accepted, and rejected purchase requests.

## Contributors

- [saan25420-lab](https://github.com/saan25420-lab)
