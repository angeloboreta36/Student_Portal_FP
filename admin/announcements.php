<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../includes/auth.php';

require_faculty();

$categories = array('Academic', 'Event', 'Urgent', 'General');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'create') {
        $title    = isset($_POST['title']) ? trim($_POST['title']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : 'General';
        $content  = isset($_POST['content']) ? trim($_POST['content']) : '';
        $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
        $authorId = intval($_SESSION['user_id']);

        if (empty($title) || empty($content)) {
            set_flash('error', 'Title and bulletin content are required.');
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO announcements (author_id, title, category, content, is_pinned) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssi", $authorId, $title, $category, $content, $isPinned);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            log_activity($authorId, 'ANNOUNCEMENT_CREATE', "Published bulletin: $title");
            set_flash('success', 'Announcement published successfully!');
            header('Location: ' . base_url('admin/announcements.php'));
            exit;
        }
    } elseif ($action == 'update') {
        $id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title    = isset($_POST['title']) ? trim($_POST['title']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : 'General';
        $content  = isset($_POST['content']) ? trim($_POST['content']) : '';
        $isPinned = isset($_POST['is_pinned']) ? 1 : 0;

        if (!$id || empty($title) || empty($content)) {
            set_flash('error', 'Missing required bulletin fields.');
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE announcements SET title = ?, category = ?, content = ?, is_pinned = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssii", $title, $category, $content, $isPinned, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            log_activity($_SESSION['user_id'], 'ANNOUNCEMENT_UPDATE', "Updated bulletin ID #$id");
            set_flash('success', 'Announcement updated successfully!');
            header('Location: ' . base_url('admin/announcements.php'));
            exit;
        }
    } elseif ($action == 'delete') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            $stmt = mysqli_prepare($conn, "DELETE FROM announcements WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            log_activity($_SESSION['user_id'], 'ANNOUNCEMENT_DELETE', "Deleted bulletin ID #$id");
            set_flash('info', 'Announcement deleted successfully.');
            header('Location: ' . base_url('admin/announcements.php'));
            exit;
        }
    }
}


$announcements = array();
$query = "
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.is_pinned DESC, a.created_at DESC
";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = $row;
}

$page_title = "Manage Bulletins - PCNL";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
    
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Campus Bulletins & Advisories</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Philippine College of Northwestern Luzon &bull; Official Announcements</p>
        </div>
        <button onclick="openModal('addAnnModal')" class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-blue-700 hover:bg-blue-800 text-white shadow-sm transition">
            <i class="fa-solid fa-bullhorn mr-1.5"></i>Post New Announcement
        </button>
    </div>

    <?php render_flash(); ?>

    
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Category</th>
                        <th class="px-5 py-3.5 font-bold">Announcement Title</th>
                        <th class="px-5 py-3.5 font-bold">Author</th>
                        <th class="px-5 py-3.5 font-bold">Date Posted</th>
                        <th class="px-5 py-3.5 font-bold text-center">Pinned</th>
                        <th class="px-5 py-3.5 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($announcements)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">No announcements posted yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $a): ?>
                            <tr class="hover:bg-blue-50/30 transition border-b border-slate-100">
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider 
                                        <?= $a['category'] == 'Urgent' ? 'bg-rose-100 text-rose-700' : ($a['category'] == 'Academic' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                        <?= sanitize($a['category']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900"><?= sanitize($a['title']) ?></div>
                                    <div class="text-xs text-slate-500 line-clamp-1 max-w-md"><?= sanitize($a['content']) ?></div>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold text-slate-700">
                                    <?= sanitize($a['first_name'] . ' ' . $a['last_name']) ?>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-400">
                                    <?= date('M j, Y', strtotime($a['created_at'])) ?> &bull; <?= date('g:i A', strtotime($a['created_at'])) ?>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <?php if ($a['is_pinned']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <i class="fa-solid fa-thumbtack mr-1 text-[9px]"></i>Pinned
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-300">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-right space-x-1">
                                    <button onclick='editAnnModal(<?= json_encode($a) ?>)' class="text-blue-700 hover:text-blue-900 text-xs font-semibold p-1.5 rounded hover:bg-blue-50" title="Edit Announcement">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="<?= base_url('admin/announcements.php') ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this bulletin?')">
                                        <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="text-rose-600 hover:text-rose-900 text-xs font-semibold p-1.5 rounded hover:bg-rose-50" title="Delete Announcement">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="addAnnModal" class="hidden modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative my-8 border-2 border-blue-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Post Campus Announcement</h3>
            <button onclick="closeModal('addAnnModal')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="<?= base_url('admin/announcements.php') ?>" class="mt-4 space-y-4">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Title *</label>
                <input type="text" name="title" required placeholder="e.g. Schedule of Term Examinations"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center pt-5">
                    <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_pinned" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                        <span>Pin to top of feed</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Detailed Content *</label>
                <textarea name="content" rows="5" required placeholder="Write announcement details..."
                          class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('addAnnModal')" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-700 text-white hover:bg-blue-800 shadow-sm">Publish Bulletin</button>
            </div>
        </form>
    </div>
</div>


<div id="editAnnModal" class="hidden modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative my-8 border-2 border-blue-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Edit Announcement</h3>
            <button onclick="closeModal('editAnnModal')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="<?= base_url('admin/announcements.php') ?>" class="mt-4 space-y-4">
            <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-ann-id" name="id">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Title *</label>
                <input type="text" id="edit-ann-title" name="title" required
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category</label>
                    <select id="edit-ann-category" name="category" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center pt-5">
                    <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="edit-ann-pinned" name="is_pinned" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                        <span>Pin to top of feed</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Detailed Content *</label>
                <textarea id="edit-ann-content" name="content" rows="5" required
                          class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('editAnnModal')" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-700 text-white hover:bg-blue-800 shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editAnnModal(ann) {
        document.getElementById('edit-ann-id').value = ann.id;
        document.getElementById('edit-ann-title').value = ann.title;
        document.getElementById('edit-ann-category').value = ann.category;
        document.getElementById('edit-ann-content').value = ann.content;
        document.getElementById('edit-ann-pinned').checked = (parseInt(ann.is_pinned) == 1);

        openModal('editAnnModal');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
