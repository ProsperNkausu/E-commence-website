<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | TemaTech Innovation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e2937 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        /* Subtle tech background pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 25% 30%, rgba(255, 107, 53, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 75% 70%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        .error-container {
            text-align: center;
            padding: 40px 20px;
            max-width: 680px;
            z-index: 2;
            position: relative;
        }

        .error-icon {
            font-size: 140px;
            margin-bottom: 20px;
            color: #FF6B35;
            filter: drop-shadow(0 10px 20px rgba(255, 107, 53, 0.3));
            animation: float 3s ease-in-out infinite;
        }

        .error-number {
            font-size: 120px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #FF6B35, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        h2 {
            font-size: 36px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #e2e8f0;
        }

        .error-message {
            font-size: 18px;
            line-height: 1.7;
            color: #94a3b8;
            max-width: 480px;
            margin: 0 auto 40px;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 42px;
            background: #FF6B35;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.35);
        }

        .btn-home:hover {
            background: #e55a28;
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(255, 107, 53, 0.5);
        }

        .btn-home i {
            font-size: 20px;
        }

        /* Tech accent lines */
        .tech-line {
            position: absolute;
            height: 2px;
            background: linear-gradient(90deg, transparent, #FF6B35, transparent);
            opacity: 0.3;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        @media (max-width: 768px) {
            .error-icon {
                font-size: 100px;
            }

            .error-number {
                font-size: 80px;
            }

            h2 {
                font-size: 28px;
            }

            .btn-home {
                padding: 14px 32px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-laptop-code"></i>
        </div>

        <div class="error-number">404</div>
        <h2>Page Not Found</h2>

        <p class="error-message">
            Oops! It looks like the page you're looking for has been moved,
            deleted, or doesn't exist in our innovation hub.
        </p>

        <a href="index.php?page=home" class="btn-home">
            <i class="fas fa-home"></i>
            Return to Homepage
        </a>

        <p style="margin-top: 50px; font-size: 15px; color: #64748b;">
            Need help? <a href="index.php?page=contact" style="color: #FF6B35; text-decoration: none;">Contact Support</a>
        </p>
    </div>

    <!-- Optional subtle tech decoration -->
    <div class="tech-line" style="top: 20%; left: 0; width: 40%;"></div>
    <div class="tech-line" style="bottom: 30%; right: 0; width: 35%;"></div>
</body>

</html>