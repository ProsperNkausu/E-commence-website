# Database Schema Documentation

## Overview

TemaTech-Innovation uses a **relational MySQL database** with 16 interconnected tables. The schema supports e-commerce operations including product management, shopping carts, orders, payments, and customer communication.

**Database Name:** `tematech_innovation`  
**Charset:** utf8mb4 (supports Unicode, emojis, special characters)  
**Engine:** InnoDB (supports transactions, foreign keys, cascading)

## Database Diagram (Text Representation)

```
USERS
┌─ admins ◄──┐              ┌─ customers
└──────────────────┐        │
                   │        └─ carts ◄─────┬─ cart_items
                   │                      │      │
admin_roles ──┐    │                      │      └─► products ◄────────┐
              └────┘                      │           │               │
                                          └ orders ───┼─ order_items ─┘
                                              │       │
                                              └──────►product_categories
                                                      ◄─────► categories
PAYMENTS & INVOICING
              payment_history ◄────► orders
              invoices ◄────────────► orders

MESSAGING
        conversations ◄──► conversation_participants
              │
              └──► messages ◄──► message_stars
                      │
                      └─► customers/admins
```

## Complete Table Definitions

### 1. **admins** - Administrator Accounts

**Purpose:** Store admin/staff user accounts with authentication

```sql
CREATE TABLE `admins` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `role_id` CHAR(36) NOT NULL,         -- Foreign key to admin_roles
  `first_name` VARCHAR(100),           -- Admin's first name
  `last_name` VARCHAR(100),            -- Admin's last name
  `email` VARCHAR(255) UNIQUE NOT NULL,-- Unique email address
  `password_hash` VARCHAR(255) NOT NULL,-- bcrypt hashed password
  `phone` VARCHAR(30),                 -- Contact phone number
  `is_active` TINYINT(1) DEFAULT 1,    -- 1=active, 0=inactive
  `last_login_at` TIMESTAMP NULL,      -- Last login timestamp
  `created_at` TIMESTAMP DEFAULT NOW(),-- Account creation time
  KEY `role_id` (role_id),
  KEY `email` (email),
  FOREIGN KEY (role_id) REFERENCES admin_roles(id)
)
```

**Sample Data:**
- admin@TemaTech.com (role: admin) - Full system access
- nkausuprosper@yahoo.com (role: staff) - Limited access

---

### 2. **admin_roles** - Admin Role Definitions

**Purpose:** Define admin role types and permissions

```sql
CREATE TABLE `admin_roles` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID
  `name` VARCHAR(50) NOT NULL UNIQUE,  -- Role name: 'admin' or 'staff'
  `description` VARCHAR(255),          -- Role permissions description
  `created_at` TIMESTAMP DEFAULT NOW() -- Creation timestamp
)
```

**Available Roles:**
| Role | Description |
|------|-------------|
| admin | Full system access to all features |
| staff | Limited operational access, no settings/users |

---

### 3. **customers** - Customer Accounts

**Purpose:** Store customer user accounts and authentication

```sql
CREATE TABLE `customers` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `first_name` VARCHAR(100) NOT NULL,  -- Customer's first name
  `last_name` VARCHAR(100) NOT NULL,   -- Customer's last name
  `email` VARCHAR(255) UNIQUE NOT NULL,-- Unique email (login credential)
  `password_hash` VARCHAR(255) NOT NULL,-- bcrypt hashed password
  `phone` VARCHAR(30),                 -- Contact phone
  `is_active` TINYINT(1) DEFAULT 1,    -- 1=active, 0=banned/inactive
  `reset_token` VARCHAR(255) NULL,     -- Password reset token
  `reset_token_expires` TIMESTAMP NULL,-- Token expiration (1 hour)
  `created_at` TIMESTAMP DEFAULT NOW(),-- Account creation
  `updated_at` TIMESTAMP DEFAULT NOW()
    ON UPDATE NOW(),                   -- Last update
  KEY `email` (email),
  KEY `is_active` (is_active)
)
```

**Notes:**
- Email is unique and used for login
- Password reset token is time-limited (1 hour validity)
- UUID ensures privacy (no sequential customer IDs)

---

### 4. **categories** - Product Categories

**Purpose:** Define product categories for browsing and filtering

