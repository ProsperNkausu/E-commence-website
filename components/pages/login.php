<?php
// Helper function for page URLs
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

// Get login error from session
$loginError = $_SESSION['login_error'] ?? '';
$emailValue = $_SESSION['login_email'] ?? '';   // Preserve email on error

// Clear session errors after displaying
if (!empty($loginError)) {
    unset($_SESSION['login_error']);
}
if (isset($_SESSION['login_email'])) {
    unset($_SESSION['login_email']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TemaTech Innovation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            animation: gradientShift 10s ease infinite;
            background-size: 200% 200%;
        }

        /* Success Message Styles */
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #10b981;
            animation: fadeInUp 0.5s ease-out;
        }

        .success-icon {
            animation: checkPop 0.6s ease-out;
        }

        @keyframes checkPop {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.3);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* New: Field error styles */
        .form-group.error input {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .error-text {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .shake {
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-8px);
            }

            75% {
                transform: translateX(8px);
            }
        }


        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
            animation: slideIn 0.5s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .login-logo {
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

        .login-header h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            animation: fadeInUp 0.6s ease-out both;
        }

        .form-group:nth-child(1) {
            animation-delay: 0.3s;
        }

        .form-group:nth-child(2) {
            animation-delay: 0.4s;
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
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: fadeInUp 0.4s ease-out;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #FF6B35;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out 0.5s both;
        }

        .btn-login:hover {
            background: #e55a28;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
            animation: fadeInUp 0.6s ease-out 0.55s both;
        }

        .login-footer a {
            color: #FF6B35;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-footer a:hover {
            text-decoration: underline;
            color: #e55a28;
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }

        .back-home a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            color: #FF6B35;
            transform: translateX(-3px)x;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-home a:hover {
            color: #FF6B35;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <span class="logo-tema">Tema</span> <span class="logo-tech">Tech</span>
            </div>
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue</p>
        </div>

        <?php if (!empty($loginError)): ?>
            <div class="error-message" id="main-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($loginError) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="auth/login_process.php" id="loginForm">
            <div class="form-group <?= !empty($loginError) ? 'error' : '' ?>" id="email-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($emailValue) ?>"
                    required
                    placeholder="Enter your email">
                <div class="error-text" id="email-error" style="display: none;">
                    <i class="fas fa-info-circle"></i> Please enter a valid email
                </div>
            </div>

            <div class="form-group <?= !empty($loginError) ? 'error' : '' ?>" id="password-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Enter your password">
                <div class="error-text" id="password-error" style="display: none;">
                    <i class="fas fa-info-circle"></i> Password is required
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="login-footer" style="margin-top: 25px; text-align: center;">
            Don't have an account? <a href="<?= pageUrl('register') ?>">Sign Up</a>
        </div>

        <div class="back-home">
            <a href="<?= pageUrl('home') ?>">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const mainError = document.getElementById('main-error');

        // Add shake animation when there's a server error
        if (mainError) {
            const container = document.querySelector('.login-container');
            container.classList.add('shake');

            // Remove shake after animation
            setTimeout(() => {
                container.classList.remove('shake');
            }, 600);
        }

        // Basic client-side validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            // Reset previous errors
            document.getElementById('email-group').classList.remove('error');
            document.getElementById('password-group').classList.remove('error');

            // Email validation
            if (!emailInput.value.trim() || !emailInput.value.includes('@')) {
                document.getElementById('email-group').classList.add('error');
                isValid = false;
            }

            // Password validation
            if (!passwordInput.value.trim()) {
                document.getElementById('password-group').classList.add('error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                loginBtn.style.transform = 'scale(0.95)';
                setTimeout(() => loginBtn.style.transform = 'scale(1)', 150);
            } else {
                // Disable button to prevent double submission
                loginBtn.disabled = true;
                loginBtn.innerHTML = `
                    <i class="fas fa-spinner fa-spin"></i> 
                    Signing you in...
                `;
            }
        });

        // Clear main error when user starts typing
        document.getElementById('email').addEventListener('input', () => {
            if (mainError) mainError.style.display = 'none';
        });

        document.getElementById('password').addEventListener('input', () => {
            if (mainError) mainError.style.display = 'none';
        });
    </script>
</body>

</html>