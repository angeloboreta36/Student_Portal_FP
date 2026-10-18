<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_student()) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized. Only students may enlist in subjects.'));
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

$action = isset($input['action']) ? $input['action'] : '';
$courseId = isset($input['course_id']) ? intval($input['course_id']) : 0;
$studentId = intval($_SESSION['user_id']);

if (!$courseId) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid subject selected.'));
    exit;
}

if ($action === 'enroll') {
    
    $cQuery = "SELECT * FROM courses WHERE id = $courseId LIMIT 1";
    $cResult = mysqli_query($conn, $cQuery);
    $course = $cResult ? mysqli_fetch_assoc($cResult) : null;

    if (!$course) {
        http_response_code(404);
        echo json_encode(array('status' => 'error', 'message' => 'Subject not found.'));
        exit;
    }

    
    $eQuery = "SELECT * FROM enrollments WHERE student_id = $studentId AND course_id = $courseId LIMIT 1";
    $eResult = mysqli_query($conn, $eQuery);
    $existing = $eResult ? mysqli_fetch_assoc($eResult) : null;

    if ($existing) {
        if ($existing['status'] === 'Enrolled') {
            http_response_code(409);
            echo json_encode(array('status' => 'error', 'message' => 'You are already enrolled in this subject.'));
            exit;
        } else {
            
            $enrollId = intval($existing['id']);
            mysqli_query($conn, "UPDATE enrollments SET status = 'Enrolled', enrollment_date = NOW() WHERE id = $enrollId");
            log_activity($studentId, 'ENLISTMENT', "Re-enrolled in " . $course['course_code'] . " - " . $course['course_title']);
            echo json_encode(array('status' => 'success', 'message' => "Successfully re-enrolled in " . $course['course_code'] . "!"));
            exit;
        }
    }

    
    $countRes = mysqli_query($conn, "SELECT COUNT(*) FROM enrollments WHERE course_id = $courseId AND status = 'Enrolled'");
    $rowCount = $countRes ? mysqli_fetch_row($countRes) : array(0);
    $currentCount = intval($rowCount[0]);

    if ($currentCount >= intval($course['capacity'])) {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'message' => 'This class section has reached maximum student capacity.'));
        exit;
    }

    
    $stmt = mysqli_prepare($conn, "INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, 'Enrolled')");
    mysqli_stmt_bind_param($stmt, "ii", $studentId, $courseId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    log_activity($studentId, 'ENLISTMENT', "Enrolled in " . $course['course_code'] . " - " . $course['course_title']);

    echo json_encode(array(
        'status' => 'success',
        'message' => "Enrolled in " . $course['course_code'] . " (" . $course['course_title'] . ") successfully!",
        'course_id' => $courseId,
        'enrolled_count' => $currentCount + 1
    ));
    exit;
} elseif ($action === 'drop') {
    
    $findQuery = "SELECT e.*, c.course_code FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = $studentId AND e.course_id = $courseId LIMIT 1";
    $findRes = mysqli_query($conn, $findQuery);
    $enrollment = $findRes ? mysqli_fetch_assoc($findRes) : null;

    if (!$enrollment) {
        http_response_code(404);
        echo json_encode(array('status' => 'error', 'message' => 'Enrollment record not found.'));
        exit;
    }

    
    if ($enrollment['prelim'] !== null || $enrollment['midterm'] !== null || $enrollment['semifinal'] !== null || $enrollment['final'] !== null) {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'message' => 'Cannot drop this subject online because faculty has already submitted term grades. Please coordinate with the Registrar.'));
        exit;
    }

    $enrollId = intval($enrollment['id']);
    mysqli_query($conn, "DELETE FROM enrollments WHERE id = $enrollId");

    log_activity($studentId, 'DROP_COURSE', "Dropped subject " . $enrollment['course_code']);

    echo json_encode(array(
        'status' => 'success',
        'message' => "Subject " . $enrollment['course_code'] . " has been dropped successfully.",
        'course_id' => $courseId
    ));
    exit;
} else {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid enlistment action.'));
    exit;
}
