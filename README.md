# Cheesy Bees

A beginner-friendly pizza shop ERP/POS project made with plain PHP, MySQL, HTML, and CSS.

## First-time setup

1. Create a MySQL database called `simple_site`.
2. Copy `db.example.php` and name the copy `db.php`.
3. Open `db.php` and enter your own MySQL username and password.
4. Run `products.sql` in the `simple_site` database to create the products table.
5. Run `orders.sql` in the same database to create the orders and order items tables.
6. Open `index.php` in your local PHP web server.

For local PHP setup, keep your database credentials in `db.php` out of Git. The Docker image uses `docker/db.php`, which reads its connection settings from environment variables.

## Run with Docker

1. Create `.env` from `.env.example` if you do not already have one. Set different values for `DB_PASSWORD` and `DB_ROOT_PASSWORD`.
2. Run `docker compose up --build -d` from the repository root.
3. Open `http://localhost:8080` (or the `APP_PORT` set in `.env`). Register an account to start.

`docker build -t cheesy-bees:local .` builds only the PHP image. Docker Compose also starts MySQL and keeps its data in the `mysql_data` volume. Run `docker compose down` to stop the containers; the volume remains.

## Create the products table

In phpMyAdmin, select the `simple_site` database, open the **SQL** tab, paste the contents of `products.sql`, and click **Go**.

The Products page will show a helpful message rather than an HTTP 500 error when the table has not been created yet.

## Sales

After logging in, use **New order** to select available products and enter the cash received. The app calculates the total and change, then saves the sale. **Order history** shows the latest 100 sales and lets you view each receipt. Product names and prices are saved with each order, so past receipts remain accurate when products change or are deleted.

For an existing Docker database volume created before orders were added, run `orders.sql` once:

```sh
docker compose exec -T db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -u root "$MYSQL_DATABASE"' < orders.sql
```

New Docker volumes get all tables from `init.sql`. MySQL runs that file only when it initializes an empty volume.
