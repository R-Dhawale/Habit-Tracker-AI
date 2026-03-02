<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Handle delete request
if (isset($_GET['delete']) && isset($_GET['habit_id'])) {
    $habit_id = (int)$_GET['habit_id'];
    
    // Verify habit belongs to user
    $verify_query = "SELECT habit_id FROM habits WHERE habit_id = ? AND user_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $habit_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $delete_query = "DELETE FROM habits WHERE habit_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $habit_id);
        
        if ($stmt->execute()) {
            header("Location: habits.php?success=deleted");
            exit();
        }
    }
}

// Get all user habits with statistics
$habits_query = "SELECT h.habit_id, h.title, c.category_name, h.frequency, h.preferred_time,
                 (SELECT COUNT(*) FROM habit_logs WHERE habit_id = h.habit_id AND status = 'DONE') as total_completed,
                 (SELECT COUNT(*) FROM habit_logs WHERE habit_id = h.habit_id AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = 'DONE') as completed_week,
                 h.created_at
                 FROM habits h
                 LEFT JOIN categories c ON h.category_id = c.category_id
                 WHERE h.user_id = ?
                 ORDER BY h.created_at DESC";
$stmt = $conn->prepare($habits_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$habits = $stmt->get_result();

$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') {
        $success_message = "Habit added successfully!";
    } elseif ($_GET['success'] === 'updated') {
        $success_message = "Habit updated successfully!";
    } elseif ($_GET['success'] === 'deleted') {
        $success_message = "Habit deleted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Habits - HabitTracker AI</title>
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
                        <a class="nav-link active" href="habits.php">
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
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
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
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="fas fa-list"></i> My Habits</h2>
                <p class="text-muted">Manage all your habits in one place</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="add_habit.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Add New Habit
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($habits->num_rows > 0): ?>
            <div class="row">
                <?php while ($habit = $habits->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bullseye text-primary"></i>
                                        <?php echo htmlspecialchars($habit['title']); ?>
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="edit_habit.php?habit_id=<?php echo $habit['habit_id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" 
                                                   href="habits.php?delete=1&habit_id=<?php echo $habit['habit_id']; ?>"
                                                   onclick="return confirm('Are you sure you want to delete this habit?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <span class="badge bg-primary">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($habit['category_name']); ?>
                                    </span>
                                    <span class="badge bg-success">
                                        <i class="fas fa-calendar"></i> <?php echo $habit['frequency']; ?>
                                    </span>
                                    <?php if ($habit['preferred_time']): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-clock"></i> <?php echo htmlspecialchars($habit['preferred_time']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <h4 class="text-primary mb-0"><?php echo $habit['total_completed']; ?></h4>
                                        <small class="text-muted">Total Completed</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-0"><?php echo $habit['completed_week']; ?>/7</h4>
                                        <small class="text-muted">This Week</small>
                                    </div>
                                </div>

                                <div class="text-muted small">
                                    <i class="fas fa-calendar-plus"></i> 
                                    Created: <?php echo date('M d, Y', strtotime($habit['created_at'])); ?>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="log_habit.php?habit_id=<?php echo $habit['habit_id']; ?>&action=done" 
                                   class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-check"></i> Mark as Done Today
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox display-1 text-muted mb-4"></i>
                            <h3>No Habits Yet</h3>
                            <p class="text-muted mb-4">Start your journey to building better habits today!</p>
                            <a href="add_habit.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus-circle"></i> Create Your First Habit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>