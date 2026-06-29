-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2026 at 05:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



--
-- Indexes for table `payments`
--

ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rad_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks` decimal(5,2) NOT NULL DEFAULT 0.00,
  `max_marks` int(11) NOT NULL DEFAULT 100,
  `grade` varchar(5) NOT NULL DEFAULT '',
  `term` varchar(20) NOT NULL DEFAULT 'Term 1',
  `year` int(11) NOT NULL,
  `graded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `marks`, `max_marks`, `grade`, `term`, `year`, `graded_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 87.50, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(2, 1, 2, 92.00, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(3, 1, 3, 78.50, 100, 'B+', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(4, 1, 4, 85.00, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(5, 1, 5, 88.75, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(6, 1, 6, 91.25, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(7, 1, 7, 80.00, 100, 'B+', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'Term 1 Grades Released', 'Your Term 1 grades have been posted. Check your grades page to view your results.', 0, '2026-06-08 14:30:00'),
(2, 1, 'Homework Submission Reminder', 'Remember to submit your Math assignment by Friday. Late submissions will not be accepted.', 0, '2026-06-07 10:15:00'),
(3, 1, 'School Event Announcement', 'Annual Sports Day is scheduled for June 15, 2026. All students are required to participate.', 1, '2026-06-05 09:00:00'),
(4, 1, 'Payment Due', 'Your school fees for June are now due. Please make payment before June 30, 2026.', 1, '2026-06-01 08:30:00'),
(5, 1, 'Class Cancelled', 'Biology practical class on June 10 has been rescheduled to June 12 due to lab maintenance.', 0, '2026-06-06 15:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `year_group` varchar(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `year_group`, `name`, `created_at`) VALUES
(1, '10-11', 'BM', '2026-06-02 12:12:04'),
(2, '10-11', 'Math', '2026-06-02 12:12:04'),
(3, '10-11', 'English', '2026-06-02 12:12:04'),
(4, '10-11', 'Science', '2026-06-02 12:12:04'),
(5, '10-11', 'History', '2026-06-02 12:12:04'),
(6, '10,11', 'Account', '2026-06-02 12:12:04'),
(7, '10-11', 'Geography', '2026-06-02 12:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `year_group` varchar(10) NOT NULL,
  `year` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `p1` varchar(40) DEFAULT '',
  `p2` varchar(40) DEFAULT '',
  `p3` varchar(40) DEFAULT '',
  `p4` varchar(40) DEFAULT '',
  `p5` varchar(40) DEFAULT '',
  `p6` varchar(40) DEFAULT '',
  `p7` varchar(40) DEFAULT '',
  `sport_day` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `year_group`, `year`, `day`, `p1`, `p2`, `p3`, `p4`, `p5`, `p6`, `p7`, `sport_day`) VALUES
(1, '10-11', 11, 'Monday', 'BM', 'ENG', 'HIS', 'MAN', 'ACC', 'ECO', 'SCI', 0),
(2, '10-11', 11, 'Tuesday', 'MATH', 'BM', 'MAN', 'SCI', 'HIS', 'ACC', 'ECO', 0),
(3, '10-11', 11, 'Wednesday', 'ENG', 'BM', 'MATH', 'MAN', 'SCI', 'HIS', 'ACC', 0),
(4, '10-11', 11, 'Thursday', 'ECO', 'ENG', 'BM', 'MATH', 'MAN', 'SCI', 'ACC', 0),
(5, '10-11', 11, 'Friday', 'SPORT', 'SPORT', 'ENG', 'BM', 'ACC', 'ECO', 'SCI', 1),
(6, '1-6', 6, 'Monday', 'SPORT', 'Math', 'BREAK', 'BM', 'ENG', '', '', 1),
(7, '1-6', 6, 'Tuesday', 'Eng', 'Math', 'BREAK', 'SCI', 'HIS', '', '', 0),
(8, '1-6', 6, 'Wednesday', 'ENG', 'BM', 'BREAK', 'HIS', 'SCI', '', '', 0),
(9, '1-6', 6, 'Thursday', 'HIS', 'ENG', 'BREAK', 'MATH', 'SCI', '', '', 0),
(10, '1-6', 6, 'Friday', 'MATH', 'HIS', 'BREAK', 'BM', 'SCI', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `useremail` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT 'Student',
  `year` int(11) DEFAULT 10,
  `PASSWORD` varchar(25) DEFAULT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `useremail`, `name`, `year`, `PASSWORD`, `role`) VALUES
(1, 'user@student.maplewood.edu.my', 'Isaac', 11, '454567', 'student'),
(2, 'admin@teacher.maplewood.edu.my', 'Tommy', 0, '878756', 'teacher');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(8) NOT NULL DEFAULT 'RM',
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Invoice Sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `amount`, `currency`, `description`, `status`, `created_at`, `paid_at`) VALUES
(1, 1, 450.00, 'RM', 'School fees - June 2026', 'Invoice Sent', '2026-06-01 08:00:00', NULL),
(2, 1, 450.00, 'RM', 'School fees - May 2026', 'Paid', '2026-05-01 08:00:00', '2026-05-02 12:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`term`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
