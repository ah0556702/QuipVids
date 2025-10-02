<?php
require_once __DIR__ . '/../src/QuipData.php';
$data = new QuipData(__DIR__ . '/../resources/data/api.json');
$quips = $data->all();

$baseDomain = "https://quipvid.com"; // prepend to relative URLs
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QuipVids</title>

    <style>
        .btn-upload {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, #7df9ff, #5d5dfc);
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 0 10px rgba(125,249,255,0.5);
        }
        .btn-upload:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 18px rgba(125,249,255,0.8);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0e0e1a;
            color: #f0f0ff;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 40px;
            background: #12122b;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #7df9ff;
            text-shadow: 0 0 8px #00f7ff;
        }

        .search-box input {
            padding: 10px 14px;
            border-radius: 8px;
            border: none;
            background: #1d1d3b;
            color: #fff;
            font-size: 1rem;
            width: 240px;
            outline: none;
            box-shadow: 0 0 8px #00f7ff inset;
        }

        h2 {
            margin: 30px 40px 10px;
            font-size: 1.3rem;
            color: #9d8bff;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 20px 40px 60px;
        }

        .card {
            display: flex;
            flex-direction: column;
            background: #1a1a33;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
        }

        .card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
        }

        .card p {
            margin: 10px;
            font-size: 0.95rem;
            color: #f0f0ff;
            text-align: center;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">QUIPVID ⚡</div>
    <div style="text-align:right; margin-bottom:20px;">
        <a href="/upload.php" class="btn-upload">➕ Submit a Quip</a>
    </div>


    <div class="search-box">
        <input type="text" id="search" placeholder="Search quips...">
    </div>
</header>

<h2>Recent</h2>
<div class="grid" id="quipGrid">
    <?php foreach ($quips as $quip): ?>
        <?php
        $name   = htmlspecialchars($quip['name'] ?? 'Untitled');
        $title  = htmlspecialchars($quip['title'] ?? '');
        $image  = htmlspecialchars($quip['image'] ?? 'https://placehold.co/300x200?text=No+Image');
        $url    = $baseDomain . ($quip['url'] ?? '#'); // prepend domain
        ?>
        <a class="card" href="<?= $url ?>" target="_blank" data-name="<?= strtolower($name . ' ' . $title) ?>">
            <img src="<?= $image ?>" alt="<?= $title ?>">
            <p><?= $name ?> <br><small><?= $title ?></small></p>
        </a>
    <?php endforeach; ?>
</div>

<script>
    const searchInput = document.getElementById('search');
    const cards = document.querySelectorAll('.card');

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        cards.forEach(card => {
            const name = card.dataset.name;
            if (name.includes(query)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });
</script>
</body>
</html>
