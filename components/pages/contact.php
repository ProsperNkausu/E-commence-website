<?php
session_start();
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

// UI-only mode - form submission disabled for demo
$contactSuccess = false;
$contactError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactSuccess = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - TemaTech Innovation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/../../includes/navbar-styles.php'; ?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            opacity: 0;
            animation: pageLoad 0.6s ease-out forwards;
        }

        @keyframes pageLoad {
            to {
                opacity: 1;
            }
        }

        /* Scroll animations */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .scroll-slide-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-slide-left.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .scroll-slide-right {
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-slide-right.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .stagger-item {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .stagger-item.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .fa-whatsapp:hover {
            color: #25D366;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #FF6B35 0%, #FF6B35 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero-section p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Main Content */
        .contact-content {
            padding: 4rem 0;
        }

        .contact-grid {
            display: flex;
            
            gap: 3rem;
            margin-bottom: 4rem;
        }

        /* Contact Form */
        .contact-form-section {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .contact-form-section h2 {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            color: #212529;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #E85A2A;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .success-message {
            background: #d1fae5;
            color: #059669;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Contact Info */
        .contact-info-section {
            display: flex;
            flex-direction: row;
            gap: 2rem;
            
        }

        .info-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FF6B35 0%, #E85A2A 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .info-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #212529;
        }

        .info-card p {
            color: #6c757d;
            line-height: 1.6;
        }

        .info-card a {
            color: #FF6B35;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .info-card a:hover {
            color: #E85A2A;
            text-decoration: underline;
        }

        /* FAQ Section */
        .faq-section {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 4rem;
        }

        .faq-section h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #212529;
            text-align: center;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .faq-item {
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #FF6B35;
        }

        .faq-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: #212529;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .faq-item h3 i {
            color: #FF6B35;
        }

        .faq-item p {
            color: #6c757d;
            line-height: 1.6;
        }

        /* Social Section */
        .social-section {
            text-align: center;
            padding: 3rem;
            background: linear-gradient(135deg, #FF6B35 0%, #FF6B35 100%);
            border-radius: 12px;
            color: white;
            margin-bottom: 4rem;
        }

        .social-section h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .social-section p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .social-link {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: white;
            color: #667eea;
            transform: translateY(-5px);
        }

        /* Footer */
        .footer {
            background-color: #212529;
            color: #f8f9fa;
            padding: 3rem 0 1.5rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h4 {
            margin-bottom: 1rem;
            color: #FF6B35;
        }

        .footer-section a {
            display: block;
            color: #adb5bd;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #FF6B35;
        }

        .footer-divider {
            border-top: 1px solid #495057;
            padding-top: 1.5rem;
            text-align: center;
            color: #6c757d;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }

            .social-links {
                flex-wrap: wrap;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section scroll-reveal">
        <div class="container">
            <h1>Get in Touch</h1>
            <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="contact-content">
        <div class="container">
            <!-- Contact Form and Info Grid -->
            <div class="contact-grid">
                

                <!-- Contact Info -->
                <div class="contact-info-section scroll-slide-right">
                    <div class="info-card stagger-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Visit Us</h3>
                        <p>Limson House,<br>Lusaka 10101</p>
                        <p>
                            Direction:
                            <a href="https://www.google.com/maps/search/?api=1&query=Tematech+Innovations+Limson+House+Lusaka+10101" target="_blank">
                                Google Maps
                            </a>
                        </p>
                    </div>

                    <div class="info-card stagger-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3>Call Us</h3>
                        <p>
                            Phone: <a href="tel:+26097530083">+260-975-30083</a><br>
                            Mon-Fri: 9:00 AM - 6:00 PM PST
                        </p>
                    </div>

                    <div class="info-card stagger-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email Us</h3>
                        <p>
                            General: <a href="mailto:info@tematech.com">tematechinnovatons@gmail.com</a><br>

                        </p>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <section class="faq-section scroll-reveal">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-grid">
                    <div class="faq-item stagger-item">
                        <h3><i class="fas fa-question-circle"></i> What are your shipping options?</h3>
                        <p>We offer free standard shipping on orders over $50, with expedited shipping options available. Most orders ship within 24-48 hours.</p>
                    </div>

                    <div class="faq-item stagger-item">
                        <h3><i class="fas fa-question-circle"></i> What is your return policy?</h3>
                        <p>We offer a 30-day money-back guarantee on all products. Items must be unused and in original packaging to qualify for a full refund.</p>
                    </div>

                    <div class="faq-item stagger-item">
                        <h3><i class="fas fa-question-circle"></i> Do you offer technical support?</h3>
                        <p>Yes! Our support team is available 24/7 via email, chat, and phone to help with any technical questions or issues you may have.</p>
                    </div>

                    <div class="faq-item stagger-item">
                        <h3><i class="fas fa-question-circle"></i> Are your products authentic?</h3>
                        <p>Absolutely! All products sold on TemaTech are 100% authentic and come with manufacturer warranties. We're authorized retailers for all brands we carry.</p>
                    </div>
                </div>
            </section>

            <!-- Social Section -->
            <section class="social-section scroll-reveal">
                <h2>Connect With Us</h2>
                <p>Follow us on social media for updates, deals, and tech tips</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/profile.php?id=61552755659465&locale=en_GB#" class="social-link" title="Facebook" style="background-color:#FFFFFF86;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/260975300083" class="social-link" title="WhatsApp" style="background-color:#FFFFFF86;">
                        <i class="fab fa-whatsapp" ></i>
                    </a>

                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <!-- Scroll Animation Script -->
    <script>
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.scroll-reveal, .scroll-slide-left, .scroll-slide-right, .stagger-item').forEach(el => {
                observer.observe(el);
            });

            // Add stagger delays
            document.querySelectorAll('.contact-info-section .info-card').forEach((card, index) => {
                card.style.transitionDelay = `${index * 0.15}s`;
            });

            document.querySelectorAll('.faq-grid .faq-item').forEach((item, index) => {
                item.style.transitionDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>

</html>