```sql
CREATE TABLE `categories` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `name` VARCHAR(100) UNIQUE NOT NULL, -- Category name (browsable)
  `icon` VARCHAR(50),                  -- Icon image path
  `created_at` TIMESTAMP DEFAULT NOW() -- Creation timestamp
)
```

**Available Categories (7 total):**
| ID | Name | Icon |
|----|------|------|
| ... | Computers | category/computer.png |
| ... | Laptops | category/laptops.png |
| ... | Keyboards | category/Keyboard.png |
| ... | Motherboards | category/motherboard.png |
| ... | Processors | category/processor.jpg |
| ... | Mouse | category/mouse.png |
| ... | Accessories | category/accessories.png |

---

### 5. **products** - Product Catalog

**Purpose:** Store product information and inventory

```sql
CREATE TABLE `products` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `sku` VARCHAR(50) UNIQUE NOT NULL,   -- Stock Keeping Unit
  `name` VARCHAR(255) NOT NULL,        -- Product name
  `description` LONGTEXT,              -- Detailed description
  `specifications` JSON,               -- Technical specs (JSON)
  `price` DECIMAL(10,2) NOT NULL,      -- Product price
  `discount_price` DECIMAL(10,2),      -- Discounted price (optional)
  `stock_quantity` INT NOT NULL DEFAULT 0,-- Current inventory
  `status` ENUM('active','inactive','discontinued'),-- Availability
  `rating` DECIMAL(3,2),               -- Average rating (1-5)
  `review_count` INT DEFAULT 0,        -- Number of reviews
  `created_at` TIMESTAMP DEFAULT NOW(),-- Upload date
  `updated_at` TIMESTAMP DEFAULT NOW()
    ON UPDATE NOW(),                   -- Last modified
  KEY `sku` (sku),
  KEY `status` (status),
  KEY `name` (name),
  INDEX `price` (price)
)
```

**Fields Explanation:**
- **sku** - Unique product code (e.g., LAP-001, KEY-USB-01)
- **price** - Base selling price
- **discount_price** - Sale price if on promotion
- **stock_quantity** - Units available
- **status** - Controls visibility to customers
- **specifications** - JSON for flexible specs (RAM, processor, etc.)

---

### 6. **product_categories** - Product-Category Many-to-Many

**Purpose:** Connect products to multiple categories

```sql
CREATE TABLE `product_categories` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `product_id` CHAR(36) NOT NULL,      -- Foreign key to products
  `category_id` CHAR(36) NOT NULL,     -- Foreign key to categories
  `created_at` TIMESTAMP DEFAULT NOW(),
  UNIQUE KEY `unique_product_category` (product_id, category_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)
```

**Purpose:** Allows one product in multiple categories  
**Example:** Laptop can be in both "Computers" and "Laptops" categories

---

### 7. **product_images** - Product Photos

**Purpose:** Store product image URLs and thumbnails

```sql
CREATE TABLE `product_images` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `product_id` CHAR(36) NOT NULL,      -- Foreign key to products
  `image_url` VARCHAR(500) NOT NULL,   -- File path/URL
  `is_primary` TINYINT(1) DEFAULT 0,   -- 1 if main product image
  `alt_text` VARCHAR(255),             -- Image alt text (SEO)
  `created_at` TIMESTAMP DEFAULT NOW(),
  KEY `product_id` (product_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)
```

**Storage:** Images stored in `public/images/products/`

---

### 8. **carts** - Shopping Carts

**Purpose:** Persist shopping carts for customers (guests and authenticated)

```sql
CREATE TABLE `carts` (
  `id` CHAR(36) PRIMARY KEY DEFAULT UUID(),-- UUID primary key
  `customer_id` CHAR(36),              -- Foreign key to customers (or NULL for guests)
  `status` ENUM('active','ordered','abandoned','inactive')
    DEFAULT 'active',                  -- Cart status
  `created_at` TIMESTAMP DEFAULT NOW(),-- Cart creation
  `updated_at` TIMESTAMP DEFAULT NOW()
    ON UPDATE NOW(),                   -- Last modified
  KEY `customer_id` (customer_id),
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
)
```

**Cart Statuses:**
| Status | Meaning |
|--------|---------|
| active | Current shopping cart |
| ordered | Converted to order, no longer active |
| abandoned | Customer didn't checkout (30+ days old) |
| inactive | Marked as inactive by system |

