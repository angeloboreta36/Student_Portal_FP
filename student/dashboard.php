<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();
$student = current_user();
$studentId = intval($student['id']);


$enrollments = array();
$query = "
    SELECT e.*, c.course_code, c.course_title, c.units, c.instructor, c.schedule, c.room
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = $studentId AND e.status = 'Enrolled'
    ORDER BY c.course_code ASC
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $enrollments[] = $row;
    }
}


$totalUnits = 0;
$totalSubjects = count($enrollments);
$completedAverages = array();
$passedCount = 0;

foreach ($enrollments as $e) {
    $totalUnits += intval($e['units']);
    if ($e['final_average'] !== null) {
        $completedAverages[] = floatval($e['final_average']);
        if ($e['remarks'] == 'Passed') {
            $passedCount++;
        }
    }
}

$overallAverage = !empty($completedAverages) ? round(array_sum($completedAverages) / count($completedAverages), 2) : null;


$announcements = array();
$annQuery = "
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.is_pinned DESC, a.created_at DESC 
    LIMIT 4
";
$annResult = mysqli_query($conn, $annQuery);
if ($annResult) {
    while ($row = mysqli_fetch_assoc($annResult)) {
        $announcements[] = $row;
    }
}

$page_title = "Student Home - PCNL Portal";
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
                        Welcome, <?= sanitize($student['first_name']) ?>!
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        <?= sanitize($student['course'] ? $student['course'] : 'BSIT') ?>
                    </span>
                </div>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    ID: <span class="font-mono font-bold text-slate-700"><?= sanitize($student['student_id_number']) ?></span> &bull; 
                    Year: <span class="font-bold text-slate-700"><?= sanitize($student['year_level'] ? $student['year_level'] : '2nd Year') ?></span> &bull; 
                    Section: <span class="font-bold text-slate-700"><?= sanitize($student['section'] ? $student['section'] : '2-A') ?></span>
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= base_url('student/enlistment.php') ?>" class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-sm transition">
                <i class="fa-solid fa-plus-circle mr-1.5"></i>Enlist Subjects
            </a>
            <a href="<?= base_url('student/grades.php') ?>" class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-white hover:bg-blue-50 border border-blue-200 text-blue-900 shadow-xs transition">
                <i class="fa-solid fa-print mr-1.5 text-blue-600"></i>Official Grade Slip
            </a>
        </div>
    </div>

    <?php render_flash(); ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Term General Average</span>
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black <?= $overallAverage !== null ? ($overallAverage >= 75 ? 'text-slate-900' : 'text-rose-600') : 'text-slate-400' ?>">
                    <?= $overallAverage !== null ? number_format($overallAverage, 2) . '%' : 'Pending' ?>
                </div>
                <div class="text-xs font-semibold text-blue-700 mt-1">
                    <?= $overallAverage !== null ? ($overallAverage >= 90 ? '🌟 Dean\'s List Standing' : ($overallAverage >= 75 ? 'Good Academic Standing' : 'Needs Academic Attention')) : 'Awaiting completed grades' ?>
                </div>
            </div>
        </div>

        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Enrolled Units</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $totalUnits ?> <span class="text-base font-normal text-slate-400">units</span></div>
                <div class="text-xs font-semibold text-slate-500 mt-1">Maximum Regular Load: 24 units</div>
            </div>
        </div>

        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Registered Subjects</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900"><?= $totalSubjects ?></div>
                <div class="text-xs font-semibold text-emerald-600 mt-1"><?= $passedCount ?> subject(s) passed</div>
            </div>
        </div>

        
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Passing Benchmark</span>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-certificate"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-slate-900">75.00%</div>
                <div class="text-xs font-semibold text-slate-500 mt-1">Prelim &bull; Midterm &bull; Semi &bull; Final</div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-clipboard-check text-blue-700"></i>
                    <h2 class="text-sm font-bold text-slate-900">Current Semester Grade Breakdown</h2>
                </div>
                <a href="<?= base_url('student/grades.php') ?>" class="text-xs font-bold text-blue-700 hover:text-blue-900">
                    Official Grade Slip &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5 font-bold">Subject</th>
                            <th class="px-4 py-3.5 font-bold text-center">Prelim</th>
                            <th class="px-4 py-3.5 font-bold text-center">Midterm</th>
                            <th class="px-4 py-3.5 font-bold text-center">Semi-Final</th>
                            <th class="px-4 py-3.5 font-bold text-center">Final</th>
                            <th class="px-4 py-3.5 font-bold text-center">Average</th>
                            <th class="px-5 py-3.5 font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($enrollments)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-graduation-cap text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-medium">You are not enrolled in any subjects yet.</p>
                                    <a href="<?= base_url('student/enlistment.php') ?>" class="mt-2 inline-flex items-center text-xs text-blue-700 font-bold hover:underline">
                                        Click here to enlist in subjects &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($enrollments as $e): ?>
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-blue-700 font-mono"><?= sanitize($e['course_code']) ?></div>
                                        <div class="text-xs text-slate-500 truncate max-w-[180px]"><?= sanitize($e['course_title']) ?></div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono text-xs text-slate-700">
                                        <?= $e['prelim'] !== null ? number_format($e['prelim'], 2) : '<span class="text-slate-300">--</span>' ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono text-xs text-slate-700">
                                        <?= $e['midterm'] !== null ? number_format($e['midterm'], 2) : '<span class="text-slate-300">--</span>' ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono text-xs text-slate-700">
                                        <?= $e['semifinal'] !== null ? number_format($e['semifinal'], 2) : '<span class="text-slate-300">--</span>' ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono text-xs text-slate-700">
                                        <?= $e['final'] !== null ? number_format($e['final'], 2) : '<span class="text-slate-300">--</span>' ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono font-bold text-xs <?= ($e['final_average'] !== null && $e['final_average'] < 75) ? 'text-rose-600' : 'text-slate-900' ?>">
                                        <?= $e['final_average'] !== null ? number_format($e['final_average'], 2) . '%' : '<span class="text-slate-300 font-normal">--</span>' ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <?= get_remarks_badge($e['remarks']) ?>
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
                    <h2 class="text-sm font-bold text-slate-900">PCNL Bulletins</h2>
                </div>
                <a href="<?= base_url('student/announcements.php') ?>" class="text-xs font-bold text-blue-700 hover:text-blue-900">
                    All notices &rarr;
                </a>
            </div>

            <div class="p-6 divide-y divide-slate-100 flex-1 space-y-4">
                <?php if (empty($announcements)): ?>
                    <p class="text-xs text-slate-400 text-center py-8">No campus bulletins posted yet.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $ann): ?>
                        <div class="pt-4 first:pt-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                    <?= $ann['category'] == 'Urgent' ? 'bg-rose-100 text-rose-700' : ($ann['category'] == 'Academic' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                    <?= sanitize($ann['category']) ?>
                                </span>
                                <span class="text-[11px] text-slate-400"><?= date('M j, Y', strtotime($ann['created_at'])) ?></span>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 hover:text-blue-700 transition">
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
