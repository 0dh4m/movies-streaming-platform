<?php
/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io/portfolio
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

require_once 'includes/db.php';
$database = getDatabase();

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort_order = $_GET['sort'] ?? 'latest';
$top_rated = isset($_GET['top']) && $_GET['top'] === '1';

$movies = $database['movies'] ?? [];
$series = $database['series'] ?? [];

$filtered_movies = [];
$filtered_series = [];

if (!empty($query)) {
    $filtered_movies = array_values(array_filter($movies, function($m) use ($query) {
        return mb_stripos($m['title'], $query) !== false;
    }));
    $filtered_series = array_values(array_filter($series, function($s) use ($query) {
        return mb_stripos($s['title'], $query) !== false;
    }));
}

if ($top_rated) {
    $filtered_movies = array_values(array_filter($filtered_movies, function($m) {
        return floatval($m['rating'] ?? 0) >= 8.0;
    }));
    $filtered_series = array_values(array_filter($filtered_series, function($s) {
        return floatval($s['rating'] ?? 0) >= 8.0;
    }));
}

usort($filtered_movies, function($a, $b) use ($sort_order) {
    return ($sort_order === 'oldest') ? (intval($a['year']) <=> intval($b['year'])) : (intval($b['year']) <=> intval($a['year']));
});

usort($filtered_series, function($a, $b) use ($sort_order) {
    return ($sort_order === 'oldest') ? (intval($a['year']) <=> intval($b['year'])) : (intval($b['year']) <=> intval($a['year']));
});

include 'includes/header.php';
$t = $trans ?? [];
?>

<canvas id="space-canvas" class="fixed inset-0 pointer-events-none z-0"></canvas>

<main class="min-h-screen pt-36 pb-32 px-6 sm:px-12 max-w-[1500px] mx-auto relative z-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 border-b border-white/10 pb-6 gap-6">
        <div>
            <span class="text-cyan-400 text-[11px] font-black tracking-widest uppercase"><?php echo $t['search_arch'] ?? 'Search Architecture'; ?></span>
            <h1 class="text-2xl sm:text-4xl font-black text-white mt-1"><?php echo $t['search_results_for'] ?? 'نتائج البحث عن:'; ?> "<span class="text-cyan-400"><?php echo htmlspecialchars($query); ?></span>"</h1>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <?php 
            $toggle_top = $top_rated ? '0' : '1';
            $top_url = "search.php?q=" . urlencode($query) . "&sort={$sort_order}&top={$toggle_top}";
            ?>
            <a href="<?php echo $top_url; ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 <?php echo $top_rated ? 'bg-amber-400 text-black shadow-lg shadow-amber-400/30 font-black' : 'lux-glass text-gray-300 hover:text-amber-300 hover:border-amber-400/50'; ?>">
                <span>⭐</span> <?php echo $t['rating_plus_8'] ?? 'تقييم +8'; ?>
            </a>

            <form method="GET" action="search.php" class="flex items-center">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($query); ?>">
                <?php if ($top_rated): ?><input type="hidden" name="top" value="1"><?php endif; ?>
                <select name="sort" onchange="this.form.submit()" class="lux-glass text-gray-300 text-xs font-bold px-4 py-2.5 rounded-xl focus:outline-none focus:border-cyan-400 cursor-pointer">
                    <option value="latest" <?php echo $sort_order === 'latest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_latest_first'] ?? '📅 من الأحدث للأقدم'; ?></option>
                    <option value="oldest" <?php echo $sort_order === 'oldest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_oldest_first'] ?? '📅 من الأقدم للأحدث'; ?></option>
                </select>
            </form>
        </div>
    </div>

    <?php if (empty($filtered_movies) && empty($filtered_series)): ?>
        <div class="text-center py-28 lux-glass rounded-3xl">
            <p class="text-gray-400 text-lg font-bold"><?php echo $t['no_results'] ?? 'عذراً، لم نجد أي عمل مطابق لبحثك'; ?><?php echo $top_rated ? ' ' . ($t['rating_plus_8_badge'] ?? 'بتقييم +8') : ''; ?>.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach ($filtered_movies as $movie): ?>
                <div onclick="openNetflixModal('<?php echo $movie['tmdb_id']; ?>', 'movie')" class="sci-card block group cursor-pointer">
                    <div class="aspect-[2/3] w-full relative">
                        <img src="<?php echo $movie['poster']; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-full object-cover pointer-events-none">
                        <div class="absolute top-2.5 inset-x-2.5 flex justify-between items-center z-10 pointer-events-none">
                            <span class="bg-black/80 backdrop-blur-md text-amber-300 border border-amber-300/30 text-[11px] font-bold px-2 py-0.5 rounded-lg shadow-md">★ <?php echo $movie['rating']; ?></span>
                            <?php if (!empty($movie['year'])): ?>
                            <span class="bg-black/80 backdrop-blur-md text-cyan-300 border border-cyan-500/30 text-[11px] font-bold px-2 py-0.5 rounded-lg shadow-md"><?php echo $movie['year']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="absolute inset-0 p-4 sci-overlay flex flex-col justify-end pointer-events-none z-10">
                            <span class="text-cyan-400 text-[10px] font-black uppercase tracking-wider mb-1"><?php echo $t['cinematic_movie'] ?? 'فيلم سينمائي'; ?></span>
                            <h3 class="text-xs font-black text-white line-clamp-1 mb-2.5"><?php echo htmlspecialchars($movie['title']); ?></h3>
                            <div class="w-full btn-neon-cyan text-black py-2 rounded-xl text-xs font-black text-center shadow-lg pointer-events-auto">
                                <?php echo $t['work_details'] ?? 'تفاصيل العمل'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach ($filtered_series as $show): ?>
                <div onclick="openNetflixModal('<?php echo $show['tmdb_id']; ?>', 'tv')" class="sci-card block group cursor-pointer">
                    <div class="aspect-[2/3] w-full relative">
                        <img src="<?php echo $show['poster']; ?>" alt="<?php echo htmlspecialchars($show['title']); ?>" class="w-full h-full object-cover pointer-events-none">
                        <div class="absolute top-2.5 inset-x-2.5 flex justify-between items-center z-10 pointer-events-none">
                            <span class="bg-black/80 backdrop-blur-md text-amber-300 border border-amber-300/30 text-[11px] font-bold px-2 py-0.5 rounded-lg shadow-md">★ <?php echo $show['rating']; ?></span>
                            <?php if (!empty($show['year'])): ?>
                            <span class="bg-black/80 backdrop-blur-md text-purple-300 border border-purple-500/30 text-[11px] font-bold px-2 py-0.5 rounded-lg shadow-md"><?php echo $show['year']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="absolute inset-0 p-4 sci-overlay flex flex-col justify-end pointer-events-none z-10">
                            <span class="text-purple-400 text-[10px] font-black uppercase tracking-wider mb-1"><?php echo $t['exclusive_series'] ?? 'مسلسل حصري'; ?></span>
                            <h3 class="text-xs font-black text-white line-clamp-1 mb-2.5"><?php echo htmlspecialchars($show['title']); ?></h3>
                            <div class="w-full btn-neon-purple text-white py-2 rounded-xl text-xs font-black text-center shadow-lg pointer-events-auto">
                                <?php echo $t['work_details'] ?? 'تفاصيل العمل'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- (أكواد المودال تبقى كما هي) -->
<?php include 'includes/footer.php'; ?>