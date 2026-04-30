<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['forgot_email'] ?? '';

    if (!$email) {
        die("Email is required");
    }

    try {
        // ========================
        // CHECK USER
        // ========================
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Always respond the same (security)
        if (!$user) {
            header("Location: ../index.php?page=profile&msg=reset_sent");
            exit;
        }

        // ========================
        // GENERATE TOKEN
        // ========================
        $token   = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $pdo->prepare("
            UPDATE customers 
            SET reset_token = ?, reset_expires = ? 
            WHERE id = ?
        ");
        $update->execute([$token, $expires, $user['id']]);

        // ========================
        // RESET LINK
        // ========================
        $resetLink = "http://localhost/yourproject/reset-password.php?token=" . $token;

        // ========================
        // SEND EMAIL (SMTP)
        // ========================
        $mail = new PHPMailer(true);

        try {
            // SMTP CONFIG
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // or SendGrid
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your@gmail.com';
            $mail->Password   = 'your_app_password'; // Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Email setup
            $mail->setFrom('no-reply@yourdomain.com', 'Your App');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "
                <h3>Password Reset</h3>
                <p>Click the button below to reset your password:</p>
                <a href='$resetLink' style='
                    display:inline-block;
                    padding:12px 20px;
                    background:#111827;
                    color:#fff;
                    border-radius:8px;
                    text-decoration:none;
                '>Reset Password</a>
                <p style='margin-top:15px;'>This link expires in 1 hour.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
        }

        echo json_encode([
            "status" => "success",
            "message" => "If the email exists, a reset link has been sent."
        ]);
        exit;
    } catch (Exception $e) {
        error_log("Forgot password error: " . $e->getMessage());
        header("Location: ../index.php?page=profile&msg=error");
        exit;
    }
}
