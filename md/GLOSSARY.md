# Glossary & Reference Terms

## Project-Specific Terminology

### Business Terms

#### Cart
A shopping container where customers add products before checkout. Can be in two states:
- **Active Cart** - Current shopping cart being used
- **Abandoned Cart** - Cart not checked out within 30+ days
- **Ordered Cart** - Cart converted to order

**Related:** cart_items, cart_items table, shopping cart

#### Checkout
The process of reviewing cart contents, entering customer information, and selecting payment method before order creation.

**Related:** Order, Payment, Invoice

#### Order
A record of customer purchase containing:
- Customer information
- Products purchased (order_items)
- Total amount
- Shipping address
- Status tracking

**Statuses:** pending → paid → shipped → delivered (or cancelled)

**Related:** order_items, order_status, payment_history

#### Invoice
Official receipt/document for an order containing:
- Order details
- Itemized list with prices
- Total amount
- Customer information
- Invoice number (unique)

**Format:** PDF file for download/email

**Related:** PDF, email, invoice_number

#### Order Item
Individual line in an order representing one product quantity.

**Contains:**
- Product ID and details (name, SKU)
- Quantity ordered
- Price at time of order

#### Payment Verification
Process of confirming with payment gateway that transaction was successful.

**Steps:**
1. User completes payment on Lenco
2. Returns with payment reference
3. Backend calls Lenco API to verify
4. Updates order status to "paid"

#### Conversion
When a shopping cart becomes an order (cart → order transition).

**Process:**
1. Customer clicks checkout
2. Order created from cart_items
3. Cart status changed to "ordered"
4. Order status starts as "pending"

---

### Technical Terms

#### UUID (Universally Unique Identifier)
128-bit identifier ensuring uniqueness without coordination.

**Format:** 550e8400-e29b-41d4-a716-446655440000 (36 characters)

**Uses in TemaTech:**
- Primary key for all tables
- Better privacy than sequential IDs
- No sequential ID exposure risk

**Generation:** Ramsey\UUID library

#### PDO (PHP Data Objects)
Database abstraction layer providing prepared statements for secure SQL queries.

**Benefits:**
- SQL injection prevention
- Multiple database support
- Consistent interface

**Usage:**
```php
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
```

#### Session
Server-side storage of authenticated user state.

**Contains for Customers:**
- user_id
- first_name, last_name
- email
- role (customer)

**Contains for Admins:**
- admin_id
- admin_role_id
- admin_email
- admin_first_name, admin_last_name

**Duration:** Until logout or timeout

#### CSRF (Cross-Site Request Forgery)
Security attack where user is tricked into making unwanted request.

**Prevention:**
- Token validation
- Referrer checking
- Same-Site cookie flags

**Not currently implemented** - Consider adding for production

#### XSS (Cross-Site Scripting)
Injection attack inserting malicious scripts into web pages.

**Prevention:**
- Input validation
- Output escaping: `htmlspecialchars()`
- Content Security Policy headers

**Implementation:** Using `htmlspecialchars()` throughout

#### bcrypt
Password hashing algorithm using salting and iteration to prevent brute-force attacks.

**Cost Factor:** $2y$10$ (10 iterations)

**Usage:**
```php
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
password_verify($input, $hash);  // Returns true/false
```

#### JSON (JavaScript Object Notation)
Text-based data format with key-value pairs.

**Uses in TemaTech:**
- API request/response format
- Storing specifications in products table
- AJAX communication

