# API Reference Documentation

## Overview

TemaTech-Innovation exposes REST API endpoints for:
- **Customer API** (`/api/*.php`) - Shopping, cart, orders, checkout, payments
- **Admin API** (`/admin/ajax/*.php`, `/admin/actions/*.php`) - Order management, product management

## Request/Response Format

### Standard Response Format
All API endpoints return JSON with this structure:

```json
{
  "success": true|false,
  "message": "Human readable message",
  "data": {
    /* Endpoint-specific data */
  }
}
```

### HTTP Status Codes
- **200 OK** - Successful request
- **400 Bad Request** - Invalid parameters
- **401 Unauthorized** - User not authenticated
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **500 Internal Server Error** - Server exception

### Authentication
All endpoints require valid session:
- Must include `session_start()` at the top
- Check `$_SESSION['user_id']` for customers
- Check `$_SESSION['admin_id']` for admin operations
- Return 401 Unauthorized if not authenticated

---

## Customer API Endpoints

### 1. Get Cart

**Endpoint:** `api/get-cart.php`  
**Method:** GET  
**Authentication:** Required (customer)

**Description:** Retrieve user's active shopping cart with all items.

**Request:**
```
GET /api/get-cart.php
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Cart retrieved successfully",
  "data": {
    "cart_id": "550e8400-e29b-41d4-a716-446655440000",
    "status": "active",
    "items": [
      {
        "cart_item_id": "550e8400-e29b-41d4-a716-446655440001",
        "product_id": "550e8400-e29b-41d4-a716-446655440002",
        "name": "Gaming Laptop",
        "price_at_time": "1299.99",
        "quantity": 1,
        "subtotal": "1299.99"
      },
      {
        "cart_item_id": "550e8400-e29b-41d4-a716-446655440003",
        "product_id": "550e8400-e29b-41d4-a716-446655440004",
        "name": "Mechanical Keyboard",
        "price_at_time": "89.99",
        "quantity": 2,
        "subtotal": "179.98"
      }
    ],
    "totals": {
      "subtotal": 1479.97,
      "tax": 0,
      "shipping": 0,
      "total": 1479.97
    }
  }
}
```

**Response (Error - Not Authenticated):**
```json
{
  "success": false,
  "message": "User not authenticated",
  "data": null
}
```

---

### 2. Save Cart

**Endpoint:** `api/save-cart.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Save cart items to database (add/update items).

**Request:**
```
POST /api/save-cart.php
Content-Type: application/json

{
  "items": [
    {
      "product_id": "550e8400-e29b-41d4-a716-446655440002",
      "quantity": 1,
      "price": "1299.99"
    },
    {
      "product_id": "550e8400-e29b-41d4-a716-446655440004",
      "quantity": 2,
      "price": "89.99"
    }
  ]
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Cart saved successfully",
  "data": {
    "cart_id": "550e8400-e29b-41d4-a716-446655440000",
    "items_count": 2,
    "items_total": 1479.97
  }
}
```

---

### 3. Sync Cart (localStorage → Database)

**Endpoint:** `api/sync-cart.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Synchronize browser localStorage cart with database cart on login.

**Request:**
```
POST /api/sync-cart.php
Content-Type: application/json

{
  "cart_items": [
    {
      "product_id": "550e8400-e29b-41d4-a716-446655440002",
      "name": "Gaming Laptop",
      "price": 1299.99,
      "quantity": 1
    }
  ]
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Cart synced successfully",
  "data": {
    "synced_items": 1,
    "new_items": 0,
    "updated_items": 1,
    "cart_id": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

---

### 4. Clear Cart

**Endpoint:** `api/clear-cart.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Empty the customer's shopping cart.

**Request:**
```
POST /api/clear-cart.php
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Cart cleared successfully",
  "data": {
    "cart_id": "550e8400-e29b-41d4-a716-446655440000",
    "status": "active"
  }
}
```

---

### 5. Create Order

