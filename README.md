# Employee Task Management System

ETMS is a PHP 8 and MySQL 8 task coordination application for administrators, managers, and employees. It includes:

- Session authentication with inactivity timeout, password hashing, CSRF tokens, and role authorization.
- Role-aware dashboards and task queues.
- Manager task creation, employee assignment, status updates, task detail views, and comments.
- Administrator user activation, user creation, department management, and task CSV reports.
- A normalized MySQL schema for tasks, comments, files, notifications, audit logs, password resets, settings, reports, and sessions.

## XAMPP setup

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Import [`databases/schema.sql`](databases/schema.sql) in phpMyAdmin, or run it with the MySQL client.
3. Confirm the defaults in [`config/db.php`](config/db.php), or set `ETMS_DB_HOST`, `ETMS_DB_NAME`, `ETMS_DB_USER`, and `ETMS_DB_PASSWORD` in the Apache environment.
4. Create the first administrator from the project root:

   `C:\xampp\php\php.exe databases\seed_admin.php admin@example.com StrongPass1`

5. Open `http://localhost/tms/`.

The database connection uses PDO native prepared statements. Upload storage should be configured outside the public web root before enabling file attachments; the schema is ready for that module.