---

### 9. **cart_items** - Shopping Cart Contents

**Purpose:** Store individual items in a cart

```sql
CREATE TABLE `cart_items` (
  `id` CHAR(36) PRIMARY KEY DEFAULT UUID(),-- UUID primary key
  `cart_id` CHAR(36) NOT NULL,         -- Foreign key to carts
  `product_id` CHAR(36) NOT NULL,      -- Foreign key to products
  `quantity` INT NOT NULL DEFAULT 1,   -- Number of units
  `price_at_time` DECIMAL(10,2) NOT NULL,-- Price when added (for history)
  `created_at` TIMESTAMP DEFAULT NOW(),
  UNIQUE KEY `unique_cart_product` (cart_id, product_id),-- One product per cart
  KEY `product_id` (product_id),
  FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
)
```

**Important:** `price_at_time` preserves original price even if product price changes later

---

### 10. **orders** - Customer Orders

**Purpose:** Store completed orders with status tracking

```sql
CREATE TABLE `orders` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `customer_id` CHAR(36) NOT NULL,     -- Foreign key to customers
  `order_number` VARCHAR(50) UNIQUE NOT NULL,-- User-visible order #
  `total_amount` DECIMAL(10,2) NOT NULL,-- Order total
  `subtotal` DECIMAL(10,2),            -- Before taxes/shipping
  `tax_amount` DECIMAL(10,2),          -- Tax charged
  `shipping_cost` DECIMAL(10,2),       -- Shipping fee
  `discount_amount` DECIMAL(10,2),     -- Discount applied
  `status` ENUM('pending','paid','shipped','delivered','cancelled')
    DEFAULT 'pending',                 -- Order status
  `payment_method` VARCHAR(50),        -- 'lenco', 'bank_transfer', etc.
  `shipping_address` TEXT,             -- Full address
  `notes` TEXT,                        -- Order notes/instructions
  `created_at` TIMESTAMP DEFAULT NOW(),-- Order creation time
  `updated_at` TIMESTAMP DEFAULT NOW()
    ON UPDATE NOW(),                   -- Last status update
  KEY `customer_id` (customer_id),
  KEY `order_number` (order_number),
  KEY `status` (status),
  KEY `created_at` (created_at),
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
)
```

**Order Statuses:**
| Status | Meaning |
|--------|---------|
| pending | Order created, awaiting payment |
| paid | Payment verified, ready to ship |
| shipped | Sent to customer, in transit |
| delivered | Customer received |
| cancelled | Order cancelled (full refund) |

---

### 11. **order_items** - Order Line Items

**Purpose:** Store products included in each order

```sql
CREATE TABLE `order_items` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `order_id` CHAR(36) NOT NULL,        -- Foreign key to orders
  `product_id` CHAR(36) NOT NULL,      -- Foreign key to products
  `quantity` INT NOT NULL DEFAULT 1,   -- Quantity ordered
  `price` DECIMAL(10,2) NOT NULL,      -- Price at time of order
  `subtotal` DECIMAL(10,2),            -- quantity × price
  `created_at` TIMESTAMP DEFAULT NOW(),
  KEY `order_id` (order_id),
  KEY `product_id` (product_id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
)
```

---

### 12. **payment_history** - Payment Transactions

**Purpose:** Track all payment attempts and verifications

```sql
CREATE TABLE `payment_history` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `order_id` CHAR(36) NOT NULL,        -- Foreign key to orders
  `amount` DECIMAL(10,2) NOT NULL,     -- Amount paid
  `payment_method` VARCHAR(50),        -- 'lenco', 'bank_transfer', wallet
  `transaction_id` VARCHAR(255) UNIQUE,-- Lenco or bank reference
  `status` ENUM('pending','verified','failed','refunded')
    DEFAULT 'pending',                 -- Payment status
  `verified_at` TIMESTAMP NULL,        -- When payment verified
  `response_data` JSON,                -- API response from gateway
  `created_at` TIMESTAMP DEFAULT NOW(),-- Payment attempt time
  KEY `order_id` (order_id),
  KEY `transaction_id` (transaction_id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)
```

---

### 13. **invoices** - Order Invoices

