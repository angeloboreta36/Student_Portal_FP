<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$category = isset($_GET['cat']) ? trim($_GET['cat']) : 'All';

$sql = "
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a 
    JOIN users u ON a.author_id = u.id
";

if ($category !== 'All' && in_array($category, array('Academic', 'Event', 'Urgent', 'General'))) {
    $catSafe = mysqli_real_escape_string($conn, $category);
    $sql .= " WHERE a.category = '$catSafe'";
}

$sql .= " ORDER BY a.is_pinned DESC, a.created_at DESC";

$bulletins = array();
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bulletins[] = $row;
    }
}

$page_title = "Campus Bulletins - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Campus Announcements & Bulletins</h1>
        <p class="mt-1 text-xs sm:text-sm text-slate-500">Philippine College of Northwestern Luzon &bull; Official Student Notices</p>
    </div>

    
    <div class="flex items-center space-x-2 overflow-x-auto pb-4 mb-6 text-xs sm:text-sm">
        <?php 
            $tabs = array('All' => 'All Bulletins', 'Academic' => 'Academic', 'Event' => 'Campus Events', 'Urgent' => 'Urgent Notices', 'General' => 'General');
            foreach ($tabs as $k => $label):
        ?>
            <a href="?cat=<?= $k ?>" class="px-4 py-2 rounded-xl font-bold transition whitespace-nowrap <?= $category === $k ? 'bg-blue-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-blue-50 border border-slate-200' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    
    <div class="space-y-4">
        <?php if (empty($bulletins)): ?>
            <div class="bg-white p-12 text-center rounded-2xl border-2 border-blue-100 text-slate-400">
                <i class="fa-solid fa-bullhorn text-3xl mb-3 text-slate-300"></i>
                <p class="text-base font-bold text-slate-700">No announcements in this category</p>
                <p class="text-xs text-slate-400 mt-1">Check back later for university updates.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bulletins as $b): ?>
                <div class="bg-white p-6 rounded-2xl border-2 transition hover:shadow-md <?= $b['is_pinned'] ? 'border-amber-300 ring-1 ring-amber-100' : 'border-blue-100' ?>">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div class="flex items-center space-x-2">
                            <?php if ($b['is_pinned']): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    <i class="fa-solid fa-thumbtack mr-1 text-[9px]"></i>Pinned
                                </span>
                            <?php endif; ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider 
                                <?= $b['category'] === 'Urgent' ? 'bg-rose-100 text-rose-700' : ($b['category'] === 'Academic' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                <?= sanitize($b['category']) ?>
                            </span>
                        </div>
                        <div class="text-xs text-slate-400 flex items-center space-x-1">
                            <i class="fa-regular fa-clock"></i>
                            <span><?= date('F j, Y', strtotime($b['created_at'])) ?> &bull; <?= date('g:i A', strtotime($b['created_at'])) ?></span>
                        </div>
                    </div>

                    <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-2">
                        <?= sanitize($b['title']) ?>
                    </h2>

                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                        <?= sanitize($b['content']) ?>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span>Posted by: <strong class="text-slate-700 font-semibold"><?= sanitize($b['first_name'] . ' ' . $b['last_name']) ?></strong> (College Administration)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
