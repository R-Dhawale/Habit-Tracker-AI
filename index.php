<?php
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Tracker with AI Insights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-bullseye"></i> HabitTracker AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') ? 'admin/admin_dashboard.php' : 'user/dashboard.php'; ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light ms-2" href="auth/register.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Particles Background -->
        <div id="particles-js"></div>
        
        <div class="container">
            <h1 class="fade-in">Build Better Habits with AI Insights</h1>
            <p class="lead fade-in">Track your daily habits, analyze your progress, and get personalized recommendations powered by intelligent analytics</p>
            <div class="fade-in">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') ? 'admin/admin_dashboard.php' : 'user/dashboard.php'; ?>" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-light btn-lg px-5 me-3">
                        <i class="fas fa-rocket"></i> Get Started Free
                    </a>
                    <a href="auth/login.php" class="btn btn-outline-light btn-lg px-5">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose HabitTracker AI?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="display-4 text-primary mb-3">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4>Smart Analytics</h4>
                            <p>Get AI-powered insights on your habit patterns, streaks, and weak points</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="display-4 text-success mb-3">
                                <i class="fas fa-fire"></i>
                            </div>
                            <h4>Streak Tracking</h4>
                            <p>Stay motivated with visual streak counters and achievement milestones</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="display-4 text-warning mb-3">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h4>Personalized Tips</h4>
                            <p>Receive custom recommendations based on your behavior patterns</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light" id="statistics-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="p-4 stat-item">
                        <h2 class="display-4 text-primary">
                            <i class="fas fa-users"></i>
                        </h2>
                        <h3 class="stat-number">1000+</h3>
                        <p class="stat-label">Active Users</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 stat-item">
                        <h2 class="display-4 text-success">
                            <i class="fas fa-bullseye"></i>
                        </h2>
                        <h3 class="stat-number">5000+</h3>
                        <p class="stat-label">Habits Tracked</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 stat-item">
                        <h2 class="display-4 text-warning">
                            <i class="fas fa-fire"></i>
                        </h2>
                        <h3 class="stat-number">85%</h3>
                        <p class="stat-label">Success Rate</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 stat-item">
                        <h2 class="display-4 text-info">
                            <i class="fas fa-star"></i>
                        </h2>
                        <h3 class="stat-number">4.8/5</h3>
                        <p class="stat-label">User Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick FAQ Section -->
    <section class="py-5" id="quick-faq-section">
        <div class="container">
            <h2 class="text-center mb-5 section-title">Frequently Asked Questions</h2>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="quickFAQ">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    What is HabitTracker AI and how does it work?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#quickFAQ">
                                <div class="accordion-body">
                                    HabitTracker AI is your personal companion for building positive habits. Create habits you want to build, mark them complete each day, and get AI-powered insights based on your patterns. The system analyzes your behavior and gives personalized suggestions to help you succeed!
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="fas fa-gift me-2 text-success"></i>
                                    Is it free to use?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#quickFAQ">
                                <div class="accordion-body">
                                    Yes! HabitTracker AI is completely free with all features included. No hidden costs, no premium tiers, no credit card required. We believe everyone deserves access to tools that help them improve their lives.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="fas fa-brain me-2 text-warning"></i>
                                    How does the AI help me?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#quickFAQ">
                                <div class="accordion-body">
                                    The AI analyzes your habit completion patterns and identifies trends like weak days, best times for success, and struggling habits. It then gives you personalized recommendations based on YOUR behavior – not generic advice!
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="fas fa-trophy me-2 text-info"></i>
                                    What are streaks and points?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#quickFAQ">
                                <div class="accordion-body">
                                    A streak is consecutive days you've completed habits. Points are earned for each completion (10 points per habit). These gamification features keep you motivated and let you compete on the leaderboard with friends!
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="fas fa-mobile-alt me-2 text-danger"></i>
                                    Does it work on mobile?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#quickFAQ">
                                <div class="accordion-body">
                                    Absolutely! The website is fully responsive and works perfectly on phones and tablets. Track your habits anywhere, anytime from your mobile browser. No app download needed!
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="faq.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-question-circle"></i> View All FAQs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 HabitTracker AI. All rights reserved.</p>
            <p>
                <a href="about.php" class="text-white me-3">About</a>
                <a href="faq.php" class="text-white me-3">FAQ</a>
                <a href="contact.php" class="text-white me-3">Contact</a>
                <a href="#" class="text-white">Privacy Policy</a>
            </p>
        </div>
    </footer>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Particles.js Library -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <!-- Initialize Particles -->
    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#ffffff"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": false,
                    "anim": {
                        "enable": false,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 40,
                        "size_min": 0.1,
                        "sync": false
                    }
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.4,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 2,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {
                        "enable": false,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "grab"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 140,
                        "line_linked": {
                            "opacity": 1
                        }
                    },
                    "push": {
                        "particles_nb": 4
                    }
                }
            },
            "retina_detect": true
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>