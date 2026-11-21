<?php
// TMDB API Configuration
defined('TMDB_API_KEY') or define('TMDB_API_KEY', getenv('TMDB_API_KEY') ?: '4e44d9029b1270a757cddc766a1bcb63');

/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io/portfolio
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

$host = 'sql111.ezyro.com'; 
$db   = 'ezyro_42788498_cinehub_db';
$user = 'ezyro_42788498'; 
$pass = 'أكتب_كلمة_سر_cpanel_هنا'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

function getDatabase() {
    global $pdo;

    try {
        $stmt_movies = $pdo->prepare("SELECT * FROM media_items WHERE type = 'movie' ORDER BY id DESC");
        $stmt_movies->execute();
        $movies = $stmt_movies->fetchAll(PDO::FETCH_ASSOC);

        $stmt_series = $pdo->prepare("SELECT * FROM media_items WHERE type = 'series' ORDER BY id DESC");
        $stmt_series->execute();
        $series = $stmt_series->fetchAll(PDO::FETCH_ASSOC);

        return [
            'movies' => $movies ?: [],
            'series' => $series ?: []
        ];
    } catch (\PDOException $e) {
        return [
            'movies' => [],
            'series' => []
        ];
    }
}

function getGenresWithCount($items) {
    $counts = [];

    if (!is_array($items)) return $counts;

    foreach ($items as $item) {
        if (empty($item['genre'])) continue;

        $genres = is_array($item['genre']) 
            ? $item['genre'] 
            : array_map('trim', explode(',', $item['genre']));

        foreach ($genres as $genre) {
            $genre = trim($genre);
            if ($genre === '') continue;

            if (!isset($counts[$genre])) {
                $counts[$genre] = 0;
            }
            $counts[$genre]++;
        }
    }

    arsort($counts);
    return $counts;
}
?>