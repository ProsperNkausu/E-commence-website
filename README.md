# TemaTech-Innovation E-Commerce Platform

## Project Overview

**TemaTech-Innovations** is a modern, full-featured e-commerce platform specializing in computer hardware and electronics. Built with PHP 8.1, Laravel 11, and MySQL, it provides a complete shopping experience with product browsing, cart management, secure checkout, payment processing, invoice generation, and comprehensive admin controls.

## Key Features

### For Customers
- **Product Browsing** - Browse, search, and filter by category, price
- **Smart Cart** - Dual-mode cart (guest + authenticated) with automatic syncing
- **Secure Checkout** - Complete checkout process with customer information collection
- **Payment Integration** - Lenco payment gateway with real-time verification
- **Invoice Generation** - Automatic PDF invoice creation and email delivery
- **Order Tracking** - View order status and history
- **Account Management** - User profile, password reset, order history
- **Support Messaging** - Direct communication with admin team
- **Email Notifications** - Order confirmations and support alerts

### For Admins
- **Dashboard** - View statistics, sales, orders at a glance
- **Product Management** - Add, edit, delete products with bulk upload support
- **Order Management** - View, filter, update order status
- **Payment Tracking** - Monitor transactions and payment history
- **Customer Management** - View, activate/deactivate customer accounts
- **Admin Management** - Create and manage admin/staff accounts with role-based access
- **Shipping Management** - Track and manage order shipments
- **Messaging System** - Support conversations with customers

## Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 8.1+ with Laravel 11 Framework |
| **Database** | MySQL/MariaDB 10.4+ |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript (ES6+) |
| **Payment** | Lenco Payment Gateway API |
| **Email** | PHPMailer 7.0 with Gmail SMTP |
| **PDF Generation** | dompdf 3.1 / FPDF 1.8 |
| **Authentication** | bcrypt password hashing with session-based auth |
| **Icons** | Font Awesome 6.4.0 |
| **Storage** | Server-side MySQL + Client-side localStorage |

## Core Dependencies

```json
{
    "php": "^8.1",
    "laravel/framework": "^11.0",
    "phpmailer/phpmailer": "^7.0",
    "dompdf/dompdf": "^3.1",
    "setasign/fpdf": "^1.8",
    "vlucas/phpdotenv": "^5.6"
}
```

## Quick Start Guide

### Prerequisites
- PHP 8.1 or higher
- MySQL/MariaDB 10.4 or higher
- Composer
- XAMPP (with Apache and MySQL modules enabled)

### Installation Steps

1. **Clone/Copy Project**
   ```bash
   # Navigate to XAMPP htdocs directory
   cd xampp/htdocs
   # Copy TemaTech-innovation folder here
   ```

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create new database: `tematech_innovation`
   - Import SQL file: `database/tematech_innovation (clean db).sql`

3. **Install Dependencies**
   ```bash
   cd TemaTech-innovation
   composer install
   ```

4. **Configure Environment**
   - Copy `.env.example` to `.env` (if exists)
   - Update database credentials in `config/db.php`:
   ```php
   'host' => 'localhost',
   'database' => 'tematech_innovation',
   'username' => 'root',
   'password' => ''
   ```

5. **Setup Email (PHPMailer)**
   - Gmail account with app-specific password
   - Update in `config/env.php`:
   ```php
   MAIL_HOST='smtp.gmail.com'
   MAIL_PORT=587
   MAIL_USERNAME='your-email@gmail.com'
   MAIL_PASSWORD='app-specific-password'
   ```

6. **Setup Payment Gateway**
   - Register at Lenco (https://lenco.co)
   - Add API keys to `.env`:
   ```
   LENCO_SECRET_KEY=your_secret_key
   LENCO_PUBLIC_KEY=your_public_key
   ```

7. **Start Application**
   - Start Apache and MySQL via XAMPP
   - Navigate to: `http://localhost/TemaTech-innovation`
   - Admin login: `admin@TemaTech.com` / `password`

## Documentation Structure

- **[README.md](README.md)** - This file. Project overview and quick start
- **[PROJECT-STRUCTURE.md](PROJECT-STRUCTURE.md)** - Directory structure and file organization
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Technical architecture and system design
- **[DATABASE.md](DATABASE.md)** - Complete database schema with relationships
- **[FEATURES.md](FEATURES.md)** - Detailed feature documentation
- **[API.md](API.md)** - API endpoints reference and usage
- **[SETUP-AND-INSTALLATION.md](SETUP-AND-INSTALLATION.md)** - Detailed setup instructions
- **[DEVELOPMENT-GUIDE.md](DEVELOPMENT-GUIDE.md)** - Guidelines for developers
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues and solutions
- **[GLOSSARY.md](GLOSSARY.md)** - Terms and abbreviations reference

## Default Admin Credentials

```
Email: admin@TemaTech.com
Password: (Configured during setup)
Role: Administrator (Full access)
```

## Project Categories

The platform currently supports 7 product categories:
1. Computers
2. Laptops
3. Keyboards
4. Motherboards
5. Processors
6. Mouse
7. Accessories

## Security Features

- UUID-based identifiers (not sequential auto-increment)
- bcrypt password hashing
- Session-based authentication
- CSRF protection ready
- Prepared statements (PDO)
- Role-based access control (RBAC)
- Password reset with time-limited tokens

## Database Statistics

- **16 Tables** - Comprehensive relational schema
- **2 Admin Roles** - Admin (full access) and Staff (limited access)
- **UUID Primary Keys** - Enhanced privacy and scalability

## Development Workflow

1. Study [PROJECT-STRUCTURE.md](PROJECT-STRUCTURE.md) to understand codebase layout
2. Review [ARCHITECTURE.md](ARCHITECTURE.md) for system flow
3. Check [DATABASE.md](DATABASE.md) for data relationships
4. Reference [API.md](API.md) when working with endpoints
5. Follow [DEVELOPMENT-GUIDE.md](DEVELOPMENT-GUIDE.md) for coding standards
6. Use [TROUBLESHOOTING.md](TROUBLESHOOTING.md) when facing issues

## Contributing

When contributing to this project:
1. Follow the architecture patterns documented in ARCHITECTURE.md
2. Maintain database schema integrity (see DATABASE.md)
3. Use the API patterns documented in API.md
4. Follow security best practices from DEVELOPMENT-GUIDE.md
5. Test thoroughly before committing

## Support

For issues or questions:
1. Check TROUBLESHOOTING.md first
2. Review related documentation files
3. Contact project administrator: admin@TemaTech.com

## License

TemaTech-Innovation E-Commerce Platform - All Rights Reserved

---

**Last Updated:** April 8, 2026  
**Version:** 1.0  
**Status:** Production Ready
