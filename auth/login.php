<?php
require_once '../config/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: ../admin/admin_dashboard.php");
    } else {
        header("Location: ../user/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password!";
    } else {
        // Fetch user from database
        $query = "SELECT user_id, name, email, password_hash, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'ADMIN') {
                    header("Location: ../admin/admin_dashboard.php");
                } else {
                    header("Location: ../user/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password!";
            }
        } else {
            $error = "Invalid email or password!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HabitTracker AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-sign-in-alt"></i> Login to Your Account</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Enter your email" required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Don't have an account? 
                                <a href="register.php" class="text-primary fw-bold">Register here</a>
                            </p>
                        </div>
                        
                        <hr>
                        
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> Demo Credentials:</strong><br>
                            <strong>Admin:</strong> admin@habittracker.com / admin123<br>
                            <strong>User:</strong> Register a new account
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        togglePassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the icon
            toggleIcon.classList.toggle('fa-eye');
            toggleIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>