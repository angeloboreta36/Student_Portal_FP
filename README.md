# Philippine College of Northwestern Luzon (PCNL)

## Student Portal & Grade Management System

> **Institution:** Philippine College of Northwestern Luzon (PCNL) &bull; _“Qualitas et Excellentiam”_ &bull; MMVII  
> **Location:** San Antonio, Agoo, La Union, Philippines  
> **Course:** Web Application Development &bull; Final Project
> **System Developer:** Angelo L. Boreta  
> **Tech Stack:** PHP 8.x, MySQL / MariaDB, Tailwind CSS (via CDN), Vanilla JavaScript.

---

## 1. Project Overview & Institutional Branding

This web application is the official **Student Portal & Grade Management System** for the **Philippine College of Northwestern Luzon (PCNL)**. It provides a secure, role-based academic platform for faculty, administrators, and students.

### Institutional Identity & Design Theme:

- **Branding:** Philippine College of Northwestern Luzon (PCNL), featuring the official school seal and emblem.
- **Motto:** _“Qualitas et Excellentiam”_ (Quality and Excellence), established MMVII.
- **Color Palette:** PCNL Royal Blue (`#1d4ed8`), Sky/Electric Cyan (`#0284c7`), and Amber/Gold accents (`#f59e0b`).

---

## 2. Key System Modules & Features

### A. Role-Based Access Control (RBAC) & Authentication

- **Administrative Role:** Full administrative control over student accounts, course offerings, grade submissions, campus bulletins, and audit logs.
- **Faculty Role:** Dedicated faculty portal with course load tracking, student class rosters, and grade encoding for assigned subjects (IT201, IT202, IT203).
- **Student Role:** Students can check term grades, print official grade slips, enlist/drop subjects, and view announcements.
- **Flexible Login:** Authenticate using either **Institutional Email** or **Student / Employee ID Number** (e.g., `2400001`, `FAC-2401`, `ADM-2401`).

### B. 4-Term Percentage Grading System

- Raw percentage grading scale (**0.00% – 100.00%**) broken down into:
    1. **Prelim Grade** (20%)
    2. **Midterm Grade** (20%)
    3. **Semi-Final Grade** (20%)
    4. **Final Term Grade** (40%)
- **Passing Benchmark:** **75.00%** passing grade.
- **Real-time Status Badge:** Dynamic assignment of `Passed`, `Failed`, or `In Progress`.
- **Printable Grade Slip:** Transcript layout formatted for printing with institutional letterhead and signature lines.

### C. Online Subject Enlistment Module

- Real-time slot and section capacity enforcement (e.g., 25 / 40 enrolled).
- Maximum unit load enforcement (24.0 units).
- Asynchronous 1-Click Enlistment & Subject Dropping.
- Protection against dropping subjects once faculty has encoded term grades.

### D. Campus Bulletins & Advisories Bulletin Board

- Categorized announcements: `Academic`, `Event`, `Urgent`, and `General`.
- Ability to pin critical notices to the top of the feed.
- Real-time search and category filtering for students.

### E. Client-Side Interactivity

- **Debounced Live Search:** Fast client-side filtering for courses and students without page refreshes.
- **Dynamic Modal Calculation:** Computes term averages and remarks instantly as faculty inputs scores.
- **Interactive Modals & Toast Alerts:** Native vanilla JavaScript dialogs with smooth transitions.

---

## 3. Test Credentials for Evaluation & Defense

The database is pre-seeded with Angelo L. Boreta and 29 student accounts from the official class list:

| Role               | Username / ID | Email Address                      | Password     | Profile & Status                                                          |
| :----------------- | :------------ | :--------------------------------- | :----------- | :------------------------------------------------------------------------ |
| **System Admin**   | `ADM-2401`    | `angelo.boreta@portal.edu.ph`      | `admin123`   | **Angelo L. Boreta** (Full Administrator Privileges)                      |
| **Faculty Member** | `FAC-2401`    | `lexter.garcia@portal.edu.ph`      | `faculty123` | **Instr. Lexter Garcia** (IT Faculty &bull; Classes: IT201, IT202, IT203) |
| **Student 1**      | `2400001`     | `roxanne.boadilla@student.edu.ph`  | `student123` | **Roxanne D. Boadilla** (BSIT, 2nd Year, Sec 2-A)                         |
| **Student 2**      | `2400002`     | `junuel.boado@student.edu.ph`      | `student123` | **Junuel R. Boado** (BSIT, 2nd Year, Sec 2-A)                             |
| **Student 3**      | `2400003`     | `regiel.boado@student.edu.ph`      | `student123` | **Regiel Ram D. Boado** (BSIT, 2nd Year, Sec 2-A)                         |
| **Student 4**      | `2400004`     | `preciouslyn.bucao@student.edu.ph` | `student123` | **Preciouslyn C. Bucao** (BSIT, 2nd Year, Sec 2-A)                        |
| **Student 5**      | `2400007`     | `vincent.caguya@student.edu.ph`    | `student123` | **Vincent M. Caguya** (BSIT, 2nd Year &bull; Sample Failed Grade < 75%)   |

---

## 4. Database Architecture (`/database/schema.sql`)

The database consists of 5 relational tables:

1. `users`: Stores administrator, faculty, and student records, credentials (BCRYPT hashed), academic program, year level, section, and contact number. Role is defined as `ENUM('admin', 'faculty', 'student')`.
2. `courses`: Curriculum offerings, credit units, faculty, time schedules, rooms, and class capacities.
3. `enrollments`: Relational grade table with `student_id`, `course_id`, `prelim`, `midterm`, `semifinal`, `final`, `final_average`, and `remarks`.
4. `announcements`: Campus advisories with categories and pinned flags.
5. `activity_logs`: Audit trail tracking administrative updates, logins, and grade modifications.

---

## 5. Local Installation & Setup Guide

### System Requirements:

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache / Nginx / PHP Built-in Server

### Step-by-Step Instructions:

1. **Clone or Extract the Project:**

    ```bash
    git clone https://github.com/angeloboreta36/Student_Portal_FP.git
    cd Student_Portal_FP
    ```

2. **Database Import:**
    - Open **phpMyAdmin** or MySQL command line.
    - Create database `student_portal`:
        ```sql
        CREATE DATABASE student_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ```
    - Import `/database/schema.sql`:
        ```bash
        mysql -u root -p student_portal < database/schema.sql
        ```

3. **Configure Connection (`config/database.php`):**

    ```php
    $db_host = 'localhost';
    $db_name = 'student_portal';
    $db_user = 'root';
    $db_pass = '';
    ```

4. **Run Web Server:**

    ```bash
    php -S 0.0.0.0:8000
    ```

    - Access via browser: `http://localhost:8000`
