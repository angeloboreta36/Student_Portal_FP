
CREATE DATABASE IF NOT EXISTS `student_portal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `student_portal`;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `enrollments`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id_number` VARCHAR(30) UNIQUE NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `middle_name` VARCHAR(50) NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'faculty', 'student') NOT NULL DEFAULT 'student',
  `course` ENUM('BSIT', 'BEE', 'BSE', 'BSBA', 'BSA', 'BSCRIM', 'BSCE') NULL,
  `year_level` ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') NULL,
  `section` VARCHAR(20) NULL,
  `contact_number` VARCHAR(25) NULL,
  `profile_photo` VARCHAR(255) NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(20) UNIQUE NOT NULL,
  `course_title` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `units` INT NOT NULL DEFAULT 3,
  `instructor` VARCHAR(100) NOT NULL,
  `schedule` VARCHAR(100) NOT NULL,
  `room` VARCHAR(50) NOT NULL,
  `capacity` INT NOT NULL DEFAULT 40,
  `department` ENUM('BSIT', 'BEE', 'BSE', 'BSBA', 'BSA', 'BSCRIM', 'BSCE', 'General Education') DEFAULT 'General Education',
  `semester` VARCHAR(20) NOT NULL DEFAULT '1st Semester',
  `academic_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `enrollments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  `enrollment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Enrolled', 'Dropped', 'Completed') DEFAULT 'Enrolled',
  `prelim` DECIMAL(5,2) NULL,
  `midterm` DECIMAL(5,2) NULL,
  `semifinal` DECIMAL(5,2) NULL,
  `final` DECIMAL(5,2) NULL,
  `final_average` DECIMAL(5,2) NULL,
  `remarks` ENUM('Passed', 'Failed', 'Incomplete', 'In Progress') DEFAULT 'In Progress',
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_student_course` (`student_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `author_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `category` ENUM('Academic', 'Event', 'Urgent', 'General') DEFAULT 'General',
  `is_pinned` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO `users` (`id`, `student_id_number`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `role`, `course`, `year_level`, `section`, `contact_number`, `status`) VALUES
(1, 'ADM-2401', 'Angelo', 'L.', 'Boreta', 'angelo.boreta@portal.edu.ph', '$2y$12$SxF79VGOkdfyfdmNiwcrxujrbnQr3a6NhgUSEgac2kJPvdcnMHHge', 'admin', NULL, NULL, NULL, '09170001122', 'Active'),
(2, '2400001', 'Roxanne', 'D.', 'Boadilla', 'roxanne.boadilla@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234561', 'Active'),
(3, '2400002', 'Junuel', 'R.', 'Boado', 'junuel.boado@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234562', 'Active'),
(4, '2400003', 'Regiel Ram', 'D.', 'Boado', 'regiel.boado@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234563', 'Active'),
(5, '2400004', 'Preciouslyn', 'C.', 'Bucao', 'preciouslyn.bucao@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234564', 'Active'),
(6, '2400005', 'Justin', 'M.', 'Cabudoc', 'justin.cabudoc@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234565', 'Active'),
(7, '2400006', 'Richmonde', 'E.', 'Cabutotan', 'richmonde.cabutotan@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234566', 'Active'),
(8, '2400007', 'Vincent', 'M.', 'Caguya', 'vincent.caguya@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234567', 'Active'),
(9, '2400008', 'Justine Kaye', 'S.', 'Cases', 'justine.cases@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234568', 'Active'),
(10, '2400009', 'James Robin', 'J.', 'Danday', 'james.danday@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234569', 'Active'),
(11, '2400010', 'Albert John', 'V.', 'Lacebal', 'albert.lacebal@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234570', 'Active'),
(12, '2400011', 'Kaiser Jan', 'L.', 'Laron', 'kaiser.laron@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234571', 'Active'),
(13, '2400012', 'Yestin Angelo', 'E.', 'Laureta', 'yestin.laureta@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234572', 'Active'),
(14, '2400013', 'Kimberlyn', 'P.', 'Medina', 'kimberlyn.medina@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234573', 'Active'),
(15, '2400014', 'Lyka', 'G.', 'Molina', 'lyka.molina@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234574', 'Active'),
(16, '2400015', 'Kathlyn Kaye', 'M.', 'Nalupa', 'kathlyn.nalupa@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234575', 'Active'),
(17, '2400016', 'Lander Kim', 'L.', 'Naraja', 'lander.naraja@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234576', 'Active'),
(18, '2400017', 'Rey', 'M.', 'Onan', 'rey.onan@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234577', 'Active'),
(19, '2400018', 'Joenelle', 'D.', 'Orencia', 'joenelle.orencia@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234578', 'Active'),
(20, '2400019', 'Janniel', 'L.', 'Paglinawan', 'janniel.paglinawan@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234579', 'Active'),
(21, '2400020', 'Amie Jane', 'O.', 'Patacsil', 'amie.patacsil@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234580', 'Active'),
(22, '2400021', 'Dave Emmanuel', 'C.', 'Rabe', 'dave.rabe@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234581', 'Active'),
(23, '2400022', 'Frixie', 'V.', 'Resgonio', 'frixie.resgonio@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234582', 'Active'),
(24, '2400023', 'Annalie', 'O.', 'Rivera', 'annalie.rivera@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234583', 'Active'),
(25, '2400024', 'Daryl', 'C.', 'Rivera', 'daryl.rivera@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234584', 'Active'),
(26, '2400025', 'Rod Aeron', 'P.', 'Selga', 'rod.selga@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234585', 'Active'),
(27, '2400026', 'Stela Mae', 'R.', 'Soria', 'stela.soria@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234586', 'Active'),
(28, '2400027', 'Hercules', 'Rivera', 'Ngittit', 'hercules.ngittit@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234587', 'Active'),
(29, '2400028', 'Bernadeth', 'T.', 'Bernardo', 'bernadeth.bernardo@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-B', '09171234588', 'Active'),
(30, '2400029', 'Shantyl Kate', 'A.', 'Refugia', 'shantyl.refugia@student.edu.ph', '$2y$12$zo9okB5.x6tKKeRkrpwOyO.JqN9wJcsK7.rnqyJE.IKH5ap0aLObW', 'student', 'BSIT', '2nd Year', '2-A', '09171234589', 'Active'),
(31, 'FAC-2401', 'Lexter', '', 'Garcia', 'lexter.garcia@portal.edu.ph', '$2y$12$OnBXIskG/SoKV/gm6f0BY.yRhaYBEDxYhHZgfxMNJKN1ey6M5rWV6', 'faculty', NULL, NULL, NULL, '09180003344', 'Active');

INSERT INTO `courses` (`id`, `course_code`, `course_title`, `description`, `units`, `instructor`, `schedule`, `room`, `capacity`, `department`, `semester`, `academic_year`) VALUES
(1, 'IT201', 'Web Application Development', 'Full-stack web application development using PHP, MySQL, JavaScript, HTML5, and modern CSS frameworks.', 3, 'Instr. Lexter Garcia', 'MWF 9:00 AM - 10:30 AM', 'ComLab 1', 40, 'BSIT', '1st Semester', '2026-2027'),
(2, 'IT202', 'Database Management Systems', 'Relational database design, normalization, SQL queries, transactions, and performance tuning.', 3, 'Instr. Lexter Garcia', 'TTh 1:00 PM - 2:30 PM', 'ComLab 2', 40, 'BSIT', '1st Semester', '2026-2027'),
(3, 'IT203', 'Object-Oriented Programming', 'Advanced OOP principles in Java and C++, design patterns, and clean code architecture.', 3, 'Instr. Lexter Garcia', 'MWF 1:00 PM - 2:30 PM', 'ComLab 3', 35, 'BSIT', '1st Semester', '2026-2027'),
(4, 'ENG101', 'Purposive Communication', 'Communication principles, research presentations, and professional technical writing.', 3, 'Dr. Carmela Ramos', 'TTh 9:00 AM - 10:30 AM', 'Room 201', 45, 'General Education', '1st Semester', '2026-2027'),
(5, 'MATH101', 'Calculus for Engineers & Tech', 'Functions, limits, differentiation, integrals and engineering applications.', 4, 'Engr. David Castro', 'MWF 7:30 AM - 9:00 AM', 'Room 304', 40, 'BSCE', '1st Semester', '2026-2027'),
(6, 'BA101', 'Principles of Marketing', 'Marketing theories, market segmentation, digital marketing channels, and strategic planning.', 3, 'Prof. Teresa Morales', 'TTh 10:30 AM - 12:00 PM', 'Room 105', 40, 'BSBA', '1st Semester', '2026-2027'),
(7, 'CRIM101', 'Introduction to Criminology', 'Fundamental theories of crime causation, criminal law overview, and juvenile justice systems.', 3, 'Atty. Victor Villanueva', 'MWF 10:30 AM - 12:00 PM', 'Room 402', 45, 'BSCRIM', '1st Semester', '2026-2027');

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `status`, `prelim`, `midterm`, `semifinal`, `final`, `final_average`, `remarks`) VALUES
(1, 2, 1, 'Enrolled', 89.00, 91.50, 93.00, 92.50, 91.50, 'Passed'),
(2, 2, 2, 'Enrolled', 88.00, 90.00, 89.50, 91.00, 89.63, 'Passed'),
(3, 2, 3, 'Enrolled', 85.00, 87.50, 86.00, 88.50, 86.75, 'Passed'),
(4, 2, 4, 'Enrolled', 94.00, 95.00, 93.50, 94.50, 94.25, 'Passed'),

