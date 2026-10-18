<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();
$studentSession = current_user();
$studentId = intval($studentSession['id']);


$userRes = mysqli_query($conn, "SELECT * FROM users WHERE id = $studentId LIMIT 1");
$student = ($userRes && mysqli_num_rows($userRes) > 0) ? mysqli_fetch_assoc($userRes) : $studentSession;

$middleNamePart = (!empty($student['middle_name'])) ? (' ' . trim($student['middle_name'])) : '';
$studentFormattedName = (isset($student['last_name']) ? $student['last_name'] : '') . ', ' . (isset($student['first_name']) ? $student['first_name'] : '') . $middleNamePart;


$enrollments = array();
$query = "
    SELECT e.*, c.course_code, c.course_title, c.units, c.instructor, c.schedule
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
$weightedGradeSum = 0;
$gradedUnits = 0;
$passedCount = 0;
$failedCount = 0;
$incompleteCount = 0;

foreach ($enrollments as $e) {
    $units = intval($e['units']);
    $totalUnits += $units;

    if ($e['final_average'] !== null) {
        $weightedGradeSum += (floatval($e['final_average']) * $units);
        $gradedUnits += $units;
        if ($e['remarks'] == 'Passed') {
            $passedCount++;
        } elseif ($e['remarks'] == 'Failed') {
            $failedCount++;
        }
    } else {
        $incompleteCount++;
    }
}

$gwa = ($gradedUnits > 0) ? round($weightedGradeSum / $gradedUnits, 2) : null;

