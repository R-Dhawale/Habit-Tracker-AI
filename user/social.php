<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Update leaderboard points for current user
$update_leaderboard = "INSERT INTO leaderboard (user_id, total_points, completion_points, streak_points)
                       SELECT ?, 
                              COALESCE((SELECT COUNT(*) * 10 FROM habit_logs hl 
                                        JOIN habits h ON hl.habit_id = h.habit_id 
                                        WHERE h.user_id = ? AND hl.status = 'DONE'), 0),
                              COALESCE((SELECT COUNT(*) * 10 FROM habit_logs hl 
                                        JOIN habits h ON hl.habit_id = h.habit_id 
                                        WHERE h.user_id = ? AND hl.status = 'DONE'), 0),
                              0
                       ON DUPLICATE KEY UPDATE 
                       total_points = VALUES(total_points),
                       completion_points = VALUES(completion_points),
                       last_updated = CURRENT_TIMESTAMP";
$stmt = $conn->prepare($update_leaderboard);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();

// Update rankings
$conn->query("SET @rank = 0");
$conn->query("UPDATE leaderboard SET rank_position = (@rank := @rank + 1) ORDER BY total_points DESC");

// Get top 10 leaderboard
$leaderboard_query = "SELECT l.*, u.name, u.email 
                      FROM leaderboard l
                      JOIN users u ON l.user_id = u.user_id
                      ORDER BY l.total_points DESC
                      LIMIT 10";
$leaderboard = $conn->query($leaderboard_query);

