# Little Things — PHP demo shop

A responsive PHP storefront with a product catalog, session shopping bag, and demo checkout. Orders and inventory changes are saved in MySQL. No payment is collected.

## Run locally with XAMPP and phpMyAdmin

1. In XAMPP Control Panel, start **Apache** and **MySQL**.
2. Open <http://localhost/phpmyadmin>, create a database named `little_things`, select it, choose **Import**, and upload `database.sql`.
3. `connection.php` uses the local XAMPP defaults when `APP_ENV` is not `production`: host `127.0.0.1`, database `little_things`, username `root`, blank password, and port `3306`.
4. Visit <http://localhost/myhostsite/> if the project folder is named `myhostsite` inside `htdocs`.

## Deploy to hosting

The GitHub Actions workflow deploys the repository root to your hosting account over FTPS on pushes to `master`. You can also run it from GitHub's **Actions** tab with **Run workflow**.

In the repository's **Settings → Secrets and variables → Actions**, add these repository secrets:

| Secret | Value |
| --- | --- |
| `HOST` | FTP server hostname from your host |
| `USERNAME` | FTP username |
| `PASSWORD` | FTP password |
| `PORT` | FTP/FTPS port, often `21` (or `990` for legacy implicit FTPS) |

Add these repository variables (not secrets):

| Variable | Value |
| --- | --- |
| `FTP_PROTOCOL` | `ftps` (recommended), or `ftp` / `ftps-legacy` if your host requires it |
| `FTP_SERVER_DIR` | Website directory on the FTP server, often `/public_html/` |

Import `database.sql` into your hosted MySQL database with phpMyAdmin. Separately, set these environment variables for the **PHP website in your hosting control panel**:

| Variable | Value |
| --- | --- |
| `APP_ENV` | `production` |
| `DB_HOST` | Database host supplied by your provider |
| `DB_NAME` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `DB_PORT` | Database port, usually `3306` |

The app requires all five `DB_...` variables in production and will not fall back to the local XAMPP credentials. GitHub Actions FTP secrets only authenticate the file upload; they do not configure the PHP host's database environment. Set database credentials in the hosting control panel, keep passwords out of the repository, and ensure PHP has the `pdo_mysql` extension enabled.

## Notes

The checkout is a demo and does not collect payment. The newsletter form is visual only and does not save email addresses. `database.sql` is the phpMyAdmin import file.
