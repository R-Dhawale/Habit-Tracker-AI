<?php
require_once '../config/db.php';
require_login();
require_admin();

$success = '';
$error = '';

// Handle user deletion
if (isset($_GET['delete']) && isset($_GET['user_id'])) {
    $delete_user_id = (int)$_GET['user_id'];
    
    // Prevent admin from deleting themselves
    if ($delete_user_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $delete_user_id);
        
        if ($stmt->execute()) {
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user!";
        }
    }
}

// Handle role update
if (isset($_POST['update_role'])) {
    $update_user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'];
    
    // Prevent admin from changing their own role
    if ($update_user_id === $_SESSION['user_id']) {
        $error = "You cannot change your own role!";
    } else {
        $update_role_query = "UPDATE users SET role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_role_query);
        $stmt->bind_param("si", $new_role, $update_user_id);
        
        if ($stmt->execute()) {
            $success = "User role updated successfully!";
        } else {
            $error = "Failed to update user role!";
        }
    }
}

// Get statistics first
$total_users_query = "SELECT COUNT(*) as count FROM users";
$total_users = $conn->query($total_users_query)->fetch_assoc()['count'];

$admin_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'ADMIN'";
$admin_count = $conn->query($admin_count_query)->fetch_assoc()['count'];

$user_count = $total_users - $admin_count;

// Get all users with statistics
$users_query = "SELECT u.user_id, u.name, u.email, u.role, u.created_at,
                (SELECT COUNT(*) FROM habits WHERE user_id = u.user_id) as total_habits,
                (SELECT COUNT(*) FROM habit_logs hl 
                 JOIN habits h ON hl.habit_id = h.habit_id 
                 WHERE h.user_id = u.user_id AND hl.status = 'DONE') as total_completions
                FROM users u
                ORDER BY u.created_at DESC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_users.php">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_categories.php">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../user/dashboard.php">
                            <i class="fas fa-user"></i> User View
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light ms-2" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="fas fa-users"></i> User Management</h2>
                <p class="text-muted">View and manage all registered users</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $total_users; ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $user_count; ?></h3>
                            <p>Regular Users</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $admin_count; ?></h3>
                            <p>Administrators</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> All Users</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="usersTable">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Name</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Role</th>
                                <th class="text-center">Habits</th>
                                <th class="text-center">Completions</th>
                                <th class="text-center">Joined Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $modals = ''; while ($user = $users_result->fetch_assoc()): 
                                $is_current_user = ($user['user_id'] === $_SESSION['user_id']);
                            ?>
                                <tr <?php echo $is_current_user ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center align-middle"><?php echo $user['user_id']; ?></td>
                                    <td class="text-center align-middle">
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        <?php if ($is_current_user): ?>
                                            <span class="badge bg-info">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="text-center align-middle">
                                        <?php if ($user['role'] === 'ADMIN'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-shield-alt"></i> Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user"></i> User
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle"><?php echo $user['total_habits']; ?></td>
                                    <td class="text-center align-middle"><?php echo $user['total_completions']; ?></td>
                                    <td class="text-center align-middle"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal<?php echo $user['user_id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if (!$is_current_user): ?>
                                                <button class="btn btn-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $user['user_id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="manage_users.php?delete=1&user_id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-danger"
                                                   onclick="return confirm('Are you sure you want to delete this user? All their data will be permanently deleted.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

<?php ob_start(); ?>
                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo $user['user_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-user"></i> User Details
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="text-center mb-3">
                                                    <i class="fas fa-user-circle fa-5x text-info"></i>
                                                </div>
                                                <table class="table">
                                                    <tr>
                                                        <th>User ID:</th>
                                                        <td><?php echo $user['user_id']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Name:</th>
                                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email:</th>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Role:</th>
                                                        <td><?php echo $user['role']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Habits:</th>
                                                        <td><?php echo $user['total_habits']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Completions:</th>
                                                        <td><?php echo $user['total_completions']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Joined Date:</th>
                                                        <td><?php echo date('F d, Y h:i A', strtotime($user['created_at'])); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
<?php $modals .= ob_get_clean(); ?>

<?php ob_start(); ?>
                                <!-- Edit Modal -->
                                <?php if (!$is_current_user): ?>
                                <div class="modal fade" id="editModal<?php echo $user['user_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-edit"></i> Edit User Role
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label"><strong>User:</strong></label>
                                                        <p><?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Change Role:</label>
                                                        <select class="form-select" name="role" required>
                                                            <option value="USER" <?php echo ($user['role'] === 'USER') ? 'selected' : ''; ?>>
                                                                User
                                                            </option>
                                                            <option value="ADMIN" <?php echo ($user['role'] === 'ADMIN') ? 'selected' : ''; ?>>
                                                                Admin
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <strong>Warning:</strong> Changing a user to Admin will give them full system access.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_role" class="btn btn-warning">
                                                        <i class="fas fa-save"></i> Update Role
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
<?php $modals .= ob_get_clean(); ?>

                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php echo $modals; ?>
    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function(){
        var table = $('#usersTable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            scrollX: true,
            autoWidth: false,
            columnDefs: [
                { targets: 0, width: '60px' },   // ID
                { targets: 4, width: '80px' },   // Habits
                { targets: 5, width: '90px' },   // Completions
                { targets: 7, orderable: false, width: '140px' } // Actions
            ]
        });

        // Ensure header widths are recalculated and aligned with the body
        table.columns.adjust().draw();

        $(window).on('resize', function() {
            table.columns.adjust();
        });
    });
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>