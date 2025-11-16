<?php
// Initialize resident session
require_once __DIR__ . '/../config/session_resident.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Barangay Sampaguita</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Archivo:wght@300;400;500;600;700&family=Ubuntu:wght@300;400;500;600;700&display=swap');

        /* CSS VARIABLES */
        :root {
            /* Primary Colors */
            --primary: #5CB25D;
            --primary-dark: #4A9A47;
            --primary-light: #7BC67A;
            --primary-lighter: #E8F5E8;
            --primary-lightest: #F8FDF8;

            /* Secondary Colors */
            --secondary: #3F7A3C;
            --accent: #2D4A2E;

            /* Text Colors */
            --text-dark: #2D4A2E;
            --text-gray: #555555;
            --text-light-gray: #777777;
            --text-white: #FFFFFF;
            --text-placeholder: #828282;

            /* Background Colors */
            --bg-white: #FFFFFF;
            --bg-light: #F8FDF8;
            --bg-gray: #FAFAFA;
            --bg-overlay: rgba(0, 0, 0, 0.7);

            /* Border Colors */
            --border-light: #D4E8D4;
            --border-gray: #E0E0E0;
            --border-divider: #C8E0C8;

            /* Shadow Colors */
            --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 16px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 8px 32px rgba(0, 0, 0, 0.2);
            --shadow-primary: 0 4px 16px rgba(92, 178, 93, 0.3);

            /* Transitions */
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;

            /* Border Radius */
            --radius-small: 0.375rem;
            --radius-medium: 0.5rem;
            --radius-large: 0.75rem;
            --radius-xl: 1rem;
            --radius-round: 50%;

            /* Spacing */
            --space-xs: 0.5rem;
            --space-sm: 0.75rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            --space-3xl: 4rem;
        }

        /* RESET & BASE STYLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: 'Archivo', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-white);
            color: var(--text-dark);
            line-height: 1.6;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* TYPOGRAPHY */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: var(--space-md);
            color: var(--text-dark);
        }

        h1 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
        }

        h2 {
            font-size: clamp(2rem, 4vw, 2.75rem);
        }

        h3 {
            font-size: clamp(1.5rem, 3vw, 1.875rem);
        }

        h4 {
            font-size: clamp(1.25rem, 2.5vw, 1.5rem);
        }

        p {
            margin-bottom: var(--space-md);
            color: var(--text-gray);
        }

        ul {
            margin-bottom: var(--space-md);
            padding-left: var(--space-xl);
        }

        li {
            margin-bottom: var(--space-sm);
            color: var(--text-gray);
        }

        /* LAYOUT COMPONENTS */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 var(--space-lg);
        }

        .section {
            padding: var(--space-3xl) 0;
        }

        .section-title {
            text-align: center;
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 700;
            margin-bottom: var(--space-2xl);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -var(--space-md);
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: var(--radius-small);
        }

        /* HERO SECTION */
        .hero {
            position: relative;
            height: 60vh;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-white);
            overflow: hidden;
            z-index: 0;
            margin-top: -80px;
            padding-top: 80px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                /* dark overlay */
                url('../Assets/BaranggayHall.jpeg');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: -1;
        }


        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            animation: fadeInUp 1s ease-out;
        }

        .hero-content h1 {
            font-family: 'Ubuntu', sans-serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 700;
            margin-bottom: var(--space-lg);
            color: var(--text-white);
        }

        .hero-content p {
            font-size: clamp(1.125rem, 2.5vw, 1.5rem);
            margin-bottom: var(--space-xl);
            color: rgba(255, 255, 255, 0.9);
        }

        /* INTRO SECTION */
        .intro-section {
            background: var(--bg-light);
            padding: var(--space-3xl) 0;
            position: relative;
        }

        .intro-content {
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }

        .intro-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-gray);
        }

        /* ACHIEVEMENTS SECTION */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: var(--space-2xl);
            margin-top: var(--space-2xl);
        }

        .achievement-card {
            background: var(--bg-white);
            padding: var(--space-2xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-light);
            transition: var(--transition-normal);
            border-top: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }

        .achievement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-lightest) 0%, transparent 50%);
            opacity: 0;
            transition: var(--transition-normal);
        }

        .achievement-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .achievement-card:hover::before {
            opacity: 1;
        }

        .achievement-card h3 {
            color: var(--text-dark);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            position: relative;
            z-index: 2;
        }

        .achievement-icon {
            width: 48px;
            height: 48px;
            padding: var(--space-sm);
            background: var(--primary-lightest);
            border-radius: var(--radius-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: var(--transition-normal);
        }

        .achievement-card:hover .achievement-icon {
            background: var(--primary);
            transform: scale(1.1);
        }

        .achievement-card:hover .achievement-icon svg {
            fill: var(--text-white);
        }

        .achievement-card>*:not(.achievement-icon) {
            position: relative;
            z-index: 2;
        }

        /* PARTNERSHIPS SECTION */
        .partnerships-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-xl);
            margin-top: var(--space-2xl);
        }

        .partnership-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--text-white);
            padding: var(--space-2xl);
            border-radius: var(--radius-xl);
            position: relative;
            overflow: hidden;
            transition: var(--transition-normal);
        }

        .partnership-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-primary);
        }

        .partnership-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            transform: rotate(45deg);
            transition: var(--transition-normal);
        }

        .partnership-card:hover::before {
            transform: rotate(45deg) scale(1.2);
        }

        .partnership-card h4 {
            color: var(--text-white);
            margin-bottom: var(--space-md);
            position: relative;
            z-index: 2;
        }

        .partnership-card p {
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 2;
            margin-bottom: 0;
        }

        /* COMMUNITY OVERVIEW */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-xl);
            margin-top: var(--space-2xl);
        }

        .overview-card {
            background: var(--bg-white);
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-light);
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .overview-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-lightest) 0%, transparent 50%);
            opacity: 0;
            transition: var(--transition-normal);
        }

        .overview-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .overview-card:hover::before {
            opacity: 1;
        }

        .overview-card h4 {
            color: var(--text-dark);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            position: relative;
            z-index: 2;
        }

        .overview-icon {
            width: 48px;
            height: 48px;
            padding: var(--space-sm);
            background: var(--primary-lightest);
            border-radius: var(--radius-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: var(--transition-normal);
        }

        .overview-card:hover .overview-icon {
            background: var(--primary);
            transform: scale(1.1);
        }

        .overview-card:hover .overview-icon svg {
            fill: var(--text-white);
        }

        .overview-card>*:not(.overview-icon) {
            position: relative;
            z-index: 2;
        }

        /* FACILITIES LIST */
        .facilities-list {
            background: var(--primary-lightest);
            padding: var(--space-xl);
            border-radius: var(--radius-large);
            margin-top: var(--space-lg);
            position: relative;
            z-index: 2;
        }

        .facilities-list ul {
            margin: 0;
            padding-left: var(--space-lg);
        }

        .facilities-list li {
            padding: var(--space-xs) 0;
            position: relative;
        }

        .facilities-list li::marker {
            color: var(--primary);
        }

        /* ANIMATIONS */
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

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {

            .achievements-grid,
            .partnerships-grid,
            .overview-grid {
                grid-template-columns: 1fr;
            }
            .hero {
                height: 50vh;
                margin-top: -80px;
                padding-top: 80px;
            }

            .section {
                padding: var(--space-2xl) 0;
            }

            .achievement-card h3,
            .overview-card h4 {
                flex-direction: column;
                text-align: center;
                gap: var(--space-sm);
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 var(--space-md);
            }
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
    </style>
</head>

<body>
    <?php include './Navbar/navbar.php'; ?>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>About Us</h1>
            <p>Barangay Sampaguita, San Pedro, Laguna</p>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="intro-section">
        <div class="container">
            <div class="intro-content fade-in-up">
                <p>Barangay Sampaguita is a dynamic and growing community located in the heart of San Pedro, Laguna.
                    Committed to progress, inclusivity, and quality public service, the barangay has consistently worked
                    toward improving the lives of its residents through innovative programs, strategic partnerships, and
                    community-driven initiatives.</p>
            </div>
        </div>
    </section>

    <!-- Achievements Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Achievements and Recognition</h2>
            <div class="achievements-grid">
                <div class="achievement-card">
                    <h3>
                        <span class="achievement-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                            </svg>
                        </span>
                        Awards and Accolades
                    </h3>
                    <p>
                        Barangay Sampaguita has received multiple awards from the local government and regional agencies
                        for excellence in governance, cleanliness, and disaster preparedness. Notably, it earned the
                        <strong>"HAPAG Kilala and BARKADA Performance Award"</strong> a part of <strong>"KALINISAN sa
                            Bagong Pilipinas proram"</strong> promoting cleaner and greener communities.
                    </p>
                </div>

                <div class="achievement-card">
                    <h3>
                        <span class="achievement-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </span>
                        Successful Community Projects
                    </h3>
                    <p>The barangay has successfully implemented several projects including:</p>
                    <ul>
                        <li>Establishment of a barangay center</li>
                        <li>Upgraded drainage and flood control systems</li>
                    </ul>
                    <p>These efforts have significantly improved mobility, safety, and access to education.</p>
                </div>

                <div class="achievement-card">
                    <h3>
                        <span class="achievement-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </span>
                        Notable Developments
                    </h3>
                    <p>Ongoing infrastructure upgrades, expanded health services, and digitalization of records and
                        business permit applications are among the notable enhancements that reflect the barangay's
                        commitment to modern governance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Community Partnerships Section -->
    <section class="section" style="background: var(--bg-light);">
        <div class="container">
            <h2 class="section-title">Community Partnerships</h2>
            <div class="partnerships-grid">
                <div class="partnership-card">
                    <h4>Collaborations</h4>
                    <p>Barangay Sampaguita actively collaborates with non-government organizations, private sector
                        partners, and local cooperatives to bring services such as medical missions, livelihood
                        training, and disaster risk management workshops.</p>
                </div>

                <div class="partnership-card">
                    <h4>Sister Barangay Relationships</h4>
                    <p>Ties have been established with neighboring barangays and a sister barangay in Quezon Province,
                        fostering knowledge exchange and joint activities in youth development and sustainability
                        programs.</p>
                </div>

                <div class="partnership-card">
                    <h4>Government Agency Partnerships</h4>
                    <p>The barangay maintains strong partnerships with the <strong>Department of Health</strong>,
                        <strong>Department of Interior and Local Government (DILG)</strong>, and <strong>TESDA</strong>,
                        allowing access to critical support, training, and resources.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Community Overview Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Community Overview</h2>
            <div class="overview-grid">
                <div class="overview-card">
                    <h4>
                        <span class="overview-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z" />
                            </svg>
                        </span>
                        Residential Areas
                    </h4>
                    <p>Barangay Sampaguita hosts a mix of established subdivisions and growing residential communities
                        including Sampaguita Homes, Villa Alegre, and Gardenview Residences.</p>
                </div>

                <div class="overview-card">
                    <h4>
                        <span class="overview-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V16h-2.67v2.09c-2.33-.82-4-3.04-4-5.59 0-.14.01-.27.02-.4L9 12.5l-1.06-1.06c0-.01 0-.02-.01-.03C7.93 11.26 8 11.13 8 11c0-.55-.45-1-1-1s-1 .45-1 1c0 .64.42 1.18 1 1.38v.09c-.58.2-1 .76-1 1.42 0 .83.67 1.5 1.5 1.5.65 0 1.2-.42 1.4-1h.1c.2.58.76 1 1.42 1h.09c.2.83.93 1.5 1.76 1.5.55 0 1-.45 1-1 0-.64-.42-1.18-1-1.38v-.09c.58-.2 1-.76 1-1.42 0-.83-.67-1.5-1.5-1.5-.65 0-1.2.42-1.4 1h-.1c-.2-.58-.76-1-1.42-1h-.09c-.2-.83-.93-1.5-1.76-1.5-.55 0-1 .45-1 1 0 .64.42 1.18 1 1.38v.09c-.58.2-1 .76-1 1.42z" />
                            </svg>
                        </span>
                        Local Economy
                    </h4>
                    <p>The local economy thrives on small-to-medium enterprises, sari-sari stores, and service-based
                        businesses. Nearby commercial centers and public markets provide residents with employment and
                        trade opportunities.</p>
                </div>

                <div class="overview-card">
                    <h4>
                        <span class="overview-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2zm-1 4.93L9.5 9.5 7 9.84l2 1.95-.47 2.76L11 13.38l2.47 1.17L13 11.79l2-1.95-2.5-.34L11 6.93z" />
                            </svg>
                        </span>
                        Facilities
                    </h4>
                    <p>The barangay boasts essential community facilities such as:</p>
                    <div class="facilities-list">
                        <ul>
                            <li>A Barangay Health Center</li>
                            <li>Daycare and public elementary school</li>
                            <li>Covered basketball court and multi-purpose hall</li>
                        </ul>
                    </div>
                </div>

                <div class="overview-card">
                    <h4>
                        <span class="overview-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M18.92 6.01c-1.87-3.11-5.27-5.01-8.92-5.01S3.05 2.9 1.18 6.01L0 8.5l1.18 2.49c1.87 3.11 5.27 5.01 8.92 5.01s7.05-1.9 8.92-5.01L24 8.5l-1.08-2.49z" />
                                <path
                                    d="M4 10.5c.83-.67 2.96-2 5.5-2s4.67 1.33 5.5 2c-.83.67-2.96 2-5.5 2s-4.67-1.33-5.5-2z" />
                            </svg>
                        </span>
                        Transportation and Accessibility
                    </h4>
                    <p>Strategically located along accessible routes, Barangay Sampaguita is well-connected by public
                        transportation, including jeepneys and tricycles. Proximity to national highways makes commuting
                        to Metro Manila and other parts of Laguna convenient for residents.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Scroll to Top Button HTML -->
    <button id="scrollToTop" class="scroll-to-top" title="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
        // Add scroll-triggered animations
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

        // Apply animation to cards
        document.querySelectorAll('.achievement-card, .partnership-card, .overview-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
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
