<?php
// Initialize resident session
require_once __DIR__ . '/../config/session_resident.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Barangay Sampaguita - San Pedro, Laguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="Styles/Newstyle.css">
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

        /* STATS CARDS */
        .stats-section {
            background: var(--bg-light);
            padding: var(--space-2xl) 0;
            margin-top: -50px;
            position: relative;
            z-index: 3;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-xl);
            margin-top: var(--space-2xl);
        }

        .stat-card {
            background: var(--bg-white);
            padding: var(--space-2xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-light);
            transition: var(--transition-normal);
            text-align: center;
            border-top: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            margin-bottom: var(--space-sm);
        }

        .stat-label {
            color: var(--text-gray);
            font-weight: 500;
            font-size: 1.1rem;
        }

        /* DEMOGRAPHICS SECTION */
        .demographics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: var(--space-2xl);
            margin-top: var(--space-2xl);
        }

        .demo-card {
            background: var(--bg-white);
            padding: var(--space-2xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-light);
            transition: var(--transition-normal);
        }

        .demo-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .demo-card h3 {
            color: var(--text-dark);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .demo-icon {
            font-size: 1.5rem;
            color: var(--primary);
            padding: var(--space-sm);
            background: var(--primary-lightest);
            border-radius: var(--radius-medium);
        }

        /* TABLE STYLES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--space-md);
            font-size: 0.95rem;
        }

        .data-table th,
        .data-table td {
            padding: var(--space-sm) var(--space-md);
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }

        .data-table th {
            background: var(--primary-lightest);
            color: var(--text-dark);
            font-weight: 600;
        }

        .data-table tbody tr:hover {
            background: var(--primary-lightest);
        }

        /* CHART CONTAINER */
        .chart-container {
            background: var(--bg-white);
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-light);
            margin-top: var(--space-xl);
            position: relative;
            height: 400px;
        }

        /* AGE GROUPS */
        .age-groups {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-lg);
            margin-top: var(--space-lg);
        }

        .age-group {
            background: linear-gradient(135deg, var(--primary-lightest), var(--bg-white));
            padding: var(--space-lg);
            border-radius: var(--radius-large);
            text-align: center;
            border: 1px solid var(--border-light);
            transition: var(--transition-normal);
        }

        .age-group:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-light);
        }

        .age-percentage {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }

        .age-description {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-top: var(--space-sm);
        }

        /* KEY INSIGHTS */
        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-xl);
            margin-top: var(--space-2xl);
        }

        .insight-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--text-white);
            padding: var(--space-2xl);
            border-radius: var(--radius-xl);
            position: relative;
            overflow: hidden;
        }

        .insight-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            transform: rotate(45deg);
        }

        .insight-card h4 {
            color: var(--text-white);
            margin-bottom: var(--space-md);
            position: relative;
            z-index: 2;
        }

        .insight-card p {
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 2;
            margin-bottom: 0;
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .demographics-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .age-groups {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 0.85rem;
            }

            .data-table th,
            .data-table td {
                padding: var(--space-xs) var(--space-sm);
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .hero {
                height: 50vh;
                margin-top: -80px;
                padding-top: 80px;
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
    <!-- Navigation Bar -->
    <?php include './Navbar/navbar.php'; ?>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Barangay Sampaguita</h1>
            <p>A thriving community in San Pedro, Laguna</p>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number">4,941</span>
                    <div class="stat-label">Total Population (2020)</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">1.52%</span>
                    <div class="stat-label">of San Pedro's Population</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">1,238</span>
                    <div class="stat-label">Total Households (2015)</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">4.63</span>
                    <div class="stat-label">Average Household Size</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demographics Section -->
    <section id="demographics" class="section">
        <div class="container">
            <h2 class="section-title">Demographics Overview</h2>

            <div class="demographics-grid">
                <!-- Household Trends -->
                <div class="demo-card">
                    <h3>
                        <i class="fas fa-home demo-icon"></i>
                        Household Trends
                    </h3>
                    <p>Historical data showing the evolution of household composition in Sampaguita Village from 1990 to
                        2015.</p>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Census Year</th>
                                <th>Population</th>
                                <th>Households</th>
                                <th>Avg. Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1990</td>
                                <td>3,324</td>
                                <td>607</td>
                                <td>5.48</td>
                            </tr>
                            <tr>
                                <td>1995</td>
                                <td>3,286</td>
                                <td>616</td>
                                <td>5.33</td>
                            </tr>
                            <tr>
                                <td>2000</td>
                                <td>4,162</td>
                                <td>824</td>
                                <td>5.05</td>
                            </tr>
                            <tr>
                                <td>2007</td>
                                <td>4,571</td>
                                <td>888</td>
                                <td>5.15</td>
                            </tr>
                            <tr>
                                <td>2010</td>
                                <td>5,342</td>
                                <td>1,168</td>
                                <td>4.57</td>
                            </tr>
                            <tr>
                                <td>2015</td>
                                <td>5,733</td>
                                <td>1,238</td>
                                <td>4.63</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Age Distribution -->
                <div class="demo-card">
                    <h3>
                        <i class="fas fa-users demo-icon"></i>
                        Age Distribution
                    </h3>
                    <p>Population breakdown by age groups based on the 2015 Census data.</p>

                    <div class="age-groups">
                        <div class="age-group">
                            <span class="age-percentage">27.44%</span>
                            <div class="age-description">Ages 0-14<br>(Young Dependents)</div>
                        </div>
                        <div class="age-group">
                            <span class="age-percentage">66.21%</span>
                            <div class="age-description">Ages 15-64<br>(Working Age)</div>
                        </div>
                        <div class="age-group">
                            <span class="age-percentage">6.35%</span>
                            <div class="age-description">Ages 65+<br>(Senior Citizens)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <canvas id="populationChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Key Insights Section -->
    <section id="insights" class="section" style="background: var(--bg-light);">
        <div class="container">
            <h2 class="section-title">Key Population Insights</h2>

            <div class="insights-grid">
                <div class="insight-card">
                    <h4>Median Age: 28 Years</h4>
                    <p>Half of Sampaguita Village's population is younger than 28, indicating a relatively young
                        community with great potential for growth and development.</p>
                </div>

                <div class="insight-card">
                    <h4>Dependency Ratios</h4>
                    <p>For every 100 working-age residents: 41 youth dependents, 10 senior citizens, and 51 total
                        dependents - showing a balanced demographic structure.</p>
                </div>

                <div class="insight-card">
                    <h4>Peak Age Group: 15-19</h4>
                    <p>With 578 individuals, the 15-19 age group represents the largest population segment, highlighting
                        the community's youthful energy and future workforce potential.</p>
                </div>

                <div class="insight-card">
                    <h4>Household Size Trend</h4>
                    <p>Average household size has decreased from 5.48 (1990) to 4.63 (2015), reflecting modern family
                        structures and improved living standards.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Scroll to Top Button HTML -->
    <button id="scrollToTop" class="scroll-to-top" title="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Include popup forms if user is logged in -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        if (file_exists('Requests/document-popup.php'))
            include 'Requests/document-popup.php';
        if (file_exists('Requests/business-popup.php'))
            include 'Requests/business-popup.php';
        if (file_exists('Requests/complaint-popup.php'))
            include 'Requests/complaint-popup.php';
        if (file_exists('Requests/scholar-popup.php'))
            include 'Requests/scholar-popup.php';
        ?>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="Script.js"></script>
    <script>
        // Population Chart
        const ctx = document.getElementById('populationChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1990', '1995', '2000', '2007', '2010', '2015'],
                datasets: [{
                    label: 'Household Population',
                    data: [3324, 3286, 4162, 4571, 5342, 5733],
                    borderColor: '#5CB25D',
                    backgroundColor: 'rgba(92, 178, 93, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Number of Households',
                    data: [607, 616, 824, 888, 1168, 1238],
                    borderColor: '#3F7A3C',
                    backgroundColor: 'rgba(63, 122, 60, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Population and Household Trends (1990-2015)',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                }
            }
        });

        // Smooth scrolling for navigation
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

        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseFloat(counter.textContent.replace(/[^0-9.]/g, ''));
                let current = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = counter.textContent.includes('%') ? target + '%' :
                            target.toString().includes('.') ? target.toFixed(2) :
                                Math.round(target).toLocaleString();
                        clearInterval(timer);
                    } else {
                        counter.textContent = counter.textContent.includes('%') ? current.toFixed(2) + '%' :
                            target.toString().includes('.') ? current.toFixed(2) :
                                Math.round(current).toLocaleString();
                    }
                }, 20);
            });
        }

        // Trigger counter animation when stats section is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        observer.observe(document.querySelector('.stats-section'));

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
