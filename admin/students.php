<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$coursesList = array('BSIT', 'BEE', 'BSE', 'BSBA', 'BSA', 'BSCRIM', 'BSCE');
$yearLevelsList = array('1st Year', '2nd Year', '3rd Year', '4th Year');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'create') {
        $studentIdNum = isset($_POST['student_id_number']) ? trim($_POST['student_id_number']) : '';
        $firstName    = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $middleName   = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
        $lastName     = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $course       = isset($_POST['course']) ? trim($_POST['course']) : '';
        $yearLevel    = isset($_POST['year_level']) ? trim($_POST['year_level']) : '';
        $section      = isset($_POST['section']) ? trim($_POST['section']) : '';
        $contactNum   = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
        $email        = isset($_POST['email']) ? trim($_POST['email']) : '';
        $rawPass      = isset($_POST['password']) && !empty($_POST['password']) ? trim($_POST['password']) : 'student123';

        if (empty($studentIdNum) || empty($firstName) || empty($lastName) || empty($email)) {
            set_flash('error', 'Student ID, Name, and Email are required.');
        } else {
            
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE student_id_number = ? OR email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $studentIdNum, $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $exists = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if ($exists) {
                set_flash('error', 'Student ID number or Email is already registered.');
            } else {
                $hash = password_hash($rawPass, PASSWORD_BCRYPT);
                $insStmt = mysqli_prepare($conn, "INSERT INTO users (student_id_number, first_name, middle_name, last_name, email, password, role, course, year_level, section, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, 'student', ?, ?, ?, ?, 'Active')");
                
                $mid = !empty($middleName) ? $middleName : null;
                $sec = !empty($section) ? $section : null;
                $con = !empty($contactNum) ? $contactNum : null;

                mysqli_stmt_bind_param($insStmt, "ssssssssss", $studentIdNum, $firstName, $mid, $lastName, $email, $hash, $course, $yearLevel, $sec, $con);
                mysqli_stmt_execute($insStmt);
                mysqli_stmt_close($insStmt);

                log_activity($_SESSION['user_id'], 'STUDENT_CREATE', "Added student $studentIdNum - $firstName $lastName");
                set_flash('success', "Student $firstName $lastName registered successfully!");
                header('Location: ' . base_url('admin/students.php'));
                exit;
            }
        }
    } elseif ($action == 'update') {
        $id           = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $studentIdNum = isset($_POST['student_id_number']) ? trim($_POST['student_id_number']) : '';
        $firstName    = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $middleName   = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
        $lastName     = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $course       = isset($_POST['course']) ? trim($_POST['course']) : '';
        $yearLevel    = isset($_POST['year_level']) ? trim($_POST['year_level']) : '';
        $section      = isset($_POST['section']) ? trim($_POST['section']) : '';
        $contactNum   = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
        $email        = isset($_POST['email']) ? trim($_POST['email']) : '';
        $newPass      = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

        if (!$id || empty($studentIdNum) || empty($firstName) || empty($lastName) || empty($email)) {
            set_flash('error', 'Missing required fields for student update.');
        } else {
            
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE (student_id_number = ? OR email = ?) AND id != ?");
            mysqli_stmt_bind_param($stmt, "ssi", $studentIdNum, $email, $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $duplicate = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if ($duplicate) {
                set_flash('error', 'Another account is already using that Student ID or Email.');
            } else {
                $mid = !empty($middleName) ? $middleName : null;
                $sec = !empty($section) ? $section : null;
                $con = !empty($contactNum) ? $contactNum : null;

                if (!empty($newPass)) {
                    $hash = password_hash($newPass, PASSWORD_BCRYPT);
                    $upStmt = mysqli_prepare($conn, "UPDATE users SET student_id_number = ?, first_name = ?, middle_name = ?, last_name = ?, email = ?, course = ?, year_level = ?, section = ?, contact_number = ?, password = ? WHERE id = ? AND role = 'student'");
                    mysqli_stmt_bind_param($upStmt, "ssssssssssi", $studentIdNum, $firstName, $mid, $lastName, $email, $course, $yearLevel, $sec, $con, $hash, $id);
                } else {
                    $upStmt = mysqli_prepare($conn, "UPDATE users SET student_id_number = ?, first_name = ?, middle_name = ?, last_name = ?, email = ?, course = ?, year_level = ?, section = ?, contact_number = ? WHERE id = ? AND role = 'student'");
                    mysqli_stmt_bind_param($upStmt, "sssssssssi", $studentIdNum, $firstName, $mid, $lastName, $email, $course, $yearLevel, $sec, $con, $id);
                }
                mysqli_stmt_execute($upStmt);
                mysqli_stmt_close($upStmt);

                log_activity($_SESSION['user_id'], 'STUDENT_UPDATE', "Updated student ID #$id ($studentIdNum)");
                set_flash('success', "Student record updated successfully!");
                header('Location: ' . base_url('admin/students.php'));
                exit;
            }
        }
    } elseif ($action == 'delete') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            $delStmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role = 'student'");
            mysqli_stmt_bind_param($delStmt, "i", $id);
            mysqli_stmt_execute($delStmt);
            mysqli_stmt_close($delStmt);

            log_activity($_SESSION['user_id'], 'STUDENT_DELETE', "Deleted student account ID #$id");
            set_flash('info', 'Student deleted successfully.');
            header('Location: ' . base_url('admin/students.php'));
            exit;
        }
    }
}


