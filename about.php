<?php
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - HabitTracker AI</title>
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php">FAQs</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="auth/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 mb-4">About HabitTracker AI</h1>
                    <p class="lead">Your Personal Habit Building Companion Powered by Intelligent Analytics</p>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3><i class="fas fa-lightbulb text-warning"></i> Our Mission</h3>
                            <p>We believe that building positive habits is the key to personal growth and success. Our mission is to provide an intelligent, user-friendly platform that helps individuals track, analyze, and improve their daily habits through data-driven insights.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3><i class="fas fa-eye text-info"></i> Our Vision</h3>
                            <p>To become the world's leading habit tracking platform that empowers millions of people to achieve their goals through consistent habit formation and AI-powered personalized recommendations.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center mb-4">What Makes Us Different?</h3>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>AI-Powered Insights:</strong> Get personalized recommendations based on your habit patterns
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Streak Tracking:</strong> Visual motivation through streak counters and achievements
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Detailed Analytics:</strong> Understand your weak points and completion trends
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>User-Friendly Interface:</strong> Simple, clean design focused on usability
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Completely Free:</strong> No hidden costs or premium features
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center p-5">
                            <h3>Ready to Build Better Habits?</h3>
                            <p class="mb-4">Join thousands of users who are already transforming their lives one habit at a time.</p>
                            <?php if (!is_logged_in()): ?>
                                <a href="auth/register.php" class="btn btn-light btn-lg">
                                    <i class="fas fa-rocket"></i> Get Started Now
                                </a>
                            <?php else: ?>
                                <a href="user/dashboard.php" class="btn btn-light btn-lg">
                                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
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
                <a href="contact.php" class="text-white me-3">Contact</a>
                <a href="#" class="text-white">Privacy Policy</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>