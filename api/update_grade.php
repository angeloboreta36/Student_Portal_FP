<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_admin() && !is_faculty()) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized. Administrator or Faculty privileges required.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('status' => 'error', 'message' => 'Method not allowed'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$enrollmentId = isset($input['enrollment_id']) ? intval($input['enrollment_id']) : 0;
$prelim = (isset($input['prelim']) && $input['prelim'] !== '') ? floatval($input['prelim']) : null;
$midterm = (isset($input['midterm']) && $input['midterm'] !== '') ? floatval($input['midterm']) : null;
$semifinal = (isset($input['semifinal']) && $input['semifinal'] !== '') ? floatval($input['semifinal']) : null;
$final = (isset($input['final']) && $input['final'] !== '') ? floatval($input['final']) : null;

if (!$enrollmentId) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid enrollment identifier.'));
    exit;
}


$terms = array('Prelim' => $prelim, 'Midterm' => $midterm, 'Semi-Final' => $semifinal, 'Final' => $final);
foreach ($terms as $termName => $val) {
    if ($val !== null && ($val < 0 || $val > 100)) {
        http_response_code(422);
        echo json_encode(array('status' => 'error', 'message' => "$termName grade must be between 0.00 and 100.00%"));
        exit;
    }
}


$summary = compute_grade_summary($prelim, $midterm, $semifinal, $final);
$avg = $summary['average'];
$remarks = $summary['remarks'];

$prelimSql = ($prelim !== null) ? $prelim : "NULL";
$midtermSql = ($midterm !== null) ? $midterm : "NULL";
$semifinalSql = ($semifinal !== null) ? $semifinal : "NULL";
$finalSql = ($final !== null) ? $final : "NULL";
$avgSql = ($avg !== null) ? $avg : "NULL";
$remSql = mysqli_real_escape_string($conn, $remarks);

$updateQuery = "
    UPDATE enrollments 
    SET prelim = $prelimSql, 
        midterm = $midtermSql, 
        semifinal = $semifinalSql, 
        final = $finalSql, 
        final_average = $avgSql, 
        remarks = '$remSql' 
    WHERE id = $enrollmentId
";

$ok = mysqli_query($conn, $updateQuery);

if ($ok) {
    $adminId = intval($_SESSION['user_id']);
    log_activity($adminId, 'GRADE_UPDATE', "Updated grades for enrollment ID #$enrollmentId (Average: " . ($avg !== null ? $avg . '%' : 'N/A') . " - $remarks)");

    echo json_encode(array(
        'status' => 'success',
        'message' => 'Grades updated successfully!',
        'data' => array(
            'enrollment_id' => $enrollmentId,
            'prelim' => $prelim !== null ? number_format($prelim, 2) : '--',
            'midterm' => $midterm !== null ? number_format($midterm, 2) : '--',
            'semifinal' => $semifinal !== null ? number_format($semifinal, 2) : '--',
            'final' => $final !== null ? number_format($final, 2) : '--',
            'final_average' => $avg !== null ? number_format($avg, 2) . '%' : '--',
            'remarks' => $remarks,
            'remarks_badge' => get_remarks_badge($remarks)
        )
    ));
} else {
    http_response_code(500);
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Database error while saving grades: ' . mysqli_error($conn)
    ));
}
