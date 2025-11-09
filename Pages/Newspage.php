<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Process/news_handler.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Barangay Sampaguita</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5CB25D;
            --primary-dark: #4A9A47;
            --primary-light: #E8F5E8;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --text-muted: #999999;
            --border: #e0e0e0;
            --border-light: #f0f0f0;
            --background: #ffffff;
            --background-light: #fafafa;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 8px 40px rgba(0, 0, 0, 0.12);
            --navbar-height: 80px;
            --border-radius: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--background-light);
            color: var(--text-primary);
            line-height: 1.7;
            padding-top: 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Enhanced Header */
        .header {
            background-image:
                linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3)),
                url('../Assets/BaranggayHall.jpeg');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 120px 0 80px;
            color: white;
            position: relative;
            height: 60vh;
            display: flex;
            align-items: center;
            margin-top: 0;
            padding-top: 80px;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg,rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3) 100%);
        }

        .header-content {
            text-align: center;
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .header h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
            letter-spacing: -1px;
        }

        .header-subtitle {
            font-size: 1.4rem;
            font-weight: 400;
            opacity: 0.95;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.4);
            max-width: 800px;
            margin: 0 auto;
        }

        /* Enhanced Main Content */
        .main-content {
            padding: 80px 0;
            background: white;
        }

        .news-header {
            text-align: center;
            margin-bottom: 60px;
            padding-bottom: 40px;
            position: relative;
        }

        .news-title {
            font-size: 3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .news-subtitle {
            font-size: 1.3rem;
            color: var(--text-secondary);
            margin-bottom: 25px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .news-count {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: var(--shadow-light);
        }

        .news-divider {
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            margin: 40px auto 0;
            border-radius: 3px;
        }

        /* Enhanced News Articles */
        .news-list {
            max-width: 1200px;
            margin: 0 auto;
        }

        .news-article {
            background: var(--background);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            transition: all 0.4s ease;
            border: 1px solid var(--border-light);
            position: relative;
        }

        .news-article::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .news-article:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .news-article:hover::before {
            opacity: 1;
        }

        .news-article:last-child {
            margin-bottom: 0;
        }

        .article-content {
            display: flex;
            align-items: stretch;
            min-height: 280px;
        }

        .article-image {
            flex: 0 0 400px;
            position: relative;
            overflow: hidden;
        }

        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .news-article:hover .article-image img {
            transform: scale(1.08);
        }

        .image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-muted);
        }

        .image-placeholder i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.6;
        }

        .image-placeholder span {
            font-size: 1rem;
            font-weight: 500;
        }

        .article-info {
            flex: 1;
            padding: 45px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .article-text {
            flex: 1;
        }

        .article-content-text {
            font-size: 1.2rem;
            line-height: 1.8;
            color: var(--text-primary);
            margin-bottom: 30px;
            font-weight: 400;
        }

        .article-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 25px;
            border-top: 2px solid var(--border-light);
            margin-top: auto;
        }

        .article-date {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 500;
        }

        .article-date i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .article-badge {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-light);
        }

        /* Enhanced Empty State */
        .empty-state {
            text-align: center;
            padding: 120px 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .empty-state-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-light), rgba(92, 178, 93, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: var(--shadow-light);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary);
        }

        .empty-state h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            color: var(--text-primary);
            font-weight: 600;
        }

        .empty-state p {
            font-size: 1.2rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        /* Enhanced Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(30px);
            transition: all 0.4s ease;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-to-top:hover {
            background: linear-gradient(135deg, var(--primary-dark), #2c5f2d);
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-hover);
        }

        .scroll-to-top:active {
            transform: translateY(0) scale(0.95);
        }

        /* Enhanced Responsive Design */
        @media (max-width: 1024px) {
            .container {
                max-width: 1000px;
                padding: 0 20px;
            }
            
            .article-image {
                flex: 0 0 350px;
            }
            
            .article-info {
                padding: 35px;
            }
        }

        @media (max-width: 768px) {
            :root {
                --navbar-height: 60px;
            }

            .container {
                padding: 0 16px;
            }

            .header {
                padding: 80px 0 50px;
                min-height: 50vh;
            }

            .header h1 {
                font-size: 2.8rem;
            }

            .header-subtitle {
                font-size: 1.1rem;
            }

            .main-content {
                padding: 50px 0;
            }

            .news-title {
                font-size: 2.2rem;
            }

            .news-subtitle {
                font-size: 1.1rem;
            }

            .article-content {
                flex-direction: column;
                min-height: auto;
            }

            .article-image {
                flex: none;
                height: 250px;
            }

            .article-info {
                padding: 30px;
            }

            .article-content-text {
                font-size: 1.1rem;
            }

            .article-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .scroll-to-top {
                width: 50px;
                height: 50px;
                bottom: 20px;
                right: 20px;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 2.2rem;
            }

            .news-title {
                font-size: 1.8rem;
            }

            .article-info {
                padding: 25px;
            }

            .article-content-text {
                font-size: 1rem;
            }

            .empty-state {
                padding: 80px 15px;
            }

            .empty-state h2 {
                font-size: 1.8rem;
            }

            .empty-state p {
                font-size: 1rem;
            }
        }

        /* Enhanced Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* News article hover effects */
        .news-article {
            position: relative;
            overflow: hidden;
        }

        .news-article::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .news-article:hover::after {
            left: 100%;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include '../Pages/Navbar/navbar.php'; ?>

    <!-- Enhanced Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1>News & Updates</h1>
                <p class="header-subtitle">Stay informed with the latest happenings in Barangay Sampaguita, San Pedro, Laguna</p>
            </div>
        </div>
    </header>

    <!-- Enhanced Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- News Header -->
            <div class="news-header">
                <h2 class="news-title">Latest News and Announcements</h2>
                <p class="news-subtitle">Discover the most recent updates and announcements from our barangay community</p>
                <?php if (!empty($newsItems)): ?>
                    <div class="news-count">
                        <i class="fas fa-newspaper"></i>
                        <?= count($newsItems) ?> Article<?= count($newsItems) !== 1 ? 's' : '' ?> Available
                    </div>
                <?php endif; ?>
                <div class="news-divider"></div>
            </div>

            <?php if (!empty($newsItems)): ?>
                <!-- News Articles from Database -->
                <div class="news-list">
                    <?php foreach ($newsItems as $index => $news): ?>
                        <article class="news-article fade-in">
                            <div class="article-content">
                                <div class="article-image">
                                    <?php if (!empty($news['Newsimage'])): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($news['Newsimage']) ?>" alt="News Image"
                                            loading="lazy">
                                    <?php else: ?>
                                        <div class="image-placeholder">
                                            <i class="fas fa-newspaper"></i>
                                            <span>News Article</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="article-info">
                                    <div class="article-text">
                                        <div class="article-content-text">
                                            <?= nl2br(htmlspecialchars($news['Newsinfo'] ?? 'No content available')) ?>
                                        </div>
                                    </div>

                                    <div class="article-meta">
                                        <div class="article-date">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= date('F j, Y', strtotime($news['DatedReported'])) ?>
                                        </div>
                                        <span class="article-badge">Barangay Sampaguita News</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h2>No News Available</h2>
                    <p>There are currently no news articles to display. Please check back later for the latest updates and announcements from Barangay Sampaguita.</p>
                </div>
            <?php endif; ?>
        </div>

        <button id="scrollToTop" class="scroll-to-top" title="Back to Top">
            <i class="fas fa-chevron-up"></i>
        </button>
    </main>

    <script>
        // Intersection Observer for fade-in animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Apply animation to news articles
        document.querySelectorAll('.fade-in').forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(40px)';
            element.style.transition = `opacity 0.8s ease ${index * 0.1}s, transform 0.8s ease ${index * 0.1}s`;
            observer.observe(element);
        });

        // Scroll to Top Button Functionality
        document.addEventListener('DOMContentLoaded', function () {
            const scrollToTopBtn = document.getElementById('scrollToTop');

            // Show/hide button based on scroll position
            window.addEventListener('scroll', function () {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.add('show');
                } else {
                    scrollToTopBtn.classList.remove('show');
                }
            });

            // Smooth scroll to top when button is clicked
            scrollToTopBtn.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>
