-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2026 at 11:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `habit_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_history`
--

CREATE TABLE `ai_chat_history` (
  `chat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chat_history`
--

INSERT INTO `ai_chat_history` (`chat_id`, `user_id`, `question`, `answer`, `created_at`) VALUES
(1, 8, 'hi', 'I\'m having trouble connecting right now. Please try again in a moment.', '2026-01-20 04:46:48'),
(2, 8, 'How can I build better habits?', 'I\'m having trouble connecting right now. Please try again in a moment.', '2026-01-20 04:47:22'),
(3, 8, 'How can I build better habits?', 'I\'m having trouble connecting right now. Please try again in a moment.', '2026-01-20 04:49:29'),
(4, 8, 'How can I build better habits?', 'I\'m having trouble connecting right now. Error: API error (HTTP 404): models/gemini-pro is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-01-20 04:53:30'),
(5, 8, 'How can I build better habits?', 'I\'m having trouble connecting right now. Error: API key not configured. Please add your Gemini API key to config/gemini_config.php', '2026-01-20 04:56:21'),
(6, 8, 'How can I build better habits?', 'I\'m having trouble connecting right now. Error: API key not configured. Please set GEMINI_API_KEY environment variable.', '2026-01-20 05:00:25'),
(7, 8, 'How can I build better habits?', 'I\'m having trouble connecting right now. Error: API error (HTTP 404): model or method not found. Call ListModels to see available models. Available models: models/gemini-2.5-flash, models/gemini-2.5-pro, models/gemini-2.0-flash, models/gemini-2.0-flash-001, models/gemini-2.0-flash-lite-001, models/gemini-2.0-flash-lite, models/gemini-2.5-flash-lite, models/embedding-001, models/text-embedding-004', '2026-01-20 05:01:12'),
(8, 8, 'How can I build better habits?', 'I\'m having trouble connecting right now. Error: API key not configured. Please add your Gemini API key to config/gemini_config.php', '2026-01-20 05:02:08');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(6, 'Finance'),
(1, 'Health & Fitness'),
(7, 'Hobbies'),
(3, 'Learning'),
(4, 'Mindfulness'),
(2, 'Productivity'),
(5, 'Social');

-- --------------------------------------------------------

--
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `habit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `frequency` enum('DAILY','WEEKLY') DEFAULT 'DAILY',
  `preferred_time` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `habit_logs`
--

CREATE TABLE `habit_logs` (
  `log_id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `status` enum('DONE','SKIPPED') DEFAULT 'DONE',
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `leaderboard_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `streak_points` int(11) DEFAULT 0,
  `completion_points` int(11) DEFAULT 0,
  `rank_position` int(11) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaderboard`
--

INSERT INTO `leaderboard` (`leaderboard_id`, `user_id`, `total_points`, `streak_points`, `completion_points`, `rank_position`, `last_updated`) VALUES
(1, 2, 20, 0, 20, 3, '2025-12-24 08:56:40'),
(2, 1, 430, 0, 430, 1, '2026-02-04 08:02:11'),
(3, 6, 0, 0, 0, 5, '2026-01-19 08:54:57'),
(4, 4, 140, 0, 140, 2, '2026-01-19 05:38:02'),
(30, 8, 10, 0, 10, 4, '2026-01-19 08:54:57');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `habit_id` int(11) DEFAULT NULL,
  `notification_type` enum('REMINDER','STREAK','ACHIEVEMENT','GENERAL') DEFAULT 'REMINDER',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `habit_id`, `notification_type`, `message`, `is_read`, `sent_at`) VALUES
(1, 1, NULL, 'REMINDER', 'Don\'t forget to log your habits today!', 1, '2025-12-18 09:14:08'),
(2, 1, NULL, 'STREAK', 'Congratulations! You\'ve reached a 7-day streak!', 1, '2025-12-18 09:14:08'),
(3, 1, NULL, 'ACHIEVEMENT', 'You\'ve completed 50 habits. Keep it up!', 1, '2025-12-18 09:14:08'),
(4, 4, NULL, 'REMINDER', 'Don\'t forget to log your habits today!', 1, '2025-12-18 09:14:08');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otp`
--

CREATE TABLE `password_reset_otp` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_otp`
--

INSERT INTO `password_reset_otp` (`reset_id`, `user_id`, `email`, `otp_code`, `expires_at`, `is_used`, `created_at`) VALUES
(3, 2, 'abc@gmail.com', '521044', '2026-01-18 22:18:17', 0, '2026-01-18 16:38:17');

-- --------------------------------------------------------

--
-- Table structure for table `shared_habits`
--

CREATE TABLE `shared_habits` (
  `share_id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `share_code` varchar(50) NOT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `shares_count` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('USER','ADMIN') DEFAULT 'USER',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@habittracker.com', '$2y$10$Jz8hII5uET.3Qx8TTRVla.BAQb0pUWw8WGlrQhSp/BHsA.K/hp.Ce', 'ADMIN', '2025-12-18 07:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_follows`
--

CREATE TABLE `user_follows` (
  `follow_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `followed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `preference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `reminder_time` time DEFAULT '09:00:00',
  `reminder_frequency` enum('DAILY','WEEKLY','NONE') DEFAULT 'DAILY'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`preference_id`, `user_id`, `email_notifications`, `reminder_time`, `reminder_frequency`) VALUES
(1, 2, 1, '09:00:00', 'DAILY'),
(2, 1, 1, '09:00:00', 'DAILY'),
(3, 6, 1, '09:00:00', 'DAILY'),
(4, 4, 1, '09:00:00', 'DAILY'),
(5, 8, 1, '09:00:00', 'DAILY');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_chat_history`
--
ALTER TABLE `ai_chat_history`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`habit_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `habit_logs`
--
ALTER TABLE `habit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD UNIQUE KEY `unique_habit_date` (`habit_id`,`log_date`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`leaderboard_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `habit_id` (`habit_id`);

--
-- Indexes for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp` (`otp_code`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_otp_lookup` (`email`,`otp_code`,`is_used`);

--
-- Indexes for table `shared_habits`
--
ALTER TABLE `shared_habits`
  ADD PRIMARY KEY (`share_id`),
  ADD UNIQUE KEY `share_code` (`share_code`),
  ADD KEY `habit_id` (`habit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`following_id`),
  ADD KEY `following_id` (`following_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_chat_history`
--
ALTER TABLE `ai_chat_history`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `habit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `habit_logs`
--
ALTER TABLE `habit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `leaderboard_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shared_habits`
--
ALTER TABLE `shared_habits`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_follows`
--
ALTER TABLE `user_follows`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_chat_history`
--
ALTER TABLE `ai_chat_history`
  ADD CONSTRAINT `ai_chat_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `habits`
--
ALTER TABLE `habits`
  ADD CONSTRAINT `habits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `habits_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `habit_logs`
--
ALTER TABLE `habit_logs`
  ADD CONSTRAINT `habit_logs_ibfk_1` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`habit_id`) ON DELETE CASCADE;

--
-- Constraints for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`habit_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD CONSTRAINT `password_reset_otp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `shared_habits`
--
ALTER TABLE `shared_habits`
  ADD CONSTRAINT `shared_habits_ibfk_1` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`habit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shared_habits_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD CONSTRAINT `user_follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
