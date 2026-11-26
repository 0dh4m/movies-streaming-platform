<?php
/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

require_once 'includes/db.php';
$database = getDatabase();
$movies = $database['movies'] ?? [];

$selected_genre = $_GET['genre'] ?? 'all';
$sort_order = $_GET['sort'] ?? 'latest';
$top_rated = isset($_GET['top_rated']) && $_GET['top_rated'] === '1';

$genres_with_count = getGenresWithCount($movies);

if ($selected_genre !== 'all') {
    $movies = array_values(array_filter($movies, function($m) use ($selected_genre) {
        if (!isset($m['genre']) || empty($m['genre'])) return false;
        $item_genres = is_array($m['genre']) ? $m['genre'] : array_map('trim', explode(',', $m['genre']));
        $normalized = array_map(function($g) { return strtolower(trim($g)); }, $item_genres);
        return in_array(strtolower(trim($selected_genre)), $normalized);
    }));
}

if ($top_rated) {
    $movies = array_values(array_filter($movies, function($m) {
        return floatval($m['rating'] ?? 0) >= 8.0;
    }));
}

usort($movies, function($a, $b) use ($sort_order) {
    return ($sort_order === 'oldest') ? (intval($a['year']) <=> intval($b['year'])) : (intval($b['year']) <=> intval($a['year']));
});

$limit = 24;
$total_movies = count($movies);
$total_pages = ceil($total_movies / $limit) ?: 1;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$current_movies = array_slice($movies, $offset, $limit);

include 'includes/header.php';
$t = $trans ?? [];
?>

<canvas id="space-canvas" class="fixed inset-0 pointer-events-none z-0"></canvas>

<main class="min-h-screen pt-28 pb-24 px-6 sm:px-12 max-w-[1500px] mx-auto relative z-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border-b border-white/10 pb-6 gap-6">
        <div>
            <span class="text-cyan-400 text-xs font-black uppercase tracking-widest"><?php echo $t['movie_archive'] ?? 'Movie Archive'; ?></span>
            <h1 class="text-3xl sm:text-4xl font-black text-white mt-1"><?php echo $t['cinematic_movies'] ?? 'الأفلام السينمائية'; ?></h1>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <?php 
            $toggle_top_rated = $top_rated ? '0' : '1';
            $top_url = "movies.php?genre=" . urlencode($selected_genre) . "&sort={$sort_order}&top_rated={$toggle_top_rated}";
            ?>
            <a href="<?php echo $top_url; ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 <?php echo $top_rated ? 'bg-amber-400 text-black shadow-lg shadow-amber-400/30 font-black' : 'lux-glass text-gray-300 hover:text-amber-300 hover:border-amber-400/50'; ?>">
                <span>⭐</span> <?php echo $t['rating_plus_8_master'] ?? 'تنقيط +8 (Masterpieces)'; ?>
            </a>

            <form method="GET" action="movies.php" class="flex items-center">
                <?php if ($selected_genre !== 'all'): ?>
                    <input type="hidden" name="genre" value="<?php echo htmlspecialchars($selected_genre); ?>">
                <?php endif; ?>
                <?php if ($top_rated): ?>
                    <input type="hidden" name="top_rated" value="1">
                <?php endif; ?>
                <select name="sort" onchange="this.form.submit()" class="lux-glass text-gray-300 text-xs font-bold px-3 py-2.5 rounded-xl focus:outline-none focus:border-cyan-400 cursor-pointer">
                    <option value="latest" <?php echo $sort_order === 'latest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_latest_first'] ?? '📅 الأحدث أولاً'; ?></option>
                    <option value="oldest" <?php echo $sort_order === 'oldest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_oldest_first'] ?? '📅 الأقدم أولاً'; ?></option>
                </select>
            </form>
            <span class="bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 px-3.5 py-2.5 rounded-xl text-xs font-black"><?php echo $total_movies; ?> <?php echo $t['movie_badge'] ?? 'فيلم'; ?></span>
        </div>
    </div>
    
    <div class="flex flex-row-reverse items-center gap-2 overflow-x-auto pb-4 mb-8 scrollbar-none">
        <a href="movies.php?sort=<?php echo $sort_order; ?><?php echo $top_rated ? '&top_rated=1' : ''; ?>" class="px-5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?php echo $selected_genre === 'all' ? 'btn-neon-cyan text-black font-black' : 'lux-glass text-gray-300 hover:text-white'; ?>">
            🔥 <?php echo $t['all_genres'] ?? 'الكل'; ?>
        </a>
        <?php foreach ($genres_with_count as $genre => $count): ?>
            <a href="movies.php?genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort_order; ?><?php echo $top_rated ? '&top_rated=1' : ''; ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 <?php echo $selected_genre === $genre ? 'btn-neon-cyan text-black font-black' : 'lux-glass text-gray-300 hover:text-white'; ?>">
                <span><?php echo htmlspecialchars($genre); ?></span>
                <span class="text-[10px] opacity-70">(<?php echo $count; ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($current_movies)): ?>
        <div class="text-center py-20 lux-glass rounded-3xl">
            <p class="text-gray-400 text-sm font-bold"><?php echo $t['no_movies_match'] ?? 'لا توجد أفلام مطابقة لهذا الفلتر.'; ?></p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach ($current_movies as $movie): ?>
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
        </div>
    <?php endif; ?>

    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center items-center gap-3 mt-14">
        <?php if ($page > 1): ?>
            <a href="movies.php?genre=<?php echo urlencode($selected_genre); ?>&sort=<?php echo $sort_order; ?><?php echo $top_rated ? '&top_rated=1' : ''; ?>&page=<?php echo $page - 1; ?>" class="lux-glass px-4 py-2 rounded-xl text-xs font-bold hover:border-cyan-400 transition-colors"><?php echo $t['prev'] ?? 'السابق'; ?></a>
        <?php endif; ?>
        <span class="lux-glass px-5 py-2 rounded-xl text-xs font-bold text-gray-300"><?php echo $t['page'] ?? 'صفحة'; ?> <span class="text-cyan-400 font-bold"><?php echo $page; ?></span> <?php echo $t['of'] ?? 'من'; ?> <?php echo $total_pages; ?></span>
        <?php if ($page < $total_pages): ?>
            <a href="movies.php?genre=<?php echo urlencode($selected_genre); ?>&sort=<?php echo $sort_order; ?><?php echo $top_rated ? '&top_rated=1' : ''; ?>&page=<?php echo $page + 1; ?>" class="lux-glass px-4 py-2 rounded-xl text-xs font-bold hover:border-cyan-400 transition-colors"><?php echo $t['next'] ?? 'التالي'; ?></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<!-- (أكواد المودال تبقى كما هي من الملف السابق لأنها تعتمد على الجافاسكريبت والترجمة) -->
<?php include 'includes/footer.php'; ?>