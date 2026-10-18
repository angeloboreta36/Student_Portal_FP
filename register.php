<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . base_url('admin/dashboard.php'));
    } elseif (is_faculty()) {
        header('Location: ' . base_url('faculty/dashboard.php'));
    } else {
        header('Location: ' . base_url('student/dashboard.php'));
    }
    exit;
}

$errors = array();
$formData = array(
    'student_id_number' => '',
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'course' => 'BSIT',
    'year_level' => '1st Year',
    'section' => '',
    'contact_number' => '',
    'email' => ''
);

$coursesList = array('BSIT', 'BEE', 'BSE', 'BSBA', 'BSA', 'BSCRIM', 'BSCE');
$yearLevelsList = array('1st Year', '2nd Year', '3rd Year', '4th Year');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    $formData['student_id_number'] = isset($_POST['student_id_number']) ? trim($_POST['student_id_number']) : '';
    $formData['first_name']        = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $formData['middle_name']       = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
    $formData['last_name']         = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $formData['course']            = isset($_POST['course']) ? trim($_POST['course']) : '';
    $formData['year_level']        = isset($_POST['year_level']) ? trim($_POST['year_level']) : '';
    $formData['section']           = isset($_POST['section']) ? trim($_POST['section']) : '';
    $formData['contact_number']    = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
    $formData['email']             = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password                      = isset($_POST['password']) ? $_POST['password'] : '';
    $passwordConfirm               = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if (!verify_csrf($csrf)) {
        $errors[] = 'Security token invalid. Please try submitting again.';
    }

    if (empty($formData['student_id_number'])) {
        $errors[] = 'Student ID Number is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9\-]{5,20}$/', $formData['student_id_number'])) {
        $errors[] = 'Student ID Number must be between 5 to 20 alphanumeric characters (e.g. 2400030).';
    }

    if (empty($formData['first_name']) || empty($formData['last_name'])) {
        $errors[] = 'First name and last name are required.';
    }

    if (!in_array($formData['course'], $coursesList)) {
        $errors[] = 'Please select a valid college degree program.';
    }

    if (!in_array($formData['year_level'], $yearLevelsList)) {
        $errors[] = 'Please select a valid academic year level.';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password != $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }

    
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id, student_id_number, email FROM users WHERE student_id_number = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $formData['student_id_number'], $formData['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existing = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($existing) {
            if ($existing['student_id_number'] == $formData['student_id_number']) {
                $errors[] = 'This Student ID Number is already registered in the system.';
            }
            if ($existing['email'] == $formData['email']) {
                $errors[] = 'This email address is already in use by another account.';
            }
        }
    }

    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users 
            (student_id_number, first_name, middle_name, last_name, email, password, role, course, year_level, section, contact_number, status)
            VALUES (?, ?, ?, ?, ?, ?, 'student', ?, ?, ?, ?, 'Active')");

        $midName = !empty($formData['middle_name']) ? $formData['middle_name'] : null;
        $sec = !empty($formData['section']) ? $formData['section'] : null;
        $contact = !empty($formData['contact_number']) ? $formData['contact_number'] : null;

        mysqli_stmt_bind_param($stmt, "ssssssssss", 
            $formData['student_id_number'],
            $formData['first_name'],
            $midName,
            $formData['last_name'],
            $formData['email'],
            $hashedPassword,
            $formData['course'],
            $formData['year_level'],
            $sec,
            $contact
        );

        if (mysqli_stmt_execute($stmt)) {
            $newUserId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            
            $res = mysqli_query($conn, "SELECT * FROM users WHERE id = $newUserId");
            $newUser = mysqli_fetch_assoc($res);

            login_user($newUser);
            set_flash('success', 'Your student account has been created successfully! Welcome to PCNL Portal.');
            header('Location: ' . base_url('student/dashboard.php'));
            exit;
        } else {
            $errors[] = 'Registration failed: ' . mysqli_error($conn);
        }
    }
}

$page_title = "Student Registration - PCNL";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="flex-1 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center bg-gradient-to-b from-sky-50 via-white to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
    <div class="max-w-2xl w-full bg-white dark:bg-slate-900 p-8 sm:p-10 rounded-3xl border-2 border-blue-100 dark:border-slate-800 shadow-xl shadow-blue-500/5">
        
        <div class="text-center mb-6">
            <img src="<?= base_url('assets/images/pcnl-logo.png') ?>" alt="PCNL Logo" class="mx-auto w-16 h-16 object-contain drop-shadow-md mb-2">
            <h2 class="text-xl sm:text-2xl font-black text-slate-900">PCNL Student Account Registration</h2>
            <p class="text-xs text-blue-700 font-bold uppercase tracking-wider mt-0.5">Philippine College of Northwestern Luzon</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                <div class="font-bold mb-1 flex items-center">
                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i>Please fix the following errors:
                </div>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="space-y-5" method="POST" action="<?= base_url('register.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">

            
            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                <h3 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-3 flex items-center">
                    <i class="fa-solid fa-graduation-cap text-blue-700 mr-1.5"></i>Academic Enrollment Info
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Student ID Number *</label>
                        <input type="text" name="student_id_number" required placeholder="e.g. 2400030"
                               value="<?= sanitize($formData['student_id_number']) ?>"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Degree Program *</label>
                        <select name="course" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <?php foreach ($coursesList as $c): ?>
                                <option value="<?= $c ?>" <?= $formData['course'] == $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Year Level *</label>
                        <select name="year_level" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <?php foreach ($yearLevelsList as $y): ?>
                                <option value="<?= $y ?>" <?= $formData['year_level'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Section / Block</label>
                        <input type="text" name="section" placeholder="e.g. 2-A"
                               value="<?= sanitize($formData['section']) ?>"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Contact Number</label>
                        <input type="text" name="contact_number" placeholder="09171234567"
                               value="<?= sanitize($formData['contact_number']) ?>"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" required placeholder="First Name"
                           value="<?= sanitize($formData['first_name']) ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" placeholder="Middle (Optional)"
                           value="<?= sanitize($formData['middle_name']) ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" required placeholder="Last Name"
                           value="<?= sanitize($formData['last_name']) ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" required placeholder="firstname.lastname@student.edu.ph"
                           value="<?= sanitize($formData['email']) ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Password * (min. 6 chars)</label>
                        <input type="password" id="reg-password" name="password" required placeholder="••••••••"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password *</label>
                        <input type="password" id="reg-password-confirm" name="password_confirm" required placeholder="••••••••"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <div id="password-match-feedback"></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl text-white font-bold bg-blue-700 hover:bg-blue-800 shadow-md shadow-blue-600/25 transition focus:outline-none">
                Register Student Account
            </button>

            <div class="text-center text-xs text-slate-500 pt-1">
                Already registered?
                <a href="<?= base_url('login.php') ?>" class="font-bold text-blue-700 hover:text-blue-900 ml-1">Sign in here &rarr;</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
