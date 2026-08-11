
# QuickBite – Food Delivery System

QuickBite is a web-based Food Delivery System developed as an academic project. The system provides customers with an online platform to browse restaurants and food items, manage their orders, and interact with the food delivery service.

The project is implemented using PHP, MySQL/MariaDB, HTML5, CSS3, JavaScript and Bootstrap 5.

---

## 1. Project Overview

QuickBite is designed to support the main activities of an online food delivery platform.

The system provides two main user roles:

- Customer
- Administrator

### Customer Functions

Customers can:

- Register an account
- Log in and log out
- Browse food categories
- Browse restaurants
- Browse food items
- Search and filter food
- View food details
- Add food items to the shopping cart
- Update cart quantities
- Remove items from the cart
- Proceed to checkout
- Place food orders
- Select available payment methods
- View order history
- Track order status
- Manage their profile
- Manage delivery addresses
- Submit reviews
- Send contact messages

### Administrator Functions

Administrators can access the administration area to manage the system.

The administrator functions include:

- Administrator authentication
- Dashboard
- User management
- Restaurant management
- Food category management
- Food management
- Order management
- Customer message management
- Viewing system information and statistics

---

## 2. Technology Stack

### Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap 5

### Backend

- PHP
- PHP PDO
- Session-based authentication

### Database

- MySQL
- MariaDB compatible
- InnoDB storage engine
- Foreign key constraints
- Prepared statements

### Development Environment

The system can be developed and tested using:

- XAMPP
- Apache
- MySQL/MariaDB
- Visual Studio Code
- phpMyAdmin

---

## 3. System Requirements

Before running QuickBite, install:

- XAMPP
- PHP
- MySQL or MariaDB
- A modern web browser

Recommended local environment:

```text
Operating System: Windows
Web Server: Apache
Database Server: MySQL / MariaDB
PHP: Version supported by the installed XAMPP package
Browser: Microsoft Edge, Google Chrome or another modern browser
4. Project Structure
Quickbbite_SDLC/
│
├── admin/
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   └── js/
│       ├── admin-crud.js
│       ├── main.js
│       └── validation.js
│
├── database/
│   ├── migrations/
│   ├── migration_admin_system.sql
│   ├── schema.sql
│   ├── seed_data.sql
│   ├── generate_sample_images.py
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
│   ├── admin/
│   └── ...
│
├── uploads/
│
├── index.php
├── login.php
├── register.php
├── logout.php
├── restaurant.php
├── food-detail.php
├── cart.php
├── checkout.php
├── profile.php
├── contact.php
├── forgot-password.php
├── install.php
├── README.md
└── ...
5. Database
The main database used by QuickBite is:

food_delivery
The database is designed using a relational structure with foreign-key relationships between the main entities.

The project currently contains tables including:

users
admins
addresses
auth_tokens
cart
categories
contact_messages
foods
orders
order_items
payments
restaurants
reviews
sepay_webhook_logs
wishlist
The exact database structure is defined in:

database/schema.sql
Additional database changes are maintained in:

database/migration_admin_system.sql
database/migrations/
6. Database Configuration
The default local development configuration is:

Host: localhost
Database: food_delivery
Username: root
Password: empty
If the local MySQL/MariaDB server uses a password, update the database configuration in the project's configuration files.

The database connection is handled by the project's PHP configuration.

7. Installation
Step 1 – Start XAMPP
Open the XAMPP Control Panel.

Start:

Apache
MySQL
Both services should be running before accessing the application.

Step 2 – Copy the project
Copy the project folder into:

C:\xampp\htdocs\
The final directory should be:

C:\xampp\htdocs\Quickbbite_SDLC
Step 3 – Create / initialise the database
The project provides database SQL files under:

database/
The main schema is:

database/schema.sql
The project also provides:

database/seed_data.sql
for seed data.

Step 4 – Use the installer
The project includes:

install.php
The installer is intended to initialise the local Food Delivery System environment.

Open:

http://localhost/Quickbbite_SDLC/install.php
After successful installation, the application can be accessed from:

http://localhost/Quickbbite_SDLC/
The installer should only be used in the local development/testing environment. It should be disabled or removed before production deployment.

8. Default Accounts
The project uses one default administrator account.

Administrator
Name:     Jane Doe
Username: admin_staff
Email:    admin@fooddelivery.com
Password: Admin@12345
Role:     admin
Customer
Name:     Customer John
Username: customer_john
Email:    customer@fooddelivery.com
Password: Customer@123
Role:     customer
Important
There is only one default administrator account.

The following account is not used:

admin@admin.com
The duplicate administrator account was removed from the project seed data and local database.

9. Running the Application
After starting Apache and MySQL/MariaDB, open:

http://localhost/Quickbbite_SDLC/
Customer Login
http://localhost/Quickbbite_SDLC/login.php
Use the customer account provided above for customer testing.

Administrator Login
Use:

Email: admin@fooddelivery.com
Password: Admin@12345
The administrator can then access the administrative functions provided by the system.

10. Authentication and Security
QuickBite includes several security-related mechanisms.

Password Hashing
User passwords are stored using PHP password hashing.

The application uses:

password_hash()
for password creation and:

password_verify()
for password verification.

Passwords should not be stored as plain text in the database.

Prepared Statements
The application uses PDO prepared statements for database operations where applicable.

This helps reduce the risk of SQL injection attacks.

Role-Based Access
The system distinguishes between:

customer
admin
Administrative functions are restricted to users with the appropriate administrator role.

Session-Based Authentication
The application uses PHP sessions to maintain authenticated user sessions.

CSRF Protection
State-changing requests include CSRF protection mechanisms where implemented by the application.

11. Ordering Process
The main customer ordering workflow is:

Register / Login
       ↓
Browse Categories
       ↓
Browse Restaurants
       ↓
Select Food
       ↓
Add to Cart
       ↓
Review Cart
       ↓
Checkout
       ↓
Select Payment Method
       ↓
Place Order
       ↓
Order Processing
       ↓
Order Status Tracking
12. Order Management
Orders are stored in the:

orders
table.

Individual products within an order are stored in:

order_items
The system maintains order information such as:

Customer

Total amount

Delivery address

Payment method

Payment status

Order status

Order creation date

13. Payment
The system contains payment-related functionality and stores payment information in the:

payments
table.

The project also contains:

sepay_webhook_logs
for SePay-related webhook records.

Payment functionality in this academic project should be considered a development/testing implementation unless production payment credentials and services have been configured.

14. Images and Assets
The project stores frontend assets under:

assets/
including:

assets/css/
assets/js/
assets/images/
Uploaded files are stored under:

uploads/
The project also contains tools for generating sample image assets.

15. Testing
The project contains validation and testing-related files, including:

database/validate_counts.php
database/validate_seed.php
These scripts can be used during local development to check database and seed-data conditions.

The project may also contain additional testing scripts created during development.

Testing should be performed against the local MariaDB/MySQL database after installation.

16. Development Database Data
The project includes sample data for development and demonstration purposes.

This may include:

Sample customers

Sample administrator

Food categories

Restaurants

Food items

Sample orders

Payment-related records

The sample data is not intended to represent real customer or commercial transaction data.

17. Production Considerations
This project is primarily an academic/local development system.

Before deploying the application to a production server, the following should be reviewed:

Change all demonstration passwords

Use a dedicated database user instead of root

Configure a secure database password

Enable HTTPS

Protect configuration files

Remove or disable install.php

Remove temporary/debug scripts

Review file upload security

Configure production payment credentials

Secure webhook endpoints

Configure appropriate server permissions

Review authentication and session settings

Back up the production database regularly

18. Academic Project Purpose
QuickBite was developed as a Food Delivery System project to demonstrate the implementation of a database-driven web application.

The project demonstrates:

Web application development

Database design

Relational database implementation

User authentication

Role-based access control

CRUD operations

Food and restaurant management

Shopping cart functionality

Order processing

Payment-related functionality

Security considerations

System testing

19. Project Status
Current project status:

Application Type: Food Delivery System
Backend: PHP
Database: MySQL / MariaDB
Frontend: HTML5, CSS3, JavaScript, Bootstrap 5
Development Server: XAMPP / Apache
Database: food_delivery
Default Admin: admin@fooddelivery.com
The project is intended for academic demonstration and local development/testing.


## Sau khi dán README

Lưu bằng:

**Ctrl + S**

Sau đó trong Terminal tại:

```text
C:\xampp\htdocs\Quickbbite_SDLC
chạy:

git status
Bạn sẽ thấy README.md được sửa.

Sau đó:

git add README.md
git commit -m "Update README to match current system"
git push origin main
Một lưu ý quan trọng
Mình không đưa vào README những con số kiểm thử như “17/17 Pass” vì hiện tại chúng ta chưa chạy lại test_harness.php sau các thay đổi database. Khi bạn chạy test thực tế và có kết quả, lúc đó có thể bổ sung bảng testing vào README hoặc báo cáo.

Ngoài ra, README này đang thống nhất với trạng thái chúng ta vừa xác nhận:

Admin duy nhất:

admin@fooddelivery.com
Admin@12345
và database hiện tại chỉ còn 1 admin.


faa606a9-e6d8-40b6-a50b-fd153d228746.png

Được. Mình đã viết lại README.md theo cấu trúc hiện tại của Quickbbite_SDLC, tập trung vào:

Database food_delivery

Các bảng đang có trong phpMyAdmin

install.php

XAMPP + Apache + MySQL/MariaDB

Tài khoản Customer/Admin

Cách chạy project

Cách xóa admin thừa

Cách kiểm tra database

Cách push code lên GitHub

Ghi chú để nội dung README không khai báo những chức năng không có trong code

File README đã tạo:

Download README.md

⚠️ Một điểm rất quan trọng
Theo ảnh bạn vừa gửi, sau khi chạy install.php, hệ thống hiện đang hiển thị:

Customer:
customer@fooddelivery.com
Customer@123

Admin:
admin@fooddelivery.com
Admin@12345
Vì vậy README mình đã để admin@fooddelivery.com là tài khoản admin chính.

Nếu bạn muốn hệ thống cuối cùng chỉ có đúng 1 admin, thì không nên để admin@admin.com trong schema.sql nữa. Nếu không, lần sau import/reset database, tài khoản đó có thể được tạo lại.

Bạn nên kiểm tra:

SELECT id, name, username, email, role
FROM users
WHERE role = 'admin';
Kết quả cuối cùng nên chỉ còn:

admin@fooddelivery.com
Sau đó README này sẽ khớp với trạng thái database mà bạn muốn dùng để đối chiếu với báo cáo. 


README.md
Document

A

Library
/
README.md


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

Library
/
README.md


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

