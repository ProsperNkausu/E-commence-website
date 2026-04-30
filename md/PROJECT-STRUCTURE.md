# Project Structure Documentation

## Directory Overview

```
TemaTech-innovation/
├── admin/                      # Admin control panel
├── api/                        # REST API endpoints
├── auth/                       # Authentication logic
├── bootstrap/                  # Application initialization
├── components/                 # Reusable page components
├── config/                     # Configuration files
├── database/                   # Database schema
├── includes/                   # Shared includes
├── md/                         # Documentation (this folder)
├── public/                     # Static assets
├── routes/                     # Route definitions
├── users/                      # User dashboard
├── vendor/                     # Composer dependencies
├── index.php                   # Main entry point
├── composer.json               # PHP dependencies
└── .env                        # Environment variables
```

## Root Level Files

### `index.php`
**Purpose:** Main entry point for the application  
**Functionality:**
- Initializes the session
- Includes core configuration files
- Implements routing logic using `navigate()` function
- Directs requests to appropriate components based on page parameter
- Handles 404 errors for invalid routes

### `composer.json`
**Purpose:** PHP dependency management  
**Contains:**
- Project metadata (name: "tematech/innovation", type: "project")
- Required PHP version: ^8.1
- Core dependencies: Laravel, PHPMailer, dompdf, FPDF
- Autoload configuration (PSR-4 namespace "App\\")

## Directory Structure & Purposes

### 📁 `/admin` - Admin Control Panel
**Purpose:** Administrative interface for managing the business

#### Structure:
```
admin/
├── index.php                   # Admin dashboard entry
├── actions/                    # Admin action handlers
│   ├── add_single_product.php
│   ├── delete_user.php
│   ├── edit-product-process.php
│   ├── reset_password.php
│   └── toggle_user_status.php
├── ajax/                       # AJAX endpoints for admin UI
│   ├── filter-orders.php
│   ├── get-order-details.php
│   └── update-order-status.php
├── components/
│   └── pages/                  # Admin page templates
│       ├── admin_dashboard.php
│       ├── manage_products.php
│       ├── upload_products.php
│       ├── manage_orders.php
│       ├── manage_payments.php
│       ├── manage_customer.php
│       ├── manage_stats.php
│       ├── manage_admins.php
│       ├── settings.php
│       └── manage_shipping.php
├── css/
│   └── admin-dark.css          # Admin panel styling
└── includes/
    └── header.php              # Admin header template
```

**Key Responsibilities:**
- Product CRUD operations
- Order status management
- Customer account management
- Payment history tracking
- Admin user management
- Shipping coordination
- Dashboard statistics

---

### 📁 `/api` - REST API Endpoints
**Purpose:** Backend API for frontend communication  

#### Files:
| File | Method | Purpose |
|------|--------|---------|
| `get-cart.php` | GET | Retrieve user's active cart items |
| `save-cart.php` | POST | Persist cart to database |
| `sync-cart.php` | POST | Sync localStorage with database |
| `clear-cart.php` | POST | Empty customer's cart |
| `order.php` | POST | Create order from cart |
| `get-checkout.php` | POST | Prepare checkout data |
| `verify-payment.php` | POST | Verify Lenco payment status |
| `generate-invoice.php` | POST | Generate PDF invoice |
| `send-email.php` | POST | Email invoice to customer |
| `get-order-details.php` | GET | Retrieve specific order |
| `forgot-pass.php` | POST | Email password reset link |
| `user-pass-update.php` | POST | Update password with token |

**Response Format:**
```php
{
    "success": boolean,
    "message": "description",
    "data": {/* payload */}
}
```

**Authentication:** All endpoints verify `$_SESSION['user_id']` or `$_SESSION['admin_id']`

---

### 📁 `/auth` - Authentication System
**Purpose:** User authentication and session management

#### Files:
| File | Purpose |
|------|---------|
| `login_process.php` | Process login requests, create sessions |
| `register_process.php` | Handle user registration, create account |
| `logout.php` | Destroy session, log out user |

**Flow:**
1. **Login:** Email → Admin table → Customer table → Create session
2. **Register:** Validate input → Hash password → Create UUID → Insert customer + cart
3. **Logout:** Clear session → Redirect to homepage

