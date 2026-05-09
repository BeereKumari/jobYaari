# JobYaari Blog Management System

## Live Demo
- Frontend: [your-live-url]
- Admin Panel: [your-live-url/login.php]

## Admin Credentials
- **Username:** admin
- **Password:** Admin@123

## Tech Stack
- PHP 8+ (Core)
- MySQL 8+
- HTML5, CSS3
- jQuery 3.x (AJAX filtering only)

## Features
- Blog listing with AJAX category and date filtering (no page reload)
- Blog detail pages with SEO meta tags and Open Graph support
- Full-text search functionality
- Admin CRUD for blogs (add, edit, delete) with image upload
- Category management (add, delete)
- Secure admin login with bcrypt passwords and CSRF protection
- File upload with MIME type validation and image resizing
- Fully responsive design (mobile, tablet, desktop)
- PHP-based pagination
- Session-based authentication with brute force protection

## Folder Structure
```
jobyaari-blog/
|-- index.php                 # Blog listing page
|-- blog-detail.php           # Single blog post
|-- search.php                # Search results
|-- login.php                 # Admin login
|-- logout.php                # Session destroy
|-- admin/
|   |-- index.php             # Dashboard
|   |-- blogs/
|   |   |-- index.php         # List blogs
|   |   |-- add.php           # Add blog
|   |   |-- edit.php          # Edit blog
|   |   |-- delete.php        # Delete blog
|   |-- categories/
|   |   |-- index.php         # List categories
|   |   |-- add.php           # Add category
|   |   |-- delete.php        # Delete category
|   |-- profile.php           # Admin profile
|-- ajax/
|   |-- filter-blogs.php      # AJAX filter endpoint
|   |-- search-blogs.php      # AJAX search endpoint
|-- includes/
|   |-- db.php                # PDO database connection
|   |-- auth.php              # Session helpers
|   |-- functions.php         # Helper functions
|   |-- header.php            # Public header
|   |-- footer.php            # Public footer
|   |-- admin-header.php      # Admin header
|   |-- admin-footer.php      # Admin footer
|-- assets/
|   |-- css/
|   |   |-- style.css         # Public styles
|   |   |-- admin.css         # Admin styles
|   |   |-- responsive.css    # Media queries
|   |-- js/
|   |   |-- filter.js         # jQuery AJAX logic
|   |-- images/               # Static images
|   |-- uploads/blogs/        # Uploaded blog images
|-- sql/
|   |-- database.sql          # DB schema and seed data
|-- .htaccess                 # Apache config
|-- README.md
```

## Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd jobyaari-blog
   ```

2. **Import the database**
   - Create a MySQL database named `jobyaari_blog`
   - Import `sql/database.sql` into the database
   ```bash
   mysql -u root -p jobyaari_blog < sql/database.sql
   ```

3. **Configure database connection**
   - Edit `includes/db.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'jobyaari_blog');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set folder permissions**
   - Make `assets/uploads/blogs/` writable:
   ```bash
   chmod 755 assets/uploads/blogs/
   ```

5. **Run locally**
   ```bash
   php -S localhost:8000
   ```

6. **Visit the site**
   - Frontend: `http://localhost:8000`
   - Admin: `http://localhost:8000/login.php`

## Deployment

- **Recommended hosting:** InfinityFree, 000webhost, or any PHP/MySQL host
- Upload all files via FTP or Git
- Ensure PHP 8+ and MySQL 5.7+ are available
- Configure `.htaccess` if needed for your server

## Security Features

- PDO prepared statements (100% SQL injection prevention)
- `htmlspecialchars()` on all output (XSS prevention)
- CSRF tokens on all POST forms
- Session regeneration on login
- Brute force protection (5 min lockout after 5 failed attempts)
- File upload validation (MIME type, size, extension whitelist)
- Passwords stored with bcrypt hashing
- Admin routes protected by session checks

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
