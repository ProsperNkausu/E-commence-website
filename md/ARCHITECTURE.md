# System Architecture Documentation

## Overview

TemaTech-Innovation follows a **layered architecture** with clear separation between:
- **Presentation Layer** (Frontend - HTML, CSS, JavaScript)
- **API Layer** (REST endpoints for business logic)
- **Application Layer** (Business logic handlers)
- **Data Layer** (MySQL database)

## High-Level Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT LAYER (Browser)                   │
│  HTML Pages | CSS Styling | JavaScript | LocalStorage       │
└────────────────────┬────────────────────────────────────────┘
                     │ AJAX/Fetch Requests
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    API LAYER (endpoints)                    │
│  /api/order.php | /api/get-cart.php | /api/verify-payment  │
│  /admin/ajax/* | /auth/login_process.php                   │
└────────────────────┬────────────────────────────────────────┘
                     │ Validate Session & Parameters
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              APPLICATION LAYER (Business Logic)             │
│  - Cart Management                                          │
│  - Order Processing                                         │
│  - Payment Verification                                     │
│  - Invoice Generation                                       │
│  - Email Notifications                                      │
└────────────────────┬────────────────────────────────────────┘
                     │ SQL Queries (PDO)
                     ▼
┌─────────────────────────────────────────────────────────────┐
│               DATA LAYER (MySQL Database)                   │
│  Tables: customers, products, orders, carts, payments, etc  │
└─────────────────────────────────────────────────────────────┘
```

## Request/Response Flow

### 1. Customer Purchase Flow

```
START
  │
  ├─ User browses products (GET /products)
  │  └─ Frontend: components/pages/products.php
  │
  ├─ User views product details (GET /product-details?id=UUID)
  │  └─ Frontend: components/pages/product-details.php
  │
  ├─ User adds to cart (JavaScript cart-manager.js)
  │  ├─ Store in localStorage (guest)
  │  └─ Fire event to update cart UI
  │
  ├─ User clicks "Go to Cart" (GET /cart)
  │  ├─ If authenticated: Load cart from database
  │  ├─ If guest: Load from localStorage
  │  └─ Display: components/pages/cart.php
  │
  ├─ (Optional) User logs in (POST /auth/login_process.php)
  │  ├─ Validate credentials
  │  ├─ Sync localStorage cart to database (api/sync-cart.php)
  │  └─ Create user session
  │
  ├─ User proceeds to checkout (POST /api/get-checkout.php)
  │  ├─ Create pending order with order_items
  │  ├─ Save customer information
  │  └─ Return checkout form data
  │
  ├─ User enters payment details (components/pages/checkout.php)
  │  └─ Redirected to Lenco payment gateway
  │
  ├─ Payment Processing (Lenco External Service)
  │  ├─ User completes payment on Lenco
  │  └─ Lenco redirects back with payment reference
  │
  ├─ Verify Payment (POST /api/verify-payment.php)
  │  ├─ Call Lenco API to verify transaction
  │  ├─ Update order status to "paid"
  │  ├─ Mark cart as "ordered"
  │  └─ Return success to frontend
  │
  ├─ Generate & Send Invoice
  │  ├─ POST /api/generate-invoice.php (dompdf)
  │  ├─ POST /api/send-email.php (PHPMailer → Gmail SMTP)
  │  └─ Store invoice in database
  │
  ├─ Display Order Confirmation
  │  └─ Show order number, items, total, tracking info
  │
  END (Order Complete)
```

## 2. Admin Order Management Flow

```
Admin Dashboard (admin/components/pages/admin_dashboard.php)
  │
  ├─ View Orders (admin/ajax/filter-orders.php)
  │  ├─ Filter by date range, status, customer
  │  ├─ Query: SELECT FROM orders JOIN customers WHERE...
  │  └─ Return paginated results (JSON)
  │
  ├─ Click Order → Get Details (admin/ajax/get-order-details.php)
  │  ├─ Fetch order data + associated items
  │  ├─ Query: SELECT FROM orders/order_items/customers
  │  └─ Display full order view
  │
  ├─ Update Status (admin/ajax/update-order-status.php)
  │  ├─ POST new status: pending → paid → shipped → delivered
  │  ├─ Update orders table
  │  └─ (Optional) Trigger notification email
  │
  END
```

## 3. Authentication & Authorization Flow

```
┌─────────────────────────────────────────────────────────────┐
│                   LOGIN PROCESS FLOW                        │
└─────────────────────────────────────────────────────────────┘

User submits login form (POST /auth/login_process.php)
  │
  ├─ Validate: Email and password non-empty
  │  └─ If invalid: Return error, display in login form
  │
  ├─ Query admin table:
  │  └─ SELECT role_id, password_hash FROM admins WHERE email = :email
  │
  ├─ If found and password_verify(input_pass, hash):
  │  ├─ Set session vars: admin_id, admin_role, admin_email, etc.
  │  ├─ Declare user as ADMIN
  │  └─ Redirect to /admin/index.php
  │
  ├─ Else, query customer table:
  │  └─ SELECT password_hash FROM customers WHERE email = :email
  │
  ├─ If found and password_verify(input_pass, hash):
  │  ├─ Set session vars: user_id, first_name, email, role='customer'
  │  ├─ Declare user as CUSTOMER
  │  └─ Redirect to /users/index.php
  │
  ├─ Else:
  │  └─ Display error: "Invalid email or password"
  │
  END

┌─────────────────────────────────────────────────────────────┐
│              SESSION VERIFICATION PATTERN                   │
└─────────────────────────────────────────────────────────────┘

Every API endpoint starts with:

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    // User not authenticated
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

If admin operation needed:
if (!isset($_SESSION['admin_id'])) {
    // Not an admin
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}
```

## 4. Cart System Architecture

### Dual-Mode Cart (Guest + Authenticated)

```
┌─────────────────────────────────────────────────────────────┐
│                   CART SYSTEM ARCHITECTURE                  │
└─────────────────────────────────────────────────────────────┘

GUEST USER
  └─ Add to cart → JavaScript cart-manager.js
     └─ Store in window.localStorage (cart data)
        └─ No database write yet
        └─ Persists until browser cleared/logout

DURING LOGIN (api/sync-cart.php)
  └─ Retrieve localStorage cart items
  └─ Query user's active cart from database
  └─ Merge:
     - Items in localStorage but not DB → Add to DB
     - Items in DB but not localStorage → Keep in DB
     - Items in both → Update quantity if changed
  └─ Clear localStorage cart
  └─ Going forward: All cart ops use database

AUTHENTICATED USER
  └─ Add/Remove/Update → JavaScript updates both localStorage AND database
  └─ api/save-cart.php writes to carts & cart_items tables
  └─ Database is source of truth
  └─ localStorage synced for offline capability

CHECKOUT FLOW
  └─ User clicks checkout → api/get-checkout.php
     └─ Fetch active cart items from database
     └─ Create ORDER with order_items from cart_items
     └─ Clear cart (mark as inactive/ordered)
     └─ Continue to payment

┌──────────────────────────────────────────────────────────────┐
│  DATA STRUCTURES                                             │
└──────────────────────────────────────────────────────────────┘

Database (carts table):
{
  id: UUID,
  customer_id: UUID,
  status: 'active' | 'ordered' | 'abandoned' | 'inactive',
  created_at: timestamp,
  updated_at: timestamp
}

Database (cart_items table):
{
  id: UUID,
  cart_id: UUID (FK → carts),
  product_id: UUID (FK → products),
  quantity: integer,
  price_at_time: decimal(10,2),
  created_at: timestamp
}

LocalStorage (JavaScript):
{
  cartItems: [
    {
      productId: UUID,
      name: string,
      price: number,
      quantity: integer,
      image: string
    },
    ...
  ]
}
```

## 5. Payment Processing Flow (Lenco Integration)

```
┌─────────────────────────────────────────────────────────────┐
│              LENCO PAYMENT GATEWAY FLOW                     │
└─────────────────────────────────────────────────────────────┘

1. CHECKOUT PAGE (components/pages/checkout.php)
   ├─ User enters: name, email, phone, address
   ├─ User selects payment method: "Pay with Lenco"
   └─ Form POST to api/get-checkout.php

2. CREATE ORDER (api/get-checkout.php)
   ├─ Save customer information
   ├─ Create order record:
   │  └─ INSERT INTO orders (customer_id, order_number, total, status='pending')
   ├─ Copy cart items to order_items:
   │  └─ INSERT INTO order_items (FROM cart_items WHERE cart_id = user_cart)
   ├─ Update cart status to 'ordered'
   └─ Return order_id and order_number

3. REDIRECT TO LENCO PAYMENT PAGE
   ├─ JavaScript directs to Lenco checkout URL:
   │  └─ https://checkout.lenco.co/?public_key=pub_xxx&amount=XXX&reference=order_123
   ├─ User completes payment on Lenco platform
   └─ Lenco processes transaction

4. PAYMENT CALLBACK (Lenco redirects back)
   └─ URL: /components/pages/checkout.php?payment=success&ref=lenco_reference_id

5. VERIFY PAYMENT (api/verify-payment.php - AJAX)
   ├─ POST: {order_id, payment_reference}
   ├─ Call Lenco API (https://api.lenco.co/api/v1/verify)
   │  ├─ Header: Authorization: Bearer SECRET_KEY
   │  └─ Verify transaction status
   ├─ If verified:
   │  ├─ INSERT INTO payment_history (order_id, amount, transaction_id)
   │  ├─ UPDATE orders SET status='paid'
   │  ├─ Trigger Invoice generation
   │  ├─ Trigger Email notification
   │  └─ Return success
   ├─ If failed:
   │  ├─ Return error
   │  └─ Keep order status as 'pending'
   │
   END

6. INVOICE GENERATION (api/generate-invoice.php)
   ├─ Query order + order_items + customer data
   ├─ Render HTML invoice template
   ├─ Convert to PDF using dompdf
   └─ Save PDF file + store invoice record

7. EMAIL INVOICE (api/send-email.php)
   ├─ Use PHPMailer with Gmail SMTP
   ├─ Send to customer_email with:
   │  ├─ Order number and items
   │  ├─ Invoice PDF attachment
   │  └─ Estimated delivery date
   └─ Store email log in system

8. CONFIRMATION PAGE
   └─ Display order summary with order number
```

## 6. Database Transaction Flow

```
┌─────────────────────────────────────────────────────────────┐
│          TYPICAL DATABASE OPERATION                         │
└─────────────────────────────────────────────────────────────┘

Connection Setup (config/db.php):
  ├─ Create PDO instance
  ├─ MySQL host: localhost
  ├─ Database: tematech_innovation
  ├─ Charset: utf8mb4 (supports emoji, special chars)
  └─ Error mode: Exception (throw on error)

Query Execution Pattern:

1. Prepare Statement (Security - prevents SQL injection)
   └─ $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?")

2. Bind Parameters
   └─ $stmt->bindParam(1, $product_id, PDO::PARAM_STR)

3. Execute Query
   └─ $stmt->execute()

4. Fetch Results
   ├─ Associative array mode: $result = $stmt->fetch(PDO::FETCH_ASSOC)
   └─ OR fetchAll() for multiple rows

5. Return Data
   └─ json_encode($result) OR use in PHP variable

Error Handling:
  ├─ Try-Catch blocks around queries
  ├─ Log errors to file/database
  └─ Return user-friendly error messages

Example Endpoint (api/get-checkout.php):
  ├─ session_start()
  ├─ require_once '../config/db.php'
  ├─ if ($_SERVER['REQUEST_METHOD'] === 'POST') {}
  ├─ Try {
  │   ├─ $stmt = $pdo->prepare("UPDATE carts SET status = ? WHERE id = ?")
  │   ├─ $stmt->execute(['ordered', $cart_id])
  │   └─ $stmt = $pdo->prepare("INSERT INTO orders...")
  │   └─ $stmt->execute([...])
  │ }
  ├─ Catch (Exception $e) {
  │   └─ return json_encode(['success' => false, 'message' => 'Error'])
  │ }
  └─ return json_encode(['success' => true, 'data' => $order_data])
```

## 7. Email Notification System

```
┌─────────────────────────────────────────────────────────────┐
│            EMAIL NOTIFICATION ARCHITECTURE                  │
└─────────────────────────────────────────────────────────────┘

PHPMailer Configuration (api/send-email.php):
  ├─ SMTP Host: smtp.gmail.com
  ├─ SMTP Port: 587 (TLS encryption)
  ├─ Username: Gmail account email
  ├─ Password: App-specific password (not main password!)
  └─ Encryption: TLS

Email Sending Flow:
  ├─ Create PHPMailer instance
  ├─ Set sender: noreply@tematech.com (Gmail alias)
  ├─ Set recipient: customer_email
  ├─ Set subject: "Order Confirmation - TemaTech Shop"
  ├─ Load HTML template for invoice email
  ├─ Attach PDF invoice file
  ├─ $mail->send()
  ├─ Log email sent in system (email_logs table)
  └─ Return success/failure

Email Templates:
  ├─ Order Confirmation Email
  │  ├─ Order number and date
  │  ├─ Item list with prices
  │  ├─ Total amount
  │  └─ Tracking link
  │
  ├─ Password Reset Email
  │  ├─ Reset link with token (valid 1 hour)
  │  ├─ Security warning
  │  └─ Support contact info
  │
  └─ Support Response Notification
     ├─ Reply message
     ├─ Conversation link
     └─ Reply-to address for email

Retry Logic:
  ├─ On failure: Log error details
  ├─ Admin notified of email failures
  └─ Manual retry option in admin panel
```

## 8. Admin Action Processing

```
┌─────────────────────────────────────────────────────────────┐
│          ADMIN ACTION HANDLERS                              │
└─────────────────────────────────────────────────────────────┘

All admin/actions/*.php follow pattern:

1. Session verification
   └─ Verify $_SESSION['admin_id'] exists

2. Permission check
   └─ Verify admin role has permission for action

3. Parameter validation
   └─ Check required POST/GET parameters present

4. Database operation
   ├─ Prepare statement with parameters
   └─ Execute and capture result

5. Response
   ├─ Return JSON with success/error
   └─ Include relevant data for display update

Examples:

add_single_product.php
  ├─ POST: name, description, price, stock, category_ids, images
  ├─ Generate UUID for product
  ├─ INSERT into products table
  ├─ INSERT into product_categories (many-to-many)
  ├─ Handle image uploads → public/images/products/
  └─ Return product_id

toggle_user_status.php
  ├─ POST: customer_id, new_status (active/inactive)
  ├─ UPDATE customers SET is_active = ? WHERE id = ?
  └─ Log admin action

delete_user.php
  ├─ POST: customer_id
  ├─ Soft delete OR hard delete (check cascade rules)
  ├─ Update related records
  └─ Return success confirmation
```

## 9. Error Handling Architecture

```
┌─────────────────────────────────────────────────────────────┐
│          ERROR HANDLING PATTERNS                            │
└─────────────────────────────────────────────────────────────┘

API Endpoints Error Response:
  ├─ HTTP 200 with JSON:
  │  └─ {success: false, message: "User error", data: null}
  │
  ├─ HTTP 400 Bad Request:
  │  └─ Missing/invalid parameters
  │
  ├─ HTTP 401 Unauthorized:
  │  └─ User not logged in
  │
  ├─ HTTP 403 Forbidden:
  │  └─ Insufficient permissions
  │
  └─ HTTP 500 Internal Server Error:
     └─ Server exception, log details

Frontend Error Handling:
  ├─ Check response.success === false
  ├─ Display response.message to user
  ├─ Log errors to console
  └─ Redirect to login if 401

Database Error Handling:
  ├─ Try-catch around all DB operations
  ├─ Log exception details (not shown to user)
  ├─ Store in logs table for admin review
  └─ Return generic "Something went wrong" message

Security Error Handling:
  ├─ Invalid session → 401 response
  ├─ SQL injection attempt → 400 response
  ├─ Access denied → 403 response
  └─ Log suspicious activity for review
```

## 10. Security Architecture

```
┌─────────────────────────────────────────────────────────────┐
│          SECURITY LAYERS                                    │
└─────────────────────────────────────────────────────────────┘

1. INPUT VALIDATION
   ├─ Server-side validation on all inputs
   ├─ Whitelist acceptable values
   ├─ Type casting (int, string, bool)
   └─ Reject suspicious patterns

2. SQL INJECTION PREVENTION
   ├─ Use PDO prepared statements ALWAYS
   ├─ Bind parameters separately
   └─ Never concatenate user input into SQL

3. PASSWORD SECURITY
   ├─ bcrypt hashing with $2y$10$ cost
   ├─ password_hash(input, PASSWORD_BCRYPT)
   ├─ password_verify(input, hash)
   └─ No plaintext passwords stored

4. SESSION SECURITY
   ├─ session_start() at page top
   ├─ Check $_SESSION['user_id'] or $_SESSION['admin_id']
   ├─ Set secure session options:
   │  ├─ httponly flag (no JS access)
   │  └─ secure flag (HTTPS only in production)
   └─ session_destroy() on logout

5. DATA PRIVACY
   ├─ UUID primary keys (not sequential IDs)
   ├─ Email addresses unique+indexed
   ├─ Customer data encrypted at rest (optional)
   └─ Passwords hashed, never retrieved

6. AUTHORIZATION CHECKS
   ├─ Verify user permissions for each operation
   ├─ Admin vs. Customer role separation
   ├─ Staff role with limited access
   └─ Validate user owns data before returning

7. FILE UPLOAD SECURITY
   ├─ Validate MIME types
   ├─ Rename files with UUID
   ├─ Store outside web root if possible
   ├─ Check file size limits
   └─ Prevent script execution in uploads

8. EXTERNAL API SECURITY
   ├─ Lenco API calls over HTTPS
   ├─ API keys stored in .env (not in code)
   ├─ Signature verification on payment callbacks
   └─ Rate limiting on sensitive endpoints
```

---

## Technology Integration Points

### Frontend ↔ Backend Communication
- **Method:** AJAX/Fetch APIs to /api/* endpoints
- **Data Format:** JSON
- **Error Handling:** success boolean + message + data

### Database ↔ Application
- **Connection:** PDO with prepared statements
- **Transaction Support:** For multi-step operations
- **Data Serialization:** JSON for complex data

### External Services
- **Lenco Payment:** HTTPS API calls for payment verification
- **Gmail SMTP:** Email delivery via PHPMailer
- **UUID Generation:** Ramsey\\UUID library

---

This architecture ensures scalability, security, and maintainability while keeping code organized and separable.
