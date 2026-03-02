<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_email = $_SESSION['email'];

$success = '';
$error = '';

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Get user statistics
$stats_query = "SELECT 
                (SELECT COUNT(*) FROM habits WHERE user_id = ?) as total_habits,
                (SELECT COUNT(*) FROM habit_logs hl 
                 JOIN habits h ON hl.habit_id = h.habit_id 
                 WHERE h.user_id = ? AND hl.status = 'DONE') as total_completions,
                (SELECT COUNT(DISTINCT hl.log_date) FROM habit_logs hl 
                 JOIN habits h ON hl.habit_id = h.habit_id 
                 WHERE h.user_id = ? AND hl.status = 'DONE') as active_days";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Calculate days since joining
$days_joined = floor((time() - strtotime($user_data['created_at'])) / (60 * 60 * 24));

// Handle profile update
if (isset($_POST['update_profile'])) {
    $new_name = clean_input($_POST['name']);
    $new_email = clean_input($_POST['email']);
    
    if (empty($new_name) || empty($new_email)) {
        $error = "Name and email are required!";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists (excluding current user)
        $check_email = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            $update_query = "UPDATE users SET name = ?, email = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['name'] = $new_name;
                $_SESSION['email'] = $new_email;
                $user_name = $new_name;
                $user_email = $new_email;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile!";
            }
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required!";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        // Verify current password
        if (password_verify($current_password, $user_data['password_hash'])) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_pwd_query = "UPDATE users SET password_hash = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_pwd_query);
            $stmt->bind_param("si", $new_password_hash, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password!";
            }
        } else {
            $error = "Current password is incorrect!";
        }
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $confirm_delete = $_POST['confirm_delete'];
    
    if ($confirm_delete === 'DELETE') {
        // Delete user account (cascades will delete habits and logs)
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            session_destroy();
            header("Location: ../index.php?account_deleted=1");
            exit();
        } else {
            $error = "Failed to delete account!";
        }
    } else {
        $error = "Please type 'DELETE' to confirm account deletion!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - HabitTracker AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-bullseye"></i> HabitTracker AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="habits.php">
                            <i class="fas fa-list"></i> My Habits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="insights.php">
                            <i class="fas fa-brain"></i> AI Insights
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">
                                <i class="fas fa-user-circle"></i> Profile
                            </a></li>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../admin/admin_dashboard.php">
                                <i class="fas fa-shield-alt"></i> Admin Panel
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-user-circle"></i> My Profile</h2>
                <p class="text-muted">Manage your account settings and preferences</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Overview -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user_name); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user_email); ?></p>
                        <span class="badge bg-primary">
                            <?php echo ucfirst(strtolower($user_data['role'])); ?>
                        </span>
                        <hr>
                        <p class="small text-muted mb-1">
                            <i class="fas fa-calendar"></i> 
                            Member since <?php echo date('M d, Y', strtotime($user_data['created_at'])); ?>
                        </p>
                        <p class="small text-muted">
                            <i class="fas fa-clock"></i> 
                            <?php echo $days_joined; ?> days with us
                        </p>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Your Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-bullseye text-primary"></i> Total Habits</span>
                                <strong><?php echo $stats['total_habits']; ?></strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-check-circle text-success"></i> Completions</span>
                                <strong><?php echo $stats['total_completions']; ?></strong>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-calendar-check text-info"></i> Active Days</span>
                                <strong><?php echo $stats['active_days']; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Forms -->
            <div class="col-lg-8">
                <!-- Update Profile -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Update Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i> Full Name
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-key"></i> Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-lock"></i> Current Password
                                </label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock"></i> New Password
                                </label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" minlength="6" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i> Confirm New Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" minlength="6" required>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Preferences</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6><i class="fas fa-moon"></i> Dark Mode</h6>
                            <p class="text-muted small">Toggle dark mode using the button in the bottom-right corner</p>
                            <button class="btn btn-info" onclick="document.getElementById('darkModeToggle').click()">
                                <i class="fas fa-adjust"></i> Toggle Dark Mode
                            </button>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6><i class="fas fa-chart-line"></i> Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <a href="habits.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> Manage Habits
                                </a>
                                <a href="insights.php" class="btn btn-outline-success">
                                    <i class="fas fa-brain"></i> View AI Insights
                                </a>
                                <a href="reports.php" class="btn btn-outline-info">
                                    <i class="fas fa-file-alt"></i> Generate Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card shadow border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-danger">Delete Account</h6>
                        <p class="text-muted">
                            Once you delete your account, there is no going back. This will permanently delete:
                        </p>
                        <ul class="text-muted">
                            <li>Your profile information</li>
                            <li>All your habits</li>
                            <li>All your habit logs and history</li>
                            <li>All your reports and data</li>
                        </ul>

                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" 
                                data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete My Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Account Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
                        </div>
                        <p>To confirm, please type <strong>DELETE</strong> in the box below:</p>
                        <input type="text" class="form-control" name="confirm_delete" 
                               placeholder="Type DELETE to confirm" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_account" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>