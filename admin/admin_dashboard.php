<?php
require_once '../config/db.php';
require_login();
require_admin();

// Get statistics
$total_users_query = "SELECT COUNT(*) as total FROM users WHERE role = 'USER'";
$total_users = $conn->query($total_users_query)->fetch_assoc()['total'];

$total_habits_query = "SELECT COUNT(*) as total FROM habits";
$total_habits = $conn->query($total_habits_query)->fetch_assoc()['total'];

$total_logs_query = "SELECT COUNT(*) as total FROM habit_logs";
$total_logs = $conn->query($total_logs_query)->fetch_assoc()['total'];

$active_today_query = "SELECT COUNT(DISTINCT user_id) as active FROM habit_logs 
                       JOIN habits ON habit_logs.habit_id = habits.habit_id 
                       WHERE log_date = CURDATE()";
$active_today = $conn->query($active_today_query)->fetch_assoc()['active'];

// Get recent users
$recent_users_query = "SELECT user_id, name, email, created_at FROM users 
                       WHERE role = 'USER' ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($recent_users_query);

// Get categories
$categories_query = "SELECT category_id, category_name, 
                     (SELECT COUNT(*) FROM habits WHERE category_id = categories.category_id) as habit_count 
                     FROM categories ORDER BY habit_count DESC";
$categories = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HabitTracker AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_categories.php">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../user/dashboard.php">
                            <i class="fas fa-user"></i> User View
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light ms-2" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Admin Dashboard</h2>
                <p class="text-muted">Manage your HabitTracker AI platform</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $total_users; ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $total_habits; ?></h3>
                            <p>Total Habits</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-bullseye"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $total_logs; ?></h3>
                            <p>Total Logs</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $active_today; ?></h3>
                            <p>Active Today</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Users -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Recent Users</h5>
                        <a href="manage_users.php" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $recent_users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-tags"></i> Habit Categories</h5>
                        <a href="manage_categories.php" class="btn btn-primary btn-sm">Manage</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Habits Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo $cat['habit_count']; ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <a href="manage_users.php" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-users fa-2x mb-2"></i><br>
                                    Manage Users
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="manage_categories.php" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-tags fa-2x mb-2"></i><br>
                                    Manage Categories
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="../user/dashboard.php" class="btn btn-info btn-lg w-100">
                                    <i class="fas fa-user fa-2x mb-2"></i><br>
                                    User Dashboard
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="../index.php" class="btn btn-warning btn-lg w-100">
                                    <i class="fas fa-home fa-2x mb-2"></i><br>
                                    Go to Homepage
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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