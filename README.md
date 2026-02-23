# ITAM System - IT Asset Management

A comprehensive IT Asset Management System built for P-line Company, Vientiane, Laos.

## Features

- **User Authentication**: Secure login with role-based access (Admin/User)
- **Asset Management**: Create, read, update, delete IT assets with auto-generated codes
- **Check-In/Check-Out**: Track asset assignments with full audit trail
- **User Management**: Admin can manage users with proper validation
- **Reports**: Generate various reports (PDF/Excel export ready)
- **Dashboard**: Visual statistics with charts and recent activity
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Glassmorphism UI**: Modern, elegant interface design

## Technology Stack

- **Backend**: PHP 7.4+ (8.x recommended)
- **Database**: MySQL 5.7+ / MariaDB 10.4+
- **Frontend**: Bootstrap 5.3.8, Chart.js, Bootstrap Icons
- **Architecture**: MVC pattern with PDO database layer
- **Security**: CSRF protection, XSS prevention, password hashing, prepared statements

## Installation

### 1. Database Setup

1. Create a MySQL database named `itam_system`
2. Import the SQL file: `sql/schema.sql` (or use the provided SQL dump)
3. Update database credentials in `config/database.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'itam_system');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 2. Web Server Configuration

**Apache:**
- Ensure `mod_rewrite` is enabled
- Place files in web root (e.g., `htdocs/itam-system/`)
- The `.htaccess` file handles URL rewriting

**Nginx:**
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/itam-system;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. File Permissions

Ensure these directories are writable by the web server:
```bash
chmod 755 public/uploads/assets/
chmod 755 public/uploads/
```

### 4. Default Login Credentials

**Admin Account:**
- Email: admin@pline.com
- Password: password

**User Account:**
- Email: user@pline.com  
- Password: password

> **Note:** Change default passwords immediately after installation!

## Directory Structure

```
itam-system/
├── config/             # Configuration files
│   ├── config.php      # App configuration
│   └── database.php    # Database connection
├── controllers/        # Business logic controllers
│   ├── AuthController.php
│   ├── AssetController.php
│   ├── UserController.php
│   └── DashboardController.php
├── models/             # Data models (MVC)
│   ├── Model.php       # Base model
│   ├── User.php
│   ├── Asset.php
│   └── CheckLog.php
├── views/              # View templates
│   ├── layouts/        # Shared layouts
│   ├── auth/           # Login/logout
│   ├── admin/          # Admin pages
│   └── user/           # User pages
├── public/             # Public assets
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── uploads/
├── helpers/            # Helper functions
├── sql/                # Database schemas
├── .htaccess           # Apache config
├── index.php           # Front controller
└── README.md
```

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **XSS Prevention**: Output escaping with `htmlspecialchars()`
- **SQL Injection Prevention**: PDO prepared statements throughout
- **Password Security**: Bcrypt hashing with `password_hash()`
- **Session Security**: Secure session settings
- **HTTP Headers**: Security headers (X-Frame-Options, X-XSS-Protection, etc.)
- **Input Validation**: Server-side validation on all inputs

## API Endpoints (Optional AJAX)

The system is structured to easily support AJAX endpoints. Add to `api/` directory:

```php
// Example: api/assets.php
require_once '../config/config.php';
require_once '../controllers/AssetController.php';

header('Content-Type: application/json');

$controller = new AssetController();
$assets = $controller->getAssets($_GET);

echo json_encode(['success' => true, 'data' => $assets]);
```

## Customization

### Changing Colors
Edit CSS variables in `public/assets/css/style.css`:

```css
:root {
    --color-primary: #2563EB;
    --color-secondary: #7C3AED;
    --color-success: #10B981;
    --color-warning: #F59E0B;
    --color-error: #EF4444;
}
```

### Adding New Asset Categories
Edit the category dropdown in `views/admin/assets.php`:

```php
<select name="category" class="form-select">
    <option value="Computer">Computer</option>
    <option value="Phone">Phone</option>
    <option value="Printer">Printer</option>
    <option value="Accessory">Accessory</option>
    <!-- Add new categories here -->
</select>
```

## Troubleshooting

### Database Connection Error
- Check `config/database.php` credentials
- Ensure MySQL/MariaDB is running
- Verify database `itam_system` exists

### 404 Errors
- Ensure `.htaccess` is present and Apache `mod_rewrite` is enabled
- Check that files are in correct directory
- Verify `AllowOverride All` in Apache virtual host config

### Permission Denied
- Set correct permissions on `public/uploads/` directory
- Ensure web server user (www-data/apache) has read/write access

### Session Issues
- Check PHP session settings in `php.ini`
- Ensure `session.save_path` is writable
- Clear browser cookies/cache

## Development

### Adding a New Page

1. Create view file in `views/admin/` or `views/user/`
2. Add route to sidebar in `views/layouts/sidebar.php`
3. Create controller method if needed
4. Add any database methods to appropriate model

### Database Migrations

For schema updates, create new SQL files in `sql/`:
```sql
-- sql/migration_002.sql
ALTER TABLE assets ADD COLUMN warranty_date DATE NULL AFTER purchase_date;
```

## License

Copyright 2026 P-line Company, Vientiane, Laos.
All rights reserved.

## Support

For technical support or feature requests, contact the development team.

---

**Version:** 1.0.1  
**Last Updated:** January 2026  
**Author:** Saimond
