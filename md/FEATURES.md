# Features Documentation

## Complete Feature List

TemaTech-Innovation is a comprehensive e-commerce platform with dual user interfaces: a customer shopping application and an administrative management system.

## Customer Features

### 1. Product Browsing & Discovery

**Located:** `components/pages/home.php`, `components/pages/products.php`

#### Features:
- **Homepage:**
  - Featured product categories (4-6 top categories)
  - New products carousel (newest 4 products)
  - Promotional banners
  - Quick category access buttons

- **Product Catalog:**
  - Full product listing with images
  - Filtering options:
    - By category dropdown
    - By price range (0-100, 100-500, 500-1000, 1000+)
    - Search by product name/keywords
  - Sorting options:
    - Newest first
    - Price: Low to High
    - Price: High to Low
    - Best Rated (if ratings implemented)
  - Pagination (6 items per page)
  - Product preview cards showing:
    - Product image (primary)
    - Product name
    - Rating (if available)
    - Price and discount (if applicable)
    - Stock availability indicator

#### Files Involved:
- `components/pages/home.php` - Homepage template
- `components/pages/products.php` - Catalog page
- `includes/navbar.php` - Navigation menu

---

### 2. Product Details & Information

**Located:** `components/pages/product-details.php`

#### Features:
- **Product Information:**
  - Primary product image (large)
  - Image gallery (thumbnails for switching views)
  - Product name and SKU
  - Current price and discount price (if on sale)
  - Stock status (In Stock / Out of Stock / Limited)
  - Product rating and review count
  - Detailed description
  - Technical specifications (JSON parsed)

- **Interactive Elements:**
  - Quantity selector (dropdown or +/- buttons)
  - "Add to Cart" button
  - "Add to Wishlist" button (optional)
  - Share product buttons (social media)

- **Related Products:**
  - Suggested items in same category
  - Items viewed recently

#### Database Queries:
- Query product by ID from products table
- Fetch all product images
- Get related products by category

---

### 3. Shopping Cart

**Located:** `components/pages/cart.php`, `includes/cart-manager.js`

#### Features:
- **Cart Display:**
  - List of all items in cart
  - Product image, name, price
  - Quantity controls (edit quantity directly)
  - Remove item button
  - Item subtotal (price × quantity)
  - Cart badge showing item count

- **Cart Operations:**
  - Add to cart (from product pages)
  - Update quantity (increase/decrease)
  - Remove items
  - Clear entire cart
  - View empty cart message with "Continue Shopping" link

- **Cart Totals:**
  - Subtotal (sum of all items)
  - Tax calculation (if applicable)
  - Shipping cost estimate
  - Grand total
  - Savings display (if discounted)

- **Cart Persistence:**
  - Guest cart stored in browser localStorage
  - Authenticated user cart synced to database
  - Auto-save on each change
  - Sync localStorage ↔ database on login

#### Implementation Files:
- `includes/cart-manager.js` - JavaScript cart logic
- `api/get-cart.php` - Fetch cart from database
- `api/save-cart.php` - Save cart changes
- `api/sync-cart.php` - Sync localStorage with DB on login
- `api/clear-cart.php` - Empty cart

---

### 4. Checkout & Order Creation

**Located:** `components/pages/checkout.php`, `api/get-checkout.php`

#### Features:
- **Customer Information Form:**
  - Full name (first/last name)
  - Email address
  - Phone number
  - Billing address (street, city, state, ZIP)
  - Shipping address (option to copy billing)
  - Special instructions/notes

- **Order Summary:**
  - List of items being purchased
  - Quantity and price per item
  - Subtotal calculation
  - Taxes/fees breakdown
  - Total amount

- **Shipping Options:**
  - Standard shipping
  - Express shipping (if available)
  - Local pickup (if available)
  - Shipping cost display

- **Payment Method Selection:**
  - Radio buttons for payment options
  - Lenco payment gateway (primary)
  - Bank transfer option
  - Other payment methods (extensible)

