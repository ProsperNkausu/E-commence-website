# Development Guide

## Code Standards & Conventions

### PHP Code Style

#### File Structure
```php
<?php
// 1. Namespace (if applicable)
namespace App\Services;

// 2. Use statements
use Illuminate\Database\Connection;

// 3. Class constants
// 4. Class variables
// 5. Constructor
// 6. Public methods
// 7. Protected methods
// 8. Private methods
// 9. Magic methods (__toString, etc.)
```

#### Naming Conventions

| Element | Style | Example |
|---------|-------|---------|
| Classes | PascalCase | `OrderProcessor`, `CartManager` |
| Methods | camelCase | `processPayment()`, `getOrderDetails()` |
| Properties | camelCase | `$cartItems`, `$totalAmount` |
| Functions | snake_case | `send_email()`, `verify_payment()` |
| Constants | UPPER_SNAKE_CASE | `MAX_CART_ITEMS`, `DEFAULT_TIMEOUT` |
| Files | lowercase/snake_case | `order-processor.php`, `cart-manager.js` |

#### Code Formatting

**Indentation:**
- 4 spaces (not tabs)
- Consistent throughout file

**Braces:**
```php
// Opening brace on same line
if ($condition) {
    // Code
} else {
    // Code
}

// Functions
function processOrder($orderId) {
    // Code
}
```

**Spacing:**
```php
// Space after control structures
if ($value) { }
for ($i = 0; $i < 10; $i++) { }

// Space around operators
$total = $subtotal + $tax - $discount;
$isActive = ($status === 'active');

// No space inside parentheses
$result = calculate($a, $b, $c);
```

### HTML/CSS Style

#### HTML Structure
```html
<!-- Semantic HTML5 -->
<header>
  <nav>Navigation</nav>
</header>
<main>
  <section>
    <article>Content</article>
  </section>
</main>
<footer>Footer</footer>

<!-- Proper indentation -->
<div class="container">
  <div class="row">
    <div class="col">
      <!-- Nested content -->
    </div>
  </div>
</div>
```

#### CSS Classes
```css
/* BEM naming convention */
.product-card { }
.product-card__image { }
.product-card__title { }
.product-card--featured { }

/* Lowercase with hyphens */
.btn-primary { }
.text-center { }
.mb-20 { }
```

### JavaScript Style

#### File Organization
```javascript
// 1. Constants
const API_ENDPOINT = '/api/';
const TIMEOUT = 5000;

// 2. Helper functions
function formatPrice(price) { }
function validateEmail(email) { }

// 3. Main code
window.addEventListener('DOMContentLoaded', () => {
  initializeApp();
});

// 4. Event listeners
document.getElementById('cart-btn').addEventListener('click', () => {
  addToCart();
});
```

#### Variable Declarations
```javascript
// Use const by default
const MAX_ITEMS = 10;

// Use let for variables that change
let currentCart = [];

// Avoid var (legacy)
// var oldStyle = 'avoid';

// Descriptive names
const isUserLoggedIn = true;  // Not: let x = true

// Camel case for variables/functions
const orderTotal = 1299.99;
function calculateTax() { }
```

---

## Adding New Features

### Adding a New Customer Page

**Step 1: Create Page Component**
```php
// components/pages/new-page.php
<?php
session_start();
require_once 'config/db.php';
require_once 'includes/api-helper.js';
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Page - TemaTech</title>
    <?php include 'includes/navbar-styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <h1>New Page Title</h1>
        <!-- Page content -->
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
```

**Step 2: Update Navbar Navigation**
```php
// includes/navbar.php - Add menu item
<a href="?page=new-page" class="nav-link">New Page</a>
```

**Step 3: Add Route**
```php
// routes/web.php
'new-page' => 'components/pages/new-page.php',
```

**Step 4: Add to Navigation Menu**
```php
// includes/navbar.php
if ($page === 'new-page') {
    include 'components/pages/new-page.php';
}
```

---

### Adding a New API Endpoint

