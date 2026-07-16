# Walkthrough

This document guides you through verifying the database, contact form, and profile photo features.

## 1. Verifying Database Connection
- Open the home page (`http://localhost/portfolio/index.php`) or the admin panel login (`http://localhost/portfolio/admin/login.php`).
- Ensure no database connection failure screen is shown.
- You should see the standard login panel or the main page correctly.

## 2. Verifying Output Buffering (Admin Redirects)
- Go to `http://localhost/portfolio/admin/login.php` and sign in.
- Click on any of the administration tabs (e.g. **Contact & Social**, **Manage Projects**, **Manage Skills**, **Site Settings**).
- Submit a change or delete an item (for example, add/remove a social link).
- The action should process smoothly and redirect you back without displaying `Warning: Cannot modify header information - headers already sent`.

## 3. Testing AJAX Contact Form
- Scroll down to the **Get In Touch** section on the main portfolio page.
- Fill out your Name, Email, and a Message.
- Click **Send Message**.
- You should see a toast popup on the bottom-right stating: *"Thank you! Your message has been sent successfully."*
- Check the Admin Dashboard to verify that the message is listed under "Received Messages".

## 4. Testing Profile Photo Upload
- Go to **Site Settings** in the Admin panel.
- Under **General Settings**, scroll down to find the **Profile Photo** section.
- Click **Browse...** or **Choose File** and select an image (JPG, PNG, GIF, or WEBP).
- You will see a live preview of the image instantly.
- Click **Save Site Settings**.
- Visit your main portfolio home page (`http://localhost/portfolio/index.php`) to see the newly updated profile photo in the "About Me" section.
