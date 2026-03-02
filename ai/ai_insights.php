<?php
// AI Insights Engine - Rule-based AI for Habit Analysis

class HabitAI {
    private $conn;
    private $user_id;
    
    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }
    
    // Calculate overall completion rate
    public function getCompletionRate($days = 30) {
        $start_date = date('Y-m-d', strtotime("-$days days"));
        
        $query = "SELECT 
                    (SELECT COUNT(*) * $days FROM habits WHERE user_id = ?) as expected,
                    (SELECT COUNT(*) FROM habit_logs hl 
                     JOIN habits h ON hl.habit_id = h.habit_id 
                     WHERE h.user_id = ? AND hl.log_date >= ? AND hl.status = 'DONE') as completed";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $this->user_id, $this->user_id, $start_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $expected = $result['expected'];
        $completed = $result['completed'];
        
        return $expected > 0 ? round(($completed / $expected) * 100, 2) : 0;
    }
    
    // Detect current streak
    public function getCurrentStreak() {
        $streak = 0;
        $check_date = date('Y-m-d');
        
        while ($streak < 365) {
            $query = "SELECT COUNT(DISTINCT hl.habit_id) as count 
                     FROM habit_logs hl 
                     JOIN habits h ON hl.habit_id = h.habit_id 
                     WHERE h.user_id = ? AND hl.log_date = ? AND hl.status = 'DONE'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("is", $this->user_id, $check_date);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            
            if ($count > 0) {
                $streak++;
                $check_date = date('Y-m-d', strtotime($check_date . ' -1 day'));
            } else {
                break;
            }
        }
        
        return $streak;
    }
    
    // Find weak days of the week
    public function getWeakDays() {
        $query = "SELECT DAYNAME(hl.log_date) as day_name, COUNT(*) as count
                 FROM habit_logs hl
                 JOIN habits h ON hl.habit_id = h.habit_id
                 WHERE h.user_id = ? AND hl.log_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 AND hl.status = 'DONE'
                 GROUP BY DAYNAME(hl.log_date)
                 ORDER BY count ASC
                 LIMIT 2";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $weak_days = [];
        while ($row = $result->fetch_assoc()) {
            $weak_days[] = $row['day_name'];
        }
        
        return $weak_days;
    }
    
    // Find best performing time
    public function getBestTime() {
        $query = "SELECT h.preferred_time, COUNT(*) as completion_count
                 FROM habit_logs hl
                 JOIN habits h ON hl.habit_id = h.habit_id
                 WHERE h.user_id = ? AND hl.status = 'DONE' 
                 AND h.preferred_time IS NOT NULL AND h.preferred_time != ''
                 GROUP BY h.preferred_time
                 ORDER BY completion_count DESC
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['preferred_time'];
        }
        
        return null;
    }
    
    // Get most successful habit
    public function getMostSuccessfulHabit() {
        $query = "SELECT h.title, COUNT(*) as completions
                 FROM habit_logs hl
                 JOIN habits h ON hl.habit_id = h.habit_id
                 WHERE h.user_id = ? AND hl.status = 'DONE'
                 GROUP BY h.habit_id
                 ORDER BY completions DESC
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Get struggling habit (least completions)
    public function getStrugglingHabit() {
        $query = "SELECT h.title, 
                 COALESCE(COUNT(hl.log_id), 0) as completions
                 FROM habits h
                 LEFT JOIN habit_logs hl ON h.habit_id = hl.habit_id AND hl.status = 'DONE'
                 WHERE h.user_id = ? AND h.created_at <= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY h.habit_id
                 ORDER BY completions ASC
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Generate AI suggestions based on patterns
    public function generateSuggestions() {
        $suggestions = [];
        $completion_rate = $this->getCompletionRate(30);
        $streak = $this->getCurrentStreak();
        $weak_days = $this->getWeakDays();
        $struggling = $this->getStrugglingHabit();
        
        // Streak-based suggestions
        if ($streak >= 30) {
            $suggestions[] = [
                'icon' => 'fa-trophy',
                'color' => 'success',
                'title' => 'Excellent Consistency!',
                'message' => "You've maintained a $streak-day streak! Consider adding a new challenging habit to keep growing."
            ];
        } elseif ($streak >= 7) {
            $suggestions[] = [
                'icon' => 'fa-fire',
                'color' => 'warning',
                'title' => 'Great Momentum!',
                'message' => "You're on a $streak-day streak. Focus on maintaining this consistency for the next week."
            ];
        } elseif ($streak === 0) {
            $suggestions[] = [
                'icon' => 'fa-rocket',
                'color' => 'info',
                'title' => 'Fresh Start',
                'message' => "Start building your streak today! Complete at least one habit to begin your journey."
            ];
        }
        
        // Completion rate suggestions
        if ($completion_rate < 40) {
            $suggestions[] = [
                'icon' => 'fa-lightbulb',
                'color' => 'danger',
                'title' => 'Low Completion Rate',
                'message' => "Your completion rate is {$completion_rate}%. Consider reducing the number of habits or making them easier to achieve."
            ];
        } elseif ($completion_rate >= 40 && $completion_rate < 70) {
            $suggestions[] = [
                'icon' => 'fa-chart-line',
                'color' => 'warning',
                'title' => 'Room for Improvement',
                'message' => "You're at {$completion_rate}% completion. Try setting reminders or linking habits to existing routines."
            ];
        } elseif ($completion_rate >= 80) {
            $suggestions[] = [
                'icon' => 'fa-star',
                'color' => 'success',
                'title' => 'Outstanding Performance!',
                'message' => "Amazing! You're completing {$completion_rate}% of your habits. You might be ready for more challenging goals."
            ];
        }
        
        // Weak day suggestions
        if (!empty($weak_days)) {
            $day_list = implode(' and ', $weak_days);
            $suggestions[] = [
                'icon' => 'fa-calendar-times',
                'color' => 'info',
                'title' => 'Weak Day Pattern Detected',
                'message' => "You struggle most on $day_list. Plan easier habits or set extra reminders on these days."
            ];
        }
        
        // Struggling habit
        if ($struggling && $struggling['completions'] < 5) {
            $suggestions[] = [
                'icon' => 'fa-exclamation-triangle',
                'color' => 'warning',
                'title' => 'Struggling Habit Alert',
                'message' => "'{$struggling['title']}' has low completions. Consider breaking it into smaller steps or changing the timing."
            ];
        }
        
        // General motivation
        if (empty($suggestions)) {
            $suggestions[] = [
                'icon' => 'fa-smile',
                'color' => 'primary',
                'title' => 'Keep Going!',
                'message' => "You're doing well! Keep tracking your habits consistently to unlock more personalized insights."
            ];
        }
        
        return $suggestions;
    }
    
    // Get completion trend (last 7 days vs previous 7 days)
    public function getCompletionTrend() {
        // Last 7 days
        $query1 = "SELECT COUNT(*) as count
                  FROM habit_logs hl
                  JOIN habits h ON hl.habit_id = h.habit_id
                  WHERE h.user_id = ? AND hl.log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND hl.status = 'DONE'";
        
        $stmt = $this->conn->prepare($query1);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $last_week = $stmt->get_result()->fetch_assoc()['count'];
        
        // Previous 7 days
        $query2 = "SELECT COUNT(*) as count
                  FROM habit_logs hl
                  JOIN habits h ON hl.habit_id = h.habit_id
                  WHERE h.user_id = ? 
                  AND hl.log_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                  AND hl.log_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND hl.status = 'DONE'";
        
        $stmt = $this->conn->prepare($query2);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $prev_week = $stmt->get_result()->fetch_assoc()['count'];
        
        if ($prev_week == 0) {
            return ['trend' => 'neutral', 'percentage' => 0];
        }
        
        $change = (($last_week - $prev_week) / $prev_week) * 100;
        
        if ($change > 10) {
            return ['trend' => 'up', 'percentage' => round($change, 1)];
        } elseif ($change < -10) {
            return ['trend' => 'down', 'percentage' => round(abs($change), 1)];
        } else {
            return ['trend' => 'neutral', 'percentage' => round(abs($change), 1)];
        }
    }
}
?>