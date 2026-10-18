<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$department = isset($_GET['department']) ? trim($_GET['department']) : '';

$sql = "
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Enrolled') as enrolled_count
    FROM courses c WHERE 1=1
";

if (!empty($query)) {
    $qEsc = mysqli_real_escape_string($conn, $query);
    $sql .= " AND (c.course_code LIKE '%$qEsc%' OR c.course_title LIKE '%$qEsc%' OR c.instructor LIKE '%$qEsc%')";
}

if (!empty($department) && $department !== 'All') {
    $deptEsc = mysqli_real_escape_string($conn, $department);
    $sql .= " AND c.department = '$deptEsc'";
}

$sql .= " ORDER BY c.course_code ASC";

$courses = array();
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

echo json_encode(array(
    'status' => 'success',
    'count' => count($courses),
    'data' => $courses
));