(5, 3, 1, 'Enrolled', 84.50, 86.00, 88.00, 87.50, 86.50, 'Passed'),
(6, 3, 2, 'Enrolled', 82.00, 84.00, 85.50, 86.00, 84.38, 'Passed'),
(7, 3, 4, 'Enrolled', 88.00, 89.00, 90.50, 91.00, 89.63, 'Passed'),

(8, 4, 1, 'Enrolled', 90.00, 92.00, NULL, NULL, NULL, 'In Progress'),
(9, 4, 2, 'Enrolled', 85.00, 87.50, 89.00, NULL, NULL, 'In Progress'),
(10, 4, 3, 'Enrolled', 80.00, 82.00, NULL, NULL, NULL, 'In Progress'),

(11, 5, 1, 'Enrolled', 94.00, 96.00, 95.50, 97.00, 95.63, 'Passed'),
(12, 5, 2, 'Enrolled', 92.00, 94.50, 93.00, 95.00, 93.63, 'Passed'),
(13, 5, 4, 'Enrolled', 96.00, 98.00, 97.00, 99.00, 97.50, 'Passed'),

(14, 6, 1, 'Enrolled', 86.00, 88.50, 90.00, 89.00, 88.38, 'Passed'),
(15, 6, 2, 'Enrolled', 83.00, 85.00, 87.00, 86.50, 85.38, 'Passed'),
(16, 6, 3, 'Enrolled', 78.50, 80.00, 82.00, 81.50, 80.50, 'Passed'),

