# Monochrome Personal Portfolio Website

A premium, single-page personal portfolio website with a secure administrative dashboard. Built using clean **PHP (PDO & OOP)**, **MySQL**, **HTML5**, **Vanilla CSS**, and **JavaScript**.

---

## 🚀 Getting Started

### 1. Local Development (XAMPP / MAMP / WAMP)
This project is equipped with an **auto-install database script**. You do not need to manually configure tables to get started:
1. Copy or clone this repository into your web server directory (e.g., `C:/xampp/htdocs/cutomer_portfolio`).
2. Make sure Apache and MySQL are running in your local control panel (like XAMPP).
3. Access the site in your browser at `http://localhost/cutomer_portfolio/portfolio/`.
4. The database (`portfolio`), all required tables, and default seed data will be **created automatically** on your first page load.

> [!NOTE]
> The default admin login credentials are:
> * **Username**: `admin`
> * **Password**: `admin123`
> *(Please change the password immediately after logging into the admin portal).*

---

## 🌐 Deploying or Migrating to a New Domain

If you want to move your portfolio to another domain or live host (like InfinityFree, Hostinger, GoDaddy, etc.), follow these steps:

### Step 1: Upload Files
Upload all files inside the `portfolio/` directory to the public folder of your new host (usually named `public_html` or `htdocs`).

### Step 2: Configure the Database Connection
Open `portfolio/config/database.php` and edit the production configuration inside the `else` block (lines 14–19):

```php
} else {
    // Production Environment (Change these for your new host)
    define('DB_HOST', 'YOUR_NEW_DATABASE_HOST');     // e.g. sql103.infinityfree.com or localhost
    define('DB_USER', 'YOUR_NEW_DATABASE_USERNAME'); // Your new hosting DB user
    define('DB_PASS', 'YOUR_NEW_DATABASE_PASSWORD'); // Your new hosting DB password
    define('DB_NAME', 'YOUR_NEW_DATABASE_NAME');     // Your new hosting DB name
}
```

### Step 3: Setup Database Schema
You have two options to set up the database tables on your new host:

#### Option A: Automatic Setup (Recommended)
1. Log in to your new host's Control Panel (e.g., VistaPanel/cPanel).
2. Go to **MySQL Databases** and create a new empty database.
3. Update `portfolio/config/database.php` with the new credentials (from Step 2).
4. Simply visit your new website domain in a browser. The script will automatically populate the database with all tables and default seeds.

#### Option B: Manual Import via phpMyAdmin
1. Select your newly created empty database in phpMyAdmin.
2. Go to the **Import** tab.
3. Choose the `portfolio/schema.sql` file from your project folder.
4. Click **Import** (or **Go**).
   > [!IMPORTANT]
   > The database creation commands have been commented out in `schema.sql` to prevent permission errors on shared hosting. Just select the database in phpMyAdmin and click import.

---

## 📁 Database Schema Details (`schema.sql`)
The portfolio uses the following tables:
* `admins`: Security-hardened admin credentials.
* `settings`: Dynamic key-value site configurations (e.g., hero tags, bios, layout controls).
* `projects`: Custom projects (titles, tags, descriptions, photos, and links).
* `skills`: Tech-stack expertise percentages and modal descriptions.
* `hobbies`: Custom hobbies with clean modern SVG icons.
* `contact_info` & `social_links`: Customizable footer & contact section configurations.
* `messages`: Receives AJAX messages sent through the contact form.

---

## 🛠️ Tech Stack & Architecture
* **Frontend**: Vanilla HTML5, responsive CSS Grid/Flexbox design, Glassmorphic components.
* **Backend**: Pure PHP utilizing modern secure PDO prepared statements to safeguard against SQL Injection.
* **Database**: MySQL.
* **Security**: Session-based login protection, cross-site scripting (XSS) input sanitization.
