<?php
/**
 * Import data/movies.json into the media_items table.
 *
 * Usage:
 *   php scripts/import_movies.php
 *   php scripts/import_movies.php --limit=2000
 *
 * --limit applies per content type (movies and series).
 */

require_once __DIR__ . '/../includes/db.php';

$limit = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limit = max(0, (int) substr($arg, strlen('--limit=')));
    }
}

$jsonPath = __DIR__ . '/../data/movies.json';
if (!is_file($jsonPath)) {
    fwrite(STDERR, "Missing catalog file: data/movies.json\n");
    exit(1);
}

$payload = json_decode(file_get_contents($jsonPath), true);
if (!is_array($payload)) {
    fwrite(STDERR, "Could not decode data/movies.json\n");
    exit(1);
}

$insert = $pdo->prepare(
    "INSERT INTO media_items (tmdb_id, type, title, poster, rating, year, genre)
     VALUES (:tmdb_id, :type, :title, :poster, :rating, :year, :genre)
     ON DUPLICATE KEY UPDATE
       title = VALUES(title),
       poster = VALUES(poster),
       rating = VALUES(rating),
       year = VALUES(year),
       genre = VALUES(genre)"
);

$imported = 0;
$skipped = 0;

try {
    $pdo->beginTransaction();

    foreach (['movies' => 'movie', 'series' => 'series'] as $key => $type) {
        $items = $payload[$key] ?? [];
        if (!is_array($items)) {
            continue;
        }

        $count = 0;
        foreach ($items as $item) {
            if ($limit !== null && $count >= $limit) {
                break;
            }

            $tmdbId = trim((string)($item['tmdb_id'] ?? ''));
            $title = trim((string)($item['title'] ?? ''));

            if ($tmdbId === '' || $title === '') {
                $skipped++;
                continue;
            }

            $genres = $item['genre'] ?? [];
            if (is_array($genres)) {
                $genres = implode(', ', array_map('trim', $genres));
            }

            $insert->execute([
                ':tmdb_id' => $tmdbId,
                ':type' => $type,
                ':title' => $title,
                ':poster' => $item['poster'] ?? null,
                ':rating' => $item['rating'] ?? null,
                ':year' => $item['year'] ?? null,
                ':genre' => $genres ?: null,
            ]);

            $imported++;
            $count++;
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Import failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Imported/updated {$imported} items";
if ($skipped > 0) {
    echo " ({$skipped} skipped)";
}
echo ".\n";
