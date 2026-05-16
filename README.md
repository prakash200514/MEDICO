# 💊 Medico — Online Medicine & Pharmacy Store

> A full-featured PHP/MySQL-based online pharmacy platform with product browsing, prescription uploads, cart management, order processing, and an admin dashboard.
## 📋 Table of Contents

- [About the Project](#about-the-project)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Database Setup](#database-setup)
  - [Configuration](#configuration)
- [Usage](#usage)
  - [Customer Features](#customer-features)
  - [Admin Features](#admin-features)
- [Product Categories](#product-categories)
- [File Reference](#file-reference)
- [Contributing](#contributing)
- [License](#license)


## 📖 About the Project

**Medico** is a PHP-based online pharmacy and medicine e-commerce platform. It allows customers to browse medicines across multiple categories, upload prescriptions, manage a shopping cart, and complete purchases. Administrators can manage products, view orders, and handle prescriptions through a dedicated dashboard.

## ✨ Features

### 🛍️ Customer-Facing
- **User Registration & Login** — Secure account creation and session management
- **Product Browsing** — Browse medicines by category with search functionality
- **Shopping Cart** — Add/remove items, view totals with AJAX support
- **Prescription Upload** — Upload and manage medical prescriptions
- **Checkout & Payment** — Complete order flow with payment gateway integration
- **Order History** — View past orders and order details
- **Product Reviews & Ratings** — Star rating system with review submission
- **PDF Generation** — Downloadable prescription/order documents
- **Order Success Receipt** — Detailed order confirmation page

### 🔐 Admin Panel
- **Admin Login** — Secure admin authentication
- **Dashboard** — Overview of all orders and store activity
- **Product Management** — Add, edit, and manage products across all categories
- **Prescription Management** — View and manage uploaded customer prescriptions
- **Category-Specific Add Pages** — Dedicated forms for Tablets, Injections, Baby Products, and Veterinary items

---
## 🛠️ Tech Stack

| Layer        | Technology            |
|--------------|-----------------------|
| Backend      | PHP 7.4+              |
| Database     | MySQL / MariaDB       |
| Frontend     | HTML5, CSS3, JavaScript |
| Styling      | Custom CSS, Responsive CSS |
| Icons        | Font Awesome          |
| Server       | Apache (XAMPP)        |
| PDF          | PHP (generate_pdf.php) |

## 🛠️ Tech Stack

| Layer        | Technology            |
|--------------|-----------------------|
| Backend      | PHP 7.4+              |
| Database     | MySQL / MariaDB       |
| Frontend     | HTML5, CSS3, JavaScript |
| Styling      | Custom CSS, Responsive CSS |
| Icons        | Font Awesome          |
| Server       | Apache (XAMPP)        |
| PDF          | PHP (generate_pdf.php) |

---

## 📁 Project Structure

```
medicine/
├── index.php                   # Homepage / Landing page
├── login.php                   # User login
├── signup.php                  # User registration
├── logout.php                  # User logout
│
├── products.php                # All products listing
├── search.php                  # Product search
├── cart.php                    # Shopping cart
├── checkout.php                # Checkout & payment flow
├── payment_gateway.php         # Payment processing
├── order_success.php           # Order confirmation page
├── order_history.php           # Customer order history
│
├── prescription_upload.php     # Upload prescriptions
├── download_prescription.php   # Download prescription files
│
├── submit_review.php           # Submit product review
├── submit_review_ajax.php      # AJAX review submission
├── get_reviews.php             # Fetch product reviews
│
├── baby_products.php           # Baby products category
├── injections.php              # Injections category
├── veterinary.php              # Veterinary products category
│
├── header.php                  # Shared site header/nav
├── footer.php                  # Shared site footer
│
├── db.php                      # Database connection
├── database.sql                # Core database schema
│
├── admin_login.php             # Admin login page
├── admin_logout.php            # Admin logout
├── admin_dashboard.php         # Admin dashboard
├── admin_add_product.php       # Add general product
├── admin_add_baby.php          # Add baby product
├── admin_add_injection.php     # Add injection product
├── admin_add_veterinary.php    # Add veterinary product
├── admin_edit_product.php      # Edit existing product
├── admin_prescriptions.php     # Manage prescriptions
│
├── generate_pdf.php            # PDF generation
├── add_to_cart_ajax.php        # AJAX cart operations
│
├── style.css                   # Main stylesheet
├── responsive.css              # Responsive / mobile styles
│
├── css/                        # Additional CSS files
├── img/                        # Product and site images
├── uploads/                    # User-uploaded prescriptions
└── sounds/                     # Audio assets
```

## 🗄️ Database Schema

The project uses a database named `medicine_store` with the following core tables:

### `users`
| Column   | Type          | Description           |
|----------|---------------|-----------------------|
| id       | INT (PK, AI)  | Unique user ID        |
| name     | VARCHAR(50)   | User's full name      |
| email    | VARCHAR(100)  | User email address    |
| password | VARCHAR(100)  | Hashed password       |

### `products`
| Column   | Type           | Description           |
|----------|----------------|-----------------------|
| id       | INT (PK, AI)   | Unique product ID     |
| name     | VARCHAR(100)   | Product name          |
| price    | DECIMAL(10,2)  | Product price (₹)     |
| category | VARCHAR(50)    | Product category      |
| stock    | INT            | Available stock       |
| image    | VARCHAR(100)   | Image file path       |

### `prescriptions`
| Column      | Type          | Description                  |
|-------------|---------------|------------------------------|
| id          | INT (PK, AI)  | Prescription ID              |
| user_email  | VARCHAR(100)  | Uploading user's email       |
| file_path   | VARCHAR(255)  | Path to uploaded file        |
| uploaded_at | TIMESTAMP     | Upload timestamp             |

### `orders`
| Column         | Type           | Description               |
|----------------|----------------|---------------------------|
| id             | INT (PK, AI)   | Order ID                  |
| user_email     | VARCHAR(100)   | Ordering user's email     |
| product_id     | INT            | Ordered product ID        |
| quantity       | INT            | Quantity ordered          |
| total_price    | DECIMAL(10,2)  | Total order price (₹)     |
| payment_method | VARCHAR(50)    | e.g., COD, UPI, Card      |
| order_time     | TIMESTAMP      | Order placement timestamp |

> Additional tables for `reviews` are created via `create_reviews_table.sql`.

---