**Step 1: Create API File**
```php
// api/new-endpoint.php
<?php
header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

require_once '../config/db.php';

try {
    // Handle request method
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (empty($input['required_field'])) {
            throw new Exception('Missing required field');
        }
        
        // Process request
        $stmt = $pdo->prepare("INSERT INTO table SET ...");
        $stmt->execute([...]);
        
        // Return success
        echo json_encode([
            'success' => true,
            'message' => 'Operation successful',
            'data' => $result
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

**Step 2: Call API from Frontend**
```javascript
// Fetch data
fetch('/api/new-endpoint.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        required_field: value
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Success:', data.data);
    } else {
        console.error('Error:', data.message);
    }
})
.catch(error => console.error('Request failed:', error));
```

**Step 3: Document in API.md**

---

### Adding Admin Functionality

**Step 1: Create Admin Component**
```php
// admin/components/pages/new-feature.php
<?php
session_start();
require_once '../../config/db.php';

// Verify admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin');
    exit;
}
?>

<div class="admin-container">
    <h2>New Feature Title</h2>
    <!-- Feature content -->
</div>
```

**Step 2: Create Action Handler (if needed)**
```php
// admin/actions/handle-feature.php
<?php
session_start();
require_once '../../config/db.php';

// Verify admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Process admin action
// ...
```

**Step 3: Add to Admin Navigation**
```php
// admin/includes/header.php
<a href="?page=new-feature" class="admin-nav-link">New Feature</a>
```

---

## Database Operations

### Query Patterns

#### SELECT Query
```php
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE category_id = ? AND status = ?");
$stmt->execute([$categoryId, 'active']);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    echo $product['name'];
}
```

#### INSERT Query
```php
$stmt = $pdo->prepare("INSERT INTO orders (id, customer_id, total_amount, status) 
                       VALUES (?, ?, ?, ?)");
$stmt->execute([
    $orderId,
    $_SESSION['user_id'],
    $total,
    'pending'
]);

$insertedId = $pdo->lastInsertId();
```

#### UPDATE Query
```php
$stmt = $pdo->prepare("UPDATE customers SET phone = ?, address = ? WHERE id = ?");
$stmt->execute([
    $_POST['phone'],
    $_POST['address'],
    $_SESSION['user_id']
]);

echo "Rows affected: " . $stmt->rowCount();
```

#### DELETE Query
```php
$stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
$stmt->execute([$cartId, $productId]);
```

#### Transaction (Multiple Related Queries)
```php
try {
    $pdo->beginTransaction();
    
    // Query 1: Create order
    $stmt = $pdo->prepare("INSERT INTO orders ...");
    $stmt->execute([...]);
    $orderId = $pdo->lastInsertId();
    
    // Query 2: Create order items
    $stmt = $pdo->prepare("INSERT INTO order_items ...");
    $stmt->execute([...]);
    
    // Query 3: Update cart
    $stmt = $pdo->prepare("UPDATE carts SET status = 'ordered' WHERE id = ?");
    $stmt->execute([$cartId]);
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## Session Management

### Setting Session Variables
```php
session_start();

// On successful login
$_SESSION['user_id'] = $userId;
$_SESSION['first_name'] = $firstName;
$_SESSION['email'] = $email;
$_SESSION['role'] = 'customer';
$_SESSION['login_time'] = time();

// For admin
$_SESSION['admin_id'] = $adminId;
$_SESSION['admin_role'] = $roleId;
```

### Checking Session
```php
// At start of protected page
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Check admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Access denied');
}
```

### Destroying Session (Logout)
```php
session_start();
session_destroy();
unset($_SESSION);
setcookie('PHPSESSID', '', time() - 3600);
header('Location: /');
```

---

## Error Handling

### Try-Catch Pattern
```php
try {
    // Possibly failing code
    $stmt = $pdo->prepare("SELECT ...");
    $stmt->execute([...]);
    $result = $stmt->fetch();
    
    if (!$result) {
        throw new Exception('Record not found');
    }
    
    // Success
    echo json_encode(['success' => true, 'data' => $result]);
    
} catch (PDOException $e) {
    // Database error
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    // General error
    error_log("Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
```

### Logging Errors
```php
// Log to file
error_log("User ID: " . $_SESSION['user_id'] . " - Error: " . $error, 3, "logs/error.log");

// Log to database (optional)
$stmt = $pdo->prepare("INSERT INTO error_logs (message, trace, user_id) VALUES (?, ?, ?)");
$stmt->execute([$error, debug_backtrace(), $_SESSION['user_id'] ?? null]);
```

---

## Input Validation

### Sanitization Functions
```php
// Email validation
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email address');
}

// Number validation
$quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
if ($quantity === false || $quantity < 1) {
    throw new Exception('Invalid quantity');
}

// String trimming
$name = trim(filter_var($_POST['name'], FILTER_SANITIZE_STRING));
if (strlen($name) < 2) {
    throw new Exception('Name too short');
}

// URL validation
$url = filter_var($_POST['url'], FILTER_VALIDATE_URL);
```

### Validation Example
```php
function validateCheckout($data) {
    $errors = [];
    
    // First name
    if (empty($data['first_name'])) {
        $errors[] = 'First name required';
    } elseif (strlen($data['first_name']) < 2) {
        $errors[] = 'First name too short';
    }
    
    // Email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }
    
    // Phone
    if (empty($data['phone'])) {
        $errors[] = 'Phone required';
    } elseif (!preg_match('/^[0-9\+\-\(\)]+$/', $data['phone'])) {
        $errors[] = 'Invalid phone format';
    }
    
    // Address
    if (empty($data['address'])) {
        $errors[] = 'Address required';
    }
    
    return $errors;
}
```

---

## File Upload Handling

```php
// File upload from form
if ($_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['product_image'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type');
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File too large');
    }
    
    // Generate unique filename
    $filename = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $uploadPath = 'public/images/products/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Store in database
        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        $stmt->execute([$productId, $uploadPath]);
    } else {
        throw new Exception('Failed to upload file');
    }
}
```

---

## Security Best Practices

### CSRF Protection (Add if needed)

```php
// Generate token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// In forms
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Verify token
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('CSRF token validation failed');
}
```

### XSS Prevention
```php
// Escape output
<h1><?php echo htmlspecialchars($productName, ENT_QUOTES); ?></h1>

// In JSON
echo json_encode($data);

// In attributes
<img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES); ?>">
```

### SQL Injection Prevention (Already using PDO)
```php
// ✅ GOOD - Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ BAD - Never do this
$query = "SELECT * FROM users WHERE email = '" . $email . "'";
```

---

## Testing Checklist

Before completing a feature:

- [ ] Logged in as customer - feature works
- [ ] Logged in as admin - feature works
- [ ] Not logged in - redirected appropriately
- [ ] Invalid input - proper error shown
- [ ] Database inserted correctly
- [ ] Email sent (if applicable)
- [ ] Response JSON valid
- [ ] Page loads in < 3 seconds
- [ ] Mobile responsive
- [ ] No JavaScript console errors
- [ ] Tested on multiple browsers

---

## Common Utilities

### UUID Generation
```php
require 'vendor/autoload.php';
use Ramsey\Uuid\Uuid;

$uuid = Uuid::uuid4()->toString();
// Example: "550e8400-e29b-41d4-a716-446655440000"
```

### Password Hashing
```php
// Hash
$hash = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 10]);

// Verify
if (password_verify($_POST['password'], $hash)) {
    // Password matches
}
```

### Date Formatting
```php
$dateTime = new DateTime('2026-04-08 14:30:00');
echo $dateTime->format('M d, Y g:i A');  // Apr 08, 2026 2:30 PM
```

---

## Code Review Checklist

When reviewing code:

- [ ] Follows naming conventions
- [ ] Properly indented
- [ ] Has comments for complex logic
- [ ] Uses prepared statements for DB
- [ ] Validates all input
- [ ] Handles errors appropriately
- [ ] Returns proper HTTP status codes
- [ ] Sends correct response format
- [ ] Checks authentication/authorization
- [ ] No hardcoded credentials
- [ ] No console.log or debug code
- [ ] No commented-out code
- [ ] Performance is acceptable

---

## Resources

- **PHP Documentation:** https://www.php.net/manual/
- **Laravel Documentation:** https://laravel.com/docs
- **PDO Documentation:** https://www.php.net/manual/en/book.pdo.php
- **JavaScript MDN:** https://developer.mozilla.org/en-US/docs/Web/JavaScript/

---

This guide ensures consistent, maintainable, and secure code across the project.