**Purpose:** Store generated invoices for orders

```sql
CREATE TABLE `invoices` (
  `id` CHAR(36) PRIMARY KEY,           -- UUID primary key
  `order_id` CHAR(36) UNIQUE NOT NULL, -- One invoice per order
  `invoice_number` VARCHAR(50) UNIQUE NOT NULL,-- Invoice #
  `invoice_url` VARCHAR(500),          -- PDF file path
  `subtotal` DECIMAL(10,2),            -- Before tax
  `tax_amount` DECIMAL(10,2),          -- Tax total
  `total_amount` DECIMAL(10,2),        -- Grand total
  `generated_at` TIMESTAMP DEFAULT NOW(),-- Generation time
  `created_at` TIMESTAMP DEFAULT NOW(),
  KEY `order_id` (order_id),
  KEY `invoice_number` (invoice_number),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)
```

---

### 14. **conversations** - Support Conversations

**Purpose:** Organize support and messaging threads

```sql
CREATE TABLE `conversations` (
  `id` CHAR(36) PRIMARY KEY DEFAULT UUID(),-- UUID primary key
  `subject` VARCHAR(255) NOT NULL,     -- Conversation topic
  `category` ENUM('order','support','promotion')
    DEFAULT 'support',                 -- Type of conversation
  `created_by` CHAR(36) NOT NULL,      -- UUID of initiator
  `created_by_type` ENUM('customer','admin')-- Who started it
    NOT NULL,
  `status` ENUM('open','closed') DEFAULT 'open',-- Conversation status
  `created_at` TIMESTAMP DEFAULT NOW(),
  `updated_at` TIMESTAMP DEFAULT NOW()
    ON UPDATE NOW(),
  KEY `created_by` (created_by),
  KEY `status` (status),
  KEY `category` (category)
)
```

**Categories:**
- **order** - Questions about specific orders
- **support** - Help with products, shipping, returns
- **promotion** - Marketing messages, newsletters

---

### 15. **conversation_participants** - Conversation Members

**Purpose:** Track who participates in conversations

```sql
CREATE TABLE `conversation_participants` (
  `id` CHAR(36) PRIMARY KEY DEFAULT UUID(),-- UUID primary key
  `conversation_id` CHAR(36) NOT NULL,-- Foreign key to conversations
  `user_id` CHAR(36) NOT NULL,         -- Customer or admin ID
  `user_type` ENUM('customer','admin') NOT NULL,-- Type of user
  `joined_at` TIMESTAMP DEFAULT NOW(),
  UNIQUE KEY `unique_participant` (conversation_id, user_id, user_type),
  KEY `conversation_id` (conversation_id),
  FOREIGN KEY (conversation_id) REFERENCES conversations(id)
    ON DELETE CASCADE
)
```

---

### 16. **messages** - Chat Messages

**Purpose:** Store individual messages in conversations

```sql
CREATE TABLE `messages` (
  `id` CHAR(36) PRIMARY KEY DEFAULT UUID(),-- UUID primary key
  `conversation_id` CHAR(36) NOT NULL,-- Foreign key to conversations
  `sender_id` CHAR(36) NOT NULL,       -- Customer or admin ID
  `sender_type` ENUM('customer','admin')-- Type of sender
    NOT NULL,
  `message` LONGTEXT NOT NULL,         -- Message content
  `is_read` TINYINT(1) DEFAULT 0,      -- 0=unread, 1=read by recipient
  `created_at` TIMESTAMP DEFAULT NOW(),
  KEY `conversation_id` (conversation_id),
  KEY `sender_id` (sender_id),
  KEY `is_read` (is_read),
  FOREIGN KEY (conversation_id) REFERENCES conversations(id)
    ON DELETE CASCADE
)
```

---

### 17. **message_stars** - Starred Messages

**Purpose:** Allow users to favorite/star important messages

```sql
CREATE TABLE `message_stars` (
  `id` CHAR(36) PRIMARY KEY DEFAULT UUID(),-- UUID primary key
  `message_id` CHAR(36) NOT NULL,      -- Foreign key to messages
  `user_id` CHAR(36) NOT NULL,         -- Who starred it
  `created_at` TIMESTAMP DEFAULT NOW(),
  UNIQUE KEY `unique_star` (message_id, user_id),
  KEY `message_id` (message_id),
  KEY `user_id` (user_id),
  FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
)
```

