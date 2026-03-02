<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

$error = '';
$habit = null;

// Check if habit_id is provided
if (!isset($_GET['habit_id'])) {
    header("Location: habits.php");
    exit();
}

$habit_id = (int)$_GET['habit_id'];

// Get habit details and verify ownership
$habit_query = "SELECT * FROM habits WHERE habit_id = ? AND user_id = ?";
$stmt = $conn->prepare($habit_query);
$stmt->bind_param("ii", $habit_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: habits.php?error=not_found");
    exit();
}

$habit = $result->fetch_assoc();

// Get categories
$categories_query = "SELECT category_id, category_name FROM categories ORDER BY category_name";
$categories = $conn->query($categories_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $frequency = $_POST['frequency'];
    $preferred_time = clean_input($_POST['preferred_time']);
    
    if (empty($title)) {
        $error = "Habit title is required!";
    } elseif (strlen($title) < 3) {
        $error = "Habit title must be at least 3 characters!";
    } elseif ($category_id <= 0) {
        $error = "Please select a category!";
    } else {
        $update_query = "UPDATE habits SET title = ?, category_id = ?, frequency = ?, preferred_time = ? 
                        WHERE habit_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sissii", $title, $category_id, $frequency, $preferred_time, $habit_id, $user_id);
        
        if ($stmt->execute()) {
            header("Location: habits.php?success=updated");
            exit();
        } else {
            $error = "Failed to update habit. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Habit - HabitTracker AI</title>
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
                        <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Habit</h4>
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
                                       value="<?php echo htmlspecialchars($habit['title']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-tag"></i> Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="category_id" name="category_id" required>
                                    <option value="">-- Select Category --</option>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category_id']; ?>"
                                                <?php echo ($cat['category_id'] == $habit['category_id']) ? 'selected' : ''; ?>>
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
                                           value="DAILY" <?php echo ($habit['frequency'] == 'DAILY') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary" for="daily">
                                        <i class="fas fa-calendar-day"></i> Daily
                                    </label>

                                    <input type="radio" class="btn-check" name="frequency" id="weekly" 
                                           value="WEEKLY" <?php echo ($habit['frequency'] == 'WEEKLY') ? 'checked' : ''; ?>>
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
                                    <option value="Morning (6AM - 12PM)" 
                                            <?php echo ($habit['preferred_time'] == 'Morning (6AM - 12PM)') ? 'selected' : ''; ?>>
                                        Morning (6AM - 12PM)
                                    </option>
                                    <option value="Afternoon (12PM - 6PM)"
                                            <?php echo ($habit['preferred_time'] == 'Afternoon (12PM - 6PM)') ? 'selected' : ''; ?>>
                                        Afternoon (12PM - 6PM)
                                    </option>
                                    <option value="Evening (6PM - 10PM)"
                                            <?php echo ($habit['preferred_time'] == 'Evening (6PM - 10PM)') ? 'selected' : ''; ?>>
                                        Evening (6PM - 10PM)
                                    </option>
                                    <option value="Night (10PM - 6AM)"
                                            <?php echo ($habit['preferred_time'] == 'Night (10PM - 6AM)') ? 'selected' : ''; ?>>
                                        Night (10PM - 6AM)
                                    </option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Update Habit
                                </button>
                                <a href="habits.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                            </div>
                        </form>
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