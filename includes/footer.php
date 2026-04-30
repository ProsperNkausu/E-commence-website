<?php
// Helper function for page URLs if not already defined
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
?>
<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Company Info -->
            <div class="footer-column">
                <h3 class="footer-logo">
                    <span class="logo-tema">TemaTech</span> <span class="logo-tech">Innovations</span>
                </h3>
                <p class="footer-description">
                    Your trusted destination for the latest technology and innovative products.
                    Quality, reliability, and customer satisfaction are our priorities.
                </p>
                <div class="footer-social">
                    <a href="https://facebook.com//profile.php?id=61552755659465" target="_blank" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/260975300083" target="_blank" class="social-link">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= pageUrl('home') ?>">Home</a></li>
                    <li><a href="<?= pageUrl('products') ?>">Products</a></li>
                    <li><a href="<?= pageUrl('cart') ?>">Shopping Cart</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-column">
                <h4 class="footer-title">Categories</h4>
                <ul class="footer-links">
                    <li><a href="<?= pageUrl('products', ['category' => 'laptops']) ?>">Laptops</a></li>
                    <li><a href="<?= pageUrl('products', ['category' => 'keyboards']) ?>">Keyboards</a></li>
                    <li><a href="<?= pageUrl('products', ['category' => 'computers']) ?>">Computers</a></li>
                    <li><a href="<?= pageUrl('products', ['category' => 'headphones']) ?>">Headphones</a></li>
                    <li><a href="<?= pageUrl('products', ['category' => 'accessories']) ?>">Accessories</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-column">
                <h4 class="footer-title">Contact Us</h4>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span> Limson House, Lusaka 10101</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>+260-975-30083</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>
                            tematechinnovatons@gmail.com</span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Mon - Fri: 9:00 AM - 6:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> TemaTech Innovations. All rights reserved.</p>

            <div class="footer-bottom-links">
                <p>Powered by 
                    <span style="color: #FF6200; font-weight: bold; font-style: italic;">DeepScale</span> 
                    <span style="color: #FFFFFF; font-weight: bold; font-style: italic;">Technologies</span>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Footer Styles */
    .main-footer {
        background: #1F2937;
        color: #E5E7EB;
        padding: 3rem 0 0;
        margin-top: 4rem;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #374151;
    }

    .footer-column {
        padding: 0 1rem;
    }

    .footer-logo {
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .footer-logo .logo-tema {
        color: #FF6B35;
    }

    .footer-logo .logo-tech {
        color: white;
    }

    .footer-description {
        color: #9CA3AF;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        font-size: 0.9375rem;
    }

    .footer-social {
        display: flex;
        gap: 1rem;
    }

    .social-link {
        width: 40px;
        height: 40px;
        background: #374151;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s;
    }

    .social-link:hover {
        background: #FF6B35;
        transform: translateY(-3px);
    }

    .footer-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        color: white;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 0.75rem;
    }

    .footer-links a {
        color: #9CA3AF;
        text-decoration: none;
        font-size: 0.9375rem;
        transition: color 0.2s;
    }

    .footer-links a:hover {
        color: #FF6B35;
    }

    .footer-contact {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-contact li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: #9CA3AF;
        font-size: 0.9375rem;
    }

    .footer-contact i {
        color: #FF6B35;
        margin-top: 0.25rem;
        width: 16px;
    }

    .footer-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 0;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-bottom p {
        margin: 0;
        color: #9CA3AF;
        font-size: 0.875rem;
    }

    .footer-bottom-links {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .footer-bottom-links a {
        color: #9CA3AF;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }

    .footer-bottom-links a:hover {
        color: #FF6B35;
    }

    @media (max-width: 768px) {
        .main-footer {
            padding: 2rem 0 0;
        }

        .footer-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .footer-column {
            padding: 0;
        }

        .footer-bottom {
            flex-direction: column;
            text-align: center;
        }

        .footer-bottom-links {
            justify-content: center;
        }
    }
</style>