---

## Database Relationships

### One-to-Many Relationships
| Parent | Child | Relationship | ON DELETE |
|--------|-------|--------------|-----------|
| admin_roles | admins | 1:Many | RESTRICT |
| customers | carts | 1:Many | CASCADE |
| customers | orders | 1:Many | RESTRICT |
| customers | conversations | 1:Many | CASCADE |
| carts | cart_items | 1:Many | CASCADE |
| orders | order_items | 1:Many | CASCADE |
| orders | payment_history | 1:Many | CASCADE |
| orders | invoices | 1:Many | CASCADE |
| products | product_images | 1:Many | CASCADE |
| products | product_categories | 1:Many | CASCADE |
| conversations | messages | 1:Many | CASCADE |
| conversations | conversation_participants | 1:Many | CASCADE |
| messages | message_stars | 1:Many | CASCADE |

### Many-to-Many Relationships
| Table 1 | Join Table | Table 2 | Purpose |
|---------|-----------|--------|---------|
| products | product_categories | categories | Products in multiple categories |
| conversations | conversation_participants | customers/admins | Multiple participants in conversation |

---

## Indexes & Performance Optimization

### Primary Key Indexes (Automatic)
Every table has a UUID primary key with automatic index.

### Foreign Key Indexes (Automatic)
Every foreign key is automatically indexed.

### Additional Useful Indexes
| Table | Column(s) | Reason |
|-------|-----------|--------|
| customers | email | Frequent lookups during login |
| customers | is_active | Gender-based user filtering |
| products | sku | Product lookup by code |
| products | status | Status-based filtering |
| products | price | Sorting by price |
| orders | status | Orders filtered by status |
| orders | created_at | Time-range queries |
| cart_items | (cart_id, product_id) | Prevent duplicates, fast lookups |
| messages | is_read | Unread message counts |

---

## Data Integrity Rules

### Cascade Deletes
When a record is deleted, related records cascade:
- Delete product → Delete product_images, product_categories, cart_items, order_items
- Delete cart → Delete cart_items
- Delete order → Delete order_items, payment_history, invoices
- Delete conversation → Delete messages, conversation_participants

### Restrict Deletes
When a record would be deleted, but references exist (prevent data loss):
- Delete customer → RESTRICT (has orders)
- Delete product → RESTRICT (has orders referencing it)

### UNIQUE Constraints
- customers.email - One account per email
- products.sku - One product per SKU code
- orders.order_number - Order number globally unique
- invoices.invoice_number - Invoice number globally unique
- product_categories - One category occurrence per product
- conversation_participants - One relationship per user+conversation

---

## Query Examples

### Get Customer's Active Cart with Items
```sql
SELECT c.id, c.status, ci.*, p.name, p.price
FROM carts c
LEFT JOIN cart_items ci ON c.id = ci.cart_id
LEFT JOIN products p ON ci.product_id = p.id
WHERE c.customer_id = ? AND c.status = 'active';
```

### Get Order with Items
```sql
SELECT o.*, oi.*, p.name, p.price
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id
WHERE o.id = ?;
```

### Get Products in Category
```sql
SELECT p.*
FROM products p
RIGHT JOIN product_categories pc ON p.id = pc.product_id
WHERE pc.category_id = ? AND p.status = 'active';
```

### Get Conversation with Messages
```sql
SELECT c.*, m.*, u.first_name, u.last_name, a.first_name AS admin_first_name
FROM conversations c
LEFT JOIN messages m ON c.id = m.conversation_id
LEFT JOIN customers u ON m.sender_id = u.id AND m.sender_type = 'customer'
LEFT JOIN admins a ON m.sender_id = a.id AND m.sender_type = 'admin'
WHERE c.id = ?
ORDER BY m.created_at ASC;
```

---

## Database Statistics

- **Total Tables:** 16
- **Total Relationships:** 20+ (including many-to-many)
- **Primary Keys:** All UUID (36 chars)
- **Charset:** utf8mb4 (supports all Unicode)
- **Engine:** InnoDB (transactions, referential integrity)

---

This carefully designed schema supports scalable, relational e-commerce operations with data integrity, security, and performance in mind.
