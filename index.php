<?php
session_start();
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Transform Your Learning Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            --warning-gradient: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #1a1a2e;
            --text-secondary: #6c757d;
            --text-light: rgba(255, 255, 255, 0.9);
            --shadow-soft: 0 10px 40px rgba(31, 38, 135, 0.2);
            --shadow-medium: 0 20px 60px rgba(31, 38, 135, 0.3);
            --shadow-strong: 0 30px 80px rgba(31, 38, 135, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 75%, #f5576c 100%);
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--text-primary);
            position: relative;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            animation: float 25s ease-in-out infinite;
        }

        .bg-shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .bg-shape:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 60%;
            right: 10%;
            animation-delay: -8s;
            background: linear-gradient(45deg, rgba(240, 147, 251, 0.1), rgba(245, 87, 108, 0.1));
        }

        .bg-shape:nth-child(3) {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 15%;
            animation-delay: -16s;
            background: linear-gradient(45deg, rgba(79, 172, 254, 0.1), rgba(0, 242, 254, 0.1));
        }

        .bg-shape:nth-child(4) {
            width: 250px;
            height: 250px;
            top: 30%;
            right: 30%;
            animation-delay: -24s;
            background: linear-gradient(45deg, rgba(86, 171, 47, 0.1), rgba(168, 230, 207, 0.1));
        }

        .bg-shape:nth-child(5) {
            width: 180px;
            height: 180px;
            bottom: 40%;
            right: 5%;
            animation-delay: -32s;
            background: linear-gradient(45deg, rgba(247, 151, 30, 0.1), rgba(255, 210, 0, 0.1));
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg) scale(1);
                opacity: 0.3;
            }
            25% {
                transform: translateY(-50px) rotate(90deg) scale(1.1);
                opacity: 0.6;
            }
            50% {
                transform: translateY(-100px) rotate(180deg) scale(0.9);
                opacity: 0.4;
            }
            75% {
                transform: translateY(-50px) rotate(270deg) scale(1.05);
                opacity: 0.7;
            }
        }

        /* Particle System */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: particleFloat 15s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Enhanced Navbar */
        .navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: var(--shadow-soft);
            padding: 1.2rem 0;
            transition: all 0.3s ease;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(30px);
            box-shadow: var(--shadow-medium);
        }

        .navbar-brand {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.3));
        }

        .nav-link {
            color: var(--text-light) !important;
            font-weight: 600;
            padding: 0.8rem 1.5rem !important;
            border-radius: 30px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            margin: 0 0.2rem;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            transition: left 0.4s ease;
            z-index: -1;
        }

        .nav-link:hover::before {
            left: 0;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-3px);
            box-shadow: var(--shadow-soft);
        }

        /* Hero Section Enhancement */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 20px 80px;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 900px;
        }

        .hero-section h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(3rem, 6vw, 5rem);
            font-weight: 800;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            animation: heroFadeIn 1.2s ease-out;
            text-shadow: 0 0 50px rgba(255, 255, 255, 0.3);
        }

        .hero-section .subtitle {
            font-size: clamp(1.2rem, 2.5vw, 1.5rem);
            margin-bottom: 3rem;
            color: var(--text-light);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
            animation: heroFadeIn 1.2s ease-out 0.3s both;
            font-weight: 400;
        }

        @keyframes heroFadeIn {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced CTA Button */
        .cta-button {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 18px 40px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            transition: all 0.4s ease;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
            animation: heroFadeIn 1.2s ease-out 0.6s both;
            backdrop-filter: blur(20px);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1));
            transition: left 0.4s ease;
        }

        .cta-button:hover::before {
            left: 0;
        }

        .cta-button:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow-strong);
            color: white;
            text-decoration: none;
        }

        .cta-button i {
            font-size: 1.3rem;
            transition: transform 0.3s ease;
        }

        .cta-button:hover i {
            transform: translateX(5px);
        }

        /* Features Section */
        .features-section {
            padding: 100px 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            position: relative;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 4rem;
            background: linear-gradient(135deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--accent-gradient);
            border-radius: 2px;
        }

        .feature-card {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-soft);
            transition: all 0.5s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.5s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-strong);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .feature-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon::before {
            transform: scale(1);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
        }

        .feature-icon i {
            font-size: 2.5rem;
            color: white;
            z-index: 1;
            position: relative;
        }

        .feature-card h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
        }

        .feature-card p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 20px;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            transition: all 0.4s ease;
            margin-bottom: 2rem;
        }

        .stat-item:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-medium);
        }

        .stat-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            font-weight: 800;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 100px 20px;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(30px);
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        }

        .cta-section h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .cta-section p {
            font-size: 1.3rem;
            margin-bottom: 3rem;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            padding: 2rem 0;
            font-weight: 400;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Scroll Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s ease;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 20px 60px;
                min-height: 90vh;
            }
            
            .features-section {
                padding: 80px 20px;
            }
            
            .feature-card {
                padding: 2rem 1.5rem;
            }
            
            .cta-section {
                padding: 80px 20px;
            }
            
            .nav-link {
                padding: 0.6rem 1rem !important;
                margin: 0.2rem 0;
            }

            .navbar-collapse {
                background: rgba(0, 0, 0, 0.2);
                backdrop-filter: blur(20px);
                border-radius: 15px;
                margin-top: 1rem;
                padding: 1rem;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-gradient);
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>

    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_SESSION['role'] == 'student' ? 'student_dashboard.php' : 'teacher_dashboard.php'; ?>">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Transform Your Learning Journey</h1>
                <p class="subtitle">Experience the future of education with our interactive platform featuring AI-powered quizzes, real-time analytics, and personalized learning paths designed to unlock your potential.</p>
                <a href="<?php echo isset($_SESSION['user_id']) ? ($_SESSION['role'] == 'student' ? 'student_dashboard.php' : 'teacher_dashboard.php') : 'login.php'; ?>" class="cta-button">
                    <i class="fas fa-rocket"></i>
                    <span>Launch Your Journey</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Discover What Makes Us Different</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3>Smart Quizzes</h3>
                        <p>Engage with intelligently designed multiple-choice questions that adapt to your learning style, complete with flexible "I don't know" options for honest self-assessment.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Real-time Analytics</h3>
                        <p>Track your progress with comprehensive analytics and insights that help you understand your strengths and identify areas for improvement.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>AI Chatbot Support</h3>
                        <p>Get instant help and guidance from our intelligent chatbot that provides personalized assistance and answers to your learning questions 24/7.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Digital Library</h3>
                        <p>Access a comprehensive collection of lecture notes, study materials, and resources organized for easy discovery and seamless learning.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Collaborative Learning</h3>
                        <p>Connect with peers and instructors in a dynamic learning environment that encourages collaboration and knowledge sharing.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobile Optimized</h3>
                        <p>Learn anywhere, anytime with our fully responsive platform that works seamlessly across all devices and screen sizes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number" data-count="100">0</div>
                        <div class="stat-label">Active Students</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number" data-count="25">0</div>
                        <div class="stat-label">Expert Instructors</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number" data-count="10">0</div>
                        <div class="stat-label">Courses Available</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number" data-count="98">0</div>
                        <div class="stat-label">Success Rate %</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="animate-on-scroll">Ready to Start Your Learning Adventure?</h2>
            <p class="animate-on-scroll">Join thousands of students who have already transformed their educational journey with our innovative platform.</p>
            <a href="<?php echo isset($_SESSION['user_id']) ? ($_SESSION['role'] == 'student' ? 'student_dashboard.php' : 'teacher_dashboard.php') : 'register.php'; ?>" class="cta-button animate-on-scroll">
                <i class="fas fa-arrow-right"></i>
                <span>Get Started Today</span>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 EduLearn. Transforming education through innovation.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loading Animation
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loadingOverlay').style.opacity = '0';
                setTimeout(() => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                }, 500);
            }, 1000);
        });

        // Particle System
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll Animations
        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            const windowHeight = window.innerHeight;

            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) {
                    element.classList.add('animated');
                }
            });
        }

        // Counter Animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const increment = target / 100;
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                // Start animation when element is visible
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCounter();
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(counter);
            });
        }

        // Smooth Scrolling
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            animateOnScroll();
            animateCounters();
            
            // Add scroll event listener
            window.addEventListener('scroll', animateOnScroll);
        });

        // Add some interactive hover effects
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02) rotateY(5deg)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1) rotateY(0deg)';
            });
        });

        // Add parallax effect to background shapes
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const shapes = document.querySelectorAll('.bg-shape');
            
            shapes.forEach((shape, index) => {
                const speed = 0.5 + (index * 0.1);
                shape.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    </script>
</body>
</html>

