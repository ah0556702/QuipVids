<?php
/**
 * php install_upload_scaffold.php
 * Creates upload form + DB schema for audience submissions.
 */

$files = [

    'public/upload.php' => <<<'PHP'
<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/util.php';

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $script = trim($_POST['script'] ?? '');
    $imagePath = null;
    $videoPath = null;

    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imagePath = '/uploads/' . uniqid('img_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . $imagePath);
    }

    if (!empty($_FILES['video']['tmp_name'])) {
        $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $videoPath = '/uploads/' . uniqid('vid_') . '.' . $ext;
        move_uploaded_file($_FILES['video']['tmp_name'], __DIR__ . $videoPath);
    }

    if ($name && $script) {
        db()->prepare("INSERT INTO quip_submissions (name,title,script,image_path,video_path) VALUES (?,?,?,?,?)")
            ->execute([$name, $title, $script, $imagePath, $videoPath]);
        $msg = "✅ Thanks! Your quip has been submitted for review.";
    } else {
        $msg = "⚠️ Please provide at least a name and script.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Upload Quip</title>
  <style>
    body { font-family:sans-serif; background:#0e0e1a; color:#f0f0ff; padding:20px; }
    form { background:#16162b; padding:20px; border-radius:12px; max-width:500px; margin:auto; }
    label { display:block; margin-top:10px; }
    input, textarea { width:100%; padding:10px; border-radius:8px; border:1px solid #333; background:#12122b; color:#fff; }
    button { margin-top:15px; padding:10px 15px; border-radius:8px; background:#1e1e37; color:#fff; border:1px solid #2a2a50; }
    button:hover { border-color:#7df9ff; color:#7df9ff; }
    .msg { margin-bottom:10px; }
  </style>
</head>
<body>
  <h1>Submit Your Quip 🎬</h1>
  <?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Name</label>
    <input type="text" name="name" required>
    <label>Show / Title</label>
    <input type="text" name="title">
    <label>Script</label>
    <textarea name="script" rows="4" required></textarea>
    <label>Image (optional)</label>
    <input type="file" name="image" accept="image/*">
    <label>Video (optional)</label>
    <input type="file" name="video" accept="video/*">
    <button type="submit">Submit Quip</button>
  </form>
</body>
</html>
PHP,

];

// Create dirs and files
foreach ($files as $path => $content) {
    $full = __DIR__ . '/' . $path;
    $dir = dirname($full);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Created dir: $dir\n";
    }
    if (!file_exists($full)) {
        file_put_contents($full, $content);
        echo "Created file: $path\n";
    } else {
        echo "Skipped (exists): $path\n";
    }
}

// Add SQL migration for submissions
$sqlPath = __DIR__ . '/sql/quip_submissions.sql';
if (!file_exists($sqlPath)) {
    file_put_contents($sqlPath, "CREATE TABLE IF NOT EXISTS quip_submissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  title TEXT,
  script TEXT,
  image_path TEXT,
  video_path TEXT,
  status TEXT NOT NULL DEFAULT 'pending',
  submitted_at TEXT NOT NULL DEFAULT (datetime('now')),
  moderated_by INTEGER,
  moderated_at TEXT,
  FOREIGN KEY (moderated_by) REFERENCES users(id)
);\n");
    echo "Created migration: sql/quip_submissions.sql\n";
}

echo "✅ Upload scaffold installed. Run this once:\n";
echo "  php -r \"require 'src/db.php'; db()->exec(file_get_contents('sql/quip_submissions.sql'));\"\n";
