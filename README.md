# Lost-and-found-portal-project

## Quick setup (Windows + XAMPP)

1) Install and start services
- Install XAMPP (includes Apache + MySQL/MariaDB).
- Open XAMPP Control Panel and Start: Apache, MySQL.

2) Place the project
- Clone or copy this folder to: `d:\Xampp\htdocs\Lost-and-found-portal-project` (or `C:\xampp\htdocs\Lost-and-found-portal-project`).

3) Create and import database
- Open phpMyAdmin: http://localhost/phpmyadmin
- Create a database named `lostfounddb` with collation `utf8mb4_general_ci`.
- Import the SQL file:
	- Preferred: `lostfounddb.fixed.sql` (cleaned and ready to import)
	- Alternative: `lostfounddb.sql` (original; may fail due to a malformed section)

4) Configure database connection (if needed)
- The app uses `configure.php` (PDO) and some pages use `mysqli` with the same defaults.
- Defaults are: host=localhost, user=root, password="", db=lostfounddb.
- If your MySQL root has a password, update `configure.php` and any page-specific connections accordingly.

5) Access the site
- User portal: http://localhost/Lost-and-found-portal-project/home.php
- Admin login: http://localhost/Lost-and-found-portal-project/loginadmin.html

6) Default accounts
- Ordinary user: username `uoc`, password `uoc`
- Admin: username `siteadmin`, password `admin` (created via SQL or on first admin login attempt)

Notes
- Images are under `uploads/` and may be referenced by some pages.
- If Apache shows a 403/404, ensure the folder name in URL matches the actual folder casing.
- If you change the DB name, update it in `configure.php` and any `new mysqli(...)` usages (e.g., `authadmin.php`).
