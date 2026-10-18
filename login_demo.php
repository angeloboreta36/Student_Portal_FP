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

$error = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    $emailInput = $email;

    if (!verify_csrf($csrf)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please enter both your email/student ID and password.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? OR student_id_number = ? OR (email = 'angelo.boreta@portal.edu.ph' AND ? = 'admin@portal.edu.ph') LIMIT 1");
        mysqli_stmt_bind_param($stmt, "sss", $email, $email, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] != 'Active') {
                $error = 'Your account has been deactivated. Please contact the administrator.';
            } else {
                login_user($user);
                set_flash('success', 'Welcome back, ' . $user['first_name'] . '!');

                if ($user['role'] == 'admin') {
                    header('Location: ' . base_url('admin/dashboard.php'));
                } elseif ($user['role'] == 'faculty') {
                    header('Location: ' . base_url('faculty/dashboard.php'));
                } else {
                    header('Location: ' . base_url('student/dashboard.php'));
                }
                exit;
            }
        } else {
            $error = 'Invalid email/student ID or password combination.';
        }
    }
}

$page_title = "Log In (Demo) - PCNL Portal";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-sky-50 via-white to-slate-50">
    <div class="max-w-md w-full space-y-6 bg-white p-8 sm:p-10 rounded-3xl border-2 border-blue-100 shadow-xl shadow-blue-500/5">
        <div class="text-center">
            <img src="<?= base_url('assets/images/pcnl-logo.png') ?>" alt="PCNL Logo" class="mx-auto w-20 h-20 object-contain drop-shadow-md mb-2">
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Philippine College of Northwestern Luzon</h2>
            <p class="text-xs text-blue-700 font-bold uppercase tracking-wider mt-1">Student & Faculty Academic Portal</p>
            <p class="text-xs text-slate-500 mt-0.5">San Antonio, Agoo, La Union &bull; Evaluation Demo Mode</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-start space-x-2.5">
                <i class="fa-solid fa-circle-exclamation mt-0.5 flex-shrink-0 text-rose-500"></i>
                <div class="font-medium"><?= sanitize($error) ?></div>
            </div>
        <?php endif; ?>

        <?php render_flash(); ?>

        <form class="mt-6 space-y-4" method="POST" action="<?= base_url('login_demo.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email or Student ID Number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-id-card text-xs"></i>
                    </div>
                    <input id="email" name="email" type="text" required autofocus
                           placeholder="e.g. 2400001 or email@portal.edu.ph"
                           value="<?= sanitize($emailInput) ?>"
                           class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Password</label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-key text-xs"></i>
                    </div>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-300 text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fa-regular fa-eye text-xs" id="toggle-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl text-white font-bold bg-blue-700 hover:bg-blue-800 shadow-md shadow-blue-600/25 transition focus:outline-none focus:ring-2 focus:ring-blue-600">
                Sign In to Portal
            </button>
        </form>

        <div class="pt-4 border-t border-slate-100">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2.5">Quick Demo Login (Click to Auto-fill)</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                <button type="button" onclick="fillDemo('angelo.boreta@portal.edu.ph', 'admin123')" class="p-2.5 rounded-xl border-2 border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-900 font-bold text-center transition">
                    <i class="fa-solid fa-shield-halved mr-1 text-blue-700"></i> Angelo Boreta<br>
                    <span class="text-[10px] font-normal text-blue-600">Admin (admin123)</span>
                </button>
                <button type="button" onclick="fillDemo('lexter.garcia@portal.edu.ph', 'faculty123')" class="p-2.5 rounded-xl border-2 border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-900 font-bold text-center transition">
                    <i class="fa-solid fa-chalkboard-user mr-1 text-amber-700"></i> Lexter Garcia<br>
                    <span class="text-[10px] font-normal text-amber-600">Faculty (faculty123)</span>
                </button>
                <button type="button" onclick="fillDemo('roxanne.boadilla@student.edu.ph', 'student123')" class="p-2.5 rounded-xl border-2 border-sky-200 bg-sky-50/70 hover:bg-sky-100 text-sky-900 font-bold text-center transition">
                    <i class="fa-solid fa-user-graduate mr-1 text-sky-700"></i> Roxanne Boadilla<br>
                    <span class="text-[10px] font-normal text-sky-600">Student (student123)</span>
                </button>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100">
            New PCNL Student?
            <a href="<?= base_url('register.php') ?>" class="font-bold text-blue-700 hover:text-blue-900 ml-1">Create Student Account &rarr;</a>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const pass = document.getElementById('password');
        const eye = document.getElementById('toggle-eye');
        if (pass.type === 'password') {
            pass.type = 'text';
            eye.classList.remove('fa-eye');
            eye.classList.add('fa-eye-slash');
        } else {
            pass.type = 'password';
            eye.classList.remove('fa-eye-slash');
            eye.classList.add('fa-eye');
        }
    }

    function fillDemo(user, pass) {
        document.getElementById('email').value = user;
        document.getElementById('password').value = pass;
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
