<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Mark notification as read
if (isset($_GET['mark_read']) && isset($_GET['notification_id'])) {
    $notification_id = (int)$_GET['notification_id'];
    $update_query = "UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $update_all_query = "UPDATE notifications SET is_read = TRUE WHERE user_id = ?";
    $stmt = $conn->prepare($update_all_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

// Get user preferences
$prefs_query = "SELECT * FROM user_preferences WHERE user_id = ?";
$stmt = $conn->prepare($prefs_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prefs = $stmt->get_result()->fetch_assoc();

// If no preferences exist, create default
if (!$prefs) {
    $insert_prefs = "INSERT INTO user_preferences (user_id) VALUES (?)";
    $stmt = $conn->prepare($insert_prefs);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Fetch again
    $stmt = $conn->prepare($prefs_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $prefs = $stmt->get_result()->fetch_assoc();
}

// Update preferences
if (isset($_POST['update_preferences'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $reminder_time = $_POST['reminder_time'];
    $reminder_frequency = $_POST['reminder_frequency'];
    
    $update_prefs = "UPDATE user_preferences SET email_notifications = ?, reminder_time = ?, 
                     reminder_frequency = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_prefs);
    $stmt->bind_param("issi", $email_notifications, $reminder_time, $reminder_frequency, $user_id);
    
    if ($stmt->execute()) {
        $success = "Preferences updated successfully!";
        // Refresh preferences
        $stmt = $conn->prepare($prefs_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $prefs = $stmt->get_result()->fetch_assoc();
    }
}

// Get all notifications
$notifications_query = "SELECT n.*, h.title as habit_title 
                       FROM notifications n
                       LEFT JOIN habits h ON n.habit_id = h.habit_id
                       WHERE n.user_id = ?
                       ORDER BY n.sent_at DESC
                       LIMIT 50";
$stmt = $conn->prepare($notifications_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

// Get unread count
$unread_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE";
$stmt = $conn->prepare($unread_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - HabitTracker AI</title>
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
                        <a class="nav-link active" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="social.php">
                            <i class="fas fa-users"></i> Social
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
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="fas fa-bell"></i> Notifications</h2>
                <p class="text-muted">Stay updated with your habit reminders and achievements</p>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($unread_count > 0): ?>
                    <a href="notifications.php?mark_all_read=1" class="btn btn-primary">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Notification Preferences -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Notification Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" 
                                           name="email_notifications" <?php echo $prefs['email_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">
                                        Enable Email Notifications
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="reminder_time" class="form-label">
                                    <i class="fas fa-clock"></i> Reminder Time
                                </label>
                                <input type="time" class="form-control" id="reminder_time" 
                                       name="reminder_time" value="<?php echo $prefs['reminder_time']; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="reminder_frequency" class="form-label">
                                    <i class="fas fa-calendar"></i> Frequency
                                </label>
                                <select class="form-select" id="reminder_frequency" name="reminder_frequency">
                                    <option value="DAILY" <?php echo ($prefs['reminder_frequency'] === 'DAILY') ? 'selected' : ''; ?>>
                                        Daily
                                    </option>
                                    <option value="WEEKLY" <?php echo ($prefs['reminder_frequency'] === 'WEEKLY') ? 'selected' : ''; ?>>
                                        Weekly
                                    </option>
                                    <option value="NONE" <?php echo ($prefs['reminder_frequency'] === 'NONE') ? 'selected' : ''; ?>>
                                        None
                                    </option>
                                </select>
                            </div>

                            <button type="submit" name="update_preferences" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Save Preferences
                            </button>
                        </form>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                You'll receive reminders for incomplete habits based on your settings.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-inbox"></i> Recent Notifications 
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?> New</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($notifications->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while ($notif = $notifications->fetch_assoc()): 
                                    $icon_map = [
                                        'REMINDER' => 'fa-bell',
                                        'STREAK' => 'fa-fire',
                                        'ACHIEVEMENT' => 'fa-trophy',
                                        'GENERAL' => 'fa-info-circle'
                                    ];
                                    $color_map = [
                                        'REMINDER' => 'primary',
                                        'STREAK' => 'warning',
                                        'ACHIEVEMENT' => 'success',
                                        'GENERAL' => 'info'
                                    ];
                                    $icon = $icon_map[$notif['notification_type']];
                                    $color = $color_map[$notif['notification_type']];
                                ?>
                                    <div class="list-group-item <?php echo !$notif['is_read'] ? 'bg-light' : ''; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-3">
                                                        <i class="fas <?php echo $icon; ?> fa-2x text-<?php echo $color; ?>"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <?php echo htmlspecialchars($notif['message']); ?>
                                                            <?php if (!$notif['is_read']): ?>
                                                                <span class="badge bg-danger">New</span>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <?php if ($notif['habit_title']): ?>
                                                            <p class="mb-1 text-muted small">
                                                                <i class="fas fa-bullseye"></i> 
                                                                <?php echo htmlspecialchars($notif['habit_title']); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i>
                                                            <?php echo date('M d, Y h:i A', strtotime($notif['sent_at'])); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <?php if (!$notif['is_read']): ?>
                                                    <a href="notifications.php?mark_read=1&notification_id=<?php echo $notif['notification_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-check"></i> Mark Read
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-5x text-muted mb-3"></i>
                                <h5>No Notifications Yet</h5>
                                <p class="text-muted">You'll see reminders and updates here</p>
                            </div>
                        <?php endif; ?>
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