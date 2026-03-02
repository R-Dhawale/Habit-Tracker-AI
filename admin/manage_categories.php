<?php
require_once '../config/db.php';
require_login();
require_admin();

$success = '';
$error = '';

// Handle Add Category
if (isset($_POST['add_category'])) {
    $category_name = clean_input($_POST['category_name']);
    
    if (empty($category_name)) {
        $error = "Category name is required!";
    } else {
        // Check if category already exists
        $check_query = "SELECT category_id FROM categories WHERE category_name = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Category already exists!";
        } else {
            $insert_query = "INSERT INTO categories (category_name) VALUES (?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("s", $category_name);
            
            if ($stmt->execute()) {
                $success = "Category added successfully!";
            } else {
                $error = "Failed to add category!";
            }
        }
    }
}

// Handle Edit Category
if (isset($_POST['edit_category'])) {
    $category_id = (int)$_POST['category_id'];
    $new_category_name = clean_input($_POST['category_name']);
    
    if (empty($new_category_name)) {
        $error = "Category name is required!";
    } else {
        // Check if new name already exists (excluding current category)
        $check_query = "SELECT category_id FROM categories WHERE category_name = ? AND category_id != ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("si", $new_category_name, $category_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Category name already exists!";
        } else {
            $update_query = "UPDATE categories SET category_name = ? WHERE category_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_category_name, $category_id);
            
            if ($stmt->execute()) {
                $success = "Category updated successfully!";
            } else {
                $error = "Failed to update category!";
            }
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete']) && isset($_GET['category_id'])) {
    $delete_category_id = (int)$_GET['category_id'];
    
    // Check if category is being used
    $check_usage_query = "SELECT COUNT(*) as count FROM habits WHERE category_id = ?";
    $stmt = $conn->prepare($check_usage_query);
    $stmt->bind_param("i", $delete_category_id);
    $stmt->execute();
    $usage_count = $stmt->get_result()->fetch_assoc()['count'];
    
    if ($usage_count > 0) {
        $error = "Cannot delete category! It is being used by $usage_count habit(s). Please reassign or delete those habits first.";
    } else {
        $delete_query = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $delete_category_id);
        
        if ($stmt->execute()) {
            $success = "Category deleted successfully!";
        } else {
            $error = "Failed to delete category!";
        }
    }
}

// Get all categories with usage count
$categories_query = "SELECT c.category_id, c.category_name,
                     (SELECT COUNT(*) FROM habits WHERE category_id = c.category_id) as habit_count
                     FROM categories c
                     ORDER BY c.category_name ASC";
$categories_result = $conn->query($categories_query);

// Get total statistics
$total_categories = $categories_result->num_rows;
$total_habits_query = "SELECT COUNT(*) as count FROM habits";
$total_habits = $conn->query($total_habits_query)->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                        <a class="nav-link" href="manage_users.php">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_categories.php">
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
                <h2><i class="fas fa-tags"></i> Category Management</h2>
                <p class="text-muted">Manage habit categories for all users</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus-circle"></i> Add New Category
                </button>
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
            <div class="col-md-6">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $total_categories; ?></h3>
                            <p>Total Categories</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $total_habits; ?></h3>
                            <p>Total Habits Using Categories</p>
                        </div>
                        <div class="display-4">
                            <i class="fas fa-bullseye"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="row">
            <?php if ($categories_result->num_rows > 0): ?>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tag text-primary"></i>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#editModal<?php echo $category['category_id']; ?>">
                                                    <i class="fas fa-edit text-warning"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" 
                                                   href="manage_categories.php?delete=1&category_id=<?php echo $category['category_id']; ?>"
                                                   onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="text-center py-3">
                                    <i class="fas fa-bullseye fa-4x text-muted mb-3"></i>
                                    <h2 class="text-primary"><?php echo $category['habit_count']; ?></h2>
                                    <p class="text-muted mb-0">Habits Using This Category</p>
                                </div>

                                <?php if ($category['habit_count'] > 0): ?>
                                    <div class="alert alert-info mb-0 mt-3">
                                        <small>
                                            <i class="fas fa-info-circle"></i>
                                            This category is actively being used
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0 mt-3">
                                        <small>
                                            <i class="fas fa-exclamation-triangle"></i>
                                            No habits using this category
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $category['category_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit"></i> Edit Category
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="">
                                    <div class="modal-body">
                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="edit_category_name_<?php echo $category['category_id']; ?>" class="form-label">
                                                <i class="fas fa-tag"></i> Category Name
                                            </label>
                                            <input type="text" class="form-control" 
                                                   id="edit_category_name_<?php echo $category['category_id']; ?>"
                                                   name="category_name" 
                                                   value="<?php echo htmlspecialchars($category['category_name']); ?>" 
                                                   required>
                                        </div>

                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            Currently used by <strong><?php echo $category['habit_count']; ?></strong> habit(s)
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="edit_category" class="btn btn-warning">
                                            <i class="fas fa-save"></i> Update Category
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-tags fa-5x text-muted mb-3"></i>
                            <h4>No Categories Yet</h4>
                            <p class="text-muted">Add your first category to get started</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus-circle"></i> Add Category
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i> Add New Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">
                                <i class="fas fa-tag"></i> Category Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="category_name" 
                                   name="category_name" placeholder="e.g., Health & Fitness" required>
                            <small class="text-muted">Enter a unique category name</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb"></i>
                            <strong>Examples:</strong> Health & Fitness, Productivity, Learning, Mindfulness, Social, Finance, Hobbies
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Category
                        </button>
                    </div>
                </form>
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