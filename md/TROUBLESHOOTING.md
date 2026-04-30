# Troubleshooting Guide

## Common Issues & Solutions

### Section 1: Database Connection Issues

#### Issue 1.1: "Access Denied for User 'root'@'localhost'"

**Error Message:**
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' 
(using password: YES)
```

**Possible Causes:**
- Wrong MySQL password
- Wrong database username
- MySQL not running
- Database not created

**Solutions:**

1. **Clear text credentials:**
   ```bash
   # Stop XAMPP services
   # Delete all MySQL data
   # Restart MySQL fresh
   ```

2. **Verify MySQL is running:**
   - Open XAMPP Control Panel
   - Check MySQL shows "Running" status
   - If not, click Start

3. **Check config/db.php:**
   ```php
   'host' => 'localhost',      // Not 127.0.0.1
   'database' => 'tematech_innovation',
   'username' => 'root',       // For XAMPP
   'password' => '',           // Empty for XAMPP default
   ```

4. **Test credentials in command line:**
   ```bash
   mysql -u root -p
   # Press Enter when prompted for password (leave blank)
   # If successful, shows "mysql>"
   ```

5. **Reset MySQL password:**
   ```bash
   # Windows (XAMPP directory)
   mysql -u root < config/mysql-reset.sql
   ```

---

#### Issue 1.2: "Unknown Database 'tematech_innovation'"

**Error Message:**
```
SQLSTATE[HY000]: General error: 1049 Unknown database 'tematech_innovation'
```

**Possible Causes:**
- Database not created
- Database name misspelled
- Wrong database name in config

**Solutions:**

1. **Create database via phpMyAdmin:**
   - Open http://localhost/phpmyadmin
   - Log in (username: root, no password)
   - Click "New"
   - Enter "tematech_innovation"
   - Click Create

2. **Or via command line:**
   ```bash
   mysql -u root
   CREATE DATABASE tematech_innovation;
   USE tematech_innovation;
   SOURCE database/tematech_innovation\ \(clean\ db\).sql
   ```

3. **Verify database exists:**
   ```bash
   mysql -u root -e "SHOW DATABASES;"
   # Should list: tematech_innovation
   ```

---

#### Issue 1.3: "Table 'tematech_innovation.customers' Doesn't Exist"

**Error Message:**
```
SQLSTATE[42S02]: Table not found: 1146 Table 'tematech_innovation.customers' doesn't exist
```

**Possible Causes:**
- Database created but not imported
- SQL import failed
- Schema tables deleted

**Solutions:**

1. **Re-import database schema:**
   - Open phpMyAdmin
   - Select database
   - Click "Import"
   - Upload: `database/tematech_innovation (clean db).sql`
   - Click Import

2. **Verify all tables exist:**
   ```bash
   mysql -u root -e "USE tematech_innovation; SHOW TABLES;"
   # Should list all 16 tables
   ```

3. **Check import errors:**
   - View import log for error messages
   - Common: charset issues, duplicate keys
   - Solution: Delete database and re-import fresh

---

### Section 2: Authentication & Login Issues

#### Issue 2.1: Cannot Login - "Invalid Email or Password"

**Symptoms:**
- Correct credentials don't work
- All credentials fail
- Login loops back to login page

**Causes:**
- Password hash incorrect in database
- Session not created
- Cookies disabled

**Solutions:**

1. **Reset admin password:**
   ```sql
   USE tematech_innovation;
   UPDATE admins SET password_hash = '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lm' 
   WHERE email = 'admin@TemaTech.com';
   -- Password: password
   ```

2. **Check browser cookies:**
   - Settings → Privacy/Cookies
   - Ensure cookies are enabled for localhost
   - Clear cookies and try again

3. **Verify session configuration:**
   - Check `php.ini` for session settings
   - Ensure `/tmp` directory has write permissions:
   ```bash
   chmod 1777 /tmp
   ```

4. **Test session creation:**
   ```php
   <?php
   session_start();
   $_SESSION['test'] = 'value';
   echo "Session ID: " . session_id();
   ?>
   ```

---

#### Issue 2.2: "User Not Authenticated" on API Calls

**Error:**
```json
{
  "success": false,
  "message": "User not authenticated"
}
```

**Causes:**
- Session expired
- Session not created during login
- AJAX not sending credentials/cookies

**Solutions:**

1. **Ensure session_start() called:**
   - Check every api file starts with:
   ```php
   <?php
   session_start();
   ```

2. **Check AJAX includes credentials:**
   ```javascript
   // Missing: credentials setting
   ❌ fetch('/api/order.php', { ... })
   
   // Correct: includes credentials
   ✅ fetch('/api/order.php', {
     credentials: 'include',  // Important!
     ...
   })
   ```

3. **Verify login worked:**
   - After login, check session exists
   - F12 → Application → Cookies
   - Should see PHPSESSID cookie
   - If not, login failed

4. **Test session manually:**
   ```php
   <?php
   session_start();
   var_dump($_SESSION);  // Should show user_id
   ?>
   ```

---

### Section 3: Payment Gateway Issues

#### Issue 3.1: Lenco Payment Verification Fails

**Error:**
```
"Payment verification failed: Transaction not found"
```

**Causes:**
- Invalid Lenco API keys
- Lenco sandbox vs production keys mismatch
- Wrong payment reference passed
- Network/connectivity issue

**Solutions:**

1. **Verify API keys in .env:**
   ```
   LENCO_SECRET_KEY=your_actual_secret_key
   LENCO_PUBLIC_KEY=your_actual_public_key
   ```

2. **Test Lenco connection:**
   ```php
   <?php
   $secretKey = getenv('LENCO_SECRET_KEY');
   
   $ch = curl_init('https://api.lenco.co/api/v1/verify');
   curl_setopt($ch, CURLOPT_HTTPHEADER, [
       "Authorization: Bearer $secretKey"
   ]);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   $response = curl_exec($ch);
   curl_close($ch);
   
   echo $response;  // Should not be empty or error
   ?>
   ```

3. **Check sandbox vs production:**
   - Ensure using correct keys for environment
   - Sandbox keys for testing
   - Production keys for live

4. **Verify payment reference format:**
   - Check payment_reference passed correctly
   - Should match Lenco format exactly
   - Case-sensitive

---

#### Issue 3.2: Payment Succeeds but Order Not Updated

**Symptoms:**
- Lenco confirms payment
- Order status still "pending"
- Invoice not generated
- Email not sent

**Causes:**
- verify-payment.php not reached
- Error in status update query
- Transaction not found in database

**Solutions:**

1. **Check JavaScript redirects correctly:**
   ```javascript
   // After payment
   if (response.success) {
       // Should redirect to checkout with success param
       window.location = 'checkout.php?payment=success&ref=' + reference;
   }
   ```

2. **Verify payment_history table:**
   ```sql
   SELECT * FROM payment_history WHERE order_id = 'order_uuid';
   -- Should show transaction with 'verified' status
   ```

3. **Check order status update:**
   ```bash
   # Look in error logs for why UPDATE failed
   tail -f /var/log/php/error.log
   ```

4. **Manual order status update:**
   ```sql
   UPDATE orders SET status = 'paid' WHERE order_number = 'ORD-2026-0001';
   INSERT INTO payment_history (order_id, amount, status, transaction_id)
   VALUES ('order_uuid', 1299.99, 'verified', 'lenco_ref_123');
   ```

---

### Section 4: Email & Notification Issues

#### Issue 4.1: "Swift_TransportException: Unable to Send Message"

**Error Message:**
```
Swift_TransportException: Unable to send message with this transport
```

**Causes:**
- Gmail SMTP credentials wrong
- 2-factor authentication issue
- App password not set correctly
- Firewall blocking SMTP port

**Solutions:**

1. **Verify Gmail SMTP settings:**
   ```php
   // config/env.php should have:
   MAIL_HOST='smtp.gmail.com'
   MAIL_PORT=587
   MAIL_USERNAME='your-email@gmail.com'
   MAIL_PASSWORD='app-specific-password'  // NOT main password!
   ```

2. **Use app password, not main password:**
   - Go to myaccount.google.com
   - Select Security
   - Enable 2-Step Verification
   - Generate "App passwords"
   - Use this 16-character password
   - NOT your main Gmail password

3. **Test SMTP connection:**
   ```bash
   telnet smtp.gmail.com 587
   # Should connect successfully
   ```

4. **Check firewall:**
   - Ensure port 587 (TLS) not blocked
   - Ask host to unblock if needed
   - Some hosts only allow port 25

---

#### Issue 4.2: Emails Not Received

**Symptoms:**
- send-email.php returns success
- Email log shows sent
- Customer never receives email

**Causes:**
- Emails going to spam folder
- Email bounce/delivery error
- Wrong recipient email

**Solutions:**

1. **Check spam/promotions folder:**
   - Look for emails from noreply@tematech.com
   - May need whitelist

2. **Verify recipient email:**
   ```php
   // In send-email.php
   echo "Sending to: " . $customerEmail;  // Verify correct
   ```

3. **Check mail logs:**
   ```bash
   # Linux
   tail -f /var/log/mail.log
   
   # macOS
   tail -f /var/log/mail.log
   ```

4. **Enable SPF/DKIM:**
   - DNS records for email authentication
   - Reduces spam filtering
   - Add to domain DNS settings

---

### Section 5: File Upload Issues

#### Issue 5.1: "Cannot Upload Product Images"

**Error:**
```
Failed to upload file
```

**Causes:**
- Permissions on public/images/ directory
- File size too large
- File type not allowed
- Max upload size in php.ini

**Solutions:**

1. **Fix directory permissions:**
   ```bash
   chmod 755 public/
   chmod 755 public/images/
   chmod 755 public/images/products/
   chmod 755 public/category/
   ```

2. **Check php.ini limits:**
   ```php
   // php.ini settings
   upload_max_filesize = 10M
   post_max_size = 10M
   memory_limit = 128M
   
   // Verify with:
   phpinfo();  // Look for upload limits
   ```

3. **Check file type validation:**
   ```php
   // In add_single_product.php
   $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
   echo "File type: " . $_FILES['image']['type'];  // Debug
   ```

4. **Verify storage directory exists:**
   ```bash
   mkdir -p public/images/products
   mkdir -p public/category
   ```

---

#### Issue 5.2: Uploaded Images Not Displaying

**Symptoms:**
- File uploads successfully
- Not visible in product pages
- Broken image icon

**Causes:**
- Wrong image path in database
- Images not actually saved
- Path doesn't exist

**Solutions:**

1. **Check database image URL:**
   ```sql
   SELECT * FROM product_images LIMIT 1;
   -- Should show valid path like: public/images/products/abc123.jpg
   ```

2. **Verify file exists:**
   ```bash
   ls -la public/images/products/
   # Should list uploaded images
   ```

3. **Check file permissions:**
   ```bash
   chmod 644 public/images/products/*  # Make readable
   ```

4. **Verify path in HTML:**
   ```html
   <img src="<?php echo $imagePath; ?>">
   <!-- Debug: Check actual src attribute in browser -->
   <!-- Should be: public/images/products/filename.jpg -->
   ```

---

### Section 6: Performance Issues

#### Issue 6.1: "Page Loading Very Slow" (>10 seconds)

**Symptoms:**
- Products page loads slowly
- Admin dashboard hangs
- Database queries slow

**Causes:**
- Missing database indexes
- N+1 query problem
- Large result sets without pagination
- Slow API responses

**Solutions:**

1. **Add indexes to frequently queried columns:**
   ```sql
   -- Check for existing indexes
   SHOW INDEX FROM products;
   SHOW INDEX FROM customers;
   
   -- Add missing indexes
   CREATE INDEX idx_status ON products(status);
   CREATE INDEX idx_price ON products(price);
   CREATE INDEX idx_email ON customers(email);
   CREATE INDEX idx_created_at ON orders(created_at);
   ```

2. **Use pagination:**
   ```php
   // Instead of loading all 10,000 products
   $limit = 25;  // Per page
   $offset = ($page - 1) * $limit;
   $stmt = $pdo->prepare("SELECT * FROM products LIMIT ? OFFSET ?");
   $stmt->execute([$limit, $offset]);
   ```

3. **Use EXPLAIN to analyze queries:**
   ```sql
   EXPLAIN SELECT * FROM orders WHERE customer_id = '...' AND status = 'paid';
   -- Check if using indexes (key column not NULL)
   ```

4. **Limit result columns:**
   ```php
   // Instead of SELECT *
   ✅ SELECT id, name, price FROM products
   
   // Instead of SELECT all columns
   ❌ SELECT * FROM products
   ```

---

#### Issue 6.2: "Out of Memory" Error

**Error:**
```
Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Causes:**
- Loading too many records at once
- Infinite loops
- Large file operations
- Memory leak

**Solutions:**

1. **Increase memory limit in php.ini:**
   ```ini
   memory_limit = 256M  ; Increase from 128M
   ```

2. **Stream large data:**
   ```php
   // Process records in batches
   $limit = 100;
   for ($offset = 0; $offset < $total; $offset += $limit) {
       $stmt = $pdo->prepare("SELECT * FROM products LIMIT ? OFFSET ?");
       $stmt->execute([$limit, $offset]);
       // Process batch
   }
   ```

3. **Check for infinite loops:**
   ```php
   set_time_limit(30);  // 30-second timeout
   max_execution_time = 30  ; in php.ini
   ```

---

### Section 7: Cart & Checkout Issues

#### Issue 7.1: "Cart Items Not Persisting"

**Symptoms:**
- Items added to cart disappear
- After page reload, cart empty
- Guest cart lost after login

**Causes:**
- localStorage cleared
- Database cart not created
- sync-cart.php error
- Session issues

**Solutions:**

1. **Check browser localStorage:**
   ```javascript
   // In console
   console.log(localStorage.getItem('cart'));
   // Should show cart JSON
   ```

2. **Verify database cart created:**
   ```sql
   SELECT * FROM carts WHERE customer_id = 'user_uuid';
   -- Should show active cart
   ```

3. **Check sync-cart.php runs on login:**
   ```javascript
   // After login, should sync
   if (response.success) {
       fetch('/api/sync-cart.php', {
           method: 'POST',
           credentials: 'include',
           body: JSON.stringify({ ... })
       });
   }
   ```

4. **Debug cart-manager.js:**
   ```javascript
   // In browser console
   console.log(CartManager.getItems());
   // Should return array of items
   ```

---

#### Issue 7.2: "Checkout Error: Cart is Empty"

**Error:**
```json
{ "success": false, "message": "Cart is empty" }
```

**Causes:**
- Cart items deleted
- Wrong cart_id used
- Session user_id not matching

**Solutions:**

1. **Verify cart_id in checkout:**
   ```php
   // In get-checkout.php
   echo "User ID: " . $_SESSION['user_id'];
   echo "Cart ID: " . $cartId;
   ```

2. **Check database cart:**
   ```sql
   SELECT ci.* FROM cart_items ci
   WHERE ci.cart_id IN (
       SELECT id FROM carts WHERE customer_id = 'user_uuid' AND status = 'active'
   );
   -- Should show items
   ```

3. **Verify cart items exist:**
   ```javascript
   // Before checkout, check items
   const cart = CartManager.getItems();
   if (cart.length === 0) {
       alert('Please add items to cart');
       return;
   }
   ```

---

### Section 8: Admin Panel Issues

#### Issue 8.1: "Admin Panel Blank / Nothing Displays"

**Symptoms:**
- `/admin` shows blank white page
- No JavaScript errors
- No content

**Causes:**
- PHP error not displayed
- Missing includes
- Session check blocking
- Fatal error

**Solutions:**

1. **Enable error display (development only):**
   ```php
   // At top of admin/index.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

2. **Check error log:**
   ```bash
   # Linux
   tail -f /var/log/apache2/error.log
   
   # XAMPP Windows
   C:\xampp\apache\logs\error.log
   ```

3. **Verify session exists:**
   ```php
   session_start();
   var_dump($_SESSION);  // Should show admin_id
   ```

4. **Check admin/index.php exists:**
   ```bash
   ls -la admin/
   # Should show index.php
   ```

---

#### Issue 8.2: "Admin Cannot See Products/Orders"

**Symptoms:**
- Admin logged in
- Dashboard blank
- No data loads

**Causes:**
- Database queries failing
- No data in database
- Wrong WHERE clause

**Solutions:**

1. **Check database has records:**
   ```sql
   SELECT COUNT(*) FROM products;
   SELECT COUNT(*) FROM orders;
   -- Should not be 0
   ```

2. **Add test product:**
   ```sql
   INSERT INTO products (id, name, sku, price, status) 
   VALUES (UUID(), 'Test Product', 'TEST-001', 99.99, 'active');
   ```

3. **Check query in admin page:**
   ```php
   // Debug query
   echo "Query: " . $query;
   var_dump($stmt->errorInfo());  // Error details
   ```

---

## Quick Reference

### Emergency Procedures

**Database Connection Lost:**
1. Restart MySQL
2. Check config/db.php
3. Test credentials in phpMyAdmin

**User Locked Out:**
1. Reset password in database
2. or Create new admin account
3. Test login

**Payment Not Working:**
1. Verify Lenco keys in .env
2. Check Lenco status page
3. Test with sandbox keys

**Email Not Sending:**
1. Verify Gmail/SMTP settings
2. Check app password correct
3. Test SMTP connection

**Page Won't Load:**
1. Check error_log
2. Clear browser cache
3. Restart Apache

---

## Getting Help

1. **Check Documentation:**
   - Review relevant .md files
   - Check code comments
   - Look at similar implementations

2. **Check Logs:**
   - PHP error log
   - Apache/Nginx error log
   - Application error logs

3. **Test in Isolation:**
   - Create test script
   - Test one component at a time
   - Verify each step

4. **Consult References:**
   - PHP docs
   - Laravel docs
   - Stack Overflow

---

This troubleshooting guide should help resolve most common issues. For persistent problems, check logs and error messages carefully.
