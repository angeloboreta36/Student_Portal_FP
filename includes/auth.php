<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return array(
        'id'                => $_SESSION['user_id'],
        'student_id_number' => isset($_SESSION['student_id_number']) ? $_SESSION['student_id_number'] : '',
        'name'              => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User',
        'first_name'        => isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '',
        'middle_name'       => isset($_SESSION['middle_name']) ? $_SESSION['middle_name'] : '',
        'last_name'         => isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '',
        'email'             => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '',
        'role'              => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'student',
        'course'            => isset($_SESSION['course']) ? $_SESSION['course'] : '',
        'year_level'        => isset($_SESSION['year_level']) ? $_SESSION['year_level'] : '',
        'section'           => isset($_SESSION['section']) ? $_SESSION['section'] : '',
        'contact_number'    => isset($_SESSION['contact_number']) ? $_SESSION['contact_number'] : '',
        'profile_photo'     => isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : null,
    );
}

function is_admin() {
    return is_logged_in() && ($_SESSION['user_role'] === 'admin');
}

function is_faculty() {
    return is_logged_in() && ($_SESSION['user_role'] === 'faculty');
}

function is_student() {
    return is_logged_in() && ($_SESSION['user_role'] === 'student');
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to access this portal section.');
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'Access denied. Administrator privileges required.');
        if (is_faculty()) {
            header('Location: ' . base_url('faculty/dashboard.php'));
        } else {
            header('Location: ' . base_url('student/dashboard.php'));
        }
        exit;
    }
}

function require_faculty() {
    require_login();
    if (!is_faculty() && !is_admin()) {
        set_flash('error', 'Access denied. Faculty privileges required.');
        header('Location: ' . base_url('student/dashboard.php'));
        exit;
    }
}

function require_student() {
    require_login();
    if (!is_student()) {
        set_flash('error', 'Access denied. Student portal only.');
        if (is_faculty()) {
            header('Location: ' . base_url('faculty/dashboard.php'));
        } else {
            header('Location: ' . base_url('admin/dashboard.php'));
        }
        exit;
    }
}

function login_user($user) {
    session_regenerate_id(true);

    $_SESSION['user_id']           = $user['id'];
    $_SESSION['student_id_number'] = isset($user['student_id_number']) ? $user['student_id_number'] : '';
    $_SESSION['user_name']         = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['first_name']        = $user['first_name'];
    $_SESSION['middle_name']       = isset($user['middle_name']) ? $user['middle_name'] : '';
    $_SESSION['last_name']         = $user['last_name'];
    $_SESSION['user_email']        = $user['email'];
    $_SESSION['user_role']         = $user['role'];
    $_SESSION['course']            = isset($user['course']) ? $user['course'] : '';
    $_SESSION['year_level']        = isset($user['year_level']) ? $user['year_level'] : '';
    $_SESSION['section']           = isset($user['section']) ? $user['section'] : '';
    $_SESSION['contact_number']    = isset($user['contact_number']) ? $user['contact_number'] : '';
    $_SESSION['profile_photo']     = isset($user['profile_photo']) ? $user['profile_photo'] : null;

    log_activity($user['id'], 'LOGIN', 'User logged in successfully');
}

function logout_user() {
    if (is_logged_in()) {
        log_activity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
    }
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
