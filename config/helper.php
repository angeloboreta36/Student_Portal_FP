<?php


require_once __DIR__ . '/database.php';


function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}


function compute_grade_summary($prelim, $midterm, $semifinal, $final) {
    $scores = array();
    if ($prelim !== null && $prelim !== '' && is_numeric($prelim)) {
        $scores[] = floatval($prelim);
    }
    if ($midterm !== null && $midterm !== '' && is_numeric($midterm)) {
        $scores[] = floatval($midterm);
    }
    if ($semifinal !== null && $semifinal !== '' && is_numeric($semifinal)) {
        $scores[] = floatval($semifinal);
    }
    if ($final !== null && $final !== '' && is_numeric($final)) {
        $scores[] = floatval($final);
    }

    if (empty($scores)) {
        $res = array();
        $res['average'] = null;
        $res['remarks'] = 'In Progress';
        $res['status_class'] = 'bg-amber-100 text-amber-800 border-amber-300';
        $res['is_complete'] = false;
        return $res;
    }

    $count = count($scores);
    $avg = round(array_sum($scores) / $count, 2);
    $isComplete = ($count == 4);

    if ($isComplete) {
        if ($avg >= 75.00) {
            $remarks = 'Passed';
            $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
        } else {
            $remarks = 'Failed';
            $statusClass = 'bg-rose-100 text-rose-800 border-rose-300';
        }
    } else {
        $remarks = 'In Progress';
        $statusClass = 'bg-amber-100 text-amber-800 border-amber-300';
    }

    $res = array();
    $res['average'] = $avg;
    $res['remarks'] = $remarks;
    $res['status_class'] = $statusClass;
    $res['is_complete'] = $isComplete;
    return $res;
}


function get_remarks_badge($remarks) {
    if ($remarks == 'Passed') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                  <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500"></span>Passed
                </span>';
    } elseif ($remarks == 'Failed') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">
                  <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-rose-500"></span>Failed
                </span>';
    } elseif ($remarks == 'Incomplete') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-300">
                  <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-purple-500"></span>Incomplete
                </span>';
    } else {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                  <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-amber-500"></span>In Progress
                </span>';
    }
}


function set_flash($type, $message) {
    $_SESSION['flash'] = array(
        'type' => $type,
        'message' => $message
    );
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}


function render_flash() {
    $flash = get_flash();
    if (!$flash) return;

    $colorClass = 'bg-blue-50 border-blue-400 text-blue-800';
    if ($flash['type'] == 'success') {
        $colorClass = 'bg-emerald-50 border-emerald-400 text-emerald-800';
    } elseif ($flash['type'] == 'error') {
        $colorClass = 'bg-rose-50 border-rose-400 text-rose-800';
    } elseif ($flash['type'] == 'warning') {
        $colorClass = 'bg-amber-50 border-amber-400 text-amber-800';
    }

    echo '<div class="mb-6 p-4 rounded-xl border flex items-center justify-between shadow-sm ' . $colorClass . '" role="alert">';
    echo '<div class="flex items-center space-x-3">';
    echo '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>';
    echo '<span class="text-sm font-medium">' . sanitize($flash['message']) . '</span>';
    echo '</div>';
    echo '<button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-700 text-lg leading-none">&times;</button>';
    echo '</div>';
}


function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function verify_csrf($token) {
    if (!empty($_SESSION['csrf_token'])) {
        return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
    }
    return true;
}


function log_activity($userId, $action, $details = null) {
    global $conn;
    if (!$conn) return;

    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    $userIdVal = $userId ? intval($userId) : "NULL";
    $safeAction = mysqli_real_escape_string($conn, $action);
    $safeDetails = $details ? "'" . mysqli_real_escape_string($conn, $details) . "'" : "NULL";
    $safeIp = mysqli_real_escape_string($conn, $ip);

    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES ($userIdVal, '$safeAction', $safeDetails, '$safeIp')";
    mysqli_query($conn, $sql);
}


function base_url($path = '') {
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base = rtrim($scriptName, '/');
    if (in_array(basename($base), array('admin', 'student', 'api', 'faculty'))) {
        $base = dirname($base);
    }
    $base = ($base == '/' || $base == '\\') ? '' : $base;
    return $base . '/' . ltrim($path, '/');
}