- **Checkout Validation:**
  - Form field validation (required fields)
  - Email format validation
  - Address validation
  - Stock verification (ensure items still in stock)

#### Files:
- `components/pages/checkout.php` - Frontend form
- `api/get-checkout.php` - Process checkout, create order

---

### 5. Payment Processing

**Located:** `components/pages/checkout.php`, `api/verify-payment.php`

#### Features:
- **Lenco Payment Gateway Integration:**
  - Redirect to Lenco checkout page
  - Pass order amount and reference
  - Secure payment processing on Lenco servers
  - Return to checkout page with payment reference

- **Payment Verification:**
  - Call Lenco API to verify transaction
  - Update order status from "pending" to "paid"
  - Update payment_history table
  - Mark cart as "ordered"

- **Payment Status:**
  - Pending (awaiting payment)
  - Verified (payment confirmed)
  - Failed (payment declined)
  - Refunded (full refund issued)

- **Security:**
  - Use Lenco API key from environment
  - HTTPS communication
  - Signature verification
  - Session verification

#### Files:
- `api/verify-payment.php` - Verify payment from Lenco
- Environment variables: `LENCO_SECRET_KEY`, `LENCO_PUBLIC_KEY`

---

### 6. Invoice Generation & Email

**Located:** `api/generate-invoice.php`, `api/send-email.php`

#### Features:
- **Invoice PDF Generation:**
  - Order number and date
  - Customer information
  - Itemized list (product name, quantity, price)
  - Subtotal, taxes, shipping, total
  - Company/store information
  - Payment method used
  - Estimated delivery date
  - Professional formatting with logo

- **Email Delivery:**
  - Send invoice PDF via email
  - PHPMailer with Gmail SMTP
  - HTML formatted email template
  - Attachment: PDF invoice
  - Email to customer's registered email
  - CC: store email (optional)

- **Email Content:**
  - Greeting with customer name
  - Order confirmation message
  - Order number and date
  - Invoice attachment
  - Tracking link (when available)
  - Support contact information
  - Social media links

#### Files:
- `api/generate-invoice.php` - Create PDF invoice using dompdf
- `api/send-email.php` - Send email using PHPMailer
- Configuration: `config/env.php` for email settings

---

### 7. Order Tracking & History

**Located:** `users/components/pages/user_orders.php`, `api/get-order-details.php`

#### Features:
- **Order List:**
  - All customer's orders
  - Order number, date, total amount
  - Current status (pending/paid/shipped/delivered/cancelled)
  - Status visual indicator (badges/colors)
  - Sortable by date (newest/oldest)
  - Pagination

- **Order Details:**
  - Full order information
  - Items purchased (product names, quantities, prices)
  - Order status timeline/history
  - Expected delivery date
  - Tracking number (if available)
  - Download invoice PDF button
  - Retry payment button (if pending payment)

- **Order Actions:**
  - View order details
  - Download invoice
  - Print order
  - Contact support about order
  - Request cancellation (if eligible)

#### Files:
- `users/components/pages/user_orders.php` - Frontend
- `api/get-order-details.php` - Get order data

---

### 8. User Account Management

**Located:** `users/components/pages/user_dashboard.php`, `users/components/pages/edit-profile.php`

#### Features:
- **Profile Information:**
  - View profile: name, email, phone
  - Edit profile: update name, phone, address
  - Save profile changes
  - Account information

- **Account Settings:**
  - Change password
  - Change email
  - Multi-factor authentication (optional)
  - Account preferences

- **Dashboard:**
  - Quick stats (total orders, total spent, saved items)
  - Recent order preview
  - Quick links to orders, wishlist, settings

- **Address Book:**
  - Save multiple addresses
  - Mark default shipping address
  - Mark default billing address
  - Edit/delete addresses

---

### 9. Password Management

**Located:** `auth/login_process.php`, `api/forgot-pass.php`, `api/user-pass-update.php`

#### Features:
- **Forgot Password:**
  - Enter email address
  - System generates reset token (unique + 1-hour expiry)
  - Email sent with reset link
  - Link contains reset token

