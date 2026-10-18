<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();
$studentId = intval($_SESSION['user_id']);


$res = mysqli_query($conn, "SELECT * FROM users WHERE id = $studentId LIMIT 1");
$user = $res ? mysqli_fetch_assoc($res) : null;

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if (!verify_csrf($csrf)) {
        $errors[] = 'Invalid security token. Please try again.';
    } elseif ($action === 'update_profile') {
        $section = isset($_POST['section']) ? trim($_POST['section']) : '';
        $contactNumber = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';

        $stmt = mysqli_prepare($conn, "UPDATE users SET section = ?, contact_number = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $section, $contactNumber, $studentId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            $_SESSION['section'] = $section;
            $_SESSION['contact_number'] = $contactNumber;
            log_activity($studentId, 'PROFILE_UPDATE', 'Updated section and contact number');
            set_flash('success', 'Profile information updated successfully.');
            header('Location: ' . base_url('student/profile.php'));
            exit;
        } else {
            $errors[] = 'Failed to update profile in database.';
        }
    } elseif ($action === 'change_password') {
        $currentPass = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPass = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPass = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (!password_verify($currentPass, $user['password'])) {
            $errors[] = 'The current password you entered is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'New password and confirmation do not match.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $pStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($pStmt, "si", $newHash, $studentId);
            $pOk = mysqli_stmt_execute($pStmt);
            mysqli_stmt_close($pStmt);

            if ($pOk) {
                log_activity($studentId, 'PASSWORD_CHANGE', 'Changed account password');
                set_flash('success', 'Password updated successfully. Please use your new password next time.');
                header('Location: ' . base_url('student/profile.php'));
                exit;
            } else {
                $errors[] = 'Database error updating password.';
            }
        }
    }
}

$page_title = "My Profile - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Student Profile & Settings</h1>
        <p class="mt-1 text-xs sm:text-sm text-slate-500">Philippine College of Northwestern Luzon &bull; Account Management</p>
    </div>

    <?php render_flash(); ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm">
            <div class="font-bold mb-1 flex items-center">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>Please check the following:
            </div>
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $e): ?>
                    <li><?= sanitize($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="bg-white p-6 rounded-2xl border-2 border-blue-100 shadow-xs text-center flex flex-col items-center">
            <div class="w-24 h-24 rounded-full bg-blue-100 text-blue-700 font-black text-3xl flex items-center justify-center mb-4 border-2 border-blue-200">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></h2>
            <p class="text-xs font-mono font-bold text-blue-700 mt-0.5"><?= sanitize($user['student_id_number']) ?></p>

            <div class="w-full mt-6 pt-6 border-t border-slate-100 text-left space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-bold uppercase text-[10px]">Academic Program</span>
                    <span class="font-bold text-slate-800 text-xs sm:text-sm"><?= sanitize($user['course'] ? $user['course'] : 'BSIT') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block font-bold uppercase text-[10px]">Year Level</span>
                    <span class="font-semibold text-slate-800"><?= sanitize($user['year_level'] ? $user['year_level'] : '2nd Year') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block font-bold uppercase text-[10px]">Section</span>
                    <span class="font-semibold text-slate-800"><?= sanitize($user['section'] ? $user['section'] : '2-A') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block font-bold uppercase text-[10px]">Portal Email</span>
                    <span class="font-semibold text-slate-800 break-all"><?= sanitize($user['email']) ?></span>
                </div>
            </div>
        </div>

        
        <div class="md:col-span-2 space-y-8">
            
            <div class="bg-white p-6 rounded-2xl border-2 border-blue-100 shadow-xs">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fa-solid fa-address-card text-blue-700 mr-2"></i>Update Contact Information
                </h3>
                <form method="POST" action="<?= base_url('student/profile.php') ?>" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Section / Block</label>
                            <input type="text" name="section" value="<?= sanitize(isset($user['section']) ? $user['section'] : '') ?>" placeholder="e.g. 2-A"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Contact Number</label>
                            <input type="text" name="contact_number" value="<?= sanitize(isset($user['contact_number']) ? $user['contact_number'] : '') ?>" placeholder="e.g. 09171234567"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" class="px-5 py-2 rounded-xl text-xs sm:text-sm font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-xs transition">
                        Save Changes
                    </button>
                </form>
            </div>

            
            <div class="bg-white p-6 rounded-2xl border-2 border-blue-100 shadow-xs">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fa-solid fa-shield-halved text-blue-700 mr-2"></i>Security & Password
                </h3>
                <form method="POST" action="<?= base_url('student/profile.php') ?>" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Current Password</label>
                        <input type="password" name="current_password" required placeholder="••••••••"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">New Password (min. 6)</label>
                            <input type="password" name="new_password" required placeholder="••••••••"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" required placeholder="••••••••"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" class="px-5 py-2 rounded-xl text-xs sm:text-sm font-bold bg-slate-800 hover:bg-slate-900 text-white shadow-xs transition">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
