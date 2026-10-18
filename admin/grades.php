<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_faculty();
$currentUser = current_user();

$filterCourseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$filterStudent = isset($_GET['student']) ? trim($_GET['student']) : '';
$filterRemarks = isset($_GET['remarks']) ? trim($_GET['remarks']) : 'All';

$coursesList = array();
if (is_faculty()) {
    $facLastName = mysqli_real_escape_string($conn, $currentUser['last_name']);
    $cRes = mysqli_query($conn, "SELECT id, course_code, course_title FROM courses WHERE instructor LIKE '%$facLastName%' ORDER BY course_code ASC");
} else {
    $cRes = mysqli_query($conn, "SELECT id, course_code, course_title FROM courses ORDER BY course_code ASC");
}
while ($c = mysqli_fetch_assoc($cRes)) {
    $coursesList[] = $c;
}

$sql = "
    SELECT e.*, 
           u.student_id_number, u.first_name, u.middle_name, u.last_name, u.course as student_course, u.section,
           c.course_code, c.course_title, c.units, c.instructor
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.status = 'Enrolled'
";

if (is_faculty()) {
    $facLastName = mysqli_real_escape_string($conn, $currentUser['last_name']);
    $sql .= " AND c.instructor LIKE '%$facLastName%'";
}

if ($filterCourseId > 0) {
    $sql .= " AND e.course_id = $filterCourseId";
}

if (!empty($filterStudent)) {
    $safeStudent = mysqli_real_escape_string($conn, $filterStudent);
    $sql .= " AND (u.student_id_number LIKE '%$safeStudent%' OR u.first_name LIKE '%$safeStudent%' OR u.last_name LIKE '%$safeStudent%')";
}

if ($filterRemarks != 'All' && in_array($filterRemarks, array('Passed', 'Failed', 'In Progress', 'Incomplete'))) {
    $safeRemarks = mysqli_real_escape_string($conn, $filterRemarks);
    $sql .= " AND e.remarks = '$safeRemarks'";
}

$sql .= " ORDER BY c.course_code ASC, u.last_name ASC";

$enrollments = array();
$eRes = mysqli_query($conn, $sql);
if ($eRes) {
    while ($row = mysqli_fetch_assoc($eRes)) {
        $enrollments[] = $row;
    }
}


$totalCount = count($enrollments);
$passedCount = 0;
$failedCount = 0;
$inProgressCount = 0;

foreach ($enrollments as $e) {
    if ($e['remarks'] == 'Passed') {
        $passedCount++;
    } elseif ($e['remarks'] == 'Failed') {
        $failedCount++;
    } else {
        $inProgressCount++;
    }
}