- **Reset Password:**
  - User clicks link in email
  - Enter new password twice
  - System verifies token validity
  - Hash new password with bcrypt
  - Update password in database
  - Confirmation message
  - Redirect to login

- **Change Password (Logged In):**
  - Enter current password (verify)
  - Enter new password twice
  - Validate new password != current password
  - Update in database
  - Confirmation message

#### Files:
- `api/forgot-pass.php` - Email password reset link
- `api/user-pass-update.php` - Update password with token

---

### 10. Customer Messaging & Support

**Located:** Tables: conversations, messages (database)

#### Features:
- **Start Conversation:**
  - Create new support ticket
  - Select conversation category (order, support, promotion)
  - Subject line
  - Initial message
  - Attach files (order documents, screenshots)

- **Message Thread:**
  - View conversation history
  - Send new messages
  - See timestamps for each message
  - Identify sender (customer vs admin)

- **Conversation Management:**
  - Mark as important/star messages
  - Close conversation
  - Reopen closed conversation
  - View all conversations list
  - Filter by status (open/closed)
  - Search conversations

- **Notifications:**
  - Notified when admin replies
  - Email alert on new admin message
  - Badge showing unread messages

#### Database Tables:
- conversations - conversation threads
- messages - individual messages
- conversation_participants - users involved
- message_stars - starred messages

---

## 👨‍💼 Admin Features

### 1. Admin Dashboard

**Located:** `admin/components/pages/admin_dashboard.php`

#### Features:
- **Key Metrics:**
  - Total revenue (current month/year)
  - Number of orders (today/this week/month)
  - Number of active customers
  - Inventory status (low stock alerts)
  - Payment status summary

- **Charts & Visualizations:**
  - Sales trend chart (revenue over time)
  - Top products (by sales)
  - Top categories (by revenue)
  - Order status distribution (pie chart)
  - Customer growth chart

- **Quick Actions:**
  - New order button
  - Add product button
  - View pending orders
  - View new customers
  - Manage support tickets

- **Recent Items:**
  - Latest 5 orders
  - Recent customers
  - Low stock notifications

---

### 2. Product Management

**Located:** `admin/components/pages/manage_products.php`, `admin/actions/add_single_product.php`

#### Features:
- **Product CRUD Operations:**
  - View all products (sortable table)
  - Search products by name/SKU
  - Filter by category, status, price range
  - Pagination

- **Add Product:**
  - Product name and description
  - SKU (unique identifier)
  - Price and discount price
  - Category selection (multi-select)
  - Stock quantity
  - Product status (active/inactive)
  - Upload product images (primary + gallery)
  - Add product specifications (JSON)

- **Edit Product:**
  - Modify all product information
  - Update categories
  - Change price/discount
  - Adjust stock
  - Replace/add/remove images
  - Update status

- **Delete Product:**
  - Delete product (cascade to cart_items, order_items)
  - Archive product (soft delete alternative)

- **Bulk Operations:**
  - Bulk product upload from CSV/Excel
  - Bulk price changes
  - Bulk category assignment
  - Bulk status changes

#### Files:
- `admin/components/pages/manage_products.php` - Frontend
- `admin/actions/add_single_product.php` - Add product
- `admin/actions/edit-product-process.php` - Edit product

---

### 3. Order Management

**Located:** `admin/components/pages/manage_orders.php`, `admin/ajax/filter-orders.php`

#### Features:
- **Order List:**
  - All orders with pagination
  - Order number, date, customer name
  - Total amount and status
  - Sort by date, amount, status
  - Search by order number or customer email

- **Order Filtering:**
  - Filter by date range (from/to)
  - Filter by status (pending/paid/shipped/delivered/cancelled)
  - Filter by payment method
  - Filter by amount range

- **Order Details:**
  - Full order information
  - Customer details (name, email, address)
  - Items purchased (product name, quantity, price)
  - Payment information
  - Shipping address
  - Special notes

- **Order Actions:**
  - Update order status
  - Generate new invoice
  - Send/resend invoice email
  - Print order
  - Add order notes
  - Process refund (if needed)
  - Cancel order

