<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendInvoiceEmail($to, $invoiceUrl, $invoiceNumber, $filePath)
{
    // ================================
    // VALIDATION
    // ================================
    $to = trim($to);

    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("❌ Invalid TO email: [" . $to . "]");
        return false;
    }

    if (!file_exists($filePath)) {
        error_log("❌ Invoice file missing: " . $filePath);
        return false;
    }

    // ================================
    // INIT MAILER
    // ================================
    $mail = new PHPMailer(true);

    try {
        // ================================
        // SMTP CONFIG (GMAIL)
        // ================================
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'deepscale.info@gmail.com';
        $mail->Password   = 'xdtdgdsnmfucdhin'; // Use ENV in production!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Disable debug in production
        $mail->SMTPDebug  = 0;

        // ================================
        // EMAIL HEADERS (DELIVERABILITY)
        // ================================
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('deepscale.info@gmail.com', 'TemaTech Innovations', false);
        $mail->Sender = 'tematechinnovatons@gmail.com';

        $mail->addReplyTo('tematechinnovatons@gmail.com', 'TemaTech Support');

        $mail->MessageID = '<' . uniqid() . '@tematech.com>';
        $mail->XMailer   = 'PHP/' . phpversion();

        $mail->Priority = 3;
        $mail->WordWrap = 78;

        // ================================
        // RECIPIENTS (FIXED PROPERLY)
        // ================================
        $mail->clearAddresses();
        $mail->clearCCs();
        $mail->clearBCCs();

        // Main recipient
        $mail->addAddress($to);

        // Internal copy (company)
        $mail->addCC('tematechinnovatons@gmail.com');

        // ================================
        // EMAIL CONTENT
        // ================================
        $mail->isHTML(true);
        $mail->Subject = "Payment Successful - Invoice {$invoiceNumber}";

        // Embed logo if exists
        $logoPath = __DIR__ . '/../public/images/TemaTech_logo.jpeg';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo');
        }

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', sans-serif; color: #333; }
                .container { max-width: 600px; margin: auto; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #ff8c00; padding-bottom: 15px; }
                .content { padding: 20px 0; }
                .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:logo' width='150'><br>
                </div>

                <div class='content'>
                    <h2 style='color:#ff8c00;'>Payment Successful</h2>

                    <p>Dear Customer,</p>

                    <p>Your payment has been successfully processed.</p>

                    <p><strong>Invoice Number:</strong> {$invoiceNumber}</p>
                    <p><strong>Date:</strong> " . date('M d, Y') . "</p>

                    <p>Your invoice is attached to this email.</p>

                    <p>If you have any questions, contact us at 
                    <strong>tematechinnovatons@gmail.com</strong></p>
                </div>

                <div class='footer'>
                    <p>&copy; " . date('Y') . " TemaTech Innovations</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Invoice {$invoiceNumber}. Download: {$invoiceUrl}";

        // ================================
        // ATTACHMENT
        // ================================
        $mail->addAttachment($filePath, "Invoice-{$invoiceNumber}.pdf");

        // ================================
        // SEND
        // ================================
        if ($mail->send()) {
            error_log("Email sent successfully to: " . $to);
            return true;
        } else {
            error_log("❌ Send failed: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception: " . $mail->ErrorInfo);
        return false;
    }
}