(17, 7, 1, 'Enrolled', 88.00, 89.50, NULL, NULL, NULL, 'In Progress'),
(18, 7, 2, 'Enrolled', 84.00, 86.00, 87.50, NULL, NULL, 'In Progress'),

(19, 8, 1, 'Enrolled', 72.00, 70.00, 73.50, 71.00, 71.63, 'Failed'),
(20, 8, 2, 'Enrolled', 80.00, 82.00, 81.50, 83.00, 81.63, 'Passed'),

(21, 9, 1, 'Enrolled', 91.00, 93.00, 92.50, 94.00, 92.63, 'Passed'),
(22, 9, 4, 'Enrolled', 90.00, 92.00, 91.50, 93.00, 91.63, 'Passed'),

(23, 10, 1, 'Enrolled', 85.00, 87.00, 88.50, 89.00, 87.38, 'Passed'),
(24, 10, 3, 'Enrolled', 82.00, 84.50, 86.00, 85.50, 84.50, 'Passed'),

(25, 11, 1, 'Enrolled', 87.00, 89.00, NULL, NULL, NULL, 'In Progress'),
(26, 11, 2, 'Enrolled', 85.00, 86.50, NULL, NULL, NULL, 'In Progress'),

(27, 12, 1, 'Enrolled', 89.00, 90.50, 91.00, 92.00, 90.63, 'Passed'),
(28, 12, 2, 'Enrolled', 86.00, 88.00, 87.50, 89.00, 87.63, 'Passed'),

(29, 13, 1, 'Enrolled', 90.00, 92.00, 93.50, 94.00, 92.38, 'Passed'),
(30, 13, 3, 'Enrolled', 88.00, 89.50, 90.00, 91.50, 89.75, 'Passed'),

(31, 14, 1, 'Enrolled', 93.00, 95.00, 94.50, 96.00, 94.63, 'Passed'),
(32, 14, 4, 'Enrolled', 95.00, 96.50, 97.00, 98.00, 96.63, 'Passed');

INSERT INTO `announcements` (`id`, `author_id`, `title`, `content`, `category`, `is_pinned`, `created_at`) VALUES
(1, 1, 'Official Schedule of Term Examinations: AY 2026-2027', 'Please be advised that Midterm and Semi-Final Examinations are scheduled for the 3rd week of the month. Ensure all clearance requirements and subject enlistments are settled with your respective college departments.', 'Academic', 1, '2026-10-01 08:00:00'),
(2, 1, 'Subject Enlistment and Adding/Dropping Period Open', 'The online subject enlistment module is officially open for all enrolled students. Students may add or drop eligible courses until the deadline. Check course capacities and prerequisites before finalizing.', 'Academic', 1, '2026-10-05 09:30:00'),
(3, 1, 'System Maintenance & Server Upgrade Notice', 'The campus student portal will undergo regular data backup and security optimization this upcoming Saturday from 10:00 PM to 2:00 AM. Please save all pending submissions prior to the window.', 'Urgent', 0, '2026-10-08 14:15:00'),
(4, 1, 'Annual University Cultural Week & Tech Exhibition', 'Calling all BSIT and Engineering students! The Annual Tech & Innovation Fest begins next month. Contact your department student council to join hackathons, exhibits, and workshops.', 'Event', 0, '2026-10-10 11:00:00');

INSERT INTO `activity_logs` (`user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'LOGIN', 'Admin Angelo Boreta signed in to manage university records.', '127.0.0.1', '2026-10-11 08:39:00'),
(1, 'ANNOUNCEMENT_POST', 'Published official exam schedule bulletin.', '127.0.0.1', '2026-10-11 09:15:00'),
(1, 'GRADE_UPDATE', 'Verified and encoded 4-term grades for IT201 BSIT Section 2-A.', '127.0.0.1', '2026-10-11 10:20:00'),
(1, 'SYSTEM_INIT', 'Database schema initialized and student roster loaded successfully.', '127.0.0.1', '2026-10-11 08:00:00');