- **Status Management:**
  - Update from pending → paid
  - Update from paid → shipped
  - Update from shipped → delivered
  - Cancel order (with reason)
  - Auto-notify customer on status change

#### Files:
- `admin/components/pages/manage_orders.php` - Frontend
- `admin/ajax/filter-orders.php` - Search/filter AJAX
- `admin/ajax/get-order-details.php` - Get order details
- `admin/ajax/update-order-status.php` - Update status

---

### 4. Payment Management

**Located:** `admin/components/pages/manage_payments.php`

#### Features:
- **Payment History:**
  - All transactions in table format
  - Order number, customer name, amount
  - Payment method, transaction ID
  - Payment status (verified/failed/refunded)
  - Date of payment

- **Payment Filtering:**
  - Filter by status
  - Filter by date range
  - Filter by payment method
  - Search by transaction ID/order number

- **Refund Processing:**
  - View refund requests
  - Process refunds
  - Track refund status
  - Log refund reason

- **Reconciliation:**
  - Verify all payments
  - Identify failed transactions
  - Match orders to payments
  - Generate payment reports

---

### 5. Customer Management

**Located:** `admin/components/pages/manage_customer.php`

#### Features:
- **Customer List:**
  - All registered customers
  - Name, email, phone, registration date
  - Total orders, total spent
  - Account status (active/inactive)
  - Search by name or email

- **Customer Details:**
  - Full profile information
  - Order history
  - Contact information
  - Addresses on file
  - Communication history

- **Customer Actions:**
  - View customer profile
  - Edit customer information
  - Activate/deactivate account (`toggle_user_status.php`)
  - Delete customer (`delete_user.php`)
  - Send message/email
  - Process refunds for customer
  - View customer's conversations

- **Customer Filtering:**
  - Filter by registration date
  - Filter by total spent
  - Filter by status (active/inactive)
  - Search by location

#### Files:
- `admin/components/pages/manage_customer.php` - Frontend
- `admin/actions/toggle_user_status.php` - Activate/deactivate
- `admin/actions/delete_user.php` - Delete customer

---

### 6. Admin User Management

**Located:** `admin/components/pages/manage_admins.php`

#### Features:
- **Admin List:**
  - All admin accounts
  - Name, email, role (admin/staff)
  - Status (active/inactive)
  - Last login information

- **Add Admin:**
  - Create new admin or staff account
  - Set email and temporary password
  - Assign role (admin or staff)
  - Set status (active)

- **Edit Admin:**
  - Update admin information
  - Change role (admin ↔ staff)
  - Deactivate account
  - Force password reset

- **Permissions:**
  - Admin role: Full system access
  - Staff role: Limited to operational tasks (orders, customers, products)

- **Role-Based Actions:**
  - Admin can manage other admins
  - Admin can modify admin roles
  - Staff cannot access settings or admin management

#### Files:
- `admin/components/pages/manage_admins.php` - Frontend
- `admin/actions/reset_password.php` - Reset admin password

---

### 7. Shipping Management

**Located:** `admin/components/pages/manage_shipping.php`

#### Features:
- **Shipping List:**
  - All active shipments
  - Order number, customer, destination
  - Shipping status and tracking number
  - Estimated/actual delivery date

- **Shipping Actions:**
  - Mark order as shipped
  - Add tracking number
  - Set estimated delivery date
  - Track shipment status
  - Print shipping label

- **Carrier Integration:**
  - Integrated shipping carriers (if implemented)
  - Generate shipping labels
  - Print packing slips
  - Bulk shipment processing

---

### 8. Statistics & Analytics

**Located:** `admin/components/pages/manage_stats.php`

#### Features:
- **Sales Analytics:**
  - Total revenue (current/previous period)
  - Revenue growth percentage
  - Average order value
  - Orders per day/week/month

- **Product Analytics:**
  - Top 10 products by sales
  - Top products by revenue
  - Least selling products
  - Inventory turnover

- **Customer Analytics:**
  - New customers (period)
  - Total registered customers
  - Repeat customer rate
  - Customer lifetime value

