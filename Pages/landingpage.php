<?php
// Set resident session name BEFORE starting session
session_name('BarangayResidentSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add database connection
require_once '../Process/db_connection.php';
// News Handler
require_once '../Process/news_handler.php';
// approved notification
require_once 'approval_notification.php';
// declined notification - CORRECTED FILENAME
require_once 'decline_notification.php';

// Check for approved requests if user is logged in
if (isset($_SESSION['user_id'])) {
    $approvedRequests = checkApprovedRequests($conn, $_SESSION['user_id']);
    $declinedRequests = checkDeclinedRequests($conn, $_SESSION['user_id']);
}

// Optional: Add function to manually reset notification counter (for admin use)
if (isset($_GET['reset_notifications']) && isset($_SESSION['user_id'])) {
    resetNotificationCounter();
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Sampaguita</title>
    <link rel="stylesheet" href="../Styles/Newstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Body and Layout Fixes */
        body {
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            overflow-x: hidden;
        }

        /* Enhanced Leaders Section Styles */
        .leaders-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            padding: 0 1rem;
        }

        .leader-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 15px 35px rgba(44, 95, 45, 0.1);
            border: 2px solid transparent;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .leader-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4CAF50, #2c5f2d, #4CAF50);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .leader-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(44, 95, 45, 0.2);
            border-color: #4CAF50;
        }

        .leader-card:hover::before {
            transform: scaleX(1);
        }

        .leader-photo {
            width: 180px;
            height: 180px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #4CAF50;
            background: linear-gradient(135deg, #f0f0f0, #e8e8e8);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s ease;
        }

        .leader-photo::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4CAF50, #2c5f2d);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .leader-card:hover .leader-photo::after {
            opacity: 1;
        }

        .leader-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .leader-card:hover .leader-photo img {
            transform: scale(1.05);
        }

        .leader-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .leader-info h3 {
            color: #2c5f2d;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            transition: color 0.3s ease;
        }

        .leader-card:hover .leader-info h3 {
            color: #4CAF50;
        }

        .leader-info p {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Scroll to Top Button Styles */
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4CAF50, #2c5f2d);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-to-top:hover {
            background: linear-gradient(135deg, #45a049, #1e4620);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .scroll-to-top:active {
            transform: translateY(0);
        }

        /* Responsive adjustments for scroll button */
        @media (max-width: 768px) {
            .scroll-to-top {
                width: 45px;
                height: 45px;
                bottom: 15px;
                right: 15px;
                font-size: 1rem;
            }
        }

        /* Map Section Styles */
        .map-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-top: 2rem;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .map-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .map-info h3 {
            color: #2c5f2d;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .map-info p {
            margin: 0.5rem 0;
            color: #666;
            line-height: 1.6;
        }

        .map-info strong {
            color: #2c5f2d;
        }

        .map-links {
            margin-top: 1.5rem;
        }

        .map-embed {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .map-embed iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        .btn-green {
            background: linear-gradient(135deg, #4CAF50, #2c5f2d);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Notification positioning to avoid overlap with fixed navbar */
        .approval-notification {
            z-index: 10001;
            top: 100px !important;
            right: 20px;
        }

        .decline-notification {
            z-index: 10002;
            top: 100px !important;
            right: 450px;
        }

        @media (max-width: 1024px) {
            .decline-notification {
                right: 20px;
                top: 450px !important;
            }
        }

        @media (max-width: 480px) {
            .approval-notification,
            .decline-notification {
                width: 95%;
                right: 2.5%;
                top: 100px !important;
            }
            
            .decline-notification {
                top: 450px !important;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include '../Pages/Navbar/navbar.php'; ?>

    <!-- Pending Verification Notice -->
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['AccountStatus']) && $_SESSION['AccountStatus'] === 'pending'): ?>
        <div class="verification-notice pending" style="margin: 0; margin-top: -10px; border-radius: 0; background: linear-gradient(135deg, #e3f2fd 0%, #f3f9ff 100%); border-left: 4px solid #42a5f5; color: #1565c0; padding: 1.25rem 1.5rem; display: flex; align-items: flex-start; gap: 1.25rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
            <i class="fas fa-clock" style="font-size: 1.5rem; margin-top: 0.1rem; flex-shrink: 0;"></i>
            <div>
                <strong style="display: block; margin-bottom: 0.4rem; font-size: 1.05rem; font-weight: 700;">Account Pending Verification</strong>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; opacity: 0.9;">Your profile has been completed. Please wait for admin verification to access all services.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-content">
            <div class="row d-flex justify-content-center h-100" data-aos="fade-right" data-aos-duration="800"
                data-aos-offset="200" data-aos-easing="ease-in">
                <h1>Barangay Sampaguita
                    <br><br>
                    <p>Welcome to Barangay Sampaguita — where progress blooms and the community thrives together!</p>
                </h1>
            </div>
            <a href="learnmore.php" class="btn btn-green">Learn More →</a>
        </div>
    </div>

    <!-- ARTA Section -->
    <div class="section arta-section" id="arta">
        <div class="section-divider"></div>
        <h2 class="section-title">Anti Red-Tape Authority (ARTA)</h2>

        <div class="arta-grid">
            <div class="arta-card" data-aos="fade-up" data-aos-delay="100">
                <h3><i class="fas fa-gavel arta-icon"></i> Mandate</h3>
                <p>The Anti Red-Tape Authority (ARTA) oversees the implementation of Base Doing Business and efficient
                    government services delivery act of 2018. Barangay Sampaguita is committed to fostering an
                    environment conducive to business growth and ensuring efficient delivery of services to its
                    constituents. We recognize the importance of streamlining processes, eliminating red tape, and
                    promoting transparency in governance.</p>
            </div>

            <div class="arta-card" data-aos="fade-up" data-aos-delay="200">
                <h3><i class="fas fa-eye arta-icon"></i> Vision</h3>
                <p>A quiet and progressive community where residents are people who love and fear the Lord God, are
                    loving and compassionate towards others, believe in the importance of cooperation and unity, and
                    show care, striving for a higher quality of life for all.</p>
            </div>

            <div class="arta-card" data-aos="fade-up" data-aos-delay="300">
                <h3><i class="fas fa-bullseye arta-icon"></i> Mission</h3>
                <p>To elevate the standard of living of the citizens of Barangay Sampaguita and maintain a community
                    that is honest, cooperative, loving and caring towards one another, living in a place where the Lord
                    God reigns, a community that is peaceful, prosperous, clean, and pleasant.</p>
            </div>

            <div class="arta-card" data-aos="fade-up" data-aos-delay="400" style="grid-column: 1 / -1;">
                <h3><i class="fas fa-handshake arta-icon"></i> Service Pledge</h3>
                <p>We commit to provide efficient and transparent services to support the needs of our barangay. As part
                    of our dedication to fostering efficient delivery of government services, we pledge to:</p>
                <ul class="pledge-list">
                    <li>Respond promptly to all inquiries, applications, and requests for business permits and
                        clearances</li>
                    <li>Communicate clearly and effectively with all relevant parties, providing accurate information on
                        procedures, requirements and timelines</li>
                    <li>Streamline processes and reduce bureaucratic obstacles to ensure a smooth and efficient
                        experience for business owners and entrepreneurs</li>
                    <li>Uphold transparency in all our dealings, making information readily available to the public</li>
                    <li>Hold ourselves accountable for the quality and timeliness of our services, continuously striving
                        for improvement and excellence</li>
                    <li>Exceed the expectations of individuals by delivering services that meet their necessities and
                        needs</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Leaders Section -->
    <div class="section" id="officials">
        <div class="section-divider"></div>
        <h2 class="section-title">Barangay Officials</h2>
        <div class="leaders-container">
            <div class="leader-card" data-aos="fade-up" data-aos-delay="100">
                <div class="leader-photo">
                    <img src="../Assets/CAPTAIN.png" alt="Barangay Captain"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjBmMGYwIi8+CjxjaXJjbGUgY3g9IjEwMCIgY3k9IjgwIiByPSIzMCIgZmlsbD0iIzJjNWYyZCIvPgo8cGF0aCBkPSJNNTAgMTUwQzUwIDEyNSA3NSAxMDAgMTAwIDEwMFMxNTAgMTI1IDE1MCAxNTBIMTAwSDUwWiIgZmlsbD0iIzJjNWYyZCIvPgo8L3N2Zz4K';">
                </div>
                <div class="leader-info">
                    <h3>Rhexter S. Labay</h3>
                    <p>Barangay Captain</p>
                </div>
            </div>

            <div class="leader-card" data-aos="fade-up" data-aos-delay="200">
                <div class="leader-photo">
                    <img src="../Assets/SECRETARY.png" alt="Barangay Secretary"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjBmMGYwIi8+CjxjaXJjbGUgY3g9IjEwMCIgY3k9IjgwIiByPSIzMCIgZmlsbD0iIzJjNWYyZCIvPgo8cGF0aCBkPSJNNTAgMTUwQzUwIDEyNSA3NSAxMDAgMTAwIDEwMFMxNTAgMTI1IDE1MCAxNTBIMTAwSDUwWiIgZmlsbD0iIzJjNWYyZCIvPgo8L3N2Zz4K';">
                </div>
                <div class="leader-info">
                    <h3>Beth J. Doe</h3>
                    <p>Barangay Secretary</p>
                </div>
            </div>

            <div class="leader-card" data-aos="fade-up" data-aos-delay="300">
                <div class="leader-photo">
                    <img src="../Assets/SK.png" alt="SK Chair Woman"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjBmMGYwIi8+CjxjaXJjbGUgY3g9IjEwMCIgY3k9IjgwIiByPSIzMCIgZmlsbD0iIzJjNWYyZCIvPgo8cGF0aCBkPSJNNTAgMTUwQzUwIDEyNSA3NSAxMDAgMTAwIDEwMFMxNTAgMTI1IDE1MCAxNTBIMTAwSDUwWiIgZmlsbD0iIzJjNWYyZCIvPgo8L3N2Zz4K';">
                </div>
                <div class="leader-info">
                    <h3>Mariel Camay</h3>
                    <p>SK Chair Woman</p>
                </div>
            </div>
        </div>
    </div>

    <!-- News Section -->
    <div class="section" id="news-section">
        <div class="section-divider"></div>
        <h2 class="section-title">News & Updates</h2>
        <div class="news-slider-container">
            <div class="news-slider">
                <?php foreach ($newsItems as $index => $news): ?>
                    <div class="news-slide" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
                        <?php if (!empty($news['Newsimage'])): ?>
                            <div class="news-img">
                                <img src="data:image/jpeg;base64,<?= base64_encode($news['Newsimage']) ?>" alt="News Image">
                            </div>
                        <?php else: ?>
                            <div class="news-img">
                                <div class="placeholder-image">News Image</div>
                            </div>
                        <?php endif; ?>
                        <div class="news-slide-content">
                            <h3>News Update #<?= $index + 1 ?></h3>
                            <p class="news-content"><?= htmlspecialchars($news['Newsinfo'] ?? '') ?></p>
                            <p class="news-date">Posted on: <?= date('F j, Y', strtotime($news['DatedReported'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="slider-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-next"><i class="fas fa-chevron-right"></i></button>
            <div class="slider-dots"></div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="section" id="map-section">
        <div class="section-divider"></div>
        <h2 class="section-title">Barangay Location</h2>
        <div class="map-container" data-aos="fade-up" data-aos-delay="100">
            <div class="map-info">
                <h3><i class="fas fa-map-marker-alt"></i> Find Us</h3>
                <p><strong>Address:</strong> Sampaguita Village, San Pedro, Laguna</p>
                <p><strong>Coordinates:</strong> 82VP+R5M</p>
                <p><strong>Contact:</strong> Telephone: 86380301</p>
                <div class="map-links">
                    <a href="https://www.google.com/maps/place/Barangay+Sampaguita+Village+San+Pedro/@14.3446436,121.0348789,17z"
                        target="_blank" class="btn btn-green">
                        <i class="fas fa-external-link-alt"></i> View on Google Maps
                    </a>
                </div>
            </div>
            <div class="map-embed">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3865.892441234567!2d121.0348789!3d14.3446436!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d1a8c1234567%3A0x1234567890abcdef!2sBarangay%20Sampaguita%20Village%20San%20Pedro!5e0!3m2!1sen!2sph!4v1234567890123!5m2!1sen!2sph"
                    width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="scroll-to-top" title="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer__content">
            <div class="footer__info">
                <h3>Barangay Sampaguita Village</h3>
                <p>Telephone: 86380301</p>
                <p>82VP+R5M, Sampaguita Village, San Pedro, Laguna</p>
            </div>
            <div class="footer__social">
                Follow us on <b><a href="https://www.facebook.com/profile.php?id=100064702659581"
                        class="footer__social-link"><i class="fab fa-facebook"></i></a></b>
            </div>
        </div>
    </footer>

    <script src="../Script.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            offset: 100,
            easing: 'ease-in-out'
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // News Slider Functionality
        document.addEventListener('DOMContentLoaded', function () {
            const slider = document.querySelector('.news-slider');
            const slides = document.querySelectorAll('.news-slide');
            const prevBtn = document.querySelector('.slider-prev');
            const nextBtn = document.querySelector('.slider-next');
            const dotsContainer = document.querySelector('.slider-dots');

            let currentSlide = 0;
            const slideCount = slides.length;

            // Create dots
            slides.forEach((_, index) => {
                const dot = document.createElement('div');
                dot.classList.add('slider-dot');
                if (index === 0) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(index));
                dotsContainer.appendChild(dot);
            });

            const dots = document.querySelectorAll('.slider-dot');

            // Update slider position
            function updateSlider() {
                slider.style.transform = `translateX(-${currentSlide * 100}%)`;

                // Update dots
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentSlide);
                });
            }

            // Go to specific slide
            function goToSlide(slideIndex) {
                currentSlide = slideIndex;
                updateSlider();
            }

            // Next slide
            function nextSlide() {
                currentSlide = (currentSlide + 1) % slideCount;
                updateSlider();
            }

            // Previous slide
            function prevSlide() {
                currentSlide = (currentSlide - 1 + slideCount) % slideCount;
                updateSlider();
            }

            // Event listeners
            nextBtn.addEventListener('click', nextSlide);
            prevBtn.addEventListener('click', prevSlide);

            // Auto-advance slides (optional)
            let slideInterval = setInterval(nextSlide, 5000);

            // Pause on hover
            slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
            slider.addEventListener('mouseleave', () => {
                slideInterval = setInterval(nextSlide, 5000);
            });

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') nextSlide();
                if (e.key === 'ArrowLeft') prevSlide();
            });
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

        // Enhanced real-time checking for both approvals and declines
        document.addEventListener('DOMContentLoaded', function () {
            let isChecking = false;
            let approvalShown = false;
            let declinedShown = false;

            // Function to check for new approvals and declines
            function checkForNewUpdates() {
                if (isChecking || (approvalShown && declinedShown)) {
                    return;
                }

                isChecking = true;

                fetch('../Process/check_new_updates.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'check_updates=1&user_id=<?php echo $_SESSION['user_id'] ?? 0; ?>'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasNewApprovals && !approvalShown) {
                            showNewApprovalAlert();
                            approvalShown = true;

                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }

                        if (data.hasNewDeclines && !declinedShown) {
                            showNewDeclinedAlert();
                            declinedShown = true;

                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.log('Update check error:', error);
                    })
                    .finally(() => {
                        isChecking = false;
                    });
            }

            // Show alert for new approvals
            function showNewApprovalAlert() {
                const alertBadge = document.createElement('div');
                alertBadge.id = 'newApprovalAlert';
                alertBadge.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: linear-gradient(135deg, #4CAF50, #45a049);
                        color: white;
                        padding: 12px 20px;
                        border-radius: 25px;
                        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
                        z-index: 9999;
                        font-size: 0.9rem;
                        font-weight: 500;
                        animation: slideInRight 0.5s ease-out, pulse 1s ease-in-out 2;
                        cursor: pointer;
                    ">
                        <i class="fas fa-bell"></i> New approval received! Refreshing...
                    </div>
                `;

                document.body.appendChild(alertBadge);

                setTimeout(() => {
                    if (alertBadge.parentNode) {
                        alertBadge.remove();
                    }
                }, 2000);
            }

            // Show alert for new declines
            function showNewDeclinedAlert() {
                const alertBadge = document.createElement('div');
                alertBadge.id = 'newDeclinedAlert';
                alertBadge.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 80px;
                        right: 20px;
                        background: linear-gradient(135deg, #ff6b6b, #ee5a52);
                        color: white;
                        padding: 12px 20px;
                        border-radius: 25px;
                        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
                        z-index: 9999;
                        font-size: 0.9rem;
                        font-weight: 500;
                        animation: slideInRight 0.5s ease-out, pulse 1s ease-in-out 2;
                    ">
                        <i class="fas fa-exclamation-triangle"></i> Request declined! Refreshing...
                    </div>
                `;

                document.body.appendChild(alertBadge);

                setTimeout(() => {
                    if (alertBadge.parentNode) {
                        alertBadge.remove();
                    }
                }, 2000);
            }

            // Check for updates every 30 seconds if user is logged in
            <?php if (isset($_SESSION['user_id'])): ?>
                const updateCheckInterval = setInterval(checkForNewUpdates, 30000);

                document.addEventListener('visibilitychange', function () {
                    if (!document.hidden) {
                        setTimeout(checkForNewUpdates, 1000);
                    }
                });

                window.addEventListener('beforeunload', function () {
                    clearInterval(updateCheckInterval);
                });
            <?php endif; ?>
        });

        // Function to close decline notification
        function closeDeclineNotification() {
            const notification = document.getElementById('declineNotification');
            if (notification) {
                notification.style.animation = 'slideOutRight 0.4s ease-out forwards';
                setTimeout(() => {
                    notification.remove();
                }, 400);
            }
        }
    </script>

    <?php
    // Display approval notifications
    if (isset($approvedRequests) && !empty($approvedRequests)) {
        displayApprovalNotifications($approvedRequests);
    }
    // Display declined notifications
    if (isset($declinedRequests) && !empty($declinedRequests)) {
        displayDeclineNotifications($declinedRequests);
    }
    ?>
</body>

</html>
