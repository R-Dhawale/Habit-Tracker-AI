<?php
require_once '../config/db.php';
require_once '../config/ai_config.php';
require_once '../ai/ai_insights.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Initialize AI Engines
$localAI = new HabitAI($conn, $user_id);
$realAI = new RealAI();

// Get local AI insights (rule-based)
$completion_rate = $localAI->getCompletionRate(30);
$current_streak = $localAI->getCurrentStreak();
$weak_days = $localAI->getWeakDays();
$best_time = $localAI->getBestTime();
$most_successful = $localAI->getMostSuccessfulHabit();
$struggling = $localAI->getStrugglingHabit();
$local_suggestions = $localAI->generateSuggestions();
$trend = $localAI->getCompletionTrend();

// Prepare data for Real AI Analysis
$total_habits_query = "SELECT COUNT(*) as total FROM habits WHERE user_id = ?";
$stmt = $conn->prepare($total_habits_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_habits = $stmt->get_result()->fetch_assoc()['total'];

$total_completions_query = "SELECT COUNT(*) as total FROM habit_logs hl 
                            JOIN habits h ON hl.habit_id = h.habit_id 
                            WHERE h.user_id = ? AND hl.status = 'DONE'";
$stmt = $conn->prepare($total_completions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_completions = $stmt->get_result()->fetch_assoc()['total'];

// Get days active
$days_active_query = "SELECT DATEDIFF(CURDATE(), MIN(created_at)) as days FROM habits WHERE user_id = ?";
$stmt = $conn->prepare($days_active_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$days_active = max(1, $stmt->get_result()->fetch_assoc()['days']);

// Get struggling habits
$struggling_habits = [];
$struggling_query = "SELECT h.title, COUNT(hl.log_id) as completions 
                    FROM habits h 
                    LEFT JOIN habit_logs hl ON h.habit_id = hl.habit_id AND hl.status = 'DONE'
                    WHERE h.user_id = ?
                    GROUP BY h.habit_id
                    ORDER BY completions ASC
                    LIMIT 3";
$stmt = $conn->prepare($struggling_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $struggling_habits[] = $row;
}

// Get top habits
$top_habits = [];
$top_query = "SELECT h.title, COUNT(hl.log_id) as completions 
             FROM habits h 
             LEFT JOIN habit_logs hl ON h.habit_id = hl.habit_id AND hl.status = 'DONE'
             WHERE h.user_id = ?
             GROUP BY h.habit_id
             ORDER BY completions DESC
             LIMIT 3";
$stmt = $conn->prepare($top_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $top_habits[] = $row;
}

// Build data for Real AI
$userData = [
    'total_habits' => $total_habits,
    'completion_rate' => $completion_rate,
    'streak' => $current_streak,
    'total_completions' => $total_completions,
    'avg_daily' => $total_habits > 0 ? round($total_completions / max(1, $days_active), 2) : 0,
    'weak_days' => $weak_days,
    'best_time' => $best_time,
    'struggling_habits' => $struggling_habits,
    'top_habits' => $top_habits,
    'days_active' => $days_active
];

// Get Real AI Insights (only if API key is configured)
$ai_insights = null;
$ai_error = null;
if (GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE' && $total_habits > 0) {
    try {
        $ai_insights = $realAI->generateInsights($userData);
    } catch (Exception $e) {
        $ai_error = "AI analysis temporarily unavailable. Showing local insights.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Insights - HabitTracker AI</title>
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
                        <a class="nav-link active" href="insights.php">
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-brain text-primary"></i> AI-Powered Insights</h2>
                <p class="text-muted">Personalized analysis of your habit patterns and recommendations</p>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <h3><?php echo $completion_rate; ?>%</h3>
                        <p class="mb-0">Completion Rate (30 days)</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-fire fa-2x mb-2"></i>
                        <h3><?php echo $current_streak; ?> Days</h3>
                        <p class="mb-0">Current Streak</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-2x mb-2"></i>
                        <h3><?php echo $most_successful ? substr($most_successful['title'], 0, 15) . '...' : 'N/A'; ?></h3>
                        <p class="mb-0">Top Habit</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-trend-<?php echo $trend['trend'] === 'up' ? 'up' : ($trend['trend'] === 'down' ? 'down' : 'flat'); ?> fa-2x mb-2"></i>
                        <h3><?php echo $trend['percentage']; ?>%</h3>
                        <p class="mb-0">Weekly Trend</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Suggestions -->
        <div class="row mb-4">
            <div class="col-12">
                <?php if ($ai_insights): ?>
                    <!-- Real AI Analysis -->
                    <div class="card border-primary shadow-lg">
                        <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="mb-0">
                                <i class="fas fa-robot"></i> AI-Powered Deep Analysis
                                <span class="badge bg-success float-end">Powered by Google Gemini</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Performance Analysis -->
                            <div class="mb-4">
                                <h6 class="text-primary"><i class="fas fa-chart-line"></i> Performance Analysis</h6>
                                <p class="lead"><?php echo htmlspecialchars($ai_insights['analysis']); ?></p>
                            </div>

                            <div class="row">
                                <!-- Strengths -->
                                <div class="col-md-6 mb-4">
                                    <div class="card bg-success bg-opacity-10 border-success h-100">
                                        <div class="card-body">
                                            <h6 class="text-success"><i class="fas fa-trophy"></i> Your Strengths</h6>
                                            <ul class="list-unstyled">
                                                <?php foreach ($ai_insights['strengths'] as $strength): ?>
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                        <?php echo htmlspecialchars($strength); ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Areas for Improvement -->
                                <div class="col-md-6 mb-4">
                                    <div class="card bg-warning bg-opacity-10 border-warning h-100">
                                        <div class="card-body">
                                            <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Areas to Improve</h6>
                                            <ul class="list-unstyled">
                                                <?php foreach ($ai_insights['improvements'] as $improvement): ?>
                                                    <li class="mb-2">
                                                        <i class="fas fa-arrow-up text-warning"></i>
                                                        <?php echo htmlspecialchars($improvement); ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actionable Recommendations -->
                            <div class="card bg-primary bg-opacity-10 border-primary mb-4">
                                <div class="card-body">
                                    <h6 class="text-primary"><i class="fas fa-lightbulb"></i> Actionable Recommendations</h6>
                                    <ol class="mb-0">
                                        <?php foreach ($ai_insights['recommendations'] as $recommendation): ?>
                                            <li class="mb-2"><?php echo htmlspecialchars($recommendation); ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            </div>

                            <!-- Motivation & Milestone -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0">
                                        <h6 class="alert-heading"><i class="fas fa-heart"></i> Motivational Message</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($ai_insights['motivation']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-success mb-0">
                                        <h6 class="alert-heading"><i class="fas fa-flag-checkered"></i> Next Milestone</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($ai_insights['milestone']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($ai_error): ?>
                    <!-- AI Error Message -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $ai_error; ?>
                    </div>
                <?php elseif (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE'): ?>
                    <!-- API Key Not Configured -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> AI Analysis Available!</h5>
                        <p>To enable advanced AI-powered insights:</p>
                        <ol>
                            <li>Get a free API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
                            <li>Open <code>config/ai_config.php</code></li>
                            <li>Replace <code>YOUR_GEMINI_API_KEY_HERE</code> with your actual API key</li>
                            <li>Refresh this page to see AI-powered deep analysis!</li>
                        </ol>
                    </div>
                <?php endif; ?>

                <!-- Local AI Suggestions (Always show as backup) -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Quick Insights</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($local_suggestions as $suggestion): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="alert alert-<?php echo $suggestion['color']; ?> d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="fas <?php echo $suggestion['icon']; ?> fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="alert-heading mb-1"><?php echo $suggestion['title']; ?></h6>
                                            <p class="mb-0"><?php echo $suggestion['message']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pattern Analysis -->
        <div class="row">
            <!-- Performance Visualization -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Performance Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart"></canvas>
                        <div class="mt-3 text-center">
                            <p class="mb-1"><strong>Completion Rate:</strong> <?php echo $completion_rate; ?>%</p>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?php echo $completion_rate >= 70 ? 'success' : ($completion_rate >= 40 ? 'warning' : 'danger'); ?>" 
                                     style="width: <?php echo $completion_rate; ?>%">
                                    <?php echo $completion_rate; ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Streak Visualization -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-fire"></i> Streak Progress</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="streakChart"></canvas>
                        <div class="mt-3 text-center">
                            <h2 class="mb-0"><?php echo $current_streak; ?> Days</h2>
                            <p class="text-muted">Current Streak</p>
                            <?php if ($current_streak >= 30): ?>
                                <span class="badge bg-danger fs-5">🔥 On Fire!</span>
                            <?php elseif ($current_streak >= 7): ?>
                                <span class="badge bg-warning fs-5">⚡ Great Momentum!</span>
                            <?php else: ?>
                                <span class="badge bg-info fs-5">💪 Keep Going!</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weak Days -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-times"></i> Weak Days</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($weak_days)): ?>
                            <div class="text-center py-3">
                                <?php foreach ($weak_days as $day): ?>
                                    <span class="badge bg-danger fs-5 mb-2"><?php echo $day; ?></span><br>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-muted small mt-3">
                                <i class="fas fa-info-circle"></i> 
                                These are your least productive days. Consider setting extra reminders or planning easier habits.
                            </p>
                        <?php else: ?>
                            <p class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-3x mb-3"></i><br>
                                No weak days detected! You're consistent throughout the week.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Best Time -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Best Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-3">
                            <?php if ($best_time): ?>
                                <i class="fas fa-sun fa-4x text-warning mb-3"></i>
                                <h4 class="text-success"><?php echo htmlspecialchars($best_time); ?></h4>
                                <p class="text-muted small mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    You're most consistent during this time. Schedule important habits here!
                                </p>
                            <?php else: ?>
                                <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Not enough data yet. Set preferred times for your habits!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Struggling Habit -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Needs Attention</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($struggling): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-flag fa-3x text-warning mb-3"></i>
                                <h6><?php echo htmlspecialchars($struggling['title']); ?></h6>
                                <p class="text-muted">
                                    Only <?php echo $struggling['completions']; ?> completions
                                </p>
                                <a href="edit_habit.php" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Adjust Habit
                                </a>
                            </div>
                            <p class="text-muted small mt-3">
                                <i class="fas fa-info-circle"></i>
                                Try making it easier, changing the time, or breaking it into smaller steps.
                            </p>
                        <?php else: ?>
                            <p class="text-center text-muted py-4">
                                <i class="fas fa-smile fa-3x mb-3"></i><br>
                                All habits are progressing well!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- How AI Works -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-robot"></i> How Our AI Works</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-database fa-3x text-primary mb-2"></i>
                                <h6>Data Collection</h6>
                                <p class="small text-muted">Analyzes your habit logs and completion patterns</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-chart-line fa-3x text-success mb-2"></i>
                                <h6>Pattern Detection</h6>
                                <p class="small text-muted">Identifies trends, streaks, and weak points</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-lightbulb fa-3x text-warning mb-2"></i>
                                <h6>Smart Recommendations</h6>
                                <p class="small text-muted">Generates personalized suggestions</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-sync fa-3x text-info mb-2"></i>
                                <h6>Continuous Learning</h6>
                                <p class="small text-muted">Updates insights as you log more habits</p>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Performance Pie Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        const completionRate = <?php echo $completion_rate; ?>;
        const remaining = 100 - completionRate;
        
        new Chart(performanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Missed'],
                datasets: [{
                    data: [completionRate, remaining],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Last 30 Days Performance',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });

        // Streak Bar Chart
        const streakCtx = document.getElementById('streakChart').getContext('2d');
        const currentStreak = <?php echo $current_streak; ?>;
        const streakData = [];
        const streakLabels = [];
        
        // Generate streak visualization data
        for (let i = Math.max(0, currentStreak - 7); i <= currentStreak; i++) {
            streakLabels.push('Day ' + i);
            streakData.push(i);
        }
        
        new Chart(streakCtx, {
            type: 'line',
            data: {
                labels: streakLabels,
                datasets: [{
                    label: 'Streak Progress',
                    data: streakData,
                    backgroundColor: 'rgba(251, 191, 36, 0.2)',
                    borderColor: 'rgb(251, 191, 36)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgb(251, 191, 36)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Streak Buildup',
                        font: {
                            size: 16
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>