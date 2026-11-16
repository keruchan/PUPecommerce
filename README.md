# Little Things — PHP demo shop

A responsive PHP storefront with a product catalog, session shopping bag, and demo checkout. Orders and inventory changes are saved in MySQL. No payment is collected.

## Run locally with XAMPP and phpMyAdmin

1. In XAMPP Control Panel, start **Apache** and **MySQL**.
2. Open <http://localhost/phpmyadmin>, create a database named `little_things`, select it, choose **Import**, and upload `database.sql`.
3. The included `connection.php` uses the XAMPP defaults (`127.0.0.1`, database `little_things`, user `root`, blank password).
4. Visit <http://localhost/myhostsite/> if the project folder is named `myhostsite` inside `htdocs`.

For hosting, replace the values in `connection.php` with your provider's database host, name, username, and password. Many hosts use a database name and username with an account prefix. PHP needs the `pdo_mysql` extension enabled.

## Notes

The checkout is a demo and does not collect payment. The newsletter form is visual only and does not save email addresses. `database.sql` is the phpMyAdmin import file.