// Get current user's rank
$my_rank_query = "SELECT * FROM leaderboard WHERE user_id = ?";
$stmt = $conn->prepare($my_rank_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_rank = $stmt->get_result()->fetch_assoc();

// Get shareable habits
$shareable_habits_query = "SELECT h.*, c.category_name,
                          (SELECT COUNT(*) FROM habit_logs WHERE habit_id = h.habit_id AND status = 'DONE') as total_completions,
                          sh.share_code, sh.shares_count, sh.views_count, sh.is_public
                          FROM habits h
                          LEFT JOIN categories c ON h.category_id = c.category_id
                          LEFT JOIN shared_habits sh ON h.habit_id = sh.habit_id
                          WHERE h.user_id = ?
                          ORDER BY h.created_at DESC";
$stmt = $conn->prepare($shareable_habits_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$shareable_habits = $stmt->get_result();

// Handle share habit
if (isset($_POST['share_habit'])) {
    $habit_id = (int)$_POST['habit_id'];
    $share_code = bin2hex(random_bytes(8));
    
    // Check if already shared
    $check_share = "SELECT share_id FROM shared_habits WHERE habit_id = ?";
    $stmt = $conn->prepare($check_share);
    $stmt->bind_param("i", $habit_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        $insert_share = "INSERT INTO shared_habits (habit_id, user_id, share_code, is_public) VALUES (?, ?, ?, TRUE)";
        $stmt = $conn->prepare($insert_share);
        $stmt->bind_param("iis", $habit_id, $user_id, $share_code);
        
        if ($stmt->execute()) {
            $share_success = "Habit shared successfully! Share code: " . $share_code;
        }
    } else {
        $share_error = "Habit is already shared!";
    }
}

// Total users count
$total_users_query = "SELECT COUNT(*) as count FROM users WHERE role = 'USER'";
$total_users = $conn->query($total_users_query)->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Hub - HabitTracker AI</title>
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
                        <a class="nav-link" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="social.php">
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
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-users"></i> Social Hub</h2>
                <p class="text-muted">Connect with others and share your habit journey</p>
            </div>
        </div>

        <?php if (isset($share_success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $share_success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- My Rank Card -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-medal"></i> My Ranking</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if ($my_rank && $my_rank['rank_position'] <= 3): ?>
                                <i class="fas fa-trophy fa-5x text-warning mb-3"></i>
                            <?php else: ?>
                                <i class="fas fa-user-circle fa-5x text-primary mb-3"></i>
                            <?php endif; ?>
                        </div>
                        <h2 class="text-primary">#<?php echo $my_rank ? $my_rank['rank_position'] : 'N/A'; ?></h2>
                        <p class="text-muted">Out of <?php echo $total_users; ?> users</p>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-success"><?php echo $my_rank ? $my_rank['total_points'] : 0; ?></h4>
                                <small class="text-muted">Total Points</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-info"><?php echo $my_rank ? $my_rank['completion_points'] : 0; ?></h4>
                                <small class="text-muted">Completion Points</small>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <small><i class="fas fa-info-circle"></i> Earn 10 points per habit completion!</small>
                        </div>
                    </div>
                </div>

                <!-- Share Habits Card -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-share-alt"></i> Share Your Habits</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($shareable_habits->num_rows > 0): ?>
                            <p class="text-muted small">Click share to get a shareable link for your habit</p>
                            <?php while ($habit = $shareable_habits->fetch_assoc()): ?>
                                <div class="card mb-2">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($habit['title']); ?></h6>
                                                <small class="text-muted"><?php echo $habit['total_completions']; ?> completions</small>
                                            </div>
                                            <div>
                                                <?php if ($habit['share_code']): ?>
                                                    <span class="badge bg-success">Shared</span>
                                                <?php else: ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="habit_id" value="<?php echo $habit['habit_id']; ?>">
                                                        <button type="submit" name="share_habit" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-share"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-2"></i><br>
                                No habits to share yet
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-trophy"></i> Global Leaderboard - Top 10</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-warning">
                                    <tr>
                                        <th width="80">Rank</th>
                                        <th>User</th>
                                        <th>Total Points</th>
                                        <th>Completions</th>
                                        <th>Badge</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($leaderboard->num_rows > 0): ?>
                                        <?php while ($rank = $leaderboard->fetch_assoc()): 
                                            $is_current_user = ($rank['user_id'] === $user_id);
                                            $medal_icons = [1 => 'fa-trophy text-warning', 2 => 'fa-medal text-secondary', 3 => 'fa-medal text-danger'];
                                        ?>
                                            <tr <?php echo $is_current_user ? 'class="table-primary"' : ''; ?>>
                                                <td class="text-center">
                                                    <?php if ($rank['rank_position'] <= 3): ?>
                                                        <i class="fas <?php echo $medal_icons[$rank['rank_position']]; ?> fa-2x"></i>
                                                    <?php else: ?>
                                                        <h5 class="mb-0">#<?php echo $rank['rank_position']; ?></h5>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($rank['name']); ?></strong>
                                                    <?php if ($is_current_user): ?>
                                                        <span class="badge bg-primary">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success fs-6"><?php echo $rank['total_points']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $rank['completion_points'] / 10; ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($rank['total_points'] >= 1000) echo '<span class="badge bg-danger">Legend</span>';
                                                    elseif ($rank['total_points'] >= 500) echo '<span class="badge bg-warning">Master</span>';
                                                    elseif ($rank['total_points'] >= 200) echo '<span class="badge bg-info">Expert</span>';
                                                    elseif ($rank['total_points'] >= 50) echo '<span class="badge bg-primary">Rookie</span>';
                                                    else echo '<span class="badge bg-secondary">Beginner</span>';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-users-slash fa-3x text-muted mb-2"></i>
                                                <p class="text-muted">No users on leaderboard yet</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Badges Guide -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-award"></i> Badge System</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <span class="badge bg-secondary fs-6 mb-1">Beginner</span>
                                <p class="small text-muted mb-0">0-49 pts</p>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-primary fs-6 mb-1">Rookie</span>
                                <p class="small text-muted mb-0">50-199 pts</p>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-info fs-6 mb-1">Expert</span>
                                <p class="small text-muted mb-0">200-499 pts</p>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-warning fs-6 mb-1">Master</span>
                                <p class="small text-muted mb-0">500-999 pts</p>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-danger fs-6 mb-1">Legend</span>
                                <p class="small text-muted mb-0">1000+ pts</p>
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