---

### 📁 `/bootstrap` - Application Initialization
**Purpose:** Laravel framework configuration

#### Files:
| File | Purpose |
|------|---------|
| `app.php` | Laravel Application singleton configuration |

**Configures:**
- Request routing (web.php)
- Middleware pipeline
- Exception handling
- Service providers

---

### 📁 `/components/pages` - Customer Frontend Pages
**Purpose:** User-facing page templates

#### Structure:
```
components/pages/
├── home.php                    # Landing page
├── products.php                # Product catalog
├── product-details.php         # Single product view
├── cart.php                    # Shopping cart
├── checkout.php                # Checkout & payment
├── login.php                   # Login form
├── register.php                # Registration form
├── about.php                   # About page
├── contact.php                 # Contact page
└── 404.php                     # Error page
```

**Page Details:**

| Page | Features |
|------|----------|
| **home.php** | Featured categories, newest products, promotions |
| **products.php** | Category filter, price filter, search, sorting, pagination (6 items/page) |
| **product-details.php** | Images, description, specifications, add-to-cart, reviews |
| **cart.php** | Item list, quantity controls, subtotal, checkout button |
| **checkout.php** | Customer info form, address, payment method selection |
| **login.php** | Email/password login, register link, forgot password link |
| **register.php** | Full name, email, password confirmation, auto-cart creation |
| **about.php** | Company information and mission |
| **contact.php** | Contact form and support details |
| **404.php** | Error message and navigation links |

---

### 📁 `/config` - Configuration Files
**Purpose:** Application configuration and settings

#### Files:

**`db.php`** - Database Connection
```php
// PDO MySQL Connection
Host: localhost
Database: tematech_innovation
User: root
Password: (blank - XAMPP default)
Charset: utf8mb4
Options: Exception mode, associative array fetch
```

**`env.php`** - Environment Variables Loader
- Loads sensitive data from `.env` file
- Provides access to:
  - Lenco payment gateway keys
  - Email service credentials
  - API endpoints

---

### 📁 `/database` - Database Schema
**Purpose:** Database structure and initialization

#### Files:
| File | Purpose |
|------|---------|
| `tematech_innovation (clean db).sql` | Complete database schema dump |

**Contains:**
- Table definitions (16 tables)
- Indexes and constraints
- Default data (categories, admin roles, demo admin accounts)
- UUID generation functions
- Relationships and foreign keys

---

### 📁 `/includes` - Shared Components
**Purpose:** Reusable templates and utilities

#### Files:
| File | Purpose |
|------|---------|
| `navbar.php` | Main navigation header |
| `footer.php` | Page footer |
| `navbar-styles.php` | Navigation CSS styling |
| `navbar-script.js` | Navigation JavaScript |
| `cart-manager.js` | Client-side cart management |
| `api-helper.js` | AJAX helper functions |
| `page-loader.php` | Page loading spinner/animation |

**Key Scripts:**

**`cart-manager.js`** - Shopping Cart Logic
- LocalStorage-based cart for guests
- Add/remove/update item quantity
- Cart persistence
- Event listeners for UI updates

**`api-helper.js`** - AJAX Communication
- Wrapper for fetch API
- Request/response handling
- Error management
- Session validation

---

### 📁 `/public` - Static Assets
**Purpose:** Publicly accessible files

#### Structure:
```
public/
├── category/                   # Category icons
│   ├── computer.png
│   ├── laptops.png
│   ├── keyboard.png
│   ├── motherboard.png
│   ├── processor.jpg
│   ├── mouse.png
│   └── accessories.png
└── images/                     # Product and content images
    ├── products/
    ├── banners/
    └── uploads/
```

---

### 📁 `/routes` - Route Definitions
**Purpose:** Application routing configuration

#### Files:
**`web.php`** - Router Configuration
- Defines route patterns for pages
- Maps URLs to components
- Handles GET/POST requests
- Error handling (404)

**Route Pattern:**
```
GET /products → components/pages/products.php
GET /product-details?id=UUID → components/pages/product-details.php
POST /api/order → api/order.php
etc.
```

