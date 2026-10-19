# Cheesy Bees

A beginner-friendly pizza shop ERP/POS project made with plain PHP, MySQL, HTML, and CSS.

## First-time setup

1. Create a MySQL database called `simple_site`.
2. Copy `db.example.php` and name the copy `db.php`.
3. Open `db.php` and enter your own MySQL username and password.
4. Run `products.sql` in the `simple_site` database to create the products table.
5. Open `index.php` in your local PHP web server.

`db.php` is intentionally not included in Git. It contains private database credentials.

## Create the products table

In phpMyAdmin, select the `simple_site` database, open the **SQL** tab, paste the contents of `products.sql`, and click **Go**.

The Products page will show a helpful message rather than an HTTP 500 error when the table has not been created yet.