**Endpoint:** `api/order.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Create order from current cart items.

**Request:**
```
POST /api/order.php
Content-Type: application/json

{
  "cart_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "order_id": "550e8400-e29b-41d4-a716-446655440010",
    "order_number": "ORD-2026-0001",
    "total_amount": "1479.97",
    "status": "pending",
    "items_count": 3
  }
}
```

---

### 6. Get Checkout

**Endpoint:** `api/get-checkout.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Process checkout - save customer info, create order from cart, return checkout data.

**Request:**
```
POST /api/get-checkout.php
Content-Type: application/json

{
  "customer_info": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "zip": "10001",
    "country": "USA"
  },
  "shipping_same_as_billing": true,
  "payment_method": "lenco"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Checkout prepared successfully",
  "data": {
    "order_id": "550e8400-e29b-41d4-a716-446655440010",
    "order_number": "ORD-2026-0001",
    "total_amount": "1479.97",
    "items": [
      {
        "name": "Gaming Laptop",
        "quantity": 1,
        "price": "1299.99"
      }
    ],
    "payment_info": {
      "method": "lenco",
      "lenco_public_key": "pub-5b0e840753aee253b88d8ead1c405023191ced84384a13ba",
      "amount": 1479.97,
      "currency": "USD"
    }
  }
}
```

---

### 7. Verify Payment

**Endpoint:** `api/verify-payment.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Verify Lenco payment gateway transaction and update order status.

**Request:**
```
POST /api/verify-payment.php
Content-Type: application/json

{
  "order_id": "550e8400-e29b-41d4-a716-446655440010",
  "payment_reference": "lenco_ref_123456789"
}
```

**Response (Success - Payment Verified):**
```json
{
  "success": true,
  "message": "Payment verified successfully",
  "data": {
    "order_id": "550e8400-e29b-41d4-a716-446655440010",
    "order_number": "ORD-2026-0001",
    "status": "paid",
    "transaction_id": "lenco_ref_123456789",
    "amount": "1479.97",
    "verified_at": "2026-04-08T14:30:00Z"
  }
}
```

**Response (Error - Payment Failed):**
```json
{
  "success": false,
  "message": "Payment verification failed",
  "data": {
    "reason": "Transaction not found in payment gateway",
    "order_status": "pending"
  }
}
```

---

### 8. Generate Invoice

**Endpoint:** `api/generate-invoice.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Generate PDF invoice for order.

**Request:**
```
POST /api/generate-invoice.php
Content-Type: application/json

{
  "order_id": "550e8400-e29b-41d4-a716-446655440010"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Invoice generated successfully",
  "data": {
    "invoice_id": "550e8400-e29b-41d4-a716-446655440020",
    "invoice_number": "INV-2026-0001",
    "invoice_url": "/invoices/INV-2026-0001.pdf",
    "generated_at": "2026-04-08T14:35:00Z"
  }
}
```

---

### 9. Send Invoice Email

**Endpoint:** `api/send-email.php`  
**Method:** POST  
**Authentication:** Required (customer)

**Description:** Email invoice PDF to customer.

**Request:**
```
POST /api/send-email.php
Content-Type: application/json

{
  "order_id": "550e8400-e29b-41d4-a716-446655440010",
  "recipient_email": "john@example.com"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Invoice email sent successfully",
  "data": {
    "email": "john@example.com",
    "invoice_number": "INV-2026-0001",
    "sent_at": "2026-04-08T14:36:00Z"
  }
}
```

---

### 10. Get Order Details

**Endpoint:** `api/get-order-details.php`  
**Method:** GET  
**Authentication:** Required (customer)

**Parameters:**
- `order_id` - UUID of order

**Description:** Retrieve specific order details.

