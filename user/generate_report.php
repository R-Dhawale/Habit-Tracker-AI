<?php
require_once '../config/db.php';
require_once '../ai/ai_insights.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_email = $_SESSION['email'];

// Determine report type and date range
$report_type = isset($_GET['type']) ? $_GET['type'] : 'weekly';

if ($report_type === 'weekly') {
    $start_date = date('Y-m-d', strtotime('-7 days'));
    $end_date = date('Y-m-d');
    $report_title = 'Weekly Habit Report';
    $date_range = 'Last 7 Days';
} elseif ($report_type === 'monthly') {
    $start_date = date('Y-m-d', strtotime('-30 days'));
    $end_date = date('Y-m-d');
    $report_title = 'Monthly Habit Report';
    $date_range = 'Last 30 Days';
} elseif ($report_type === 'custom') {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $report_title = 'Custom Habit Report';
    $date_range = date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date));
}

// Calculate days
$days_diff = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

// Get total habits
$habits_query = "SELECT COUNT(*) as total FROM habits WHERE user_id = ?";
$stmt = $conn->prepare($habits_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_habits = $stmt->get_result()->fetch_assoc()['total'];

// Get completed logs in date range
$completed_query = "SELECT COUNT(*) as total FROM habit_logs hl 
                    JOIN habits h ON hl.habit_id = h.habit_id 
                    WHERE h.user_id = ? AND hl.log_date BETWEEN ? AND ? AND hl.status = 'DONE'";
$stmt = $conn->prepare($completed_query);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$completed_total = $stmt->get_result()->fetch_assoc()['total'];

// Calculate expected completions
$expected_total = $total_habits * $days_diff;
$completion_percentage = $expected_total > 0 ? round(($completed_total / $expected_total) * 100, 2) : 0;

// Get habit-wise breakdown
$habit_breakdown_query = "SELECT h.title, c.category_name,
                          COUNT(hl.log_id) as completions,
                          ROUND((COUNT(hl.log_id) / ?) * 100, 2) as completion_rate
                          FROM habits h
                          LEFT JOIN categories c ON h.category_id = c.category_id
                          LEFT JOIN habit_logs hl ON h.habit_id = hl.habit_id 
                              AND hl.log_date BETWEEN ? AND ? AND hl.status = 'DONE'
                          WHERE h.user_id = ?
                          GROUP BY h.habit_id
                          ORDER BY completions DESC";
$stmt = $conn->prepare($habit_breakdown_query);
$stmt->bind_param("issi", $days_diff, $start_date, $end_date, $user_id);
$stmt->execute();
$habit_breakdown = $stmt->get_result();

// Get daily completion data for chart
$daily_data = [];
$current_date = $start_date;
while (strtotime($current_date) <= strtotime($end_date)) {
    $day_query = "SELECT COUNT(*) as count FROM habit_logs hl 
                  JOIN habits h ON hl.habit_id = h.habit_id 
                  WHERE h.user_id = ? AND hl.log_date = ? AND hl.status = 'DONE'";
    $stmt = $conn->prepare($day_query);
    $stmt->bind_param("is", $user_id, $current_date);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    
    $daily_data[] = [
        'date' => $current_date,
        'day' => date('D', strtotime($current_date)),
        'count' => $count
    ];
    
    $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
}

// Get AI insights
$ai = new HabitAI($conn, $user_id);
$streak = $ai->getCurrentStreak();
$suggestions = $ai->generateSuggestions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $report_title; ?> - <?php echo htmlspecialchars($user_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .card { page-break-inside: avoid; }
        }
        
        body {
            background: white;
            color: #333;
        }
        
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stat-box h2 {
            color: #667eea;
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
        }
        
        .footer-note {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Print Button -->
        <div class="text-end mb-3 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
            <a href="reports.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-chart-bar"></i> <?php echo $report_title; ?></h1>
                    <p class="mb-0 fs-5"><?php echo $date_range; ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <p class="mb-1"><strong>User:</strong> <?php echo htmlspecialchars($user_name); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                    <p class="mb-0"><strong>Generated:</strong> <?php echo date('M d, Y h:i A'); ?></p>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <h3 class="mb-4"><i class="fas fa-chart-pie"></i> Summary Statistics</h3>
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-bullseye fa-2x text-primary mb-2"></i>
                    <h2><?php echo $total_habits; ?></h2>
                    <p class="text-muted mb-0">Active Habits</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h2><?php echo $completed_total; ?></h2>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                    <h2><?php echo $completion_percentage; ?>%</h2>
                    <p class="text-muted mb-0">Completion Rate</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-fire fa-2x text-warning mb-2"></i>
                    <h2><?php echo $streak; ?></h2>
                    <p class="text-muted mb-0">Current Streak</p>
                </div>
            </div>
        </div>

        <!-- Daily Progress Chart -->
        <h3 class="mb-4"><i class="fas fa-chart-line"></i> Daily Progress</h3>
        <div class="card mb-5">
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Habit-wise Breakdown -->
        <h3 class="mb-4"><i class="fas fa-list-ul"></i> Habit-wise Performance</h3>
        <div class="card mb-5">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Habit Name</th>
                            <th>Category</th>
                            <th>Completions</th>
                            <th>Completion Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $index = 1;
                        while ($habit = $habit_breakdown->fetch_assoc()): 
                            $rate = $habit['completion_rate'];
                            $status_class = $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger');
                            $status_text = $rate >= 70 ? 'Excellent' : ($rate >= 40 ? 'Good' : 'Needs Work');
                        ?>
                            <tr>
                                <td><?php echo $index++; ?></td>
                                <td><strong><?php echo htmlspecialchars($habit['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($habit['category_name']); ?></td>
                                <td><?php echo $habit['completions']; ?> / <?php echo $days_diff; ?></td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-<?php echo $status_class; ?>" 
                                             style="width: <?php echo $rate; ?>%">
                                            <?php echo $rate; ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AI Insights & Recommendations -->
        <h3 class="mb-4"><i class="fas fa-brain"></i> AI-Powered Insights</h3>
        <div class="card mb-5">
            <div class="card-body">
                <?php foreach (array_slice($suggestions, 0, 3) as $suggestion): ?>
                    <div class="alert alert-<?php echo $suggestion['color']; ?>">
                        <h5>
                            <i class="fas <?php echo $suggestion['icon']; ?>"></i>
                            <?php echo $suggestion['title']; ?>
                        </h5>
                        <p class="mb-0"><?php echo $suggestion['message']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Performance Analysis -->
        <h3 class="mb-4"><i class="fas fa-chart-bar"></i> Performance Analysis</h3>
        <div class="card mb-5">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Strengths</h5>
                        <ul>
                            <?php if ($completion_percentage >= 70): ?>
                                <li>Excellent overall completion rate (<?php echo $completion_percentage; ?>%)</li>
                            <?php endif; ?>
                            <?php if ($streak >= 7): ?>
                                <li>Strong consistency with <?php echo $streak; ?>-day streak</li>
                            <?php endif; ?>
                            <li>Total of <?php echo $completed_total; ?> successful habit completions</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Areas for Improvement</h5>
                        <ul>
                            <?php if ($completion_percentage < 70): ?>
                                <li>Focus on improving completion rate (currently <?php echo $completion_percentage; ?>%)</li>
                            <?php endif; ?>
                            <?php if ($streak < 7): ?>
                                <li>Work on building a consistent daily streak</li>
                            <?php endif; ?>
                            <li>Review struggling habits and adjust as needed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            <p><strong>HabitTracker AI - Personalized Habit Tracking with AI Insights</strong></p>
            <p class="small">This report was automatically generated on <?php echo date('F d, Y'); ?> at <?php echo date('h:i A'); ?></p>
            <p class="small">© 2024 HabitTracker AI. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daily Progress Chart
        const ctx = document.getElementById('dailyChart').getContext('2d');
        const dailyData = <?php echo json_encode($daily_data); ?>;
        
        const labels = dailyData.map(item => item.day + ' ' + item.date.substring(5));
        const data = dailyData.map(item => item.count);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Habits Completed',
                    data: data,
                    backgroundColor: 'rgba(102, 126, 234, 0.6)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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

        // Auto-print functionality
        <?php if (isset($_GET['auto_print'])): ?>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
        <?php endif; ?>
    </script>
</body>
</html>