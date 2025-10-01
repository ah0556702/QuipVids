<?php
require_once __DIR__ . '/../src/QuipData.php';

$data = new QuipData();
$quips = $data->all();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quips</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Movie Quips</h1>
<?php foreach ($quips as $q): ?>
    <div class="quip">
        <h3><?= htmlspecialchars($q['movie'] ?? 'Unknown Movie') ?></h3>
        <blockquote><?= htmlspecialchars($q['quote'] ?? '') ?></blockquote>
        <small><?= htmlspecialchars($q['author'] ?? 'Unknown') ?></small>
    </div>
<?php endforeach; ?>
</body>
</html>
