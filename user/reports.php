<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get user's total habits
$total_habits_query = "SELECT COUNT(*) as total FROM habits WHERE user_id = ?";
$stmt = $conn->prepare($total_habits_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_habits = $stmt->get_result()->fetch_assoc()['total'];

// Get total completed logs
$total_logs_query = "SELECT COUNT(*) as total FROM habit_logs hl 
                     JOIN habits h ON hl.habit_id = h.habit_id 
                     WHERE h.user_id = ? AND hl.status = 'DONE'";
$stmt = $conn->prepare($total_logs_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_logs = $stmt->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - HabitTracker AI</title>
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
                        <a class="nav-link active" href="reports.php">
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
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-chart-bar"></i> Habit Reports</h2>
                <p class="text-muted">Generate detailed reports of your habit tracking progress</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-bullseye fa-3x mb-2"></i>
                        <h3><?php echo $total_habits; ?></h3>
                        <p class="mb-0">Total Active Habits</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <h3><?php echo $total_logs; ?></h3>
                        <p class="mb-0">Total Completions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Generation Options -->
        <div class="row">
            <!-- Weekly Report -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-week"></i> Weekly Report</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-calendar-week fa-5x text-primary mb-3"></i>
                        </div>
                        <h6>Generate Last 7 Days Report</h6>
                        <p class="text-muted">
                            Get a comprehensive overview of your habit performance for the past week including:
                        </p>
                        <ul>
                            <li>Daily completion statistics</li>
                            <li>Habit-wise breakdown</li>
                            <li>Completion percentage</li>
                            <li>Visual charts and graphs</li>
                            <li>AI-powered insights</li>
                        </ul>
                        <div class="d-grid gap-2">
                            <a href="generate_report.php?type=weekly" class="btn btn-primary btn-lg" target="_blank">
                                <i class="fas fa-file-pdf"></i> Generate Weekly Report
                            </a>
                            <button class="btn btn-outline-primary" onclick="window.open('generate_report.php?type=weekly', '_blank'); setTimeout(() => window.print(), 1000);">
                                <i class="fas fa-print"></i> Generate & Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Report -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Monthly Report</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-calendar-alt fa-5x text-success mb-3"></i>
                        </div>
                        <h6>Generate Last 30 Days Report</h6>
                        <p class="text-muted">
                            Get an in-depth analysis of your habit journey over the past month including:
                        </p>
                        <ul>
                            <li>30-day completion trends</li>
                            <li>Category-wise performance</li>
                            <li>Best & worst performing habits</li>
                            <li>Detailed analytics</li>
                            <li>Monthly summary & recommendations</li>
                        </ul>
                        <div class="d-grid gap-2">
                            <a href="generate_report.php?type=monthly" class="btn btn-success btn-lg" target="_blank">
                                <i class="fas fa-file-pdf"></i> Generate Monthly Report
                            </a>
                            <button class="btn btn-outline-success" onclick="window.open('generate_report.php?type=monthly', '_blank'); setTimeout(() => window.print(), 1000);">
                                <i class="fas fa-print"></i> Generate & Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Date Range Report -->
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Custom Date Range Report</h5>
                    </div>
                    <div class="card-body">
                        <form action="generate_report.php" method="GET" target="_blank">
                            <input type="hidden" name="type" value="custom">
                            <div class="row">
                                <div class="col-md-5">
                                    <label class="form-label"><i class="fas fa-calendar-day"></i> Start Date</label>
                                    <input type="date" class="form-control" name="start_date" 
                                           max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label"><i class="fas fa-calendar-day"></i> End Date</label>
                                    <input type="date" class="form-control" name="end_date" 
                                           max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-info w-100">
                                        <i class="fas fa-file-alt"></i> Generate
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Features Info -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Report Features</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-print fa-3x text-primary mb-2"></i>
                                <h6>Print Ready</h6>
                                <p class="small text-muted">Optimized layout for printing to PDF</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-chart-line fa-3x text-success mb-2"></i>
                                <h6>Visual Charts</h6>
                                <p class="small text-muted">Interactive graphs and statistics</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-brain fa-3x text-warning mb-2"></i>
                                <h6>AI Insights</h6>
                                <p class="small text-muted">Intelligent pattern analysis</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-download fa-3x text-info mb-2"></i>
                                <h6>Easy Download</h6>
                                <p class="small text-muted">Save as PDF using browser print</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-lightbulb"></i> 
                            <strong>Tip:</strong> To save as PDF, click "Generate" button, then use your browser's 
                            Print function (Ctrl+P or Cmd+P) and select "Save as PDF" as the destination.
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