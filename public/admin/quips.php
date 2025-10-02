<?php // /admin/quips.php

require_once __DIR__ . '/../../config.php';

require_once BASE_PATH . '/src/db.php';
require_once BASE_PATH . '/src/auth.php';
require_once BASE_PATH . '/src/util.php';


require_login(); require_role('admin','moderator');

$apiFile = __DIR__ . '/../resources/data/api.json';
$quips   = json_decode(file_get_contents($apiFile), true);
if (!is_array($quips)) $quips = [];

// actions
$msg = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $qid = (string)($_POST['quip_id'] ?? '');
    $act = (string)($_POST['action'] ?? '');
    $reason = trim((string)($_POST['reason'] ?? ''));

    if ($qid && in_array($act,['approve','reject'], true)) {
        $status = $act === 'approve' ? 'approved' : 'rejected';
        db()->prepare('INSERT INTO quip_moderation(quip_id,status,reason,moderated_by,moderated_at)
                   VALUES(?,?,?,?,datetime("now"))
                   ON CONFLICT(quip_id) DO UPDATE SET status=excluded.status, reason=excluded.reason, moderated_by=excluded.moderated_by, moderated_at=excluded.moderated_at')
            ->execute([$qid, $status, $reason ?: null, user()['id']]);
        audit(user()['id'], 'quip.'.$status, 'quip', $qid, ['reason'=>$reason]);
        $msg = "Quip {$qid} marked {$status}.";
    }
}

// current statuses
$st = db()->query('SELECT quip_id, status, reason FROM quip_moderation')->fetchAll();
$statusMap = [];
foreach ($st as $row) $statusMap[$row['quip_id']] = $row;

// filter/search
$q = strtolower(trim((string)($_GET['q'] ?? '')));

$title='Moderation';
ob_start(); ?>
    <h1>Moderation</h1>
<?php if ($msg): ?><div class="card"><?= e($msg) ?></div><?php endif; ?>

    <div class="card">
        <form method="get">
            <div class="form-row">
                <label>Search</label>
                <input class="input" name="q" value="<?= e($q) ?>" placeholder="name, show title, script...">
            </div>
        </form>
    </div>

    <div class="card">
        <table>
            <tr>
                <th>Preview</th><th>Name</th><th>Show</th><th>Status</th><th>Actions</th>
            </tr>
            <?php foreach ($quips as $row):
                $name   = (string)($row['name'] ?? '');
                $title  = (string)($row['title'] ?? '');
                $id     = (string)($row['id'] ?? '');
                $image  = (string)($row['image'] ?? '');
                $url    = 'https://quipvid.com' . (string)($row['url'] ?? '#');
                $script = (string)($row['script'] ?? '');
                $hay = strtolower("$name $title $script $id");
                if ($q && strpos($hay, $q) === false) continue;
                $status = $statusMap[$id]['status'] ?? 'pending';
                $reason = $statusMap[$id]['reason'] ?? '';
                ?>
                <tr>
                    <td><a href="<?= e($url) ?>" target="_blank"><img src="<?= e($image) ?>" alt="" style="width:120px;height:70px;object-fit:cover;border-radius:6px"></a></td>
                    <td><strong><?= e($name) ?></strong><br><small><?= e($id) ?></small></td>
                    <td><?= e($title) ?></td>
                    <td><?= $status==='approved'?'✅ approved':($status==='rejected'?'⛔ rejected':'🕒 pending') ?>
                        <?php if ($reason): ?><br><small style="color:var(--muted)">Reason: <?= e($reason) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="quip_id" value="<?= e($id) ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="btn" type="submit">Approve</button>
                        </form>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="quip_id" value="<?= e($id) ?>">
                            <input type="hidden" name="action" value="reject">
                            <input class="input" name="reason" placeholder="reason" style="width:160px">
                            <button class="btn danger" type="submit">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/_layout.php';
