<?php
require_once 'config/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $subject = clean_input($_POST['subject']);
    $message = clean_input($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // In a real application, you would send an email or store in database
        // For now, we'll just show a success message
        $success = "Thank you for contacting us! We'll get back to you soon.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - HabitTracker AI</title>
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
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
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

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 mb-4">Get In Touch</h1>
                    <p class="lead">Have questions or feedback? We'd love to hear from you!</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user"></i> Your Name
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               placeholder="Enter your name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope"></i> Email Address
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Enter your email" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">
                                        <i class="fas fa-tag"></i> Subject
                                    </label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           placeholder="What is this about?" required>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment"></i> Message
                                    </label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              placeholder="Write your message here..." required></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane"></i> Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Contact Info Cards -->
                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <div class="display-4 text-primary mb-3">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <h5>Email</h5>
                                    <p>support@habittracker.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <div class="display-4 text-success mb-3">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <h5>Phone</h5>
                                    <p>+91 1234567890</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <div class="display-4 text-warning mb-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <h5>Location</h5>
                                    <p>India</p>
                                </div>
                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>