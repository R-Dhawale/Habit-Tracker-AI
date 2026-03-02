<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get total habits count
$total_habits_query = "SELECT COUNT(*) as total FROM habits WHERE user_id = ?";
$stmt = $conn->prepare($total_habits_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_habits = $stmt->get_result()->fetch_assoc()['total'];

// Get today's completed habits
$today = date('Y-m-d');
$completed_today_query = "SELECT COUNT(DISTINCT hl.habit_id) as completed 
                          FROM habit_logs hl 
                          JOIN habits h ON hl.habit_id = h.habit_id 
                          WHERE h.user_id = ? AND hl.log_date = ? AND hl.status = 'DONE'";
$stmt = $conn->prepare($completed_today_query);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$completed_today = $stmt->get_result()->fetch_assoc()['completed'];

// Calculate current streak (consecutive days with at least one habit completed)
$streak = 0;
$check_date = date('Y-m-d');
while (true) {
    $streak_query = "SELECT COUNT(DISTINCT hl.habit_id) as count 
                     FROM habit_logs hl 
                     JOIN habits h ON hl.habit_id = h.habit_id 
                     WHERE h.user_id = ? AND hl.log_date = ? AND hl.status = 'DONE'";
    $stmt = $conn->prepare($streak_query);
    $stmt->bind_param("is", $user_id, $check_date);
    $stmt->execute();
    $day_count = $stmt->get_result()->fetch_assoc()['count'];
    
    if ($day_count > 0) {
        $streak++;
        $check_date = date('Y-m-d', strtotime($check_date . ' -1 day'));
    } else {
        break;
    }
    
    // Safety limit
    if ($streak > 365) break;
}

// Calculate completion percentage (last 7 days)
$seven_days_ago = date('Y-m-d', strtotime('-7 days'));
$total_expected_query = "SELECT COUNT(*) * 7 as expected FROM habits WHERE user_id = ?";
$stmt = $conn->prepare($total_expected_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_expected = $stmt->get_result()->fetch_assoc()['expected'];

$completed_last_week_query = "SELECT COUNT(*) as completed 
                               FROM habit_logs hl 
                               JOIN habits h ON hl.habit_id = h.habit_id 
                               WHERE h.user_id = ? AND hl.log_date >= ? AND hl.status = 'DONE'";
$stmt = $conn->prepare($completed_last_week_query);
$stmt->bind_param("is", $user_id, $seven_days_ago);
$stmt->execute();
$completed_last_week = $stmt->get_result()->fetch_assoc()['completed'];

$completion_percentage = $total_expected > 0 ? round(($completed_last_week / $total_expected) * 100) : 0;

// Get today's habits
$today_habits_query = "SELECT h.habit_id, h.title, c.category_name, h.preferred_time,
                       (SELECT COUNT(*) FROM habit_logs WHERE habit_id = h.habit_id AND log_date = ? AND status = 'DONE') as is_done
                       FROM habits h 
                       LEFT JOIN categories c ON h.category_id = c.category_id 
                       WHERE h.user_id = ? AND h.frequency = 'DAILY'
                       ORDER BY h.preferred_time ASC";
$stmt = $conn->prepare($today_habits_query);
$stmt->bind_param("si", $today, $user_id);
$stmt->execute();
$today_habits = $stmt->get_result();

// Get weekly progress data for chart (last 7 days)
$weekly_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime($date));
    
    $day_query = "SELECT COUNT(*) as count 
                  FROM habit_logs hl 
                  JOIN habits h ON hl.habit_id = h.habit_id 
                  WHERE h.user_id = ? AND hl.log_date = ? AND hl.status = 'DONE'";
    $stmt = $conn->prepare($day_query);
    $stmt->bind_param("is", $user_id, $date);
    $stmt->execute();
    $day_count = $stmt->get_result()->fetch_assoc()['count'];
    
    $weekly_data[] = [
        'day' => $day_name,
        'count' => $day_count
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HabitTracker AI</title>
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
                        <a class="nav-link active" href="dashboard.php">
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
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="social.php">
                            <i class="fas fa-users"></i> Social
                        </a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-warning ms-2" href="../admin/admin_dashboard.php">
                            <i class="fas fa-shield-alt"></i> Back to Admin
                        </a>
                    </li>
                    <?php endif; ?>
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
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-0">Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
                <p class="text-muted">Here's your habit tracking overview for today</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
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
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $completed_today; ?>/<?php echo $total_habits; ?></h3>
                            <p>Completed Today</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $streak; ?> Days</h3>
                            <p>Current Streak</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $completion_percentage; ?>%</h3>
                            <p>Weekly Progress</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Today's Habits -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Today's Habits</h5>
                        <a href="add_habit.php" class="btn btn-light btn-sm">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($today_habits->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while ($habit = $today_habits->fetch_assoc()): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php if ($habit['is_done']): ?>
                                                    <i class="fas fa-check-circle text-success"></i>
                                                <?php else: ?>
                                                    <i class="far fa-circle text-muted"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($habit['title']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($habit['category_name']); ?>
                                                <?php if ($habit['preferred_time']): ?>
                                                    | <i class="fas fa-clock"></i> <?php echo htmlspecialchars($habit['preferred_time']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php if (!$habit['is_done']): ?>
                                                <a href="log_habit.php?habit_id=<?php echo $habit['habit_id']; ?>&action=done" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Done
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox display-1 text-muted mb-3"></i>
                                <h5>No Habits Yet</h5>
                                <p class="text-muted">Start building better habits today!</p>
                                <a href="add_habit.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Your First Habit
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Weekly Progress Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Weekly Progress</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyChart"></canvas>
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
                                <a href="add_habit.php" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                    Add Habit
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="habits.php" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-list fa-2x mb-2"></i><br>
                                    View All Habits
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="insights.php" class="btn btn-warning btn-lg w-100">
                                    <i class="fas fa-brain fa-2x mb-2"></i><br>
                                    AI Insights
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="reports.php" class="btn btn-info btn-lg w-100">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                    Generate Report
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Weekly Progress Chart
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyData = <?php echo json_encode($weekly_data); ?>;
        
        const labels = weeklyData.map(item => item.day);
        const data = weeklyData.map(item => item.count);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Habits Completed',
                    data: data,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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

        // Celebration Popup & Confetti Animation
        <?php if (isset($_GET['success']) && $_GET['success'] === 'habit_logged'): ?>
        // Create confetti effect
        function createConfetti() {
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ffa500', '#ff1493'];
            const confettiCount = 100;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * window.innerWidth + 'px';
                confetti.style.top = '-10px';
                confetti.style.opacity = '1';
                confetti.style.zIndex = '9999';
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.pointerEvents = 'none';
                
                document.body.appendChild(confetti);
                
                // Animate confetti falling
                const duration = 2000 + Math.random() * 2000;
                const rotationSpeed = Math.random() * 360;
                const horizontalSpeed = (Math.random() - 0.5) * 200;
                
                confetti.animate([
                    { 
                        transform: 'translateY(0) rotate(0deg) translateX(0)',
                        opacity: 1
                    },
                    { 
                        transform: `translateY(${window.innerHeight + 20}px) rotate(${rotationSpeed}deg) translateX(${horizontalSpeed}px)`,
                        opacity: 0
                    }
                ], {
                    duration: duration,
                    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                });
                
                // Remove confetti after animation
                setTimeout(() => {
                    confetti.remove();
                }, duration);
            }
        }

        // Show celebration modal
        window.addEventListener('DOMContentLoaded', function() {
            // Create celebration modal
            const modalHTML = `
                <div class="modal fade" id="celebrationModal" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="modal-body text-center p-5 text-white">
                                <div class="celebration-icon mb-4" style="animation: bounce 1s infinite;">
                                    <i class="fas fa-trophy fa-5x" style="color: #ffd700;"></i>
                                </div>
                                <h2 class="mb-3 fw-bold">🎉 Awesome! 🎉</h2>
                                <h4 class="mb-3">Habit Completed!</h4>
                                <p class="lead mb-4">You're one step closer to your goals!</p>
                                <div class="mb-4">
                                    <span class="badge bg-light text-primary fs-5 px-4 py-2">
                                        <i class="fas fa-plus"></i> +10 Points
                                    </span>
                                </div>
                                <button type="button" class="btn btn-light btn-lg px-5" data-bs-dismiss="modal">
                                    <i class="fas fa-check"></i> Continue
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                    @keyframes bounce {
                        0%, 100% { transform: translateY(0); }
                        50% { transform: translateY(-20px); }
                    }
                    @keyframes pulse {
                        0%, 100% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                    }
                    .celebration-icon {
                        animation: pulse 1s infinite;
                    }
                </style>
            `;
            
            // Insert modal into body
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const celebrationModal = new bootstrap.Modal(document.getElementById('celebrationModal'));
            celebrationModal.show();
            
            // Trigger confetti
            createConfetti();
            
            // Play success sound (optional - using Web Audio API)
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch(e) {
                console.log('Audio not supported');
            }
            
            // Clean up URL
            setTimeout(() => {
                window.history.replaceState({}, document.title, 'dashboard.php');
            }, 500);
        });
        <?php endif; ?>
    </script>
</body>
</html>