# Modern Online Male Boutique Store (Omoja Male Boutique)

An ultra-modern, African-inspired web-based e-commerce boutique store built with **Pure PHP (OOP)** and **PDO**, tailored for male fashion (Kaftans, Agbadas, Dashikis, Suits, and Accessories).

Inspired by modern African boutique brands like **Omoja.shop**, this application features large product displays, gold & emerald green royal styling, a mobile-first user experience, an interactive Admin Panel, and a direct **WhatsApp Checkout Process** that redirects customer orders directly to the store manager.

---

## 🌟 Key Features

### Customer Storefront
- **Modern African Aesthetics:** Emerald green (`#004d25`) and metallic gold (`#c5a059`) styling, Playfair Display serif typography, and responsive bottom mobile navigation.
- **Hero Carousel Banner:** Dynamic Swiper.js slider with custom call-to-actions.
- **Catalog & Filtering:** Filter garments by Category, Size (S, M, L, XL, XXL), Price range, and Color, plus live search and sorting.
- **Product Details Page:** Multi-image gallery, interactive size selector, tailoring size guide modal, related products, and recently viewed garments.
- **Shopping Cart & Wishlist:** Real-time quantity updates, persistent session bag, and item management.
- **WhatsApp Direct Checkout:** Collects customer name, contact phone, and delivery address, creates a record in the database, and automatically redirects to WhatsApp with a formatted order summary.
- **User Portal:** Customer registration, login, profile management, and order history tracking.
- **Interactive Security:** CAPTCHA verification on forms, token-based password reset, and rate-limited actions.

### Admin Panel
- **Secure Access Control:** Dedicated admin login (`/admin/login.php`) protected with Role-Based Access Control (RBAC).
- **Dashboard & Analytics:** Metric cards (Total Revenue, Orders, Products, Customers) and Chart.js monthly sales trend analytics.
- **Product Management:** Complete CRUD operations with secure multi-image uploader (JPG, PNG, WebP with type/size validation).
- **Category & Hero Banner Managers:** Add and edit boutique collections and front page sliders.
- **Order Management:** View WhatsApp orders, review customer tailoring notes, and update delivery statuses (Pending, Processing, Completed, Cancelled).
- **Customer Locker:** View customer accounts and toggle active/locked status.
- **Security Audit Logs:** Comprehensive audit trail recording administrative actions, logins, and IP addresses.
- **Store Settings:** Configure WhatsApp store phone numbers, currency symbols (`GH¢`), and contact details.

---

## 🔒 Mandatory Security Features Implemented

1. **Password Hashing:** `password_hash()` with `PASSWORD_BCRYPT` and `password_verify()`.
2. **Strong Password Policy:** Enforces minimum 8 characters, uppercase, lowercase, number, and special character.
3. **Account Lockout:** Locks accounts after 5 consecutive failed login attempts.
4. **Token-Based Password Reset:** Secure 64-character token expiration system (`password_resets` table).
5. **Session Security:** `session_regenerate_id()`, `HttpOnly`, `SameSite=Lax`, and `Secure` cookie parameters.
6. **Idle Session Expiration:** Automatic logout after 30 minutes of inactivity.
7. **SQL Injection Protection:** 100% PDO prepared statements across all database interactions.
8. **XSS Protection:** Contextual output escaping helper on all user input displays.
9. **CSRF Protection:** Token generation and strict form validation (`verify_csrf_token()`).
10. **Clickjacking & Security Headers:** `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Content-Security-Policy`, and `Referrer-Policy`.
11. **Input Sanitization & Validation:** Strict trimming, email validation, and type casting.
12. **File Upload Security:** File extension and MIME type verification (`finfo_file`), file renaming using cryptographic random bytes, and size limits (5MB).
13. **Role-Based Access Control (RBAC):** Admin routes strictly verified against session roles.
14. **Custom Security CAPTCHA:** Inline SVG CAPTCHA generator (`includes/captcha.php`).
15. **Audit Logging:** System actions logged in `audit_logs` table.

---

## 🛠️ Technology Stack

- **Backend:** Pure PHP 8.x (OOP Architecture) + PDO
- **Frontend:** HTML5, CSS3, JavaScript (ES6)
- **CSS Framework:** Tailwind CSS CDN + Custom CSS (`assets/css/style.css`)
- **Libraries & Icons:** Font Awesome 6, Swiper.js, AOS Animation, Chart.js
- **Database:** MySQL / MariaDB (XAMPP compatible)

---

## 🚀 Installation & Setup Guide (XAMPP / Apache / Nginx)

1. **Clone or Copy Repository:**
   Place the project files into your XAMPP `htdocs` directory (e.g., `C:/xampp/htdocs/omoja/`).

2. **Database Import:**
   - Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Create a database named `boutique_db`.
   - Import `database.sql` into `boutique_db`.

3. **Database Configuration:**
   - Open `config/database.php` and verify the host, database name, username, and password:
     ```php
     private $host = '127.0.0.1';
     private $db   = 'boutique_db';
     private $user = 'root';
     private $pass = '';
     ```

4. **Launch Application:**
   - Customer Storefront: `http://localhost/omoja/index.php`
   - Admin Panel Portal: `http://localhost/omoja/admin/login.php`

---

## 🔑 Default Administrator Credentials

- **Admin Login URL:** `http://localhost/omoja/admin/login.php`
- **Email:** `admin@omoja.shop`
- **Password:** `AdminPassword123!`

---

## 🧪 Running Automated Unit & Functional Tests

Run the built-in CLI test suite:
```bash
php tests/test_suite.php
```
This tests PDO database initialization, user registration/login, category/product CRUD, shopping cart totals, and WhatsApp order creation.
