<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();
$student = current_user();
$studentId = intval($student['id']);


$enrolledList = array();
$enrolledQuery = "
    SELECT e.id as enrollment_id, e.prelim, e.midterm, e.semifinal, e.final,
           c.id as course_id, c.course_code, c.course_title, c.units, c.instructor, c.schedule, c.room
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = $studentId AND e.status = 'Enrolled'
    ORDER BY c.course_code ASC
";
$enrolledRes = mysqli_query($conn, $enrolledQuery);
if ($enrolledRes) {
    while ($row = mysqli_fetch_assoc($enrolledRes)) {
        $enrolledList[] = $row;
    }
}

$enrolledCourseIds = array();
$totalEnrolledUnits = 0;
foreach ($enrolledList as $item) {
    $enrolledCourseIds[] = intval($item['course_id']);
    $totalEnrolledUnits += intval($item['units']);
}


$allCourses = array();
$coursesQuery = "
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Enrolled') as enrolled_count
    FROM courses c
    ORDER BY c.course_code ASC
";
$coursesRes = mysqli_query($conn, $coursesQuery);
if ($coursesRes) {
    while ($row = mysqli_fetch_assoc($coursesRes)) {
        $allCourses[] = $row;
    }
}

$departments = array('BSIT', 'BEE', 'BSE', 'BSBA', 'BSA', 'BSCRIM', 'BSCE', 'General Education');

