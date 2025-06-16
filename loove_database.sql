-- Loove Application Database Schema

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('male','female','non-binary','other','prefer_not_to_say') DEFAULT NULL,
  `sexual_orientation` enum('straight','gay','lesbian','bisexual','pansexual','asexual','other','prefer_not_to_say') DEFAULT NULL,
  `profile_picture_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `preferences` text DEFAULT NULL, -- e.g., JSON or comma-separated values for partner preferences
  `relation_type` varchar(100) DEFAULT NULL, -- e.g., "Friendship", "Serious Relationship"
  `interests` text DEFAULT NULL, -- e.g., comma-separated values
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `last_location_update` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1, -- For account activation/deactivation by admin
  `is_verified` tinyint(1) NOT NULL DEFAULT 0, -- For email or identity verification
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `likes`
--
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `liker_user_id` int(11) NOT NULL, -- User who performed the like
  `liked_user_id` int(11) NOT NULL, -- User who was liked
  `liked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`liker_user_id`,`liked_user_id`),
  KEY `liker_user_id` (`liker_user_id`),
  KEY `liked_user_id` (`liked_user_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`liker_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`liked_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `matches`
--
DROP TABLE IF EXISTS `matches`;
CREATE TABLE `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `matched_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_match` (`user1_id`,`user2_id`), -- Ensure user1_id < user2_id in application logic to avoid duplicates like (1,2) and (2,1)
  KEY `user1_id` (`user1_id`),
  KEY `user2_id` (`user2_id`),
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `messages`
--
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) DEFAULT NULL, -- Optional, if messages are strictly tied to matches
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `match_id` (`match_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL -- Or CASCADE if messages should be deleted with match
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `subscription_plans`
--
DROP TABLE IF EXISTS `subscription_plans`;
CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL, -- e.g., "Premium Monthly", "Premium Yearly"
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL, -- e.g., 30 for monthly, 365 for yearly
  `features` text DEFAULT NULL, -- JSON or comma-separated list of features
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `user_subscriptions`
--
DROP TABLE IF EXISTS `user_subscriptions`;
CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NOT NULL,
  `status` enum('active','expired','cancelled','pending_payment') NOT NULL DEFAULT 'active',
  `payment_id` varchar(255) DEFAULT NULL, -- From payment gateway
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `reports`
--
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_user_id` int(11) NOT NULL,
  `reported_user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_notes` text DEFAULT NULL,
  `resolved_by_admin_id` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporter_user_id` (`reporter_user_id`),
  KEY `reported_user_id` (`reported_user_id`),
  KEY `resolved_by_admin_id` (`resolved_by_admin_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`resolved_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `notifications`
--
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL, -- The user who receives the notification
  `type` enum('new_match','new_message','profile_view','reminder','system') NOT NULL,
  `related_entity_id` int(11) DEFAULT NULL, -- e.g., match_id, message_id, user_id (who viewed profile)
  `content` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `profile_views` (Premium Feature)
--
DROP TABLE IF EXISTS `profile_views`;
CREATE TABLE `profile_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `viewer_user_id` int(11) NOT NULL,
  `viewed_user_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `viewer_user_id` (`viewer_user_id`),
  KEY `viewed_user_id` (`viewed_user_id`),
  CONSTRAINT `profile_views_ibfk_1` FOREIGN KEY (`viewer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `profile_views_ibfk_2` FOREIGN KEY (`viewed_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Insert a default admin user (change password in a real application)
-- Password for 'admin123' is: $2y$10$Y... (generated by password_hash('admin123', PASSWORD_DEFAULT))
-- You should generate your own hash for security.
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `birth_date`, `gender`, `sexual_orientation`, `role`, `is_verified`) VALUES
('admin@loove.com', '$2y$10$Y.tP8M5XjZUJ2X0Z6s0kUu/vaN0O8X3g7J2Q.k6Z5s9X.y5G.Z0mK', 'Admin', 'User', '1990-01-01', 'other', 'other', 'admin', 1);

-- Insert sample subscription plans
INSERT INTO `subscription_plans` (`name`, `price`, `duration_days`, `features`, `is_active`) VALUES
('Premium Monthly', 9.99, 30, '["See profile viewers","Advanced search filters","Unlimited messages"]', 1),
('Premium Yearly', 99.99, 365, '["See profile viewers","Advanced search filters","Unlimited messages","Profile boost"]', 1);


SET foreign_key_checks = 1;
