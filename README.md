QuickBbite SDLC – Food Delivery System

1. Project Overview

QuickBbite SDLC is a PHP/MySQL food delivery web application developed as a demonstration project for an SDLC-based academic project.

The system provides two main user roles:

Customer – browse food, manage cart, place orders and view order information.

Administrator – manage restaurants, categories, food items, users, orders and contact messages through the admin dashboard.

The application uses a relational MySQL database named:

food_delivery

2. Technologies Used

PHP – server-side application logic

MySQL / MariaDB – relational database

HTML5 – page structure

CSS3 – styling and responsive layout

JavaScript – client-side interactions and validation

Bootstrap 5 – responsive UI components

PDO – PHP database access

Apache – local web server through XAMPP

phpMyAdmin – database administration

3. Project Structure

Important project directories and files include:

Quickbbite_SDLC/
│
├── admin/
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
│   └── validation files
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

4. Database

The application uses the MySQL/MariaDB database:

food_delivery

The database contains the main tables required by the food delivery system, including:

users

admins

addresses

auth_tokens

cart

categories

restaurants

foods

orders

order_items

payments

reviews

wishlist

contact_messages

sepay_webhook_logs

The database schema is stored in:

database/schema.sql

Additional database setup/migration files are located in:

database/migrations/
database/migration_admin_system.sql
database/seed_data.sql

5. Requirements

Before running the project, install:

XAMPP

Apache

MySQL or MariaDB

A modern web browser

PHP must have PDO/MySQL support enabled.

The project can be placed inside the XAMPP web directory:

C:\xampp\htdocs\Quickbbite_SDLC

6. Installation

Step 1 – Copy the project

Copy the project folder into:

C:\xampp\htdocs\

The final path should be:

C:\xampp\htdocs\Quickbbite_SDLC

Step 2 – Start XAMPP

Open XAMPP Control Panel and start:

Apache
MySQL

Step 3 – Run the installer

Open:

http://localhost/Quickbbite_SDLC/install.php

The installer will:

Connect to MySQL.

Create the food_delivery database if it does not already exist.

Execute database/schema.sql.

Seed categories, restaurants and food data.

Create the default customer/admin accounts configured by the installer.

Generate local placeholder image assets.

When the installation is successful, the page displays:

Installation Completed Successfully!

Important

The installer is intended for local development/testing.

Because the installer resets user-related seed data, do not repeatedly run install.php on a database containing real application data.

7. Default Test Accounts

After running the current installer, use the credentials displayed on the installation result page.

Customer

Email: customer@fooddelivery.com
Password: Customer@123

Administrator

Email: admin@fooddelivery.com
Password: Admin@12345

The administrator account is used to access the admin dashboard.

Do not publish real production passwords in a public GitHub repository. The credentials above are development/demo credentials only.

8. Admin Access

Open the application:

http://localhost/Quickbbite_SDLC/

Log in using the administrator account:

Email: admin@fooddelivery.com
Password: Admin@12345

After successful authentication, the administrator can access the admin dashboard and manage system data.

9. Database Configuration

Database connection settings are stored in the project's configuration files under:

includes/config/

For a default XAMPP installation, the local MySQL configuration is normally:

Host: localhost
Username: root
Password: 
Database: food_delivery

If the local MySQL installation uses a password, update the configuration accordingly.

Do not commit production database credentials to GitHub.

10. Database Reset / Reinstallation

For a clean academic/demo installation, the recommended process is:

Back up any required data.

Remove the existing food_delivery database in phpMyAdmin.

Create/start MySQL through XAMPP.

Open:

http://localhost/Quickbbite_SDLC/install.php

Allow the installer to recreate the database and seed the demo data.

11. Removing an Unwanted Admin Account

If an extra administrator account exists in the local database, it can be removed from phpMyAdmin.

Open:

http://localhost/phpmyadmin

Select:

food_delivery

Then open the users table.

To identify administrator accounts, run:

SELECT id, name, username, email, role
FROM users
WHERE role = 'admin';

Before deleting an account, verify that it is the unwanted account.

For example:

DELETE FROM users
WHERE email = 'admin@admin.com';

If the corresponding record exists in the admins table, remove that profile first or use the appropriate foreign-key-safe deletion method for the current schema.

Verify the result:

SELECT id, name, username, email, role
FROM users
WHERE role = 'admin';

The final database should contain only the intended administrator account.

12. Main System Functions

Customer Functions

User registration

User login/logout

Browse food categories

Browse restaurants

Browse food items

View food details

Search/filter food

Add items to cart

Manage cart

Place orders

View order information

Manage profile/address information

Submit reviews

Manage wishlist

Contact the system

Administrator Functions

Administrator authentication

Dashboard

User management

Restaurant management

Category management

Food management

Order management

Contact message management

Administrative CRUD operations

Dashboard statistics and order information

13. Security Features

The application includes security-related mechanisms such as:

Password hashing using PHP password hashing functions

PDO prepared statements for database operations

Role-based access control

Session-based authentication

Input validation

Authentication checks for protected pages

For production deployment, additional security hardening should be applied, including:

HTTPS/SSL

Secure environment-based credentials

CSRF protection

Secure cookie configuration

Production error handling

Database least-privilege accounts

Regular backups

14. Testing

The project includes database validation/testing files under:

database/

Examples include:

validate_counts.php
validate_seed.php

The application can also be tested locally using:

http://localhost/Quickbbite_SDLC/

Recommended testing areas include:

Registration

Login/logout

Customer navigation

Food browsing

Cart operations

Order placement

Admin login

Admin CRUD functions

Database relationships

Form validation

Responsive layout

Error handling

15. Academic Project Scope

This project is designed as an academic Food Delivery System implementation and demonstration.

The repository contains the source code, database schema, seed data and supporting configuration required to demonstrate the implemented system.

The README describes the implementation currently contained in the repository. Any feature claimed in an academic report should be verified against the corresponding source code and database implementation.

16. GitHub Repository

Repository:

https://github.com/kietvo2005/Quickbbite_SDLC

17. Notes for Developers

When modifying the project:

Keep database changes synchronized with database/schema.sql or the relevant migration.

Test changes locally using XAMPP.

Verify both customer and administrator workflows.

Check database records in phpMyAdmin after CRUD operations.

Do not commit passwords, API keys or production credentials.

Commit and push tested changes to GitHub.

Example Git workflow:

git status
git add .
git commit -m "Update application and database"
git push origin main

18. Project Status

Environment: Local development / academic demonstration

Database: MySQL / MariaDB

Database name: food_delivery

Web server: Apache via XAMPP

Primary administrator: admin@fooddelivery.com

Customer test account: customer@fooddelivery.com