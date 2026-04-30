<?php
/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| Helper Function
|--------------------------------------------------------------------------
*/
if (!function_exists('pageUrl')) {
    function pageUrl($page, $params = [])
    {
        $url = 'index.php?page=' . urlencode($page);
        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        return $url;
    }
}

/*
|--------------------------------------------------------------------------
| Session Messages
|--------------------------------------------------------------------------
*/
$errors = $_SESSION['register_errors'] ?? [];
$success = $_SESSION['register_success'] ?? '';
$old = $_SESSION['old_input'] ?? [];

unset($_SESSION['register_errors'], $_SESSION['register_success'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - TemaTech Innovation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- YOUR ORIGINAL STYLES REMAIN UNCHANGED -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #FFFFFF 0%, #FFFFFF 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-logo {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .logo-tema {
            color: #FF6B35;
        }

        .logo-tech {
            color: #111;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: #FF6B35;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-message {
            background: #d1fae5;
            color: #059669;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .register-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
        }

        .register-footer a {
            color: #FF6B35;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>

    <div class="register-container">

        <div class="register-header">
            <div class="register-logo">
                <span class="logo-tema">Tema</span> <span class="logo-tech">Tech</span>
            </div>
            <h2>Create Your Account</h2>
            <p>Join us today and start shopping</p>
        </div>

        <!-- ERROR DISPLAY -->
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- SUCCESS DISPLAY -->
        <?php if ($success): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="auth/register_process.php">

            <!-- CSRF TOKEN -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name"
                        value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name"
                        value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone"
                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                    placeholder="+260" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    placeholder="@example.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                    placeholder="At least 8 characters" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password"
                    placeholder="Re-enter your password" required>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Create Account
            </button>

            <p style="margin-top:15px;font-size:13px;">
                By signing up, you agree to our Terms of Service and Privacy Policy
            </p>
        </form>

        <div class="register-footer">
            Already have an account?
            <a href="<?= pageUrl('login') ?>">Sign In</a>
        </div>

    </div>

</body>

</html>