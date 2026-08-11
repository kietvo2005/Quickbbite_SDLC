# 🍔 QuickBbite SDLC – Food Delivery System

<p align="center">
  <strong>PHP/MySQL Food Delivery Web Application</strong>
</p>

<p align="center">
  Academic Software Development Project
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL%2FMariaDB-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/Apache-XAMPP-D22128?style=for-the-badge&logo=apache&logoColor=white" alt="Apache">
</p>

---

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Project Objectives](#-project-objectives)
- [User Roles](#-user-roles)
- [Technologies Used](#️-technologies-used)
- [Project Structure](#-project-structure)
- [Database](#️-database)
- [Database Tables](#-database-tables)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Default Test Accounts](#-default-test-accounts)
- [Admin Access](#-admin-access)
- [Database Configuration](#️-database-configuration)
- [Database Reset](#-database-reset)
- [Main Features](#-main-features)
- [Security Features](#-security-features)
- [Testing](#-testing)
- [Local Development](#️-local-development)
- [Academic Project Scope](#-academic-project-scope)
- [Project Status](#-project-status)
- [Git Workflow](#-git-workflow)
- [Important Notes](#️-important-notes)

---

# 📌 Project Overview

**QuickBbite SDLC** is a PHP/MySQL-based **Food Delivery System** developed as an academic software development project.

The application demonstrates a food delivery workflow that allows customers to browse restaurants and food items, manage their shopping cart, place orders and manage their account.

The system also provides an administrator area for managing application data and monitoring system activity.

The application uses a relational database named:

```text
food_delivery
```

---

# 🎯 Project Objectives

The main objectives of the QuickBbite SDLC project are to demonstrate the implementation of a web-based Food Delivery System using PHP and MySQL/MariaDB.

The project focuses on:

- Requirements implementation
- Web application development
- Relational database design
- User authentication
- Role-based access control
- CRUD operations
- Food and restaurant management
- Shopping cart functionality
- Order management
- Customer account management
- Database integration
- Input validation
- Security considerations
- Application testing

---

# 👥 User Roles

The system contains two main user roles.

## 👤 Customer

Customers can:

- Register an account
- Log in and log out
- Browse food categories
- Browse restaurants
- Browse food items
- View food details
- Search for food
- Filter food items
- Add food to the shopping cart
- Manage cart items
- Place orders
- View order information
- Manage delivery addresses
- Submit reviews
- Manage wishlist items
- Contact the system

---

## 👨‍💼 Administrator

The administrator can access the administration area and manage system information.

Administrator functions include:

- Dashboard
- User management
- Restaurant management
- Category management
- Food management
- Order management
- Contact message management
- Administrative CRUD operations
- Order and system information

---

# 🛠️ Technologies Used

| Technology | Purpose |
|---|---|
| **PHP** | Server-side application logic |
| **MySQL / MariaDB** | Relational database |
| **HTML5** | Web page structure |
| **CSS3** | Styling and layout |
| **JavaScript** | Client-side interactions and validation |
| **Bootstrap 5** | Responsive UI components |
| **PDO** | PHP database access |
| **Apache** | Local web server |
| **XAMPP** | Local development environment |
| **phpMyAdmin** | Database administration |

---

# 📂 Project Structure

The main project structure is:

```text
Quickbbite_SDLC/
│
├── admin/
│
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
│
├── database/
│   ├── migrations/
│   ├── migration_admin_system.sql
│   ├── schema.sql
│   ├── seed_data.sql
│   ├── validate_counts.php
│   └── validate_seed.php
│
├── includes/
│   ├── config/
│   ├── database/
│   ├── functions/
│   └── lang/
│
├── pages/
│   └── admin/
│
├── uploads/
│
├── index.php
├── login.php
├── register.php
├── logout.php
├── restaurant.php
├── food-detail.php
├── invoice.php
├── contact.php
├── install.php
└── README.md
```

---

# 🗄️ Database

The application uses **MySQL/MariaDB** as its relational database management system.

Database name:

```text
food_delivery
```

The main database schema is stored in:

```text
database/schema.sql
```

Additional database files are stored in:

```text
database/
```

including:

```text
database/
├── migrations/
├── migration_admin_system.sql
├── schema.sql
├── seed_data.sql
├── validate_counts.php
└── validate_seed.php
```

---

# 📊 Database Tables

The database contains tables supporting the main functionality of the Food Delivery System.

| Table | Purpose |
|---|---|
| `users` | Stores customer and administrator accounts |
| `admins` | Stores administrator profile information |
| `addresses` | Stores customer delivery addresses |
| `auth_tokens` | Stores authentication token information |
| `cart` | Stores shopping cart information |
| `categories` | Stores food categories |
| `restaurants` | Stores restaurant information |
| `foods` | Stores food items |
| `orders` | Stores customer orders |
| `order_items` | Stores individual items belonging to orders |
| `payments` | Stores payment information |
| `reviews` | Stores customer reviews |
| `wishlist` | Stores customer wishlist information |
| `contact_messages` | Stores messages submitted through the contact system |
| `sepay_webhook_logs` | Stores payment/webhook log information |

---

# 💻 Requirements

Before running the application, install the following:

- XAMPP
- Apache
- MySQL or MariaDB
- PHP with PDO/MySQL support
- A modern web browser

The project is designed to run in a local XAMPP environment.

Recommended project location:

```text
C:\xampp\htdocs\Quickbbite_SDLC
```

---

# 🚀 Installation

## Step 1 – Copy the Project

Copy the project folder into the XAMPP web directory:

```text
C:\xampp\htdocs\
```

The final project path should be:

```text
C:\xampp\htdocs\Quickbbite_SDLC
```

---

## Step 2 – Start XAMPP

Open the **XAMPP Control Panel**.

Start the following services:

```text
Apache
MySQL
```

Make sure both services are running before continuing.

---

## Step 3 – Run the Installer

Open the following URL in your browser:

```text
http://localhost/Quickbbite_SDLC/install.php
```

The installer automatically performs the initial database setup.

The installer will:

1. Connect to the MySQL server.
2. Create the `food_delivery` database if it does not exist.
3. Select the `food_delivery` database.
4. Execute `database/schema.sql`.
5. Create the required database tables.
6. Seed categories.
7. Seed restaurants.
8. Seed food items.
9. Create development user accounts.
10. Seed sample historical order data.
11. Generate local placeholder image assets.

If the installation is successful, the installer displays:

```text
Installation Completed Successfully!
```

---

# 🔐 Default Test Accounts

After running the current installer, the development accounts are displayed on the installation result page.

## 👤 Customer Account

```text
Email: customer@fooddelivery.com
Password: Customer@123
```

## 👨‍💼 Administrator Account

```text
Email: admin@fooddelivery.com
Password: Admin@12345
```

These credentials are intended for **local academic/demo testing only**.

Do not use these credentials for a production deployment.

---

# 🔑 Admin Access

After completing the installation, open:

```text
http://localhost/Quickbbite_SDLC/
```

Use the administrator credentials:

```text
Email: admin@fooddelivery.com
Password: Admin@12345
```

After successful authentication, the administrator can access the administration dashboard.

The administrator dashboard provides access to the available management functions implemented in the project.

---

# 🗃️ Database Configuration

The application's database configuration is stored under:

```text
includes/config/
```

The default XAMPP database configuration is:

```text
Host: localhost
Username: root
Password:
Database: food_delivery
```

For a default XAMPP installation, the MySQL `root` account may not have a password.

If your local MySQL/MariaDB installation uses a password, update the project's database configuration accordingly.

### ⚠️ Security

Do not commit the following information to a public repository:

- Production database passwords
- API keys
- Payment credentials
- Private authentication secrets
- Production server credentials

---

# 🔄 Database Reset

For a clean academic/demo installation, the database can be recreated.

## Step 1 – Backup Required Data

Before resetting the database, back up any data that needs to be preserved.

## Step 2 – Open phpMyAdmin

Open:

```text
http://localhost/phpmyadmin/
```

## Step 3 – Select the Database

Select:

```text
food_delivery
```

## Step 4 – Remove the Existing Database

Delete the existing `food_delivery` database if a clean installation is required.

## Step 5 – Start XAMPP

Make sure:

```text
Apache
MySQL
```

are running.

## Step 6 – Run the Installer

Open:

```text
http://localhost/Quickbbite_SDLC/install.php
```

The installer will recreate the database and seed the development data.

---

# 🧹 Removing an Unwanted Administrator

If an additional administrator account exists in the local database, it can be removed through phpMyAdmin.

Open:

```text
http://localhost/phpmyadmin/
```

Select:

```text
food_delivery
```

Open the SQL interface and check all administrator accounts:

```sql
SELECT id, name, username, email, role
FROM users
WHERE role = 'admin';
```

The intended administrator account is:

```text
admin@fooddelivery.com
```

If an unwanted administrator account exists, verify the account before deleting it.

For example:

```sql
DELETE FROM users
WHERE email = 'admin@admin.com';
```

Then verify the remaining administrator accounts:

```sql
SELECT id, name, username, email, role
FROM users
WHERE role = 'admin';
```

The expected result should contain the intended administrator account:

```text
admin@fooddelivery.com
```

> ⚠️ Always verify the email address before executing a `DELETE` statement.

---

# ✨ Main Features

## 🛍️ Customer Features

| Feature | Description |
|---|---|
| Registration | Create a customer account |
| Login | Authenticate an existing user |
| Logout | End the current session |
| Food Categories | Browse food by category |
| Restaurants | Browse restaurant information |
| Food Details | View detailed food information |
| Search | Search for food items |
| Filtering | Filter available food |
| Shopping Cart | Add and manage food items |
| Orders | Place and view orders |
| Addresses | Manage delivery addresses |
| Reviews | Submit customer reviews |
| Wishlist | Save favourite food items |
| Contact | Submit contact messages |

---

## 👨‍💼 Administrator Features

| Feature | Description |
|---|---|
| Administrator Login | Secure administrator authentication |
| Dashboard | View administrative information |
| User Management | Manage registered users |
| Restaurant Management | Manage restaurants |
| Category Management | Manage food categories |
| Food Management | Manage food items |
| Order Management | Manage customer orders |
| Contact Management | Manage contact messages |
| CRUD Operations | Create, read, update and delete records |
| Statistics | Display available order/system information |

---

# 🔒 Security Features

The application includes several security-related mechanisms.

## Password Hashing

User passwords are stored using PHP password hashing functions rather than plain-text passwords.

## PDO Prepared Statements

PDO prepared statements are used for database operations to reduce the risk of SQL injection.

## Authentication

Session-based authentication is used for user login and protected functionality.

## Role-Based Access Control

Administrator functionality is restricted according to the user's role.

## Input Validation

User input is validated before being processed by the application.

---

# 🧪 Testing

The project contains database validation scripts under:

```text
database/
```

Available validation files include:

```text
database/validate_counts.php
database/validate_seed.php
```

Recommended application testing areas include:

- User registration
- User login
- User logout
- Customer navigation
- Restaurant browsing
- Food browsing
- Food details
- Search functionality
- Filtering
- Shopping cart
- Order placement
- Customer account functions
- Administrator login
- Administrator dashboard
- User CRUD operations
- Restaurant CRUD operations
- Category CRUD operations
- Food CRUD operations
- Order management
- Contact messages
- Database relationships
- Form validation
- Error handling
- Responsive interface

---

# 🖥️ Local Development

The application is designed to run using the following local development stack:

```text
XAMPP
│
├── Apache
│   └── PHP
│
└── MySQL / MariaDB
    └── food_delivery
```

Application URL:

```text
http://localhost/Quickbbite_SDLC/
```

Installer:

```text
http://localhost/Quickbbite_SDLC/install.php
```

phpMyAdmin:

```text
http://localhost/phpmyadmin/
```

---

# 🖼️ Screenshots

Screenshots can be added to the repository to demonstrate the implemented system.

Recommended screenshots include:

```text
docs/
└── screenshots/
    ├── homepage.png
    ├── restaurant.png
    ├── food-detail.png
    ├── cart.png
    ├── login.png
    ├── register.png
    └── admin-dashboard.png
```

For example:

```markdown
![Homepage](docs/screenshots/homepage.png)
```

You can also add screenshots of:

- Customer homepage
- Restaurant page
- Food details
- Shopping cart
- Login page
- Registration page
- Order page
- Administrator dashboard
- Database structure
- phpMyAdmin database tables

---

# 🎓 Academic Project Scope

QuickBbite SDLC is an academic **Food Delivery System** project.

The project demonstrates the implementation of a PHP/MySQL web application using a relational database architecture.

The repository contains:

- Application source code
- PHP pages
- CSS files
- JavaScript files
- Image assets
- Database schema
- Database seed data
- Database migration files
- Validation scripts
- Installation script
- Project documentation

The README describes the implementation contained within the project repository.

Any feature claimed in an academic report should be verified against the corresponding source code and database implementation.

---

# 📈 Project Status

| Component | Status |
|---|---|
| PHP Application | ✅ Implemented |
| MySQL/MariaDB Database | ✅ Implemented |
| Customer Authentication | ✅ Implemented |
| Administrator Authentication | ✅ Implemented |
| Food Categories | ✅ Implemented |
| Restaurant Management | ✅ Implemented |
| Food Management | ✅ Implemented |
| Shopping Cart | ✅ Implemented |
| Order Management | ✅ Implemented |
| Reviews | ✅ Implemented |
| Wishlist | ✅ Implemented |
| Contact Messages | ✅ Implemented |
| Database Seeder | ✅ Implemented |
| Installation Script | ✅ Implemented |
| Local XAMPP Environment | ✅ Supported |

---

# 🔧 Git Workflow

After modifying the project, check the current Git status:

```bash
git status
```

Add the changes:

```bash
git add .
```

Create a commit:

```bash
git commit -m "Update Food Delivery System"
```

Push the changes to GitHub:

```bash
git push origin main
```

To check the remote repository:

```bash
git remote -v
```

The project repository is:

```text
https://github.com/kietvo2005/Quickbbite_SDLC
```

---

# 📦 Recommended Git Workflow

Before pushing changes to GitHub:

### 1. Check the project

```bash
git status
```

### 2. Review modified files

```bash
git diff
```

### 3. Add files

```bash
git add .
```

### 4. Commit

```bash
git commit -m "Update project"
```

### 5. Push

```bash
git push origin main
```

### 6. Verify GitHub

Open the repository and refresh the page:

```text
https://github.com/kietvo2005/Quickbbite_SDLC
```

---

# ⚠️ Important Notes

## Development Environment

This project is designed primarily for:

```text
Local Development
Academic Demonstration
Software Testing
```

It is not presented as a production-ready commercial deployment.

---

## Installer Security

The file:

```text
install.php
```

is intended for local installation and testing.

Do not leave the installer publicly accessible on a production server.

---

## Database Safety

The installation process may create or reset development/seed data.

Do not run the installer against a database containing important production data.

Always create a database backup before performing a reset or migration.

---

## Production Deployment

Before deploying the application to a production server, additional security and configuration measures should be applied, including:

- HTTPS/SSL
- Secure session cookies
- CSRF protection
- Secure environment variables
- Production database credentials
- Database least-privilege accounts
- Production error handling
- Regular backups
- Secure payment configuration
- Removal or protection of development tools
- Removal or restriction of the installer

---

# 📚 Project Documentation

The repository contains the main source code and database resources required to demonstrate the Food Delivery System.

Important files include:

```text
README.md
install.php
database/schema.sql
database/seed_data.sql
database/migration_admin_system.sql
database/validate_counts.php
database/validate_seed.php
```

---

# 👨‍💻 Developer

**QuickBbite SDLC**

Food Delivery System

Academic Software Development Project

---

<p align="center">

### 🍔 QuickBbite SDLC

**Food Delivery System**

Built with PHP • MySQL/MariaDB • Bootstrap 5 • JavaScript

</p>

---

<p align="center">
  <sub>Academic project documentation</sub>
</p>