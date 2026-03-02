<?php
require_once '../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];

if (isset($_GET['habit_id']) && isset($_GET['action'])) {
    $habit_id = (int)$_GET['habit_id'];
    $action = $_GET['action'];
    $today = date('Y-m-d');
    
    // Verify habit belongs to user
    $verify_query = "SELECT habit_id FROM habits WHERE habit_id = ? AND user_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $habit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: dashboard.php?error=invalid_habit");
        exit();
    }
    
    if ($action === 'done') {
        // Check if already logged today
        $check_query = "SELECT log_id FROM habit_logs WHERE habit_id = ? AND log_date = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("is", $habit_id, $today);
        $stmt->execute();
        $existing = $stmt->get_result();
        
        if ($existing->num_rows === 0) {
            // Insert new log
            $insert_query = "INSERT INTO habit_logs (habit_id, log_date, status) VALUES (?, ?, 'DONE')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("is", $habit_id, $today);
            
            if ($stmt->execute()) {
                header("Location: dashboard.php?success=habit_logged&habit_id=" . $habit_id);
            } else {
                header("Location: dashboard.php?error=log_failed");
            }
        } else {
            header("Location: dashboard.php?error=already_logged");
        }
    } elseif ($action === 'skip') {
        // Log as skipped
        $check_query = "SELECT log_id FROM habit_logs WHERE habit_id = ? AND log_date = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("is", $habit_id, $today);
        $stmt->execute();
        $existing = $stmt->get_result();
        
        if ($existing->num_rows === 0) {
            $insert_query = "INSERT INTO habit_logs (habit_id, log_date, status) VALUES (?, ?, 'SKIPPED')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("is", $habit_id, $today);
            $stmt->execute();
        }
        header("Location: dashboard.php?success=habit_skipped");
    }
} else {
    header("Location: dashboard.php");
}

exit();
?>