**Example Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { "id": "uuid", "name": "Product" }
}
```

#### localhost
Special hostname referring to the local machine (127.0.0.1).

**Uses:**
- Development environment
- Testing
- phpMyAdmin access: http://localhost/phpmyadmin

#### XAMPP
Free Apache/MySQL/PHP suite for local development.

**Components:**
- Apache web server
- MySQL database
- PHP interpreter
- phpMyAdmin tool

**Location:**
- Windows: C:\xampp
- macOS: /Applications/XAMPP
- Linux: /opt/lampp

---

### Data Terms

#### SKU (Stock Keeping Unit)
Unique code identifying a product for inventory management.

**Format:** Category-descriptor-number (e.g., LAP-GAMING-001)

**Purpose:**
- Easy product identification
- Barcode generation
- Reordering

**Database:** products.sku (UNIQUE)

#### Category
Product grouping/classification for browsing and filtering.

**Available Categories:**
1. Computers
2. Laptops
3. Keyboards
4. Motherboards
5. Processors
6. Mouse
7. Accessories

**Many-to-Many:** One product can be in multiple categories

#### Product Status
Availability indicator for products.

**Values:**
| Status | Meaning |
|--------|---------|
| active | Available for purchase |
| inactive | Hidden from customers |
| discontinued | No longer sold |

#### Cart Status
Status of shopping cart.

**Values:**
| Status | Meaning |
|--------|---------|
| active | Current shopping cart |
| ordered | Converted to order |
| abandoned | Not checked out for 30+ days |
| inactive | Manually deactivated |

#### Order Status
Stage of order fulfillment.

**Values (in sequence):**
| Status | Meaning |
|--------|---------|
| pending | Created, awaiting payment |
| paid | Payment verified |
| shipped | Sent to customer |
| delivered | Customer received |
| cancelled | Order cancelled, refund issued |

#### Payment Status
State of payment transaction.

**Values:**
| Status | Meaning |
|--------|---------|
| pending | Awaiting verification |
| verified | Successfully processed |
| failed | Declined or error |
| refunded | Money returned to customer |

---

### User Role Terms

#### Customer
Regular user who:
- Browses products
- Adds to cart
- Checks out
- Makes payments
- Views orders
- Contacts support

**Session Variable:** $_SESSION['user_id']

**Table:** customers

#### Admin
Highest privilege user who:
- Manages all aspects of system
- Creates other admins
- Changes system settings
- Full report access

**Session Variable:** $_SESSION['admin_id'] with admin role

**Table:** admins (with admin_roles)

#### Staff
Limited privilege user who:
- Manages orders
- Manages customers
- Manages products
- Cannot access settings or admin management

**Session Variable:** $_SESSION['admin_id'] with staff role

**Table:** admins (with staff role)

#### Guest
Unauthenticated user who:
- Views products
- Adds to cart (localStorage only)
- Cannot checkout until account created

**No Session Variable** - localStorage only

---

### Communication Terms

#### Conversation
Multi-message thread between customer and admin(s).

**Categories:**
- **order** - Questions about specific order
- **support** - Help with products/service
- **promotion** - Marketing communications

**Status:**
- Open - Ongoing conversation
- Closed - Resolved/archived

#### Message
Individual text in a conversation.

**Properties:**
- sender_id (customer or admin UUID)
- sender_type (customer or admin)
- message (text content)
- is_read (boolean)
- created_at (timestamp)

**Can be:** Starred (message_stars table)

#### Participant
User involved in a conversation (customer or admin).

**Relationship:** conversation_participants table

---

### Gateway & Service Terms

#### Lenco
Third-party payment gateway provider (https://lenco.co).

**Integration:**
- Processes credit/debit card payments
- Returns payment reference
- Provides verification API

**Keys Required:**
- LENCO_SECRET_KEY - For API calls
- LENCO_PUBLIC_KEY - For frontend

**Endpoint:** https://api.lenco.co

#### PHPMailer
PHP email library for sending messages via SMTP.

**Configuration:**
- SMTP Host: smtp.gmail.com
- Port: 587 (TLS)
- Requires Gmail app-specific password

**Uses:**
- Invoice delivery
- Password reset emails
- Order confirmations

#### dompdf
PHP library converting HTML to PDF documents.

**Uses in TemaTech:**
- Invoice PDF generation
- Packing slip generation
- Receipt generation

**Process:**
1. Generate HTML
2. Pass to dompdf
3. Output PDF file

#### SMTP (Simple Mail Transfer Protocol)
Protocol for sending emails.

**Settings:**
- Host: smtp.gmail.com
- Port: 587 (encrypted)
- Authentication: Required
- TLS: Required

---

### File Terms

#### Favicon
Small icon displayed in browser tab.

**Location:** /public/favicon.ico

#### Icon
Visual symbol for categories or actions.

**Locations:**
- /public/category/*.png - Category icons

#### Asset
Static file (image, CSS, JavaScript).

**Location:** /public/

#### View
HTML template file (usually .php).

**Location:** /components/pages/, /admin/components/pages/, /users/components/pages/

#### Include
PHP file meant to be included in other files (shared code).

**Location:** /includes/

#### Helper
JavaScript or PHP utility functions.

**Files:**
- /includes/cart-manager.js
- /includes/api-helper.js

---

### Database Terms

#### Foreign Key
Database constraint linking two tables.

**Example:** carts.customer_id → customers.id

**Benefits:**
- Data integrity
- Referential consistency
- Cascade operations

#### Primary Key
Unique identifier for each record in a table.

**In TemaTech:** UUID (char 36)

**Properties:** NOT NULL, UNIQUE, indexed

#### Composite Key
Primary key using multiple columns.

**Example:** product_categories.(product_id, category_id)

#### Index
Database structure for faster lookups.

**Types:**
- Primary index (automatic)
- Unique index (enforces uniqueness)
- Regular index (speeds up queries)

#### Relation
Connection between tables.

**Types:**
- One-to-Many (1:N) - One customer many orders
- Many-to-Many (N:N) - Products in categories

#### Constraint
Rule enforcing data validity.

**Types:**
- UNIQUE - Value cannot repeat
- NOT NULL - Field required
- CHECK - Value meets condition
- DEFAULT - Default value if not provided
- FOREIGN KEY - Links to another table

#### Collation
Character set and sorting order (utf8mb4).

**Properties:**
- Supports Unicode
- EMOJI support
- Case-insensitive sorting

---

### HTTP Terms

#### HTTP Status Code
Number indicating server response outcome.

**Common Codes:**
| Code | Meaning |
|------|---------|
| 200 | OK - Success |
| 301/302 | Redirect |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Not authenticated |
| 403 | Forbidden - Not permitted |
| 404 | Not Found - Page doesn't exist |
| 500 | Server Error |

#### Content-Type
Header indicating data format being sent.

**Values:**
- application/json - JSON format
- text/html - HTML page
- text/plain - Plain text
- multipart/form-data - File upload

#### RESTful API
Architectural style using HTTP methods for operations.

**Methods:**
- GET - Retrieve data
- POST - Create/Update data
- PUT - Replace data
- DELETE - Remove data

#### Redirect
HTTP response directing browser to different URL.

**Types:**
- 301 - Permanent (SEO)
- 302 - Temporary

---

### Security Terms

#### Hashing
One-way encryption to store passwords securely.

**Algorithm:** bcrypt (PASSWORD_BCRYPT)

**Properties:** Not reversible, salted, computationally expensive

#### Salt
Random data mixed with password before hashing.

**Purpose:** Prevents rainbow table attacks

#### Token
Unique string for password reset/verification.

**Properties:**
- Random generation
- Time-limited (1 hour)
- One-time use
- Sent via email

#### Environment Variable
Configuration value stored outside code.

**File:** .env

**Values:** Sensitive data like API keys

#### HTTPS
Encrypted HTTP protocol for secure communication.

**Port:** 443

**Certificate:** SSL/TLS (Let's Encrypt)

---

## Abbreviations Reference

| Abbreviation | Full Form | Context |
|--------------|-----------|---------|
| **API** | Application Programming Interface | Endpoints for data exchange |
| **AJAX** | Asynchronous JavaScript and XML | Async client-server communication |
| **JSON** | JavaScript Object Notation | Data format |
| **UUID** | Universally Unique Identifier | Primary key format |
| **SKU** | Stock Keeping Unit | Product identifier |
| **PDO** | PHP Data Objects | Database abstraction |
| **ORM** | Object-Relational Mapping | Database layer |
| **CSRF** | Cross-Site Request Forgery | Security attack/prevention |
| **XSS** | Cross-Site Scripting | Security attack/prevention |
| **HTTPS** | HTTP Secure | Encrypted web protocol |
| **SSL** | Secure Sockets Layer | Encryption protocol |
| **TLS** | Transport Layer Security | Modern SSL replacement |
| **SMTP** | Simple Mail Transfer Protocol | Email sending |
| **HTTP** | HyperText Transfer Protocol | Web protocol |
| **PHP** | PHP: Hypertext Preprocessor | Server language |
| **SQL** | Structured Query Language | Database queries |
| **CRUD** | Create, Read, Update, Delete | Basic database operations |
| **MVC** | Model-View-Controller | Architecture pattern |
| **RBAC** | Role-Based Access Control | Permission system |
| **MB** | Megabyte | File size unit |
| **RAM** | Random Access Memory | Computer memory |
| **CPU** | Central Processing Unit | Processor |
| **FTP** | File Transfer Protocol | File upload |
| **SSH** | Secure Shell | Remote access |
| **CLI** | Command Line Interface | Terminal/console |
| **GUI** | Graphical User Interface | Visual interface |
| **DOM** | Document Object Model | Web page structure |
| **CSS** | Cascading Style Sheets | Page styling |
| **HTML** | HyperText Markup Language | Page structure |
| **JS** | JavaScript | Client-side language |
| **PDF** | Portable Document Format | Document format |
| **CSV** | Comma-Separated Values | Data format |
| **URL** | Uniform Resource Locator | Web address |
| **URI** | Uniform Resource Identifier | Resource identifier |
| **CDN** | Content Delivery Network | Fast content distribution |
| **SPF** | Sender Policy Framework | Email authentication |
| **DKIM** | DomainKeys Identified Mail | Email signature |

---

## Quick Reference Tables

### Order Flow Statuses
```
START → pending (order created, awaiting payment)
      ↓
      paid (payment verified, ready to ship)
      ↓
      shipped (sent to customer, in transit)
      ↓
      delivered (customer received)
      ↓
      END
```

### Cart Flow
```
GUEST CART
  localStorage only
  ↓ (user logs in)
  ↓ sync-cart.php
  DATABASE
  ↓ (adds items)
  ↓ sync-cart.php
  ACTIVE CART
  ↓ (checkout)
  ↓
  ORDERED (converted to order)
```

### Authentication Flow
```
Login Form
  ↓
Check Admin Table
  ├─ Found + Password Match → Admin Session
  └─ Not Found
     ↓
     Check Customer Table
     ├─ Found + Password Match → Customer Session  
     └─ Not Found → Error: Invalid credentials
```

---

This glossary provides complete reference for all project-specific and technical terms used throughout TemaTech-Innovation.
