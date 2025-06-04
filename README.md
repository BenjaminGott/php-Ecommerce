# PHP E-commerce Platform

A modern e-commerce platform built with PHP.

##  Authors

- Benjamin GOTTRANT
- Romeo BERNARD

##  Prerequisites

- PHP (recommended version 7.4 or higher)
- MySQL/MariaDB
- XAMPP/WAMP/MAMP or any PHP development environment
- Web browser

##  Installation

1. Clone the repository:
```bash
git clone https://github.com/BenjaminGott/php-Ecommerce.git
```

2. Configure your database:
   - Create a new database in phpMyAdmin
   - Choose one of the following SQL files to execute:
     - `db-create.sql` - For basic database structure with admin account only
     - `php-ecommerce.sql` - For database structure with sample data

3. Configure environment variables:
   - Create a `.env` file in the root directory
   - Use the following template:
```env
DB_HOST="your host"
DB_NAME="your db name"
DB_USER="username"
DB_PASSWORD="password"
```

4. Start your local server (XAMPP/WAMP/MAMP)

5. Access the project through your web browser

## 👨‍💼 Default Admin Account

Use these credentials to access the admin panel:
- Email: admin@admin.com
- Password: admin

## 🔑 Features

- User authentication system
- Product management
- Shopping cart functionality
- Admin dashboard

## 🛠️ Technologies Used

- PHP
- MySQL
- CSS


