-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 06:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


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
(1, '10-11', 'BM', '2026-05-19 15:49:09'),
(2, '10-11', 'Math', '2026-05-19 15:49:09'),
(3, '10-11', 'English', '2026-05-19 15:49:09'),
(4, '10-11', 'Science', '2026-05-19 15:49:09'),
(5, '10-11', 'History', '2026-05-19 15:49:09'),
(6, '10,11', 'Account', '2026-05-19 15:49:09'),
(7, '10-11', 'Geography', '2026-05-19 15:49:09');

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
(1, '10-11', 10, 'Monday', 'BM', 'Math', 'English', 'Science', 'Geography', 'BM', 'Account', 0),
(2, '10-11', 10, 'Tuesday', 'Math', 'Science', 'BM', 'History', 'AddMath', 'Science', 'Math', 0),
(3, '10-11', 10, 'Wednesday', 'Science', 'English', 'Account', 'Math', 'Science', 'English', 'History', 0),
(4, '10-11', 10, 'Thursday', 'History', 'Geography', 'Math', 'BM', 'English', 'AddMath', 'Account', 0),
(5, '10-11', 10, 'Friday', 'SPORT', 'SPORT', 'BREAK', 'Geography', 'BM', 'English', 'Account', 1);

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
(252564, 'Aminah@teacher.edu.my', 'Cikgu Aminah', NULL, 'teacher123$', 'teacher'),
(275486, 'Ali_Hassan@student.edu.my', 'Ali Hassan', 10, '989856', 'student');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=275488;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
