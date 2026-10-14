-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2026 at 07:59 PM
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
-- Database: `spi`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_offerings`
--

CREATE TABLE `class_offerings` (
  `offering_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('Active','Completed','Withdrawn') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `section_id`, `enrollment_date`, `status`) VALUES
(1, 1, 1, '2026-10-15', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_subjects`
--

CREATE TABLE `enrollment_subjects` (
  `enrollment_subject_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `offering_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grading_weights`
--

CREATE TABLE `grading_weights` (
  `offering_id` int(11) NOT NULL,
  `written_work_pct` decimal(5,2) NOT NULL,
  `performance_task_pct` decimal(5,2) NOT NULL,
  `quarterly_assessment_pct` decimal(5,2) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_pins`
--

CREATE TABLE `password_reset_pins` (
  `reset_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_pins`
--

INSERT INTO `password_reset_pins` (`reset_id`, `user_id`, `pin_hash`, `expires_at`, `attempts`, `verified_at`, `used_at`, `created_at`) VALUES
(1, 1, '$2y$10$QB.Ug5t0.1XdClO9ZRGEHeYDeCR0DdWReIGRLqu1tuwR2cABx7tR2', '2026-10-14 23:37:49', 0, NULL, '2026-10-14 23:37:57', '2026-10-14 15:27:49'),
(2, 1, '$2y$10$bpLSQ/UkA1eatxlX0000duNkj87Bs8eD0lWno6An8yyyBR8vylZR6', '2026-10-14 23:47:57', 0, NULL, '2026-10-15 00:22:22', '2026-10-14 15:37:57'),
(3, 2, '$2y$10$MrS5bQVHf8oFB5X2FSkgs.sYe0Is0pwNqyP/LspWItZI1Yywzy/Ru', '2026-10-14 23:52:46', 0, NULL, '2026-10-14 23:45:35', '2026-10-14 15:42:46'),
(4, 2, '$2y$10$pm0WUQYCKPjibDo9uALME./SwVPr8CFOHKPVFhERqkU6f6Z9zjYVq', '2026-10-14 23:55:35', 0, '2026-10-14 23:46:30', '2026-10-14 23:47:22', '2026-10-14 15:45:35'),
(5, 1, '$2y$10$.8BKjUil2HQoqvUM8GPu0usKgUwa8NIyCNuInkgaXLlCNzXwIfF7S', '2026-10-15 00:32:22', 0, '2026-10-15 00:22:53', '2026-10-15 00:23:17', '2026-10-14 16:22:22');

-- --------------------------------------------------------

--
-- Table structure for table `quarter_grades`
--

CREATE TABLE `quarter_grades` (
  `quarter_grade_id` int(11) NOT NULL,
  `enrollment_subject_id` int(11) NOT NULL,
  `quarter` enum('Q1','Q2','Q3','Q4') NOT NULL,
  `written_work_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `performance_task_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `quarterly_assessment_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `initial_grade` decimal(5,2) DEFAULT NULL,
  `transmuted_grade` decimal(5,2) DEFAULT NULL,
  `entered_by_teacher_id` int(11) DEFAULT NULL,
  `status` enum('Draft','Finalized') NOT NULL DEFAULT 'Draft',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `school_year_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`school_year_id`, `school_year`, `start_date`, `end_date`, `is_current`) VALUES
(1, '2026-2027', '2026-06-01', '2027-05-31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `grade_level` tinyint(4) NOT NULL,
  `section_name` varchar(100) NOT NULL
) ;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `school_year_id`, `track_id`, `grade_level`, `section_name`) VALUES
(1, 1, 3, 11, 'Quartz');

-- --------------------------------------------------------

--
-- Table structure for table `semester_grades`
--

CREATE TABLE `semester_grades` (
  `semester_grade_id` int(11) NOT NULL,
  `enrollment_subject_id` int(11) NOT NULL,
  `semester` enum('1st','2nd') NOT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `action_taken` varchar(50) DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admission_date` date DEFAULT NULL,
  `placement_grade_level` tinyint(4) DEFAULT NULL,
  `placement_section` varchar(100) DEFAULT NULL,
  `placement_school_year_id` int(11) DEFAULT NULL,
  `placement_completed_at` datetime DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `guardian_contact` varchar(30) DEFAULT NULL
) ;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `admission_date`, `placement_grade_level`, `placement_section`, `placement_school_year_id`, `placement_completed_at`, `phone_number`, `birth_date`, `home_address`, `guardian_name`, `guardian_contact`) VALUES
(1, 1, NULL, 11, 'Quartz', 1, '2026-10-15 01:51:51', NULL, NULL, NULL, NULL, NULL),
(2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(150) NOT NULL,
  `subject_type` enum('Core','Applied','Specialized') NOT NULL,
  `semester` enum('1st','2nd') NOT NULL,
  `grade_level` tinyint(4) NOT NULL,
  `track_id` int(11) DEFAULT NULL
) ;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `subject_type`, `semester`, `grade_level`, `track_id`) VALUES
(1, 'CORE-PD', 'Personal Development', 'Core', '1st', 12, 1),
(2, 'CORE-PS', 'Physical Science', 'Core', '1st', 12, 1),
(3, 'CORE-PEH3', 'Physical Education and Health 3', 'Core', '1st', 12, 1),
(4, 'CORE-CPAR', 'Contemporary Philippine Arts from the Regions', 'Core', '1st', 12, 1),
(5, 'CORE-SERV', 'Servanthood', 'Core', '1st', 12, 1),
(6, 'CORE-PR2', 'Practical Research 2', 'Core', '1st', 12, 1),
(7, 'CORE-FPL', 'Filipino sa Piling Larang', 'Core', '1st', 12, 1),
(8, 'HUMSS-CL', 'Creative Language', 'Specialized', '1st', 12, 2),
(9, 'HUMSS-PG', 'Philippine Government', 'Specialized', '1st', 12, 2),
(10, 'ICT-CCS3', 'Contact Center Service 3', 'Specialized', '1st', 12, 3),
(11, 'ICT-TD3', 'Technical Drafting 3', 'Specialized', '1st', 12, 3),
(12, 'ABM-FABM', 'Fundamentals of Accountancy, Business, and Management', 'Specialized', '1st', 12, 4),
(13, 'ABM-BF', 'Business Finance', 'Specialized', '1st', 12, 4),
(14, 'STEM-GC', 'General Chemistry', 'Specialized', '1st', 12, 5),
(15, 'STEM-GP', 'General Physics', 'Specialized', '1st', 12, 5),
(16, 'HE-COOK', 'Cookery', 'Specialized', '1st', 12, 6),
(17, 'HE-FBS', 'Food and Beverage Services', 'Specialized', '1st', 12, 6);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_num` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `user_id`, `employee_num`) VALUES
(1, 4, 'CODEX-TEST-TEACHER-20261014');

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

CREATE TABLE `tracks` (
  `track_id` int(11) NOT NULL,
  `track_name` varchar(100) NOT NULL,
  `strand_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tracks`
--

INSERT INTO `tracks` (`track_id`, `track_name`, `strand_name`) VALUES
(4, 'Academic', 'ABM'),
(2, 'Academic', 'HUMSS'),
(5, 'Academic', 'STEM'),
(1, 'Core', 'Core Subjects'),
(6, 'TVL', 'HE'),
(3, 'TVL', 'ICT');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `lrn`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, '107872100503', 'fredierick', 'Labiano', 'ugalino', 'ufredierick@gmail.com', '$2y$10$OjdqCUsle431qTX5SklDmuCUR9CNaRGGWIMPXbpuU1/NvkvNG7iBW', 'student', '2026-10-13 09:41:50'),
(2, '107872100500', 'Bernadette', 'C', 'Cabantog', 'cabantogbernadette06@gmail.com', '$2y$10$8ygxAX9hU7NRbzM025Y9P.YOjsetIylce7Y5sNfqk7lB.EC3QB5HG', 'student', '2026-10-14 15:41:07'),
(3, '99999999999999999901', 'Codex Test Student', NULL, 'Account', 'stperegrineinstitute1+codexstudent20261014@gmail.com', '$2y$10$6w9CzfCVg5tY9yNQS1pgruHVJRekVBXs06j/vX7ctntNfYd3ljlLm', 'student', '2026-10-14 15:50:14'),
(4, NULL, 'Codex Test Teacher', NULL, 'Account', 'codex.teacher.test.20261014@example.com', '$2y$10$6w9CzfCVg5tY9yNQS1pgruHVJRekVBXs06j/vX7ctntNfYd3ljlLm', 'teacher', '2026-10-14 15:50:14'),
(5, NULL, 'Codex Test Admin', NULL, 'Account', 'codex.admin.test.20261014@example.com', '$2y$10$6w9CzfCVg5tY9yNQS1pgruHVJRekVBXs06j/vX7ctntNfYd3ljlLm', 'admin', '2026-10-14 15:50:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `class_offerings`
--
ALTER TABLE `class_offerings`
  ADD PRIMARY KEY (`offering_id`),
  ADD UNIQUE KEY `section_id` (`section_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`section_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `enrollment_subjects`
--
ALTER TABLE `enrollment_subjects`
  ADD PRIMARY KEY (`enrollment_subject_id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`,`offering_id`),
  ADD KEY `offering_id` (`offering_id`);

--
-- Indexes for table `grading_weights`
--
ALTER TABLE `grading_weights`
  ADD PRIMARY KEY (`offering_id`);

--
-- Indexes for table `password_reset_pins`
--
ALTER TABLE `password_reset_pins`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `idx_reset_pins_user_expiry` (`user_id`,`expires_at`);

--
-- Indexes for table `quarter_grades`
--
ALTER TABLE `quarter_grades`
  ADD PRIMARY KEY (`quarter_grade_id`),
  ADD UNIQUE KEY `enrollment_subject_id` (`enrollment_subject_id`,`quarter`),
  ADD KEY `entered_by_teacher_id` (`entered_by_teacher_id`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`school_year_id`),
  ADD UNIQUE KEY `school_year` (`school_year`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `school_year_id` (`school_year_id`,`grade_level`,`section_name`,`track_id`),
  ADD KEY `track_id` (`track_id`);

--
-- Indexes for table `semester_grades`
--
ALTER TABLE `semester_grades`
  ADD PRIMARY KEY (`semester_grade_id`),
  ADD UNIQUE KEY `enrollment_subject_id` (`enrollment_subject_id`,`semester`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `placement_school_year_id` (`placement_school_year_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `track_id` (`track_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_num` (`employee_num`);

--
-- Indexes for table `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`track_id`),
  ADD UNIQUE KEY `track_name` (`track_name`,`strand_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `lrn` (`lrn`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_offerings`
--
ALTER TABLE `class_offerings`
  MODIFY `offering_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollment_subjects`
--
ALTER TABLE `enrollment_subjects`
  MODIFY `enrollment_subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_pins`
--
ALTER TABLE `password_reset_pins`
  MODIFY `reset_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quarter_grades`
--
ALTER TABLE `quarter_grades`
  MODIFY `quarter_grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `school_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `semester_grades`
--
ALTER TABLE `semester_grades`
  MODIFY `semester_grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tracks`
--
ALTER TABLE `tracks`
  MODIFY `track_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `class_offerings`
--
ALTER TABLE `class_offerings`
  ADD CONSTRAINT `class_offerings_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `class_offerings_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `class_offerings_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

--
-- Constraints for table `enrollment_subjects`
--
ALTER TABLE `enrollment_subjects`
  ADD CONSTRAINT `enrollment_subjects_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_subjects_ibfk_2` FOREIGN KEY (`offering_id`) REFERENCES `class_offerings` (`offering_id`);

--
-- Constraints for table `grading_weights`
--
ALTER TABLE `grading_weights`
  ADD CONSTRAINT `grading_weights_ibfk_1` FOREIGN KEY (`offering_id`) REFERENCES `class_offerings` (`offering_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_pins`
--
ALTER TABLE `password_reset_pins`
  ADD CONSTRAINT `fk_reset_pins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `quarter_grades`
--
ALTER TABLE `quarter_grades`
  ADD CONSTRAINT `quarter_grades_ibfk_1` FOREIGN KEY (`enrollment_subject_id`) REFERENCES `enrollment_subjects` (`enrollment_subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quarter_grades_ibfk_2` FOREIGN KEY (`entered_by_teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`school_year_id`),
  ADD CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`track_id`);

--
-- Constraints for table `semester_grades`
--
ALTER TABLE `semester_grades`
  ADD CONSTRAINT `semester_grades_ibfk_1` FOREIGN KEY (`enrollment_subject_id`) REFERENCES `enrollment_subjects` (`enrollment_subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_placement_school_year_fk` FOREIGN KEY (`placement_school_year_id`) REFERENCES `school_years` (`school_year_id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`track_id`) ON DELETE SET NULL;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
