<?php
require_once __DIR__ . '/auth.php';
$currentUser = current_user();
$currentScript = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-4 lg:space-x-6">
                <a href="<?= is_admin() ? base_url('admin/dashboard.php') : (is_faculty() ? base_url('faculty/dashboard.php') : (is_student() ? base_url('student/dashboard.php') : base_url('index.php'))) ?>" class="flex items-center space-x-3 group">
                    <img src="<?= base_url('assets/images/pcnl-logo.png') ?>" alt="PCNL Logo" class="w-10 h-10 sm:w-11 sm:h-11 object-contain drop-shadow-sm group-hover:scale-105 transition-transform">
                    <div>
                        <span class="text-sm sm:text-base font-black tracking-tight text-blue-900 block leading-tight">PCNL Portal</span>
                        <span class="text-[9px] sm:text-[9.5px] font-bold text-blue-600 uppercase tracking-wider block">Phil. College of Northwestern Luzon</span>
                    </div>
                </a>

                <div class="hidden md:flex items-center space-x-1">
                    <?php if (is_admin()): ?>
                        <a href="<?= base_url('admin/dashboard.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'dashboard.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-chart-pie mr-1.5 text-xs text-blue-700"></i>Dashboard
                        </a>
                        <a href="<?= base_url('admin/students.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'students.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-user-graduate mr-1.5 text-xs text-blue-700"></i>Students
                        </a>
                        <a href="<?= base_url('admin/courses.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'courses.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-book-bookmark mr-1.5 text-xs text-blue-700"></i>Courses
                        </a>
                        <a href="<?= base_url('admin/grades.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'grades.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-award mr-1.5 text-xs text-blue-700"></i>Grade Encoding
                        </a>
                        <a href="<?= base_url('admin/announcements.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'announcements.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-bullhorn mr-1.5 text-xs text-blue-700"></i>Bulletins
                        </a>
                    <?php elseif (is_faculty()): ?>
                        <a href="<?= base_url('faculty/dashboard.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'dashboard.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-chalkboard-user mr-1.5 text-xs text-blue-700"></i>Classes
                        </a>
                        <a href="<?= base_url('admin/grades.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'grades.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-award mr-1.5 text-xs text-blue-700"></i>Grade Encoding
                        </a>
                        <a href="<?= base_url('admin/announcements.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'announcements.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-bullhorn mr-1.5 text-xs text-blue-700"></i>Bulletins
                        </a>
                    <?php elseif (is_student()): ?>
                        <a href="<?= base_url('student/dashboard.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'dashboard.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-house mr-1.5 text-xs text-blue-700"></i>Home
                        </a>
                        <a href="<?= base_url('student/grades.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'grades.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-file-invoice mr-1.5 text-xs text-blue-700"></i>My Grades
                        </a>
                        <a href="<?= base_url('student/enlistment.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'enlistment.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-list-check mr-1.5 text-xs text-blue-700"></i>Enlistment
                        </a>
                        <a href="<?= base_url('student/announcements.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'announcements.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-bullhorn mr-1.5 text-xs text-blue-700"></i>Bulletins
                        </a>
                        <a href="<?= base_url('student/profile.php') ?>" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $currentScript === 'profile.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                            <i class="fa-solid fa-id-card mr-1.5 text-xs text-blue-700"></i>Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-3">
                <?php if ($currentUser): ?>
                    <div class="hidden sm:flex items-center space-x-3 pl-2 sm:pl-3 border-l border-slate-200">
                        <div class="text-right">
                            <div class="text-xs sm:text-sm font-bold text-slate-900 leading-tight"><?= sanitize($currentUser['name']) ?></div>
                            <div class="text-[11px] text-slate-500 flex items-center justify-end space-x-1">
                                <?php if (is_admin()): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 text-blue-800 uppercase">Administrator</span>
                                <?php elseif (is_faculty()): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">Faculty</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 text-blue-800"><?= sanitize($currentUser['course'] ? $currentUser['course'] : 'BSIT') ?></span>
                                    <span>&bull; <?= sanitize($currentUser['student_id_number']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center font-bold text-xs overflow-hidden shadow-xs">
                            <?= strtoupper(substr($currentUser['first_name'] ? $currentUser['first_name'] : 'U', 0, 1) . substr($currentUser['last_name'] ? $currentUser['last_name'] : 'P', 0, 1)) ?>
                        </div>
                    </div>

                    <a href="<?= base_url('logout.php') ?>" onclick="return confirm('Are you sure you want to log out?')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                    </a>
                <?php else: ?>
                    <div class="hidden sm:flex items-center space-x-2">
                        <a href="<?= base_url('login.php') ?>" class="text-xs sm:text-sm font-bold text-slate-700 hover:text-blue-700 px-3 py-2 rounded-xl hover:bg-slate-100 transition">Log In</a>
                        <a href="<?= base_url('register.php') ?>" class="inline-flex items-center px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl text-white bg-blue-700 hover:bg-blue-800 shadow-sm transition">
                            <i class="fa-solid fa-user-plus mr-1.5 text-xs"></i>Register
                        </a>
                    </div>
                <?php endif; ?>

                <button type="button" id="mobile-menu-btn" class="md:hidden p-2 rounded-xl text-slate-600 hover:text-blue-700 hover:bg-slate-100 focus:outline-none transition" aria-label="Open Navigation Menu">
                    <i class="fa-solid fa-bars text-xl" id="menu-icon"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-2 shadow-lg">
        <?php if (is_admin()): ?>
            <a href="<?= base_url('admin/dashboard.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'dashboard.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-chart-pie mr-2.5 text-blue-700 w-5 text-center"></i>Dashboard
            </a>
            <a href="<?= base_url('admin/students.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'students.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-user-graduate mr-2.5 text-blue-700 w-5 text-center"></i>Students Directory
            </a>
            <a href="<?= base_url('admin/courses.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'courses.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-book-bookmark mr-2.5 text-blue-700 w-5 text-center"></i>Courses & Curriculum
            </a>
            <a href="<?= base_url('admin/grades.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'grades.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-award mr-2.5 text-blue-700 w-5 text-center"></i>Grade Management
            </a>
            <a href="<?= base_url('admin/announcements.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'announcements.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-bullhorn mr-2.5 text-blue-700 w-5 text-center"></i>Campus Bulletins
            </a>
        <?php elseif (is_faculty()): ?>
            <a href="<?= base_url('faculty/dashboard.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'dashboard.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-chalkboard-user mr-2.5 text-blue-700 w-5 text-center"></i>My Classes & Load
            </a>
            <a href="<?= base_url('admin/grades.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'grades.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-award mr-2.5 text-blue-700 w-5 text-center"></i>Grade Encoding
            </a>
            <a href="<?= base_url('admin/announcements.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'announcements.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-bullhorn mr-2.5 text-blue-700 w-5 text-center"></i>Campus Bulletins
            </a>
        <?php elseif (is_student()): ?>
            <a href="<?= base_url('student/dashboard.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'dashboard.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-house mr-2.5 text-blue-700 w-5 text-center"></i>Student Home
            </a>
            <a href="<?= base_url('student/grades.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'grades.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-file-invoice mr-2.5 text-blue-700 w-5 text-center"></i>View Grades & Transcript
            </a>
            <a href="<?= base_url('student/enlistment.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'enlistment.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-list-check mr-2.5 text-blue-700 w-5 text-center"></i>Subject Enlistment
            </a>
            <a href="<?= base_url('student/announcements.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'announcements.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-bullhorn mr-2.5 text-blue-700 w-5 text-center"></i>Announcements
            </a>
            <a href="<?= base_url('student/profile.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'profile.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-id-card mr-2.5 text-blue-700 w-5 text-center"></i>My Profile
            </a>
        <?php else: ?>
            <a href="<?= base_url('index.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold <?= $currentScript === 'index.php' ? 'bg-blue-100 text-blue-800' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-house mr-2.5 text-blue-700 w-5 text-center"></i>Portal Home
            </a>
            <a href="<?= base_url('login.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold text-blue-700 bg-blue-50 hover:bg-blue-100">
                <i class="fa-solid fa-right-to-bracket mr-2.5 text-blue-700 w-5 text-center"></i>Access Student Portal (Log In)
            </a>
            <a href="<?= base_url('register.php') ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-700 hover:bg-blue-800">
                <i class="fa-solid fa-user-plus mr-2.5 text-white w-5 text-center"></i>New Student Registration
            </a>
            <div class="px-3 pt-2 text-[11px] text-slate-400">
                Philippine College of Northwestern Luzon &bull; San Antonio, Agoo, La Union
            </div>
        <?php endif; ?>

        <?php if ($currentUser): ?>
            <div class="pt-3 pb-1 border-t border-slate-200">
                <div class="flex items-center px-3 mb-2">
                    <div>
                        <div class="text-sm font-bold text-slate-900"><?= sanitize($currentUser['name']) ?></div>
                        <div class="text-xs text-slate-500"><?= sanitize($currentUser['email']) ?></div>
                    </div>
                </div>
                <a href="<?= base_url('logout.php') ?>" onclick="return confirm('Are you sure you want to log out?')" class="flex items-center px-3 py-2 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-2 text-rose-600"></i>Log Out
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script>
    (function() {
        var menuBtn = document.getElementById('mobile-menu-btn');
        var mobileMenu = document.getElementById('mobile-menu');
        var menuIcon = document.getElementById('menu-icon');
        if (menuBtn && mobileMenu) {
            menuBtn.addEventListener('click', function() {
                var isHidden = mobileMenu.classList.toggle('hidden');
                if (isHidden) {
                    if (menuIcon) {
                        menuIcon.classList.remove('fa-xmark');
                        menuIcon.classList.add('fa-bars');
                    }
                } else {
                    if (menuIcon) {
                        menuIcon.classList.remove('fa-bars');
                        menuIcon.classList.add('fa-xmark');
                    }
                }
            });
        }
    })();
</script>