$page_title = "Certificate of Grades - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    
    <div class="no-print mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl border-2 border-blue-100 shadow-xs">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Official Certificate of Grades</h1>
            <p class="text-xs text-slate-500">Official academic transcript generated via PCNL Portal</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-sm transition">
                <i class="fa-solid fa-print mr-2"></i>Print Official Grade Slip
            </button>
            <a href="<?= base_url('student/dashboard.php') ?>" class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    
    <div class="print-card bg-white p-6 sm:p-10 rounded-2xl border-2 border-slate-200 shadow-xs">
        
        <div class="flex flex-col sm:flex-row items-center justify-between border-b-2 border-blue-900 pb-5 mb-6 text-center sm:text-left gap-4">
            <div class="flex items-center space-x-4">
                <img src="<?= base_url('assets/images/pcnl-logo.png') ?>" alt="PCNL Official Logo" class="w-20 h-20 sm:w-24 sm:h-24 object-contain">
                <div>
                    <h2 class="text-xs sm:text-sm font-semibold tracking-widest text-slate-600 uppercase">Republic of the Philippines</h2>
                    <h1 class="text-lg sm:text-2xl font-black text-blue-950 tracking-tight leading-tight uppercase font-serif">
                        Philippine College of Northwestern Luzon
                    </h1>
                    <p class="text-xs italic text-blue-800 font-serif font-bold">"Qualitas et Excellentiam" &bull; MMVII</p>
                    <p class="text-[11px] text-slate-600">San Antonio, Agoo, La Union, Philippines &bull; Office of the College Registrar</p>
                </div>
            </div>
            <div class="text-center sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200">
                <div class="inline-block px-3 py-1 bg-blue-900 text-white text-[10px] font-black uppercase tracking-widest rounded mb-1">
                    Official Grade Slip
                </div>
                <div class="text-[11px] font-mono text-slate-700">Date: <?= date('F j, Y') ?></div>
                <div class="text-[10px] text-slate-600">A.Y. 2024-2025 &bull; 1st Semester</div>
            </div>
        </div>

        
        <div class="bg-slate-50 rounded-xl p-4 sm:p-5 mb-6 border border-slate-200 text-xs text-slate-800 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-2 gap-x-4">
            <div>
                <span class="text-slate-600 font-medium">Student ID:</span>
                <span class="font-bold text-slate-900 font-mono ml-1"><?= sanitize($student['student_id_number']) ?></span>
            </div>
            <div>
                <span class="text-slate-600 font-medium">Student Name:</span>
                <span class="font-bold text-slate-900 uppercase ml-1"><?= sanitize($studentFormattedName) ?></span>
            </div>
            <div>
                <span class="text-slate-600 font-medium">Program / Course:</span>
                <span class="font-bold text-blue-900 ml-1"><?= sanitize($student['course'] ? $student['course'] : 'BSIT') ?></span>
            </div>
            <div>
                <span class="text-slate-600 font-medium">Year Level:</span>
                <span class="font-bold text-slate-900 ml-1"><?= sanitize($student['year_level'] ? $student['year_level'] : '2nd Year') ?></span>
            </div>
            <div>
                <span class="text-slate-600 font-medium">Section / Block:</span>
                <span class="font-bold text-slate-900 ml-1"><?= sanitize($student['section'] ? $student['section'] : '2-A') ?></span>
            </div>
            <div>
                <span class="text-slate-600 font-medium">Contact Number:</span>
                <span class="font-mono text-slate-800 ml-1"><?= sanitize($student['contact_number'] ? $student['contact_number'] : 'N/A') ?></span>
            </div>
        </div>

        
        <div class="overflow-x-auto border border-slate-300 rounded-lg mb-6">
            <table class="w-full text-left text-xs">
                <thead class="bg-blue-900 text-white uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="px-3 py-2.5">Course Code</th>
                        <th class="px-3 py-2.5">Descriptive Title</th>
                        <th class="px-2 py-2.5 text-center">Units</th>
                        <th class="px-2 py-2.5 text-center">Prelim (20%)</th>
                        <th class="px-2 py-2.5 text-center">Midterm (20%)</th>
                        <th class="px-2 py-2.5 text-center">Semi (20%)</th>
                        <th class="px-2 py-2.5 text-center">Final (40%)</th>
                        <th class="px-2 py-2.5 text-center bg-blue-950 font-black">Average</th>
                        <th class="px-3 py-2.5 text-center">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($enrollments)): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-400">No subject records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($enrollments as $e): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2.5 font-bold font-mono text-blue-900"><?= sanitize($e['course_code']) ?></td>
                                <td class="px-3 py-2.5 text-slate-800 font-medium"><?= sanitize($e['course_title']) ?></td>
                                <td class="px-2 py-2.5 text-center font-mono text-slate-700"><?= intval($e['units']) ?></td>
                                <td class="px-2 py-2.5 text-center font-mono text-slate-700">
                                    <?= $e['prelim'] !== null ? number_format($e['prelim'], 2) : '--' ?>
                                </td>
                                <td class="px-2 py-2.5 text-center font-mono text-slate-700">
                                    <?= $e['midterm'] !== null ? number_format($e['midterm'], 2) : '--' ?>
                                </td>
                                <td class="px-2 py-2.5 text-center font-mono text-slate-700">
                                    <?= $e['semifinal'] !== null ? number_format($e['semifinal'], 2) : '--' ?>
                                </td>
                                <td class="px-2 py-2.5 text-center font-mono text-slate-700">
                                    <?= $e['final'] !== null ? number_format($e['final'], 2) : '--' ?>
                                </td>
                                <td class="px-2 py-2.5 text-center font-mono font-bold text-slate-900 bg-blue-50/50">
                                    <?= $e['final_average'] !== null ? number_format($e['final_average'], 2) . '%' : '--' ?>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <?= get_remarks_badge($e['remarks']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300 text-slate-800">
                    <tr>
                        <td colspan="2" class="px-3 py-2.5 text-right uppercase tracking-wider text-[11px]">Total Units / General Weighted Average:</td>
                        <td class="px-2 py-2.5 text-center font-mono"><?= $totalUnits ?></td>
                        <td colspan="4" class="px-2 py-2.5 text-right text-slate-500 font-normal italic text-[11px]">Formula: 20% + 20% + 20% + 40%</td>
                        <td class="px-2 py-2.5 text-center font-mono text-sm text-blue-900 font-black bg-blue-100/60">
                            <?= $gwa !== null ? number_format($gwa, 2) . '%' : '--' ?>
                        </td>
                        <td class="px-3 py-2.5 text-center text-[11px] font-bold <?= ($gwa !== null && $gwa >= 75) ? 'text-emerald-700' : 'text-slate-500' ?>">
                            <?= $gwa !== null ? ($gwa >= 75 ? 'PASSED' : 'FAILED') : 'PENDING' ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] text-slate-600 p-4 rounded-xl bg-slate-50 border border-slate-200 mb-8">
            <div>
                <span class="font-bold text-slate-800 uppercase block mb-1">Grading System (PCNL Standard):</span>
                <p>Grades are computed on a percentage scale (0–100%).</p>
                <p class="mt-0.5">Passing grade is <strong class="text-slate-800">75.00%</strong>.</p>
                <p class="mt-0.5">Component Weights: Prelim 20% &bull; Midterm 20% &bull; Semi-Final 20% &bull; Final Exam 40%.</p>
            </div>
            <div>
                <span class="font-bold text-slate-800 uppercase block mb-1">Academic Standing Legend:</span>
                <p><span class="font-bold text-emerald-800">Passed (75% - 100%):</span> Credit granted for course.</p>
                <p><span class="font-bold text-rose-800">Failed (Below 75%):</span> Re-enrollment required.</p>
                <p><span class="font-bold text-slate-700">Incomplete (INC):</span> Subject components pending submission.</p>
            </div>
        </div>

        
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 pt-6 text-center text-xs">
            <div>
                <div class="border-b border-slate-400 pb-1 mb-1 font-bold text-slate-900 uppercase">
                    <?= sanitize($student['first_name'] . $middleNamePart . ' ' . $student['last_name']) ?>
                </div>
                <div class="text-[10px] text-slate-500">Student's Signature</div>
            </div>
            <div>
                <div class="border-b border-slate-400 pb-1 mb-1 font-bold text-slate-900 uppercase">
                    Angelo L. Boreta
                </div>
                <div class="text-[10px] text-slate-500">System Administrator / IT Faculty</div>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <div class="border-b border-slate-400 pb-1 mb-1 font-bold text-slate-900 uppercase">
                    Office of the Registrar
                </div>
                <div class="text-[10px] text-slate-500">Authorized College Signatory</div>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-200 text-center text-[10px] text-slate-400">
            Philippine College of Northwestern Luzon (PCNL) &bull; Student Information & Grade Management System &bull; System Verified Document
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background-color: #ffffff !important; }
    nav, footer { display: none !important; }
    .print-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
