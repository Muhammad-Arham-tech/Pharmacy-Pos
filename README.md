# Med-Quick - Secure Pharmacy POS

This is the initial project skeleton for "Med-Quick", a secure, enterprise-grade Pharmacy Point-of-Sale system. This document outlines the project structure and provides guidance on how to set up and run the application.

## Project Structure

```
.
├── api/
│   ├── process_checkout.php  # Handles the final checkout logic.
│   └── search_medicine.php   # API endpoint for real-time medicine search.
├── css/
│   └── style.css             # Main stylesheet (Dark Theme, Glassmorphism).
├── js/
│   └── app.js                # Core frontend logic (AJAX, POS functions, etc.).
├── modules/
│   ├── bank.php              # Placeholders for all sidebar modules...
│   ├── categories.php
│   ├── dashboard.php
│   ├── logs.php
│   ├── manufacturers.php
│   ├── medicines.php
│   ├── pos.php
│   ├── purchases.php
│   ├── reports.php
│   ├── sales.php
│   ├── settings.php
│   ├── stock.php
│   ├── suppliers.php
│   └── users.php
├── php/
│   └── SecurityHelper.php    # Class for AES-256-GCM encryption and token generation.
├── index.php                 # Main application layout and entry point (secured).
├── login.php                 # The application login page.
├── login_handler.php         # Processes login credentials.
├── logout.php                # Destroys the user session.
└── schema.sql                # The complete MariaDB database schema.
```

## Features Implemented

1.  **Database Schema (`schema.sql`):** A comprehensive, normalized MariaDB schema has been created. It includes tables for users (with RBAC), medicines, stock batches, sales, security logs, and more.

2.  **Authentication:** A complete login/logout system is in place.
    *   `index.php` is now secured and will redirect to `login.php` if no active session is found.
    *   **Demo Credentials**: Use `username: admin` and `password: password` to log in.

3.  **Visual Identity (`css/style.css`):** A professional dark theme has been implemented with a glassmorphism header, custom scrollbars, and emerald green accents.

4.  **Master Layout (`index.php`):** A responsive master page features a full sidebar navigation menu. All navigation links now load their respective (placeholder) modules into the main content area via AJAX, ensuring a zero-page-reload user experience.

5.  **Functional POS Engine (`js/app.js`):** The core frontend logic includes:
    *   An AJAX module loader.
    *   Functions for cart management (`addToCart`, `updateQty`, `calculateTotals`).
    *   A real-time search that calls the backend API.
    *   A 2-minute inactivity timer for automatic logout.
    *   A `processCheckout` function to send sale data to the backend.

6.  **Security Foundation (`php/SecurityHelper.php`):** A robust security class provides AES-256-GCM encryption/decryption methods and a function to generate idempotency tokens for transactions.

## How to Run the Application

1.  **Set up the Database:**
    *   Create a new MariaDB database named `med_quick_pos`.
    *   Import the `schema.sql` file to create all the necessary tables.
    *   Populate the tables with some sample data to test the POS functionalities.

2.  **Configure Environment:**
    *   **Crucially**, you must define the `ENCRYPTION_KEY` in your PHP environment. The `php/SecurityHelper.php` file expects a 32-byte key. You can create a `config.php` file and include it where needed.
        ```php
        // config.php
        define('ENCRYPTION_KEY', 'your-super-secret-32-byte-key-here'); // Replace with a real, randomly generated key.
        ```
    *   Create a `db_connect.php` file to handle your database connection and include it in the API scripts.

3.  **Run the Application:**
    *   Place the entire project folder in the web root of your local server (e.g., `C:\wamp64\www\pos4`).
    *   Navigate to `http://localhost/pos4/` in your web browser. You will be redirected to the login page.

