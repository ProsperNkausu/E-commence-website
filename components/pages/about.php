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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - TemaTech Innovation</title>
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

        .scroll-fade-in {
            opacity: 0;
            transition: opacity 1s ease-out;
        }

        .scroll-fade-in.revealed {
            opacity: 1;
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

        /* Hero Section */
        .hero-section {
            background:
                url('/tematech-innovation/public/images/about-2.png') center/contain no-repeat;
            height: 300px;
            color: white;
            padding: 5rem 0;
            text-align: center;
            margin-bottom: -100px;
        }

        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero-section p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* About Content */
        .about-content {
            padding: 4rem 0;
        }

        .about-section {
            margin-bottom: 4rem;
        }

        .about-section h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #212529;
            text-align: center;
        }

        .about-section p {
            font-size: 1.1rem;
            color: #495057;
            line-height: 1.8;
            text-align: center;
            max-width: 800px;
            margin: 0 auto 1.5rem;
        }

        /* Two Column Layout */
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 3rem;
            align-items: center;
        }

        .column-content h3 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
            color: #212529;
        }

        .column-content p {
            font-size: 1rem;
            color: #495057;
            line-height: 1.8;
            text-align: left;
            margin-bottom: 1rem;
        }

        .column-image {
            background:
                url('/tematech-innovation/public/images/mission.png') center/cover no-repeat;
            border-radius: 12px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 5rem;
        }

        /* Values Grid */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }

        .value-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .value-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FF6B35 0%, #E85A2A 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .value-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #212529;
        }

        .value-card p {
            color: #6c757d;
            line-height: 1.6;
        }

        /* Team Section */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }

        .team-member {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .team-photo {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
        }

        .team-info {
            padding: 1.5rem;
            text-align: center;
        }

        .team-info h4 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #212529;
        }

        .team-info p {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .team-social a {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #495057;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .team-social a:hover {
            background: #FF6B35;
            color: white;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            margin: 4rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            background: white;
            padding: 4rem 2rem;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin: 4rem 0;
        }

        .cta-section h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #212529;
        }

        .cta-section p {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .btn-primary {
            padding: 1rem 2rem;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #E85A2A;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
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

            .two-column {
                grid-template-columns: 1fr;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: 1fr 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .team-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
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
            <!-- <h1>About TemaTech Innovation</h1> -->
            <!-- <p>We're on a mission to bring cutting-edge technology to everyone. Founded in 2020, we've grown from a small startup to a leading e-commerce platform for tech enthusiasts worldwide.</p> -->
        </div>
    </section>

    <!-- Main Content -->
    <main class="about-content">
        <div class="container">
            <!-- Our Story -->
            <section class="about-section scroll-reveal">
                <h2>Our Story</h2>
                <p>TemaTech Innovation started with a simple idea: make premium technology accessible to everyone. What began as a passion project has evolved into a trusted platform serving thousands of customers globally.</p>
                <p>We believe technology should empower people, not intimidate them. That's why we carefully curate every product, ensuring quality, innovation, and value in everything we offer.</p>
            </section>

            <!-- Mission & Vision -->
            <div class="two-column">
                <div class="column-content scroll-reveal">
                    <h3>Our Mission</h3>
                    <p>To democratize access to cutting-edge technology by providing high-quality products at competitive prices, backed by exceptional customer service.</p>
                    <p>We're committed to helping our customers stay ahead in the digital age, whether they're professionals, students, or tech enthusiasts.</p>
                </div>
                <div class="column-image scroll-reveal">

                </div>
            </div>

            <!-- Values -->
            <section class="about-section scroll-reveal">
                <h2>Our Values</h2>
                <div class="values-grid">
                    <div class="value-card stagger-item">
                        <div class="value-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Quality First</h3>
                        <p>We never compromise on quality. Every product is thoroughly tested and verified before reaching our customers.</p>
                    </div>
                    <div class="value-card stagger-item">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Customer Focus</h3>
                        <p>Our customers are at the heart of everything we do. Their satisfaction and success drive our decisions.</p>
                    </div>
                    <div class="value-card stagger-item">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Innovation</h3>
                        <p>We constantly seek new ways to improve and stay ahead of technology trends to serve you better.</p>
                    </div>
                </div>
            </section>
        </div>



        <div class="container">


            <!-- CTA Section -->
            <section class="cta-section scroll-reveal">
                <h2>Ready to Experience the Difference?</h2>
                <p>Join thousands of satisfied customers who trust TemaTech for their technology needs</p>
                <a href="<?= pageUrl('products') ?>" class="btn-primary">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <!-- Include Footer -->
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
            document.querySelectorAll('.scroll-reveal, .scroll-fade-in, .stagger-item').forEach(el => {
                observer.observe(el);
            });

            // Add stagger delays
            document.querySelectorAll('.values-grid .value-card').forEach((card, index) => {
                card.style.transitionDelay = `${index * 0.15}s`;
            });

            document.querySelectorAll('.team-grid .team-member').forEach((member, index) => {
                member.style.transitionDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>

</html>