**Request:**
```
GET /api/get-order-details.php?order_id=550e8400-e29b-41d4-a716-446655440010
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Order details retrieved",
  "data": {
    "order": {
      "id": "550e8400-e29b-41d4-a716-446655440010",
      "order_number": "ORD-2026-0001",
      "status": "shipped",
      "total_amount": "1479.97",
      "created_at": "2026-04-06T10:00:00Z",
      "updated_at": "2026-04-08T14:00:00Z"
    },
    "items": [
      {
        "product_id": "550e8400-e29b-41d4-a716-446655440002",
        "name": "Gaming Laptop",
        "quantity": 1,
        "price": "1299.99"
      }
    ],
    "customer": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "address": "123 Main St, NY 10001"
    },
    "timeline": [
      {
        "status": "pending",
        "timestamp": "2026-04-06T10:00:00Z"
      },
      {
        "status": "paid",
        "timestamp": "2026-04-06T10:15:00Z"
      },
      {
        "status": "shipped",
        "timestamp": "2026-04-08T14:00:00Z"
      }
    ]
  }
}
```

---

### 11. Forgot Password

**Endpoint:** `api/forgot-pass.php`  
**Method:** POST

**Description:** Send password reset email with token.

**Request:**
```
POST /api/forgot-pass.php
Content-Type: application/json

{
  "email": "john@example.com"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Password reset link sent to your email",
  "data": {
    "email": "john@example.com",
    "message": "Check your email for password reset instructions"
  }
}
```

**Response (Error - Email Not Found):**
```json
{
  "success": false,
  "message": "Email address not found",
  "data": null
}
```

---

### 12. Update Password

**Endpoint:** `api/user-pass-update.php`  
**Method:** POST

**Description:** Update password with reset token or current password.

**Request (Using Token):**
```
POST /api/user-pass-update.php
Content-Type: application/json

{
  "reset_token": "abc123def456",
  "new_password": "NewPassword123",
  "confirm_password": "NewPassword123"
}
```

**Request (Using Current Password):**
```
POST /api/user-pass-update.php
Content-Type: application/json

{
  "current_password": "OldPassword123",
  "new_password": "NewPassword123",
  "confirm_password": "NewPassword123"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Password updated successfully",
  "data": {
    "message": "Your password has been changed"
  }
}
```

---

## Admin API Endpoints

### 1. Filter Orders (Search/List)

**Endpoint:** `admin/ajax/filter-orders.php`  
**Method:** GET  
**Authentication:** Required (admin)

**Parameters:**
- `search` - Order number or customer email
- `status` - Order status filter
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 25)

**Description:** Search and filter orders with advanced filters.

**Request:**
```
GET /admin/ajax/filter-orders.php?search=john@example.com&status=paid&page=1&limit=25
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Orders retrieved",
  "data": {
    "orders": [
      {
        "id": "550e8400-e29b-41d4-a716-446655440010",
        "order_number": "ORD-2026-0001",
        "customer_name": "John Doe",
        "customer_email": "john@example.com",
        "total_amount": "1479.97",
        "status": "paid",
        "created_at": "2026-04-06T10:00:00Z"
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 25,
    "pages": 1
  }
}
```

---

### 2. Get Order Details (Admin)

**Endpoint:** `admin/ajax/get-order-details.php`  
**Method:** GET  
**Authentication:** Required (admin)

**Parameters:**
- `order_id` - UUID of order

**Description:** Admin view of order with all details and actions.

**Request:**
```
GET /admin/ajax/get-order-details.php?order_id=550e8400-e29b-41d4-a716-446655440010
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Order details retrieved",
  "data": {
    "order": {
      "id": "550e8400-e29b-41d4-a716-446655440010",
      "order_number": "ORD-2026-0001",
      "status": "paid",
      "total_amount": "1479.97",
      "payment_method": "lenco",
      "shipping_address": "123 Main St, NY 10001",
      "notes": "Leave at front door"
    },
    "items": [
      {
        "product_id": "550e8400-e29b-41d4-a716-446655440002",
        "name": "Gaming Laptop",
        "sku": "LAP-GAMING-001",
        "quantity": 1,
        "price": "1299.99"
      }
    ],
    "customer": {
      "id": "550e8400-e29b-41d4-a716-446655440050",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    },
    "payment": {
      "transaction_id": "lenco_ref_123456789",
      "verified_at": "2026-04-06T10:15:00Z"
    },
    "invoice": {
      "invoice_number": "INV-2026-0001",
      "generated_at": "2026-04-06T10:20:00Z",
      "invoice_url": "/invoices/INV-2026-0001.pdf"
    }
  }
}
```

