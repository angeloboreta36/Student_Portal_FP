<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    } elseif (is_faculty()) {
        header('Location: ' . base_url('faculty/dashboard.php'));
        exit;
    } else {
        header('Location: ' . base_url('student/dashboard.php'));
        exit;
    }
}

$bulletins = array();
$bQuery = "
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.is_pinned DESC, a.created_at DESC 
    LIMIT 3
";
$bRes = mysqli_query($conn, $bQuery);
if ($bRes) {
    while ($row = mysqli_fetch_assoc($bRes)) {
        $bulletins[] = $row;
    }
}

$page_title = "Welcome to PCNL Portal";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-1 w-full flex flex-col overflow-x-hidden">
    <section class="relative bg-gradient-to-b from-sky-100/80 via-white to-blue-50/50 py-12 sm:py-20 border-b border-blue-100">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[340px] bg-gradient-to-tr from-cyan-400/20 via-blue-500/20 to-indigo-500/10 blur-3xl pointer-events-none rounded-full"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="flex flex-col items-center justify-center mb-6">
                <img src="<?= base_url('assets/images/pcnl-logo.png') ?>" alt="PCNL Official Logo" class="w-24 h-24 sm:w-32 sm:h-32 object-contain drop-shadow-md hover:scale-105 transition-transform duration-300">
                <div class="mt-4 inline-flex items-center space-x-2 px-4 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900 border border-blue-300 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                    <span class="tracking-widest uppercase">Qualitas et Excellentiam &bull; MMVII</span>
                </div>
            </div>

            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-900 max-w-4xl mx-auto leading-tight">
                Philippine College of Northwestern Luzon
            </h1>
            <p class="text-base sm:text-xl font-bold text-blue-700 mt-2">
                Official Student Portal & 4-Term Grade Management System
            </p>

            <p class="mt-4 text-xs sm:text-sm md:text-base text-slate-600 max-w-2xl mx-auto leading-relaxed">
                Welcome to the official PCNL academic portal. Access official grade slips, evaluate percentage standing across 4 academic terms (75.00% passing mark), enlist in curriculum subjects, and stay informed with university advisories.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto sm:max-w-none">
                <a href="<?= base_url('login.php') ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-2xl text-xs sm:text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>Access Student Portal
                </a>
                <a href="<?= base_url('register.php') ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-2xl text-xs sm:text-sm font-bold text-blue-900 bg-white hover:bg-blue-50 border-2 border-blue-200 shadow-xs transition">
                    <i class="fa-solid fa-user-plus mr-2 text-blue-700"></i>Student Account Registration
                </a>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200/80 max-w-3xl mx-auto">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-3">Accredited Academic Programs</span>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <?php 
                    $programs = array(
                        'BSIT' => 'Information Technology',
                        'BEE' => 'Elementary Education',
                        'BSE' => 'Secondary Education',
                        'BSBA' => 'Business Administration',
                        'BSA' => 'Accountancy',
                        'BSCRIM' => 'Criminology',
                        'BSCE' => 'Civil Engineering'
                    );
                    foreach ($programs as $code => $name): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-white border border-slate-200 text-slate-700 shadow-2xs" title="<?= $name ?>">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mr-1.5"></span><?= $code ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 sm:py-16 bg-white border-b border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Core Academic Modules</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Conforming to official PCNL 200-point course project specifications</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-50 p-6 rounded-3xl border-2 border-blue-100 shadow-xs hover:border-blue-300 transition">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl mb-4 shadow-xs">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">4-Term Grading Scale</h3>
                    <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                        Automatic weighted computation across <strong class="text-slate-800">Prelim (20%)</strong>, <strong class="text-slate-800">Midterm (20%)</strong>, <strong class="text-slate-800">Semi-Final (20%)</strong>, and <strong class="text-slate-800">Final (40%)</strong> with standard 75.00% passing threshold.
                    </p>
                </div>

                <div class="bg-slate-50 p-6 rounded-3xl border-2 border-blue-100 shadow-xs hover:border-blue-300 transition">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xl mb-4 shadow-xs">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Online Course Enlistment</h3>
                    <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                        Students can browse active curriculum schedules, check real-time class section limits, and enlist or drop subjects with 1-click asynchronous verification.
                    </p>
                </div>

                <div class="bg-slate-50 p-6 rounded-3xl border-2 border-blue-100 shadow-xs hover:border-blue-300 transition">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-4 shadow-xs">
                        <i class="fa-solid fa-print"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Official Certificate of Grades</h3>
                    <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                        Printable Registrar grade report format complete with official PCNL seal, Latin motto, San Antonio, Agoo, La Union location, and Registrar certification signature lines.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($bulletins)): ?>
    <section class="py-12 sm:py-16 bg-slate-50/70 border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Recent Campus Bulletins</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Stay up-to-date with official PCNL academic notices</p>
                </div>
                <a href="<?= base_url('login.php') ?>" class="text-xs sm:text-sm font-bold text-blue-700 hover:text-blue-900 inline-flex items-center">
                    Log in to view all bulletins &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($bulletins as $b): ?>
                    <div class="bg-white p-6 rounded-2xl border-2 border-blue-100 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                    <?= $b['category'] == 'Urgent' ? 'bg-rose-100 text-rose-700' : ($b['category'] == 'Academic' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                    <?= sanitize($b['category']) ?>
                                </span>
                                <span class="text-[11px] text-slate-400"><?= date('M j, Y', strtotime($b['created_at'])) ?></span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 line-clamp-1 mb-2"><?= sanitize($b['title']) ?></h3>
                            <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed"><?= sanitize($b['content']) ?></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                            By: <strong class="text-slate-700"><?= sanitize($b['first_name'] . ' ' . $b['last_name']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
