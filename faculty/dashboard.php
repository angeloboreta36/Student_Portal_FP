<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_faculty();
$faculty = current_user();
$facultyLastName = mysqli_real_escape_string($conn, $faculty['last_name']);

$teachingCourses = array();
$tQuery = "
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Enrolled') as enrolled_count
    FROM courses c 
    WHERE c.instructor LIKE '%$facultyLastName%'
    ORDER BY c.course_code ASC
";
$tRes = mysqli_query($conn, $tQuery);
if ($tRes) {
    while ($row = mysqli_fetch_assoc($tRes)) {
        $teachingCourses[] = $row;
    }
}

$courseIds = array();
$totalStudents = 0;
foreach ($teachingCourses as $tc) {
    $courseIds[] = intval($tc['id']);
    $totalStudents += intval($tc['enrolled_count']);
}

$gradedCount = 0;
if (!empty($courseIds)) {
    $cIdsStr = implode(',', $courseIds);
    $gRes = mysqli_query($conn, "SELECT COUNT(*) FROM enrollments WHERE course_id IN ($cIdsStr) AND final_average IS NOT NULL");
    if ($gRes) {
        $gRow = mysqli_fetch_row($gRes);
        $gradedCount = intval($gRow[0]);
    }
}

$announcements = array();
$aRes = mysqli_query($conn, "SELECT * FROM announcements ORDER BY is_pinned DESC, created_at DESC LIMIT 3");
if ($aRes) {
    while ($arow = mysqli_fetch_assoc($aRes)) {
        $announcements[] = $arow;
    }
}

$page_title = "Faculty Portal - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <img src="<?= base_url('assets/images/pcnl-logo.png') ?>" alt="PCNL Logo" class="w-14 h-14 object-contain drop-shadow-sm">
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Welcome, <?= sanitize($faculty['name']) ?>!
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        Faculty
                    </span>
                </div>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Faculty ID: <span class="font-mono font-bold text-slate-700"><?= sanitize($faculty['student_id_number']) ?></span> &bull; 
                    Department of Information Technology &bull; San Antonio, Agoo, La Union
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= base_url('admin/grades.php') ?>" class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-sm transition">
                <i class="fa-solid fa-award mr-1.5"></i>Encode Term Grades
            </a>
        </div>
    </div>

    <?php render_flash(); ?>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Assigned Classes</span>
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= count($teachingCourses) ?></div>
                <div class="text-xs font-semibold text-blue-700 mt-1">Active Teaching Load</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Enrolled Students</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $totalStudents ?></div>
                <div class="text-xs font-semibold text-cyan-700 mt-1">Across All Teaching Sections</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Encoded Term Grades</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $gradedCount ?></div>
                <div class="text-xs font-semibold text-emerald-700 mt-1">Student Term Records Completed</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-chalkboard-user text-blue-700"></i>
                    <h2 class="text-sm font-bold text-slate-900">Your Assigned Teaching Load (1st Semester)</h2>
                </div>
                <span class="text-xs font-bold text-blue-700">A.Y. 2026-2027</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5 font-bold">Course Code</th>
                            <th class="px-5 py-3.5 font-bold">Descriptive Title</th>
                            <th class="px-3 py-3.5 font-bold text-center">Units</th>
                            <th class="px-4 py-3.5 font-bold">Schedule & Room</th>
                            <th class="px-3 py-3.5 font-bold text-center">Enrolled</th>
                            <th class="px-5 py-3.5 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($teachingCourses)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">No classes assigned to your faculty profile.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teachingCourses as $tc): ?>
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="px-5 py-4 font-mono font-bold text-blue-700"><?= sanitize($tc['course_code']) ?></td>
                                    <td class="px-5 py-4 font-semibold text-slate-900"><?= sanitize($tc['course_title']) ?></td>
                                    <td class="px-3 py-4 text-center font-bold text-slate-700"><?= intval($tc['units']) ?></td>
                                    <td class="px-4 py-4 text-xs text-slate-600">
                                        <?= sanitize($tc['schedule']) ?><br>
                                        <span class="text-slate-400"><?= sanitize($tc['room']) ?></span>
                                    </td>
                                    <td class="px-3 py-4 text-center font-mono font-bold text-blue-900">
                                        <?= intval($tc['enrolled_count']) ?> / <?= intval($tc['capacity']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="<?= base_url('admin/grades.php?course_id=' . $tc['id']) ?>" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-xs transition">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i>Encode Grades
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-xs flex flex-col">
            <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-bullhorn text-blue-700"></i>
                    <h2 class="text-sm font-bold text-slate-900">PCNL Faculty Bulletins</h2>
                </div>
            </div>
            <div class="p-6 divide-y divide-slate-100 flex-1 space-y-4">
                <?php if (empty($announcements)): ?>
                    <p class="text-xs text-slate-400 text-center py-8">No campus notices posted.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $ann): ?>
                        <div class="pt-4 first:pt-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">
                                    <?= sanitize($ann['category']) ?>
                                </span>
                                <span class="text-[11px] text-slate-400"><?= date('M j, Y', strtotime($ann['created_at'])) ?></span>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                                <?= sanitize($ann['title']) ?>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                <?= sanitize($ann['content']) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
