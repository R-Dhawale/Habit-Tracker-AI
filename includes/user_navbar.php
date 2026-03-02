<?php
// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
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
                    <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'habits.php' || $current_page == 'add_habit.php' || $current_page == 'edit_habit.php') ? 'active' : ''; ?>" href="habits.php">
                        <i class="fas fa-list"></i> My Habits
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'insights.php') ? 'active' : ''; ?>" href="insights.php">
                        <i class="fas fa-brain"></i> AI Insights
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'social.php') ? 'active' : ''; ?>" href="social.php">
                        <i class="fas fa-users"></i> Social
                    </a>
                </li>
                
                <!-- Admin Quick Access Button (if user is admin) -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-warning text-white ms-2" href="../admin/admin_dashboard.php" title="Switch to Admin Panel">
                        <i class="fas fa-shield-alt"></i> Admin Panel
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                       data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                            <span class="badge bg-danger">Admin</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </h6>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-edit"></i> My Profile
                            </a>
                        </li>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-warning" href="../admin/admin_dashboard.php">
                                <i class="fas fa-shield-alt"></i> Admin Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-warning" href="../admin/manage_users.php">
                                <i class="fas fa-users-cog"></i> Manage Users
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-warning" href="../admin/manage_categories.php">
                                <i class="fas fa-tags"></i> Manage Categories
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>