- **Category Analytics:**
  - Sales by category
  - Top performing categories
  - Category growth trends

- **Report Generation:**
  - Generate PDF reports
  - Date range selection
  - Export to CSV/Excel
  - Schedule automated reports

---

### 9. Settings & Configuration

**Located:** `admin/components/pages/settings.php`

#### Features:
- **Store Settings:**
  - Store name and logo
  - Contact email
  - Contact phone
  - Store address
  - Business hours

- **Payment Settings:**
  - Lenco API keys (Secret & Public)
  - Payment methods enabled/disabled
  - Transaction fees

- **Email Settings:**
  - Email service configuration
  - SMTP settings (Gmail, etc.)
  - Email templates
  - Notification preferences

- **Tax Settings:**
  - Tax rates by location
  - Tax calculation method
  - Tax exemptions

- **Shipping Settings:**
  - Default shipping cost
  - Free shipping threshold
  - Shipping carriers
  - Delivery timeframes

- **General Settings:**
  - Site language
  - Currency
  - Date format
  - Time zone

---

### 10. Customer Messaging/Support

**Located:** Conversations module (admin side)

#### Features:
- **View Messages:**
  - All customer conversations
  - Filter by status, category, date
  - Search conversations
  - Sort by recent/oldest

- **Reply to Messages:**
  - Read customer messages
  - Compose replies
  - Attach files/documents
  - See conversation history

- **Conversation Management:**
  - Assign category (order, support, promotion)
  - Mark as resolved/closed
  - Pin important conversations
  - Add internal notes

- **Notifications:**
  - Alert on new customer message
  - Mark as read/unread
  - Set priority (high/medium/low)

---

## 🔐 Authentication & Authorization Features

### Login System
- **Dual-User Login:** Admin or Customer
- **Email-Based Authentication:** No username required
- **Password Hashing:** bcrypt ($2y$10$) for security
- **Session Management:** PHP $_SESSION variables
- **Login Tracking:** Last login timestamp recorded

### Role-Based Access Control (RBAC)
- **Customer Role:** Shop, checkout, view orders, messaging
- **Admin Role:** Full system access
- **Staff Role:** Limited access (orders, customers, products only)

### Password Reset
- **Forgot Password:** Email with reset link + token
- **Token Expiry:** 1 hour validity
- **Password Update:** Secure hashing before storage

### Session Management
- Session created on login
- Session destroyed on logout
- Timeout handling (configurable)
- Multiple browser session support (or replace old session)

---

## 📧 Email Notification System

### Email Types
1. **Order Confirmation** - On successful payment
2. **Order Status Updates** - On status changes (shipped, delivered)
3. **Password Reset** - For account recovery
4. **Support Responses** - When admin replies to message
5. **Promotional Emails** - Offers and announcements

### Email Configuration
- **Service:** PHPMailer 7.0
- **SMTP Server:** Gmail (configurable)
- **Port:** 587 (TLS encryption)
- **Authentication:** App-specific password (secure)

---

## 🛡️ Security Features

- ✅ **UUID Primary Keys** - No sequential ID exposure
- ✅ **bcrypt Password Hashing** - Industry standard
- ✅ **Prepared Statements** - SQL injection prevention
- ✅ **Session-Based Auth** - Server-side session management
- ✅ **Role-Based Access** - Permission system
- ✅ **HTTPS Ready** - Secure communication support
- ✅ **Input Validation** - Server and client-side
- ✅ **Environment Variables** - Sensitive data protection

---

## 🌐 7 Product Categories

1. **Computers** - Desktops, towers, workstations
2. **Laptops** - Portable computers, ultrabooks
3. **Keyboards** - Mechanical, membrane, wireless
4. **Motherboards** - Desktop and server boards
5. **Processors** - CPUs for various systems
6. **Mouse** - Wired and wireless
7. **Accessories** - Cases, cables, cooling, peripherals

---

This comprehensive feature set creates a complete e-commerce platform for both shoppers and administrators.
