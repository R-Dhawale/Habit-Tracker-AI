<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

$error = '';
$success = '';

// Get categories for dropdown
$categories_query = "SELECT category_id, category_name FROM categories ORDER BY category_name";
$categories = $conn->query($categories_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $frequency = $_POST['frequency'];
    $preferred_time = clean_input($_POST['preferred_time']);
    
    // Validation
    if (empty($title)) {
        $error = "Habit title is required!";
    } elseif (strlen($title) < 3) {
        $error = "Habit title must be at least 3 characters!";
    } elseif ($category_id <= 0) {
        $error = "Please select a category!";
    } else {
        // Insert habit
        $insert_query = "INSERT INTO habits (user_id, title, category_id, frequency, preferred_time) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isiss", $user_id, $title, $category_id, $frequency, $preferred_time);
        
        if ($stmt->execute()) {
            header("Location: habits.php?success=added");
            exit();
        } else {
            $error = "Failed to add habit. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Habit - HabitTracker AI</title>
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
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-circle"></i> Profile
                            </a></li>
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
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Habit</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="title" class="form-label">
                                    <i class="fas fa-bullseye"></i> Habit Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                       placeholder="e.g., Morning Exercise, Read for 30 minutes" required>
                                <small class="text-muted">What habit do you want to build?</small>
                            </div>

                            <div class="mb-4">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-tag"></i> Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="category_id" name="category_id" required>
                                    <option value="">-- Select Category --</option>
                                    <?php 
                                    $categories->data_seek(0); // Reset pointer
                                    while ($cat = $categories->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $cat['category_id']; ?>">
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Frequency <span class="text-danger">*</span>
                                </label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="frequency" id="daily" 
                                           value="DAILY" checked>
                                    <label class="btn btn-outline-primary" for="daily">
                                        <i class="fas fa-calendar-day"></i> Daily
                                    </label>

                                    <input type="radio" class="btn-check" name="frequency" id="weekly" 
                                           value="WEEKLY">
                                    <label class="btn btn-outline-primary" for="weekly">
                                        <i class="fas fa-calendar-week"></i> Weekly
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="preferred_time" class="form-label">
                                    <i class="fas fa-clock"></i> Preferred Time (Optional)
                                </label>
                                <select class="form-select form-select-lg" id="preferred_time" name="preferred_time">
                                    <option value="">-- Select Time --</option>
                                    <option value="Morning (6AM - 12PM)">Morning (6AM - 12PM)</option>
                                    <option value="Afternoon (12PM - 6PM)">Afternoon (12PM - 6PM)</option>
                                    <option value="Evening (6PM - 10PM)">Evening (6PM - 10PM)</option>
                                    <option value="Night (10PM - 6AM)">Night (10PM - 6AM)</option>
                                </select>
                                <small class="text-muted">When do you prefer to complete this habit?</small>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb"></i> 
                                <strong>Pro Tip:</strong> Start with small, achievable habits. 
                                Consistency is more important than intensity!
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Create Habit
                                </button>
                                <a href="habits.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Habits
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Example Habits Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Popular Habit Ideas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Health & Fitness</h6>
                                <ul class="small">
                                    <li>Exercise for 30 minutes</li>
                                    <li>Drink 8 glasses of water</li>
                                    <li>Sleep by 10 PM</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success">Productivity</h6>
                                <ul class="small">
                                    <li>Write daily journal</li>
                                    <li>Plan tomorrow's tasks</li>
                                    <li>Review daily goals</li>
                                </ul>
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