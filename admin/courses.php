<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$departments = array('BSIT', 'BEE', 'BSE', 'BSBA', 'BSA', 'BSCRIM', 'BSCE', 'General Education');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'create') {
        $code        = isset($_POST['course_code']) ? trim($_POST['course_code']) : '';
        $title       = isset($_POST['course_title']) ? trim($_POST['course_title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $units       = isset($_POST['units']) ? intval($_POST['units']) : 3;
        $instructor  = isset($_POST['instructor']) ? trim($_POST['instructor']) : '';
        $schedule    = isset($_POST['schedule']) ? trim($_POST['schedule']) : '';
        $room        = isset($_POST['room']) ? trim($_POST['room']) : '';
        $capacity    = isset($_POST['capacity']) ? intval($_POST['capacity']) : 40;
        $dept        = isset($_POST['department']) ? trim($_POST['department']) : 'General Education';
        $semester    = isset($_POST['semester']) ? trim($_POST['semester']) : '1st Semester';
        $acadYear    = isset($_POST['academic_year']) ? trim($_POST['academic_year']) : '2026-2027';

        if (empty($code) || empty($title) || empty($instructor)) {
            set_flash('error', 'Course code, title, and instructor are required.');
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id FROM courses WHERE course_code = ?");
            mysqli_stmt_bind_param($stmt, "s", $code);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $exists = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if ($exists) {
                set_flash('error', "Course code $code already exists.");
            } else {
                $insStmt = mysqli_prepare($conn, "INSERT INTO courses (course_code, course_title, description, units, instructor, schedule, room, capacity, department, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $desc = !empty($description) ? $description : null;
                mysqli_stmt_bind_param($insStmt, "sssisssisss", $code, $title, $desc, $units, $instructor, $schedule, $room, $capacity, $dept, $semester, $acadYear);
                mysqli_stmt_execute($insStmt);
                mysqli_stmt_close($insStmt);

                log_activity($_SESSION['user_id'], 'COURSE_CREATE', "Created course $code - $title");
                set_flash('success', "Course $code added successfully!");
                header('Location: ' . base_url('admin/courses.php'));
                exit;
            }
        }
    } elseif ($action == 'update') {
        $id          = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $code        = isset($_POST['course_code']) ? trim($_POST['course_code']) : '';
        $title       = isset($_POST['course_title']) ? trim($_POST['course_title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $units       = isset($_POST['units']) ? intval($_POST['units']) : 3;
        $instructor  = isset($_POST['instructor']) ? trim($_POST['instructor']) : '';
        $schedule    = isset($_POST['schedule']) ? trim($_POST['schedule']) : '';
        $room        = isset($_POST['room']) ? trim($_POST['room']) : '';
        $capacity    = isset($_POST['capacity']) ? intval($_POST['capacity']) : 40;
        $dept        = isset($_POST['department']) ? trim($_POST['department']) : 'General Education';

        if (!$id || empty($code) || empty($title)) {
            set_flash('error', 'Missing required course fields.');
        } else {
            $upStmt = mysqli_prepare($conn, "UPDATE courses SET course_code = ?, course_title = ?, description = ?, units = ?, instructor = ?, schedule = ?, room = ?, capacity = ?, department = ? WHERE id = ?");
            $desc = !empty($description) ? $description : null;
            mysqli_stmt_bind_param($upStmt, "sssisssisi", $code, $title, $desc, $units, $instructor, $schedule, $room, $capacity, $dept, $id);
            mysqli_stmt_execute($upStmt);
            mysqli_stmt_close($upStmt);

            log_activity($_SESSION['user_id'], 'COURSE_UPDATE', "Updated course $code");
            set_flash('success', "Course $code updated successfully!");
            header('Location: ' . base_url('admin/courses.php'));
            exit;
        }
    } elseif ($action == 'delete') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            $delStmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
            mysqli_stmt_bind_param($delStmt, "i", $id);
            mysqli_stmt_execute($delStmt);
            mysqli_stmt_close($delStmt);

            log_activity($_SESSION['user_id'], 'COURSE_DELETE', "Deleted course ID #$id");
            set_flash('info', 'Course and associated student enrollments deleted.');
            header('Location: ' . base_url('admin/courses.php'));
            exit;
        }
    }
}


$courses = array();
$query = "
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Enrolled') as enrolled_count
    FROM courses c
    ORDER BY c.course_code ASC
";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

$page_title = "Manage Courses - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Curriculum & Course Management</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Philippine College of Northwestern Luzon &bull; Official Subject Offerings</p>
        </div>
        <button onclick="openModal('addCourseModal')" class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-sm transition">
            <i class="fa-solid fa-plus-circle mr-1.5"></i>Create New Course
        </button>
    </div>

    <?php render_flash(); ?>

    
    <div class="bg-white p-4 rounded-2xl border-2 border-blue-100 shadow-xs mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" id="course-search-input" placeholder="Search code, title, professor..."
                   class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
        </div>
        <div class="w-full sm:w-48">
            <select id="course-dept-filter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="All">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept ?>"><?= $dept ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Course Code</th>
                        <th class="px-5 py-3.5 font-bold">Descriptive Title</th>
                        <th class="px-4 py-3.5 font-bold">Units</th>
                        <th class="px-5 py-3.5 font-bold">Schedule & Room</th>
                        <th class="px-5 py-3.5 font-bold">Instructor</th>
                        <th class="px-5 py-3.5 font-bold">Capacity</th>
                        <th class="px-5 py-3.5 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="courses-table-body">
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">No courses defined yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                            <?php $isFull = ($c['enrolled_count'] >= $c['capacity']); ?>
                            <tr class="hover:bg-blue-50/30 transition border-b border-slate-100" id="course-row-<?= $c['id'] ?>">
                                <td class="px-5 py-4 font-bold text-blue-700 font-mono"><?= sanitize($c['course_code']) ?></td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900"><?= sanitize($c['course_title']) ?></div>
                                    <div class="text-xs text-slate-500"><?= sanitize($c['department']) ?> &bull; <?= sanitize($c['semester']) ?></div>
                                </td>
                                <td class="px-4 py-4 text-slate-700 font-medium"><?= $c['units'] ?></td>
                                <td class="px-5 py-4 text-slate-600 text-xs">
                                    <?= sanitize($c['schedule']) ?><br>
                                    <span class="text-slate-400"><?= sanitize($c['room']) ?></span>
                                </td>
                                <td class="px-5 py-4 text-slate-700"><?= sanitize($c['instructor']) ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($isFull): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-700">
                                            Full (<?= $c['enrolled_count'] ?>/<?= $c['capacity'] ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-700">
                                            <?= $c['enrolled_count'] ?> / <?= $c['capacity'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-right space-x-1">
                                    <button onclick='editCourseModal(<?= json_encode($c) ?>)' class="text-blue-700 hover:text-blue-900 text-xs font-semibold p-1.5 rounded hover:bg-blue-50" title="Edit Subject">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="<?= base_url('admin/courses.php') ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this course? Enrolled students will also be removed.')">
                                        <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="text-rose-600 hover:text-rose-900 text-xs font-semibold p-1.5 rounded hover:bg-rose-50" title="Delete Subject">
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


<div id="addCourseModal" class="hidden modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative my-8 border-2 border-blue-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Add New Subject</h3>
            <button onclick="closeModal('addCourseModal')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="<?= base_url('admin/courses.php') ?>" class="mt-4 space-y-4">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Course Code *</label>
                    <input type="text" name="course_code" required placeholder="e.g. IT301"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none uppercase font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Course Title *</label>
                    <input type="text" name="course_title" required placeholder="e.g. Mobile Computing"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Units (Credits)</label>
                    <input type="number" name="units" value="3" min="1" max="6" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Department</label>
                    <select name="department" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept ?>"><?= $dept ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Max Capacity</label>
                    <input type="number" name="capacity" value="40" min="5" max="100" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Instructor *</label>
                    <input type="text" name="instructor" required placeholder="Prof. Alex Mendoza"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Room / Lab</label>
                    <input type="text" name="room" value="ComLab 1" placeholder="Room 203"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Schedule</label>
                <input type="text" name="schedule" value="MWF 9:00 AM - 10:30 AM" placeholder="TTh 1:00 PM - 2:30 PM"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('addCourseModal')" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-700 text-white hover:bg-blue-800 shadow-sm">Save Course</button>
            </div>
        </form>
    </div>
</div>


<div id="editCourseModal" class="hidden modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative my-8 border-2 border-blue-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Edit Subject Details</h3>
            <button onclick="closeModal('editCourseModal')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="<?= base_url('admin/courses.php') ?>" class="mt-4 space-y-4">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-course-id" name="id">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Course Code *</label>
                    <input type="text" id="edit-course-code" name="course_code" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none uppercase font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Course Title *</label>
                    <input type="text" id="edit-course-title" name="course_title" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Units</label>
                    <input type="number" id="edit-course-units" name="units" min="1" max="6" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Department</label>
                    <select id="edit-course-dept" name="department" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept ?>"><?= $dept ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Max Capacity</label>
                    <input type="number" id="edit-course-capacity" name="capacity" min="5" max="100" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Instructor *</label>
                    <input type="text" id="edit-course-instructor" name="instructor" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Room / Lab</label>
                    <input type="text" id="edit-course-room" name="room"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Schedule</label>
                <input type="text" id="edit-course-schedule" name="schedule"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('editCourseModal')" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-700 text-white hover:bg-blue-800 shadow-sm">Update Course</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/live_search.js') ?>"></script>
<script>
    initCourseSearch('<?= base_url('api/search_courses.php') ?>', 'courses-table-body', false);

    function editCourseModal(course) {
        document.getElementById('edit-course-id').value = course.id;
        document.getElementById('edit-course-code').value = course.course_code;
        document.getElementById('edit-course-title').value = course.course_title;
        document.getElementById('edit-course-units').value = course.units;
        document.getElementById('edit-course-dept').value = course.department || 'General Education';
        document.getElementById('edit-course-capacity').value = course.capacity;
        document.getElementById('edit-course-instructor').value = course.instructor;
        document.getElementById('edit-course-room').value = course.room;
        document.getElementById('edit-course-schedule').value = course.schedule;

        openModal('editCourseModal');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
