<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();


$resStudents = mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'student'");
$row = mysqli_fetch_row($resStudents);
$totalStudents = intval($row[0]);

$resCourses = mysqli_query($conn, "SELECT COUNT(*) FROM courses");
$row = mysqli_fetch_row($resCourses);
$totalCourses = intval($row[0]);

$resEnrollments = mysqli_query($conn, "SELECT COUNT(*) FROM enrollments WHERE status = 'Enrolled'");
$row = mysqli_fetch_row($resEnrollments);
$totalEnrollments = intval($row[0]);


$resGraded = mysqli_query($conn, "SELECT COUNT(*) FROM enrollments WHERE final_average IS NOT NULL");
$row = mysqli_fetch_row($resGraded);
$gradedCount = intval($row[0]);

$resPassed = mysqli_query($conn, "SELECT COUNT(*) FROM enrollments WHERE remarks = 'Passed'");
$row = mysqli_fetch_row($resPassed);
$passedCount = intval($row[0]);

$passingRate = ($gradedCount > 0) ? round(($passedCount / $gradedCount) * 100, 1) : 0;


$recentEnrollments = array();
$resRec = mysqli_query($conn, "
    SELECT e.*, u.student_id_number, u.first_name, u.last_name, u.course, c.course_code, c.course_title
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrollment_date DESC
    LIMIT 5
");
while ($r = mysqli_fetch_assoc($resRec)) {
    $recentEnrollments[] = $r;
}


$recentLogs = array();
$resLogs = mysqli_query($conn, "
    SELECT l.*, u.first_name, u.last_name, u.role
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 6
");
while ($l = mysqli_fetch_assoc($resLogs)) {
    $recentLogs[] = $l;
}

$page_title = "Admin Console - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <img src="<?= base_url('assets/images/pcnl-logo.png') ?>" alt="PCNL Logo" class="w-14 h-14 object-contain drop-shadow-sm">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">PCNL Administrator Console</h1>
                <p class="text-xs font-semibold text-blue-700">Philippine College of Northwestern Luzon &bull; Academic Portal</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= base_url('admin/grades.php') ?>" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-sm transition">
                <i class="fa-solid fa-award mr-1.5"></i>Encode Grades
            </a>
            <a href="<?= base_url('admin/students.php') ?>" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-white hover:bg-blue-50 border border-blue-200 text-blue-900 shadow-xs transition">
                <i class="fa-solid fa-user-plus mr-1.5 text-blue-600"></i>Add Student
            </a>
        </div>
    </div>

    <?php render_flash(); ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Enrolled Students</span>
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $totalStudents ?></div>
                <div class="text-xs font-semibold text-blue-600 mt-1">Class Roster Enrolled</div>
            </div>
        </div>

        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Curriculum Subjects</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $totalCourses ?></div>
                <div class="text-xs font-semibold text-cyan-600 mt-1">Active Offerings (1st Sem)</div>
            </div>
        </div>

        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Course Loads</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $totalEnrollments ?></div>
                <div class="text-xs font-semibold text-emerald-600 mt-1">Official Subject Enlistments</div>
            </div>
        </div>

        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Term Passing Rate</span>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $passingRate ?>%</div>
                <div class="text-xs font-semibold text-amber-600 mt-1"><?= $passedCount ?> of <?= $gradedCount ?> completed courses</div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <a href="<?= base_url('admin/students.php') ?>" class="p-4 bg-white rounded-2xl border-2 border-blue-100 hover:border-blue-500 hover:shadow-md transition group">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="font-bold text-slate-900 text-xs sm:text-sm">Manage Students</div>
            <div class="text-[11px] text-slate-400">Class roster directory</div>
        </a>
        <a href="<?= base_url('admin/courses.php') ?>" class="p-4 bg-white rounded-2xl border-2 border-blue-100 hover:border-blue-500 hover:shadow-md transition group">
            <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                <i class="fa-solid fa-book"></i>
            </div>
            <div class="font-bold text-slate-900 text-xs sm:text-sm">Curriculum Subjects</div>
            <div class="text-[11px] text-slate-400">Schedules & capacities</div>
        </a>
        <a href="<?= base_url('admin/grades.php') ?>" class="p-4 bg-white rounded-2xl border-2 border-blue-100 hover:border-blue-500 hover:shadow-md transition group">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                <i class="fa-solid fa-pen-ruler"></i>
            </div>
            <div class="font-bold text-slate-900 text-xs sm:text-sm">Encode Grades</div>
            <div class="text-[11px] text-slate-400">4-term live calculations</div>
        </a>
        <a href="<?= base_url('admin/announcements.php') ?>" class="p-4 bg-white rounded-2xl border-2 border-blue-100 hover:border-blue-500 hover:shadow-md transition group">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <div class="font-bold text-slate-900 text-xs sm:text-sm">Campus Bulletins</div>
            <div class="text-[11px] text-slate-400">Publish exam notices</div>
        </a>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-700"></i>
                    <h2 class="text-sm font-bold text-slate-900">Recent Student Enlistments & Grades</h2>
                </div>
                <a href="<?= base_url('admin/grades.php') ?>" class="text-xs font-bold text-blue-700 hover:text-blue-900">
                    Full Grade Sheet &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 font-bold">Student</th>
                            <th class="px-5 py-3 font-bold">Subject</th>
                            <th class="px-4 py-3 font-bold text-center">Average</th>
                            <th class="px-4 py-3 font-bold text-center">Remarks</th>
                            <th class="px-5 py-3 font-bold text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($recentEnrollments as $r): ?>
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900"><?= sanitize($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                    <div class="text-xs text-slate-400 font-mono"><?= sanitize($r['student_id_number']) ?> &bull; <?= sanitize($r['course']) ?></div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-blue-700 font-mono"><?= sanitize($r['course_code']) ?></div>
                                    <div class="text-xs text-slate-500 truncate max-w-[180px]"><?= sanitize($r['course_title']) ?></div>
                                </td>
                                <td class="px-4 py-3.5 text-center font-mono font-bold text-xs">
                                    <?= $r['final_average'] !== null ? number_format($r['final_average'], 2) . '%' : '<span class="text-slate-300 font-normal">--</span>' ?>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <?= get_remarks_badge($r['remarks']) ?>
                                </td>
                                <td class="px-5 py-3.5 text-right text-xs text-slate-400">
                                    <?= date('M j, Y', strtotime($r['enrollment_date'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-xs flex flex-col">
            <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-shield-halved text-blue-700"></i>
                    <h2 class="text-sm font-bold text-slate-900">Audit Activity Logs</h2>
                </div>
            </div>

            <div class="p-6 divide-y divide-slate-100 flex-1 space-y-3">
                <?php if (empty($recentLogs)): ?>
                    <p class="text-xs text-slate-400 text-center py-6">No recent audit logs.</p>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="pt-3 first:pt-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-blue-900 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 font-mono">
                                    <?= sanitize($log['action']) ?>
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    <?= date('M j, Y', strtotime($log['created_at'])) ?> &bull; <?= date('g:i A', strtotime($log['created_at'])) ?>
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 leading-normal">
                                <?= sanitize($log['details']) ?>
                            </p>
                            <div class="text-[10px] text-slate-400 mt-1">
                                <?php $logIp = ($log['ip_address'] == '::1') ? '127.0.0.1' : ($log['ip_address'] ? $log['ip_address'] : '127.0.0.1'); ?>
                                By: <strong class="text-slate-700 font-semibold"><?= sanitize($log['first_name'] ? $log['first_name'] . ' ' . $log['last_name'] : 'Angelo Boreta') ?></strong> (<?= sanitize($logIp) ?>)
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