---

### 3. Update Order Status

**Endpoint:** `admin/ajax/update-order-status.php`  
**Method:** POST  
**Authentication:** Required (admin)

**Description:** Change order status (pending → paid → shipped → delivered).

**Request:**
```
POST /admin/ajax/update-order-status.php
Content-Type: application/json

{
  "order_id": "550e8400-e29b-41d4-a716-446655440010",
  "new_status": "shipped",
  "tracking_number": "TRK-123456789",
  "notify_customer": true
}
```

**Request Body:**
- `order_id` - Order UUID (required)
- `new_status` - New status: pending|paid|shipped|delivered|cancelled (required)
- `tracking_number` - Shipping tracking number (optional)
- `notify_customer` - Send email to customer (default: true)

**Response (Success):**
```json
{
  "success": true,
  "message": "Order status updated successfully",
  "data": {
    "order_id": "550e8400-e29b-41d4-a716-446655440010",
    "order_number": "ORD-2026-0001",
    "old_status": "paid",
    "new_status": "shipped",
    "tracking_number": "TRK-123456789",
    "customer_notified": true,
    "updated_at": "2026-04-08T15:30:00Z"
  }
}
```

---

## Admin Action Endpoints

### Add Single Product

**Endpoint:** `admin/actions/add_single_product.php`  
**Method:** POST  
**Authentication:** Required (admin)

**Description:** Create new product with images and categories.

**Request:**
```
POST /admin/actions/add_single_product.php
Content-Type: multipart/form-data

{
  "name": "Gaming Laptop Pro",
  "description": "High-performance gaming laptop",
  "sku": "LAP-GAMING-001",
  "price": "1299.99",
  "discount_price": "1199.99",
  "stock_quantity": "15",
  "categories": ["550e8400-e29b-41d4-a716-446655440060"],
  "primary_image": <file>,
  "gallery_images": [<file>, <file>],
  "specifications": {
    "processor": "Intel i9",
    "ram": "32GB",
    "storage": "1TB SSD"
  }
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Product added successfully",
  "data": {
    "product_id": "550e8400-e29b-41d4-a716-446655440070",
    "sku": "LAP-GAMING-001",
    "name": "Gaming Laptop Pro",
    "price": "1299.99"
  }
}
```

---

## Error Responses

### Common Error Responses

**Unauthorized (401):**
```json
{
  "success": false,
  "message": "User not authenticated",
  "data": null
}
```

**Forbidden (403):**
```json
{
  "success": false,
  "message": "Insufficient permissions",
  "data": null
}
```

**Bad Request (400):**
```json
{
  "success": false,
  "message": "Missing required parameter: order_id",
  "data": {
    "required": ["order_id"],
    "received": {}
  }
}
```

**Server Error (500):**
```json
{
  "success": false,
  "message": "An error occurred while processing your request",
  "data": null
}
```

---

## Rate Limiting

Currently: No rate limiting implemented.

**Recommendation:** Implement rate limiting before production deployment.

---

## API Authentication Flow

1. User logs in via `auth/login_process.php`
2. Session created with `$_SESSION['user_id']` or `$_SESSION['admin_id']`
3. All API calls automatically include session cookie
4. Backend validates session on each request
5. If unauthorized → Return 401, redirect to login

---

## CORS & Cross-Origin

Currently: CORS not explicitly configured.

**Implementation Ready:** All endpoints accept JSON and return JSON suitable for AJAX calls.

---

This API documentation provides complete reference for integrating with TemaTech-Innovation's backend services.
