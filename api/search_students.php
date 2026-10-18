<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';


if (!is_admin()) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized'));
    exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$course = isset($_GET['course']) ? trim($_GET['course']) : '';
$yearLevel = isset($_GET['year_level']) ? trim($_GET['year_level']) : '';

$sql = "
    SELECT u.id, u.student_id_number, u.first_name, u.middle_name, u.last_name, u.email, u.course, u.year_level, u.section, u.contact_number, u.status,
    (SELECT COUNT(*) FROM enrollments e WHERE e.student_id = u.id AND e.status = 'Enrolled') as enrolled_subjects
    FROM users u WHERE u.role = 'student'
";

if (!empty($query)) {
    $qEsc = mysqli_real_escape_string($conn, $query);
    $sql .= " AND (u.student_id_number LIKE '%$qEsc%' OR u.first_name LIKE '%$qEsc%' OR u.last_name LIKE '%$qEsc%' OR u.email LIKE '%$qEsc%' OR CONCAT(u.first_name, ' ', u.last_name) LIKE '%$qEsc%')";
}

if (!empty($course) && $course !== 'All') {
    $cEsc = mysqli_real_escape_string($conn, $course);
    $sql .= " AND u.course = '$cEsc'";
}

if (!empty($yearLevel) && $yearLevel !== 'All') {
    $yEsc = mysqli_real_escape_string($conn, $yearLevel);
    $sql .= " AND u.year_level = '$yEsc'";
}

$sql .= " ORDER BY u.last_name ASC, u.first_name ASC";

$students = array();
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
}

echo json_encode(array(
    'status' => 'success',
    'count' => count($students),
    'data' => $students
));
