<?php
// TMDB API Configuration (optional: only used by the details modal)
defined('TMDB_API_KEY') or define('TMDB_API_KEY', getenv('TMDB_API_KEY') ?: '');

/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

$host = getenv('MOVIES_DB_HOST') ?: 'localhost';
$db   = getenv('MOVIES_DB_NAME') ?: 'cinehub_db';
$user = getenv('MOVIES_DB_USER') ?: 'root';
$pass = getenv('MOVIES_DB_PASS') ?: '';
$port = getenv('MOVIES_DB_PORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
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

/**
 * Record one visit per browser session for the admin analytics page.
 * Analytics must never break the public site.
 */
function logVisitor($pdo) {
    if (!$pdo instanceof PDO) {
        return;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['visitor_logged'])) {
        return;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $browser = 'Unknown';
    foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'] as $needle => $label) {
        if (stripos($ua, $needle) !== false) {
            $browser = $label;
            break;
        }
    }

    $os = 'Unknown';
    foreach (['Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iOS', 'iPad' => 'iOS', 'Mac OS' => 'macOS', 'Linux' => 'Linux'] as $needle => $label) {
        if (stripos($ua, $needle) !== false) {
            $os = $label;
            break;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO visitors_log (ip_address, browser, os) VALUES (?, ?, ?)");
        $stmt->execute([$ip, $browser, $os]);
        $_SESSION['visitor_logged'] = true;
    } catch (\PDOException $e) {
        // Ignore analytics failures.
    }
}
?>