$students = array();
$query = "
    SELECT u.*, 
    (SELECT COUNT(*) FROM enrollments e WHERE e.student_id = u.id AND e.status = 'Enrolled') as enrolled_subjects
    FROM users u 
    WHERE u.role = 'student'
    ORDER BY u.last_name ASC, u.first_name ASC
";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

$page_title = "Manage Students - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Student Directory (Class Roster)</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Philippine College of Northwestern Luzon &bull; Official Enrolled Students</p>
        </div>
        <button onclick="openModal('addStudentModal')" class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-sm transition">
            <i class="fa-solid fa-user-plus mr-1.5"></i>Register New Student
        </button>
    </div>

    <?php render_flash(); ?>

    
    <div class="bg-white p-4 rounded-2xl border-2 border-blue-100 shadow-xs mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" id="student-search-input" placeholder="Search by name, ID (e.g. 2400001)..."
                   class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
        </div>
        <div class="flex items-center space-x-3 w-full sm:w-auto">
            <select id="student-course-filter" class="w-full sm:w-36 px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="All">All Programs</option>
                <?php foreach ($coursesList as $c): ?>
                    <option value="<?= $c ?>"><?= $c ?></option>
                <?php endforeach; ?>
            </select>
            <select id="student-year-filter" class="w-full sm:w-36 px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="All">All Years</option>
                <?php foreach ($yearLevelsList as $y): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Student ID</th>
                        <th class="px-5 py-3.5 font-bold">Full Name</th>
                        <th class="px-5 py-3.5 font-bold">Program</th>
                        <th class="px-5 py-3.5 font-bold">Year & Section</th>
                        <th class="px-5 py-3.5 font-bold">Contact</th>
                        <th class="px-5 py-3.5 font-bold text-center">Enrolled</th>
                        <th class="px-5 py-3.5 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="students-table-body">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">No students registered yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <?php $fullName = $s['last_name'] . ', ' . $s['first_name'] . ($s['middle_name'] ? ' ' . $s['middle_name'] : ''); ?>
                            <tr class="hover:bg-blue-50/30 transition border-b border-slate-100">
                                <td class="px-5 py-4 font-mono font-bold text-blue-700"><?= sanitize($s['student_id_number']) ?></td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900"><?= sanitize($fullName) ?></div>
                                    <div class="text-xs text-slate-500"><?= sanitize($s['email']) ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200"><?= sanitize($s['course'] ? $s['course'] : 'BSIT') ?></span>
                                </td>
                                <td class="px-5 py-4 text-slate-700 text-xs">
                                    <?= sanitize($s['year_level'] ? $s['year_level'] : '2nd Year') ?> <span class="text-slate-400">(<?= sanitize($s['section'] ? $s['section'] : '2-A') ?>)</span>
                                </td>
                                <td class="px-5 py-4 text-xs font-mono text-slate-600"><?= sanitize($s['contact_number'] ? $s['contact_number'] : 'None') ?></td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                        <?= $s['enrolled_subjects'] ?> subjects
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right space-x-1">
                                    <button onclick='editStudentModal(<?= json_encode($s) ?>)' class="text-blue-700 hover:text-blue-900 text-xs font-semibold p-1.5 rounded hover:bg-blue-50" title="Edit Student">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="<?= base_url('admin/students.php') ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete student <?= addslashes($fullName) ?>? All grades and enrollments will also be removed.')">
                                        <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="text-rose-600 hover:text-rose-900 text-xs font-semibold p-1.5 rounded hover:bg-rose-50" title="Delete Student">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="addStudentModal" class="hidden modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative my-8 border-2 border-blue-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Register Student to Class Roster</h3>
            <button onclick="closeModal('addStudentModal')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="<?= base_url('admin/students.php') ?>" class="mt-4 space-y-4">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Student ID Number *</label>
                    <input type="text" name="student_id_number" required placeholder="e.g. 2400030"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Degree Program *</label>
                    <select name="course" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($coursesList as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" required placeholder="First"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" placeholder="Middle"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" required placeholder="Last"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Year Level *</label>
                    <select name="year_level" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($yearLevelsList as $y): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Section</label>
                    <input type="text" name="section" placeholder="e.g. 2-A"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Contact Number</label>
                    <input type="text" name="contact_number" placeholder="09170000000"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                <input type="email" name="email" required placeholder="student@student.edu.ph"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Initial Password (Default: student123)</label>
                <input type="password" name="password" placeholder="student123"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('addStudentModal')" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-700 text-white hover:bg-blue-800 shadow-sm">Save Student</button>
            </div>
        </form>
    </div>
</div>


<div id="editStudentModal" class="hidden modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative my-8 border-2 border-blue-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Edit Student Record</h3>
            <button onclick="closeModal('editStudentModal')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="<?= base_url('admin/students.php') ?>" class="mt-4 space-y-4">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-id" name="id">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Student ID Number *</label>
                    <input type="text" id="edit-student-id" name="student_id_number" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Degree Program *</label>
                    <select id="edit-course" name="course" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($coursesList as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">First Name *</label>
                    <input type="text" id="edit-first-name" name="first_name" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Middle Name</label>
                    <input type="text" id="edit-middle-name" name="middle_name"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Last Name *</label>
                    <input type="text" id="edit-last-name" name="last_name" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Year Level *</label>
                    <select id="edit-year-level" name="year_level" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($yearLevelsList as $y): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Section</label>
                    <input type="text" id="edit-section" name="section"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Contact Number</label>
                    <input type="text" id="edit-contact" name="contact_number"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                <input type="email" id="edit-email" name="email" required
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Reset Password (Leave blank to keep current)</label>
                <input type="password" name="new_password" placeholder="••••••••"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('editStudentModal')" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-700 text-white hover:bg-blue-800 shadow-sm">Update Student</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/live_search.js') ?>"></script>
<script>
    initStudentSearch('<?= base_url('api/search_students.php') ?>', 'students-table-body');

    function editStudentModal(student) {
        document.getElementById('edit-id').value = student.id;
        document.getElementById('edit-student-id').value = student.student_id_number;
        document.getElementById('edit-first-name').value = student.first_name;
        document.getElementById('edit-middle-name').value = student.middle_name || '';
        document.getElementById('edit-last-name').value = student.last_name;
        document.getElementById('edit-course').value = student.course || 'BSIT';
        document.getElementById('edit-year-level').value = student.year_level || '1st Year';
        document.getElementById('edit-section').value = student.section || '';
        document.getElementById('edit-contact').value = student.contact_number || '';
        document.getElementById('edit-email').value = student.email;

        openModal('editStudentModal');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