---

### 📁 `/users` - User Dashboard
**Purpose:** Authenticated user personal area

#### Structure:
```
users/
├── index.php                   # User dashboard entry
├── components/
│   └── pages/
│       ├── user_dashboard.php  # Main dashboard
│       ├── edit-profile.php    # Profile settings
│       └── user_orders.php     # Order history
└── includes/
    └── header.php              # User area header
```

**Features:**
- View profile information
- Edit personal details
- View order history
- Track order status
- Download invoices
- Manage preferences

---

### 📁 `/md` - Documentation
**Purpose:** Project documentation in Markdown format

#### Files:
| File | Purpose |
|------|---------|
| `README.md` | Project overview and quick start |
| `PROJECT-STRUCTURE.md` | This file - directory structure guide |
| `ARCHITECTURE.md` | System architecture and design patterns |
| `DATABASE.md` | Database schema and relationships |
| `FEATURES.md` | Feature documentation |
| `API.md` | API endpoints reference |
| `SETUP-AND-INSTALLATION.md` | Installation and setup guide |
| `DEVELOPMENT-GUIDE.md` | Development guidelines and standards |
| `TROUBLESHOOTING.md` | Common issues and solutions |
| `GLOSSARY.md` | Terms and abbreviations |

---

### 📁 `/vendor` - Composer Dependencies
**Purpose:** Third-party PHP libraries (auto-generated by Composer)

#### Key Packages:
- `laravel/framework` - Web framework
- `phpmailer/phpmailer` - Email service
- `dompdf/dompdf` - PDF generation
- `vlucas/phpdotenv` - Environment variable loader
- `ramsey/uuid` - UUID generation
- Supporting libraries for above

---

## File Navigation Guide

### Adding a New Customer Page
1. Create file in `components/pages/new-page.php`
2. Include common elements from `includes/`
3. Add route in `routes/web.php`
4. Update navigation in `includes/navbar.php`

### Adding a New API Endpoint
1. Create file in `api/endpoint-name.php`
2. Verify session: `session_start();` and check `$_SESSION['user_id']`
3. Return JSON: `json_encode(['success' => true, 'data' => $data])`
4. Document in `md/API.md`

### Adding Admin Feature
1. Create page in `admin/components/pages/feature.php`
2. Add action handler in `admin/actions/if-needed.php`
3. Add AJAX endpoint in `admin/ajax/if-needed.php`
4. Verify admin session check
5. Document in `md/FEATURES.md`

### Database Migration
1. Create backup of current schema
2. Modify table in `database/tematech_innovation (clean db).sql`
3. Execute changes in phpMyAdmin
4. Update `md/DATABASE.md`
5. Version the schema file

---

## Key Design Patterns

### Session-Based Architecture
- User session created on successful login
- Session variables: `user_id`, `first_name`, `last_name`, `email`, `role`
- Admin session variables: `admin_id`, `admin_first_name`, etc.

### Request/Response Flow
1. Frontend JavaScript → API endpoint via AJAX/Fetch
2. API validates session and parameters
3. Responses always include: `success`, `message`, `data`
4. Frontend updates DOM based on response

### Database Interaction
- PDO with prepared statements (security)
- UUID primary keys (privacy)
- JSON responses for API endpoints
- Session-based data ownership

### File Organization
- Logical separation by functionality (api, admin, auth, components)
- Shared code in `/includes`
- Configuration isolated in `/config`
- Database schema versioned in `/database`

---

## Customization Points

### Adding New Product Category
1. Add icon to `public/category/`
2. Insert into `categories` table
3. Update product filters in `components/pages/products.php`

### Changing Admin UI
1. Edit templates in `admin/components/pages/`
2. Modify styling in `admin/css/admin-dark.css`
3. Update navigation in `admin/includes/header.php`

### Modifying Checkout Flow
1. Edit `components/pages/checkout.php`
2. Adjust `api/get-checkout.php` logic
3. Update `api/order.php` creation
4. Modify `api/verify-payment.php` if needed

---

**This structure maintains clear separation of concerns while keeping related functionality grouped together. Follow these organizational patterns when extending the application.**