$page_title = "Grade Encoding - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Academic Grade Encoding</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Philippine College of Northwestern Luzon &bull; 4 Terms (Prelim, Midterm, Semi-Final, Final) &bull; Passing Mark: 75.00%
            </p>
        </div>
    </div>

    <?php render_flash(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border-2 border-blue-100 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Enrolled</span>
            <div class="text-2xl font-black text-slate-900 mt-1"><?= $totalCount ?></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border-2 border-emerald-200 bg-emerald-50/20 shadow-xs">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Passed (≥75%)</span>
            <div class="text-2xl font-black text-emerald-600 mt-1"><?= $passedCount ?></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border-2 border-rose-200 bg-rose-50/20 shadow-xs">
            <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Failed (&lt;75%)</span>
            <div class="text-2xl font-black text-rose-600 mt-1"><?= $failedCount ?></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border-2 border-amber-200 bg-amber-50/20 shadow-xs">
            <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">In Progress</span>
            <div class="text-2xl font-black text-amber-600 mt-1"><?= $inProgressCount ?></div>
        </div>
    </div>

    
    <form method="GET" action="<?= base_url('admin/grades.php') ?>" class="bg-white p-4 rounded-2xl border-2 border-blue-100 shadow-xs mb-6 flex flex-col sm:flex-row items-center gap-3">
        <div class="w-full sm:w-64">
            <select name="course_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="">All Subjects / Offerings</option>
                <?php foreach ($coursesList as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filterCourseId == $c['id'] ? 'selected' : '' ?>>
                        <?= sanitize($c['course_code']) ?> - <?= sanitize($c['course_title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="w-full sm:w-44">
            <select name="remarks" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="All" <?= $filterRemarks == 'All' ? 'selected' : '' ?>>All Remarks</option>
                <option value="Passed" <?= $filterRemarks == 'Passed' ? 'selected' : '' ?>>Passed</option>
                <option value="Failed" <?= $filterRemarks == 'Failed' ? 'selected' : '' ?>>Failed</option>
                <option value="In Progress" <?= $filterRemarks == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            </select>
        </div>

        <div class="relative w-full sm:w-64">
            <input type="text" name="student" value="<?= sanitize($filterStudent) ?>" placeholder="Filter student name or ID..."
                   class="w-full pl-3 pr-8 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            <button type="submit" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-700">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </button>
        </div>

        <?php if ($filterCourseId || !empty($filterStudent) || $filterRemarks != 'All'): ?>
            <a href="<?= base_url('admin/grades.php') ?>" class="text-xs text-blue-700 font-semibold hover:underline whitespace-nowrap">
                Reset Filters
            </a>
        <?php endif; ?>
    </form>

    
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Student</th>
                        <th class="px-5 py-3.5 font-bold">Subject</th>
                        <th class="px-3 py-3.5 font-bold text-center">Prelim</th>
                        <th class="px-3 py-3.5 font-bold text-center">Midterm</th>
                        <th class="px-3 py-3.5 font-bold text-center">Semi-Final</th>
                        <th class="px-3 py-3.5 font-bold text-center">Final</th>
                        <th class="px-3 py-3.5 font-bold text-center">Average</th>
                        <th class="px-4 py-3.5 font-bold text-center">Remarks</th>
                        <th class="px-5 py-3.5 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="grades-table-body">
                    <?php if (empty($enrollments)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clipboard-user text-3xl mb-2 text-slate-300"></i>
                                <p class="font-medium">No student enrollment records found.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($enrollments as $e): ?>
                            <?php 
                                $studentName = $e['last_name'] . ', ' . $e['first_name'] . ($e['middle_name'] ? ' ' . $e['middle_name'] : '');
                            ?>
                            <tr class="hover:bg-blue-50/30 transition border-b border-slate-100" id="grade-row-<?= $e['id'] ?>">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900"><?= sanitize($studentName) ?></div>
                                    <div class="text-xs text-slate-400 font-mono"><?= sanitize($e['student_id_number']) ?> &bull; <?= sanitize($e['student_course']) ?> (<?= sanitize($e['section'] ? $e['section'] : '2-A') ?>)</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-blue-700 font-mono"><?= sanitize($e['course_code']) ?></div>
                                    <div class="text-xs text-slate-500 truncate max-w-[170px]"><?= sanitize($e['course_title']) ?></div>
                                </td>
                                <td class="px-3 py-4 text-center font-mono text-xs cell-prelim">
                                    <?= $e['prelim'] !== null ? number_format($e['prelim'], 2) : '<span class="text-slate-300">--</span>' ?>
                                </td>
                                <td class="px-3 py-4 text-center font-mono text-xs cell-midterm">
                                    <?= $e['midterm'] !== null ? number_format($e['midterm'], 2) : '<span class="text-slate-300">--</span>' ?>
                                </td>
                                <td class="px-3 py-4 text-center font-mono text-xs cell-semifinal">
                                    <?= $e['semifinal'] !== null ? number_format($e['semifinal'], 2) : '<span class="text-slate-300">--</span>' ?>
                                </td>
                                <td class="px-3 py-4 text-center font-mono text-xs cell-final">
                                    <?= $e['final'] !== null ? number_format($e['final'], 2) : '<span class="text-slate-300">--</span>' ?>
                                </td>
                                <td class="px-3 py-4 text-center font-mono font-bold text-xs cell-average <?= ($e['final_average'] !== null && $e['final_average'] < 75) ? 'text-rose-600' : 'text-slate-900' ?>">
                                    <?= $e['final_average'] !== null ? number_format($e['final_average'], 2) . '%' : '<span class="text-slate-300 font-normal">--</span>' ?>
                                </td>
                                <td class="px-4 py-4 text-center cell-remarks">
                                    <?= get_remarks_badge($e['remarks']) ?>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button onclick='openGradeModal(<?= json_encode(array(
                                        "id" => $e["id"],
                                        "student_name" => $studentName,
                                        "student_id" => $e["student_id_number"],
                                        "course_code" => $e["course_code"],
                                        "course_title" => $e["course_title"],
                                        "prelim" => $e["prelim"],
                                        "midterm" => $e["midterm"],
                                        "semifinal" => $e["semifinal"],
                                        "final" => $e["final"],
                                        "final_average" => $e["final_average"],
                                        "remarks" => $e["remarks"]
                                    )) ?>)' class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 transition">
                                        <i class="fa-solid fa-pen mr-1 text-[10px]"></i>Encode
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="gradeModal" class="hidden modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative my-8 border-2 border-blue-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Encode Term Grades</h3>
                <p class="text-xs text-slate-400 mt-0.5">Prelim, Midterm, Semi-Final, Final (0 - 100%)</p>
            </div>
            <button onclick="closeModal('gradeModal')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form id="grade-encoding-form" onsubmit="submitGradeForm(event)" class="mt-4 space-y-4">
            <input type="hidden" id="modal-enrollment-id" name="enrollment_id">

            
            <div class="bg-blue-50/50 p-3.5 rounded-2xl border border-blue-100">
                <div class="text-[10px] text-blue-700 uppercase font-bold tracking-wider">Student & Subject Details</div>
                <div class="font-bold text-slate-900 text-sm mt-0.5" id="modal-student-name">Student Name</div>
                <div class="text-xs text-blue-700 font-semibold" id="modal-course-name">IT201 - Web Application Development</div>
            </div>

            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Prelim Grade (0–100)</label>
                    <input type="number" step="0.01" min="0" max="100" id="modal-prelim" name="prelim" placeholder="e.g. 88.50"
                           class="w-full px-3 py-2 text-sm font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none grade-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Midterm Grade (0–100)</label>
                    <input type="number" step="0.01" min="0" max="100" id="modal-midterm" name="midterm" placeholder="e.g. 90.00"
                           class="w-full px-3 py-2 text-sm font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none grade-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semi-Final (0–100)</label>
                    <input type="number" step="0.01" min="0" max="100" id="modal-semifinal" name="semifinal" placeholder="e.g. 92.50"
                           class="w-full px-3 py-2 text-sm font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none grade-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Final Term (0–100)</label>
                    <input type="number" step="0.01" min="0" max="100" id="modal-final" name="final" placeholder="e.g. 91.00"
                           class="w-full px-3 py-2 text-sm font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none grade-input">
                </div>
            </div>

            
            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-blue-900 uppercase tracking-wider block">Computed Average</span>
                    <span class="text-2xl font-black text-slate-900 mt-0.5 block font-mono" id="modal-calculated-avg">--</span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-blue-900 uppercase tracking-wider block mb-1">Academic Status</span>
                    <div id="modal-remarks-badge">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Not Graded</span>
                    </div>
                </div>
            </div>

            
            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('gradeModal')" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" id="save-grades-btn" class="px-5 py-2 text-xs font-bold rounded-xl bg-blue-700 text-white hover:bg-blue-800 shadow-sm flex items-center">
                    <span>Save & Update Grade</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function recalculateGradeAverage() {
        var p = parseFloat(document.getElementById('modal-prelim').value);
        var m = parseFloat(document.getElementById('modal-midterm').value);
        var s = parseFloat(document.getElementById('modal-semifinal').value);
        var f = parseFloat(document.getElementById('modal-final').value);

        var scores = [];
        if (!isNaN(p)) scores.push(p);
        if (!isNaN(m)) scores.push(m);
        if (!isNaN(s)) scores.push(s);
        if (!isNaN(f)) scores.push(f);

        var avgEl = document.getElementById('modal-calculated-avg');
        var badgeEl = document.getElementById('modal-remarks-badge');

        if (scores.length === 0) {
            avgEl.textContent = '--';
            badgeEl.innerHTML = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Not Graded</span>';
            return;
        }

        var avg = scores.reduce(function(a, b) { return a + b; }, 0) / scores.length;
        avgEl.textContent = avg.toFixed(2) + '%';

        if (scores.length === 4) {
            if (avg >= 75) {
                badgeEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500"></span>Passed</span>';
            } else {
                badgeEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-rose-500"></span>Failed</span>';
            }
        } else {
            badgeEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-amber-500"></span>In Progress</span>';
        }
    }

    function openGradeModal(data) {
        document.getElementById('modal-enrollment-id').value = data.id;
        document.getElementById('modal-student-name').textContent = data.student_name + ' (' + data.student_id + ')';
        document.getElementById('modal-course-name').textContent = data.course_code + ' - ' + data.course_title;

        document.getElementById('modal-prelim').value = data.prelim !== null ? data.prelim : '';
        document.getElementById('modal-midterm').value = data.midterm !== null ? data.midterm : '';
        document.getElementById('modal-semifinal').value = data.semifinal !== null ? data.semifinal : '';
        document.getElementById('modal-final').value = data.final !== null ? data.final : '';

        recalculateGradeAverage();
        openModal('gradeModal');
    }

    ['modal-prelim', 'modal-midterm', 'modal-semifinal', 'modal-final'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', recalculateGradeAverage);
        }
    });

    async function submitGradeForm(e) {
        e.preventDefault();
        var btn = document.getElementById('save-grades-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...';

        var payload = {
            enrollment_id: document.getElementById('modal-enrollment-id').value,
            prelim: document.getElementById('modal-prelim').value,
            midterm: document.getElementById('modal-midterm').value,
            semifinal: document.getElementById('modal-semifinal').value,
            final: document.getElementById('modal-final').value
        };

        try {
            var res = await fetch('<?= base_url('api/update_grade.php') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            var result = await res.json();

            if (res.ok && result.status == 'success') {
                showToast(result.message, 'success');
                closeModal('gradeModal');

                var row = document.getElementById('grade-row-' + payload.enrollment_id);
                if (row) {
                    row.querySelector('.cell-prelim').textContent = result.data.prelim;
                    row.querySelector('.cell-midterm').textContent = result.data.midterm;
                    row.querySelector('.cell-semifinal').textContent = result.data.semifinal;
                    row.querySelector('.cell-final').textContent = result.data.final;
                    row.querySelector('.cell-average').textContent = result.data.final_average;
                    row.querySelector('.cell-remarks').innerHTML = result.data.remarks_badge;

                    row.classList.add('bg-blue-100');
                    setTimeout(function() { row.classList.remove('bg-blue-100'); }, 1500);
                }
            } else {
                showToast(result.message || 'Error updating grades', 'error');
            }
        } catch (err) {
            showToast('Network error while saving grades', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Save & Update Grade</span>';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