$page_title = "Subject Enlistment - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Online Subject Enlistment</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Philippine College of Northwestern Luzon &bull; 1st Semester &bull; Max Allowable Load: 24 Units
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="px-4 py-2.5 rounded-2xl bg-white border-2 border-blue-100 shadow-xs flex items-center space-x-3 text-xs sm:text-sm">
                <span class="text-slate-500 font-semibold">Your Current Load:</span>
                <span class="font-bold text-blue-700 text-base" id="total-units-badge"><?= $totalEnrolledUnits ?> / 24 Units</span>
            </div>
        </div>
    </div>

    <?php render_flash(); ?>

    
    <div class="mb-10 bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/40 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-list-check text-blue-700"></i>
                <h2 class="text-sm font-bold text-slate-900">Your Officially Enrolled Subjects</h2>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                <?= count($enrolledList) ?> Subjects Registered
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm" id="enrolled-table">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Code</th>
                        <th class="px-5 py-3.5 font-bold">Descriptive Title</th>
                        <th class="px-4 py-3.5 font-bold text-center">Units</th>
                        <th class="px-5 py-3.5 font-bold">Schedule</th>
                        <th class="px-5 py-3.5 font-bold">Instructor</th>
                        <th class="px-5 py-3.5 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="enrolled-table-body">
                    <?php if (empty($enrolledList)): ?>
                        <tr id="no-enrolled-row">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300"></i>
                                <p class="font-medium">No subjects enlisted yet. Browse the curriculum offerings below.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($enrolledList as $item): ?>
                            <?php $hasGrade = ($item['prelim'] !== null || $item['midterm'] !== null || $item['semifinal'] !== null || $item['final'] !== null); ?>
                            <tr class="hover:bg-blue-50/30 transition border-b border-slate-100" id="enrolled-row-<?= $item['course_id'] ?>">
                                <td class="px-5 py-4 font-bold text-blue-700 font-mono"><?= sanitize($item['course_code']) ?></td>
                                <td class="px-5 py-4 font-semibold text-slate-900"><?= sanitize($item['course_title']) ?></td>
                                <td class="px-4 py-4 text-center font-bold text-slate-700"><?= $item['units'] ?></td>
                                <td class="px-5 py-4 text-xs text-slate-600">
                                    <?= sanitize($item['schedule']) ?><br>
                                    <span class="text-slate-400"><?= sanitize($item['room']) ?></span>
                                </td>
                                <td class="px-5 py-4 text-slate-700 font-medium"><?= sanitize($item['instructor']) ?></td>
                                <td class="px-5 py-4 text-right">
                                    <?php if ($hasGrade): ?>
                                        <span class="inline-flex items-center text-xs text-slate-400 font-semibold" title="Subject has recorded grades and cannot be dropped online">
                                            <i class="fa-solid fa-lock mr-1"></i>Graded
                                        </span>
                                    <?php else: ?>
                                        <button onclick="handleDrop(<?= $item['course_id'] ?>)" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition">
                                            <i class="fa-solid fa-xmark mr-1"></i>Drop
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-blue-100 bg-blue-50/30">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Available Course Offerings</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Search or filter by academic program to enlist into active course sections</p>
                </div>

                
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" id="course-search-input" placeholder="Search code, title, prof..."
                               class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <select id="course-dept-filter" class="w-full sm:w-44 px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="All">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept ?>"><?= $dept ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Code</th>
                        <th class="px-5 py-3.5 font-bold">Subject Title</th>
                        <th class="px-4 py-3.5 font-bold text-center">Units</th>
                        <th class="px-5 py-3.5 font-bold">Schedule & Room</th>
                        <th class="px-5 py-3.5 font-bold">Instructor</th>
                        <th class="px-5 py-3.5 font-bold text-center">Class Slots</th>
                        <th class="px-5 py-3.5 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="available-courses-tbody">
                    <?php foreach ($allCourses as $c): ?>
                        <?php 
                            $isEnrolled = in_array((int)$c['id'], $enrolledCourseIds);
                            $isFull = ((int)$c['enrolled_count'] >= (int)$c['capacity']);
                        ?>
                        <tr class="hover:bg-blue-50/30 transition border-b border-slate-100" id="course-row-<?= $c['id'] ?>">
                            <td class="px-5 py-4 font-bold text-blue-700 font-mono"><?= sanitize($c['course_code']) ?></td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900"><?= sanitize($c['course_title']) ?></div>
                                <div class="text-xs text-slate-400"><?= sanitize($c['department']) ?> &bull; <?= sanitize($c['semester']) ?></div>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-slate-700"><?= $c['units'] ?></td>
                            <td class="px-5 py-4 text-xs text-slate-600">
                                <?= sanitize($c['schedule']) ?><br>
                                <span class="text-slate-400"><?= sanitize($c['room']) ?></span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-medium"><?= sanitize($c['instructor']) ?></td>
                            <td class="px-5 py-4 text-center">
                                <?php if ($isFull): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-700">
                                        Full (<?= $c['enrolled_count'] ?>/<?= $c['capacity'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <?= $c['enrolled_count'] ?> / <?= $c['capacity'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <?php if ($isEnrolled): ?>
                                    <span class="inline-flex items-center text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                        <i class="fa-solid fa-circle-check mr-1.5"></i>Enrolled
                                    </span>
                                <?php elseif ($isFull): ?>
                                    <button disabled class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-200 text-slate-400 cursor-not-allowed">
                                        Class Full
                                    </button>
                                <?php else: ?>
                                    <button onclick="handleEnroll(<?= $c['id'] ?>)" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-xs transition">
                                        <i class="fa-solid fa-plus mr-1"></i>Enroll
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/live_search.js') ?>"></script>
<script>
    initCourseSearch('<?= base_url('api/search_courses.php') ?>', 'available-courses-tbody', true);

    async function handleEnroll(courseId) {
        try {
            const res = await fetch('<?= base_url('api/enroll_subject.php') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'enroll', course_id: courseId })
            });
            const data = await res.json();

            if (res.ok && data.status === 'success') {
                showToast(data.message, 'success');
                setTimeout(function() { window.location.reload(); }, 900);
            } else {
                showToast(data.message || 'Could not enroll in subject', 'error');
            }
        } catch (err) {
            showToast('Network error while processing enrollment', 'error');
        }
    }

    async function handleDrop(courseId) {
        if (!confirm('Are you sure you want to drop this subject?')) return;

        try {
            const res = await fetch('<?= base_url('api/enroll_subject.php') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'drop', course_id: courseId })
            });
            const data = await res.json();

            if (res.ok && data.status === 'success') {
                showToast(data.message, 'info');
                setTimeout(function() { window.location.reload(); }, 900);
            } else {
                showToast(data.message || 'Could not drop subject', 'error');
            }
        } catch (err) {
            showToast('Network error while dropping subject', 'error');
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
