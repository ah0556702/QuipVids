<?php // /src/util.php
declare(strict_types=1);

function e(string $s = null): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
