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
$series = $database['series'] ?? [];

$movie_sort = $_GET['movie_sort'] ?? 'latest';
$series_sort = $_GET['series_sort'] ?? 'latest';
$movie_top = isset($_GET['movie_top']) && $_GET['movie_top'] === '1';
$series_top = isset($_GET['series_top']) && $_GET['series_top'] === '1';

// تصفية الأفلام +8
if ($movie_top) {
    $movies = array_values(array_filter($movies, function($m) {
        return floatval($m['rating'] ?? 0) >= 8.0;
    }));
}

// تصفية المسلسلات +8
if ($series_top) {
    $series = array_values(array_filter($series, function($s) {
        return floatval($s['rating'] ?? 0) >= 8.0;
    }));
}

// ترتيب الأفلام
usort($movies, function($a, $b) use ($movie_sort) {
    return ($movie_sort === 'oldest') ? (intval($a['year']) <=> intval($b['year'])) : (intval($b['year']) <=> intval($a['year']));
});

// ترتيب المسلسلات
usort($series, function($a, $b) use ($series_sort) {
    return ($series_sort === 'oldest') ? (intval($a['year']) <=> intval($b['year'])) : (intval($b['year']) <=> intval($a['year']));
});

// اختيار فيلم قوي للهيرو
$api_key = TMDB_API_KEY;
$popular_heroes = array_values(array_filter($database['movies'] ?? [], function($m) {
    return floatval($m['rating'] ?? 0) >= 6.5;
}));

$hero = !empty($popular_heroes) ? $popular_heroes[array_rand($popular_heroes)] : (!empty($movies) ? $movies[0] : null);
$hero_trailer_key = '';

if ($hero && !empty($hero['tmdb_id'])) {
    $ctx = stream_context_create(['http' => ['timeout' => 1.2]]);
    $video_json = @file_get_contents("https://api.themoviedb.org/3/movie/{$hero['tmdb_id']}/videos?api_key={$api_key}&language=en-US", false, $ctx);
    if ($video_json) {
        $video_data = json_decode($video_json, true);
        if (!empty($video_data['results'])) {
            foreach ($video_data['results'] as $v) {
                if ($v['site'] === 'YouTube' && ($v['type'] === 'Trailer' || $v['type'] === 'Teaser')) {
                    $hero_trailer_key = $v['key'];
                    break;
                }
            }
            if (empty($hero_trailer_key) && isset($video_data['results'][0]['key'])) {
                $hero_trailer_key = $video_data['results'][0]['key'];
            }
        }
    }
}

// استدعاء الهيدر وتحميل الترجمة
include 'includes/header.php';
$t = $trans ?? [];
?>

<canvas id="space-canvas" class="fixed inset-0 pointer-events-none z-0"></canvas>

<?php if ($hero): ?>
<section class="relative min-h-[92vh] w-full flex items-center px-6 lg:px-20 pt-32 pb-20 overflow-hidden z-10">
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <?php if (!empty($hero_trailer_key)): ?>
            <iframe 
                src="https://www.youtube-nocookie.com/embed/<?php echo $hero_trailer_key; ?>?autoplay=1&mute=1&controls=0&loop=1&playlist=<?php echo $hero_trailer_key; ?>&playsinline=1&enablejsapi=1&rel=0&iv_load_policy=3" 
                class="w-[140%] h-[140%] -top-[20%] -right-[20%] absolute opacity-80" 
                allow="autoplay; encrypted-media" 
                allowfullscreen>
            </iframe>
        <?php else: ?>
            <img src="<?php echo $hero['poster']; ?>" class="w-full h-full object-cover filter blur-[45px] opacity-35 scale-125" alt="">
        <?php endif; ?>
        
        <div class="absolute inset-0 bg-gradient-to-t from-[#020204] via-transparent to-[#020204]/50"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#020204] via-[#020204]/60 to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto md:mx-0 flex flex-col md:flex-row items-center gap-10 mt-6">
        <div class="w-52 sm:w-64 aspect-[2/3] rounded-3xl overflow-hidden border border-white/20 shadow-[0_25px_60px_rgba(0,0,0,0.9)] flex-shrink-0 card-perspective">
            <img src="<?php echo $hero['poster']; ?>" class="w-full h-full object-cover" alt="">
        </div>

        <div>
            <div class="inline-flex items-center gap-2 bg-cyan-500/20 border border-cyan-500/40 px-4 py-1.5 rounded-full text-xs font-black text-cyan-300 mb-4 backdrop-blur-md shadow-[0_0_15px_rgba(6,182,212,0.25)]">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span> <?php echo $t['hero_badge'] ?? 'عرض اليوم المختار'; ?>
            </div>
            
            <h1 class="text-3xl sm:text-6xl font-black text-white tracking-tight mb-4 leading-tight drop-shadow-2xl">
                <?php echo htmlspecialchars($hero['title']); ?>
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-gray-300 mb-6">
                <span class="bg-amber-400/20 border border-amber-400/40 text-amber-300 px-3.5 py-1.5 rounded-xl backdrop-blur-md shadow-md">⭐ <?php echo $hero['rating']; ?> TMDb</span>
                <?php if (!empty($hero['year'])): ?>
                <span class="lux-glass px-3.5 py-1.5 rounded-xl">📅 <?php echo $hero['year']; ?></span>
                <?php endif; ?>
                <span class="lux-glass px-3.5 py-1.5 rounded-xl text-cyan-300 border-cyan-500/30">4K ULTRA HD</span>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <a href="watch.php?id=<?php echo $hero['tmdb_id']; ?>&type=movie" class="btn-neon-cyan px-8 py-3.5 rounded-xl font-black text-black text-xs tracking-wider inline-flex items-center gap-2 transition-all">
                    <?php echo $t['play_movie'] ?? '▶ تشغيل الفيلم'; ?>
                </a>
                <button onclick="openNetflixModal('<?php echo $hero['tmdb_id']; ?>', 'movie')" class="lux-glass hover:border-cyan-400 px-6 py-3.5 rounded-xl font-bold text-white text-xs tracking-wider inline-flex items-center gap-2 transition-all interactive-hover">
                    <?php echo $t['more_info'] ?? 'ℹ️ مزيد من المعلومات'; ?>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<main class="max-w-[1500px] mx-auto px-6 sm:px-12 relative z-20 pb-28">
    
    <!-- قسم متابعة المشاهدة -->
    <div id="continue-watching-section" class="mb-16 hidden">
        <div class="flex items-center justify-between mb-6 border-b border-white/10 pb-4">
            <div>
                <span class="text-amber-400 text-xs font-black uppercase tracking-wider"><?php echo $t['resume_subtitle'] ?? 'Resume Playback'; ?></span>
                <h2 class="text-2xl sm:text-3xl font-black text-white mt-1"><?php echo $t['continue_watching'] ?? 'متابعة المشاهدة ⏱️'; ?></h2>
            </div>
        </div>
        <div id="continue-watching-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6"></div>
    </div>

    <!-- قسم الأفلام -->
    <div class="mb-16">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4 border-b border-white/10 pb-4">
            <div>
                <span class="text-cyan-400 text-xs font-black uppercase tracking-wider"><?php echo $t['cinema_catalog'] ?? 'Cinema Catalog'; ?></span>
                <h2 class="text-2xl sm:text-3xl font-black text-white mt-1"><?php echo $t['trending_movies'] ?? 'الأفلام الأكثر طلباً'; ?></h2>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <?php 
                $toggle_movie_top = $movie_top ? '0' : '1';
                $movie_top_url = "index.php?movie_sort={$movie_sort}&series_sort={$series_sort}&series_top=" . ($series_top ? '1' : '0') . "&movie_top={$toggle_movie_top}";
                ?>
                <a href="<?php echo $movie_top_url; ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 <?php echo $movie_top ? 'bg-amber-400 text-black shadow-lg shadow-amber-400/30 font-black' : 'lux-glass text-gray-300 hover:text-amber-300 hover:border-amber-400/50'; ?>">
                    <span>⭐</span> <?php echo $t['rating_plus_8'] ?? 'تقييم +8'; ?>
                </a>

                <form method="GET" action="index.php" class="flex items-center">
                    <input type="hidden" name="series_sort" value="<?php echo htmlspecialchars($series_sort); ?>">
                    <?php if ($series_top): ?><input type="hidden" name="series_top" value="1"><?php endif; ?>
                    <?php if ($movie_top): ?><input type="hidden" name="movie_top" value="1"><?php endif; ?>
                    <select name="movie_sort" onchange="this.form.submit()" class="lux-glass text-gray-300 text-xs font-bold px-3 py-2 rounded-xl focus:outline-none focus:border-cyan-400 cursor-pointer">
                        <option value="latest" <?php echo $movie_sort === 'latest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_latest'] ?? 'الأحدث'; ?></option>
                        <option value="oldest" <?php echo $movie_sort === 'oldest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_oldest'] ?? 'الأقدم'; ?></option>
                    </select>
                </form>

                <a href="movies.php" class="text-xs font-bold text-gray-400 hover:text-cyan-400 transition-colors mx-1"><?php echo $t['view_all'] ?? 'عرض الكل'; ?></a>
            </div>
        </div>

        <?php if (empty($movies)): ?>
            <div class="text-center py-12 lux-glass rounded-2xl">
                <p class="text-gray-400 text-xs font-bold"><?php echo $t['no_movies_8'] ?? 'لا توجد أفلام بتقييم +8 حالياً.'; ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <?php foreach (array_slice($movies, 0, 12) as $movie): ?>
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
                                <span class="text-cyan-400 text-[10px] font-black uppercase tracking-wider mb-1"><?php echo $t['movie_badge'] ?? 'فيلم سينمائي'; ?></span>
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
    </div>

    <!-- قسم المسلسلات -->
    <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4 border-b border-white/10 pb-4">
            <div>
                <span class="text-purple-400 text-xs font-black uppercase tracking-wider"><?php echo $t['series_catalog'] ?? 'Series Catalog'; ?></span>
                <h2 class="text-2xl sm:text-3xl font-black text-white mt-1"><?php echo $t['exclusive_series'] ?? 'المسلسلات الحصرية'; ?></h2>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <?php 
                $toggle_series_top = $series_top ? '0' : '1';
                $series_top_url = "index.php?movie_sort={$movie_sort}&series_sort={$series_sort}&movie_top=" . ($movie_top ? '1' : '0') . "&series_top={$toggle_series_top}";
                ?>
                <a href="<?php echo $series_top_url; ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 <?php echo $series_top ? 'bg-amber-400 text-black shadow-lg shadow-amber-400/30 font-black' : 'lux-glass text-gray-300 hover:text-amber-300 hover:border-amber-400/50'; ?>">
                    <span>⭐</span> <?php echo $t['rating_plus_8'] ?? 'تقييم +8'; ?>
                </a>

                <form method="GET" action="index.php" class="flex items-center">
                    <input type="hidden" name="movie_sort" value="<?php echo htmlspecialchars($movie_sort); ?>">
                    <?php if ($movie_top): ?><input type="hidden" name="movie_top" value="1"><?php endif; ?>
                    <?php if ($series_top): ?><input type="hidden" name="series_top" value="1"><?php endif; ?>
                    <select name="series_sort" onchange="this.form.submit()" class="lux-glass text-gray-300 text-xs font-bold px-3 py-2 rounded-xl focus:outline-none focus:border-purple-400 cursor-pointer">
                        <option value="latest" <?php echo $series_sort === 'latest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_latest'] ?? 'الأحدث'; ?></option>
                        <option value="oldest" <?php echo $series_sort === 'oldest' ? 'selected' : ''; ?> class="bg-[#0e0e17]"><?php echo $t['sort_oldest'] ?? 'الأقدم'; ?></option>
                    </select>
                </form>

                <a href="series.php" class="text-xs font-bold text-gray-400 hover:text-purple-400 transition-colors mx-1"><?php echo $t['view_all'] ?? 'عرض الكل'; ?></a>
            </div>
        </div>

        <?php if (empty($series)): ?>
            <div class="text-center py-12 lux-glass rounded-2xl">
                <p class="text-gray-400 text-xs font-bold"><?php echo $t['no_series_8'] ?? 'لا توجد مسلسلات بتقييم +8 حالياً.'; ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <?php foreach (array_slice($series, 0, 12) as $show): ?>
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
                                <span class="text-purple-400 text-[10px] font-black uppercase tracking-wider mb-1"><?php echo $t['series_badge'] ?? 'مسلسل حصري'; ?></span>
                                <h3 class="text-xs font-black text-white line-clamp-1 mb-2.5"><?php echo htmlspecialchars($show['title']); ?></h3>
                                <div class="w-full btn-neon-purple text-white py-2 rounded-xl text-xs font-black text-center shadow-lg pointer-events-auto">
                                    <?php echo $t['episodes_seasons'] ?? 'الحلقات والمواسم'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- نافذة المعاينة السريعة الهولوغرامية (Netflix Quick Preview Modal) -->
<div id="netflix-modal" class="fixed inset-0 bg-black/85 backdrop-blur-xl z-50 hidden flex items-center justify-center p-4">
    <div class="relative w-full max-w-3xl bg-[#0d0d18]/95 border border-white/15 rounded-3xl overflow-hidden shadow-[0_25px_90px_rgba(0,0,0,0.95)] max-h-[90vh] overflow-y-auto scrollbar-none">
        <button onclick="closeNetflixModal()" class="absolute top-4 right-4 z-30 bg-black/70 hover:bg-rose-600 text-white w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm transition-all shadow-lg">✕</button>

        <div class="relative w-full aspect-video bg-black">
            <iframe id="modal-trailer-iframe" class="w-full h-full border-0 absolute inset-0 hidden" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            <img id="modal-backdrop-img" class="w-full h-full object-cover" src="" alt="">
            <div class="absolute inset-0 bg-gradient-to-t from-[#0d0d18] via-[#0d0d18]/40 to-transparent"></div>
            
            <div class="absolute bottom-6 right-6 left-6 flex items-end justify-between gap-4">
                <div>
                    <h2 id="modal-title" class="text-2xl sm:text-4xl font-black text-white drop-shadow-lg mb-2"></h2>
                    <div class="flex items-center gap-3 text-xs font-bold">
                        <span id="modal-rating" class="text-amber-300">★ 0.0</span>
                        <span id="modal-year" class="text-gray-300">2026</span>
                        <span id="modal-type-badge" class="bg-cyan-500/20 text-cyan-300 px-2.5 py-0.5 rounded-md uppercase text-[10px]"></span>
                        <span class="border border-white/30 text-gray-300 px-2 py-0.5 rounded text-[10px]">Ultra HD</span>
                    </div>
                </div>

                <a id="modal-watch-btn" href="#" class="btn-neon-cyan px-7 py-3 rounded-xl font-black text-black text-xs inline-flex items-center gap-2 flex-shrink-0 transition-all">
                    ▶ <?php echo $t['play'] ?? 'تشغيل'; ?>
                </a>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2" id="modal-story-title"><?php echo $t['story'] ?? 'قصة العمل'; ?></h4>
            <p id="modal-overview" class="text-sm text-gray-200 leading-relaxed mb-6 font-medium"><?php echo $t['loading'] ?? 'جاري تحميل التفاصيل...'; ?></p>

            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-white/10 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 font-bold"><?php echo $t['category'] ?? 'التصنيف:'; ?></span>
                    <span id="modal-genres" class="text-cyan-300 font-bold"></span>
                </div>
                <button id="modal-fav-btn" onclick="toggleModalFavorite()" class="lux-glass text-amber-300 px-4 py-2 rounded-xl font-bold hover:border-amber-400 transition-all flex items-center gap-1.5">
                    <span>⭐</span> <?php echo $t['add_to_fav'] ?? 'حفظ في المفضلة'; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// 1. أكواد قسم "متابعة المشاهدة"
const idxLang = <?php echo json_encode([
    'continue_btn' => $t['continue_btn'] ?? '▶ متابعة',
    'resume_btn' => $t['resume_btn'] ?? 'استكمال',
    'season_prefix' => $t['season_prefix'] ?? 'م',
    'episode_prefix' => $t['episode_prefix'] ?? 'ح'
]); ?>;

document.addEventListener('DOMContentLoaded', () => {
    const list = JSON.parse(localStorage.getItem('cinema_continue_watching') || '[]');
    const section = document.getElementById('continue-watching-section');
    const grid = document.getElementById('continue-watching-grid');

    if (list.length > 0 && section && grid) {
        section.classList.remove('hidden');
        grid.innerHTML = list.map(item => `
            <a href="watch.php?id=${item.id}&type=${item.type}&season=${item.season || 1}&episode=${item.episode || 1}" class="sci-card block group border-amber-500/30">
                <div class="aspect-[2/3] w-full relative">
                    <img src="${item.poster}" alt="${item.title}" class="w-full h-full object-cover pointer-events-none">
                    <div class="absolute top-2.5 inset-x-2.5 flex justify-between items-center z-10 pointer-events-none">
                        <span class="bg-amber-500 text-black text-[10px] font-black px-1.5 py-0.5 rounded-md shadow-md">${idxLang.continue_btn}</span>
                        ${item.type === 'tv' ? `<span class="bg-black/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">${idxLang.season_prefix}${item.season} ${idxLang.episode_prefix}${item.episode}</span>` : ''}
                    </div>
                    <div class="absolute inset-0 p-3 sci-overlay flex flex-col justify-end pointer-events-none">
                        <h3 class="text-xs font-black text-white line-clamp-1 mb-2">${item.title}</h3>
                        <div class="w-full bg-amber-400 text-black py-1.5 rounded-lg text-xs font-black text-center shadow-lg">${idxLang.resume_btn}</div>
                    </div>
                </div>
            </a>
        `).join('');
    }
});

// 2. أكواد النافذة المنبثقة (Modal Engine) المترجمة
const apiKey = "<?= TMDB_API_KEY ?>";
let activeModalItem = null;

const modalLang = <?php echo json_encode([
    'loading' => $t['loading'] ?? 'جاري تحميل التفاصيل...',
    'save_fav' => $t['add_to_fav'] ?? 'حفظ في المفضلة',
    'saved_fav' => $t['saved_to_fav'] ?? 'محفوظ في المفضلة',
    'no_desc' => $t['no_desc'] ?? 'لا يتوفر ملخص نصي لهذا العمل حالياً.',
    'general' => $t['general'] ?? 'عام',
    'series' => $t['series_badge'] ?? 'مسلسل',
    'movie' => $t['movie_badge'] ?? 'فيلم',
    'untitled' => $t['untitled'] ?? 'بدون عنوان',
    'error_loading' => $t['error_loading'] ?? 'تعذر تحميل التفاصيل. يمكنك المتابعة للمشاهدة مباشرة.',
    'play_movie' => $t['play'] ?? 'تشغيل',
    'play_series' => $t['play_episodes'] ?? 'تشغيل الحلقات'
]); ?>;

async function fetchTrailerKey(id, type) {
    try {
        const res = await fetch(`https://api.themoviedb.org/3/${type}/${id}/videos?api_key=${apiKey}&language=en-US`);
        const data = await res.json();
        
        if (data.results && data.results.length > 0) {
            let vid = data.results.find(v => v.site === 'YouTube' && v.type === 'Trailer');
            if (!vid) vid = data.results.find(v => v.site === 'YouTube' && v.type === 'Teaser');
            if (!vid) vid = data.results.find(v => v.site === 'YouTube');
            return vid ? vid.key : null;
        }
    } catch (e) { return null; }
    return null;
}

async function openNetflixModal(id, type) {
    const modal = document.getElementById('netflix-modal');
    const iframe = document.getElementById('modal-trailer-iframe');
    const backdropImg = document.getElementById('modal-backdrop-img');
    const titleEl = document.getElementById('modal-title');
    const ratingEl = document.getElementById('modal-rating');
    const yearEl = document.getElementById('modal-year');
    const overviewEl = document.getElementById('modal-overview');
    const genresEl = document.getElementById('modal-genres');
    const typeBadge = document.getElementById('modal-type-badge');
    const watchBtn = document.getElementById('modal-watch-btn');
    const favBtn = document.getElementById('modal-fav-btn');
    const lang = document.documentElement.lang === 'ar' ? 'ar' : 'en-US';

    iframe.classList.add('hidden');
    iframe.src = '';
    overviewEl.innerText = modalLang.loading;
    modal.classList.remove('hidden');

    try {
        const resAr = await fetch(`https://api.themoviedb.org/3/${type}/${id}?api_key=${apiKey}&language=${lang}`);
        const dataAr = await resAr.json();

        let title = dataAr.title || dataAr.name;
        let overview = dataAr.overview;
        const rating = (dataAr.vote_average || 0).toFixed(1);
        const year = (dataAr.release_date || dataAr.first_air_date || '').substring(0, 4);
        const backdrop = dataAr.backdrop_path ? `https://image.tmdb.org/t/p/w780${dataAr.backdrop_path}` : (dataAr.poster_path ? `https://image.tmdb.org/t/p/w500${dataAr.poster_path}` : '');
        let genres = dataAr.genres && dataAr.genres.length > 0 ? dataAr.genres.map(g => g.name).join(' • ') : '';

        if (!overview || !overview.trim() || !title || !genres) {
            const resEn = await fetch(`https://api.themoviedb.org/3/${type}/${id}?api_key=${apiKey}&language=en-US`);
            const dataEn = await resEn.json();
            if (!title) title = dataEn.title || dataEn.name || modalLang.untitled;
            if (!overview || !overview.trim()) overview = dataEn.overview;
            if (!genres && dataEn.genres) genres = dataEn.genres.map(g => g.name).join(' • ');
        }

        titleEl.innerText = title || modalLang.untitled;
        ratingEl.innerText = `★ ${rating}`;
        yearEl.innerText = year || '2026';
        overviewEl.innerText = overview && overview.trim() ? overview : modalLang.no_desc;
        genresEl.innerText = genres || modalLang.general;
        typeBadge.innerText = type === 'tv' ? modalLang.series : modalLang.movie;
        backdropImg.src = backdrop;
        watchBtn.href = `watch.php?id=${id}&type=${type}`;
        watchBtn.innerHTML = `▶ ${type === 'tv' ? modalLang.play_series : modalLang.play_movie}`;

        activeModalItem = { id: id, type: type, title: title, poster: backdrop, rating: rating };

        const favs = JSON.parse(localStorage.getItem('cinema_favorites') || '[]');
        const isFav = favs.some(i => i.id === id && i.type === type);
        favBtn.innerHTML = isFav ? `<span>✔️</span> ${modalLang.saved_fav}` : `<span>⭐</span> ${modalLang.save_fav}`;

        const videoKey = await fetchTrailerKey(id, type);
        if (videoKey) {
            iframe.src = `https://www.youtube-nocookie.com/embed/${videoKey}?autoplay=1&controls=1&rel=0`;
            iframe.classList.remove('hidden');
        }
    } catch (e) {
        overviewEl.innerText = modalLang.error_loading;
        watchBtn.href = `watch.php?id=${id}&type=${type}`;
        watchBtn.innerHTML = `▶ ${type === 'tv' ? modalLang.play_series : modalLang.play_movie}`;
    }
}

function closeNetflixModal() {
    const modal = document.getElementById('netflix-modal');
    const iframe = document.getElementById('modal-trailer-iframe');
    iframe.src = '';
    iframe.classList.add('hidden');
    modal.classList.add('hidden');
}

function toggleModalFavorite() {
    if (!activeModalItem) return;
    let favs = JSON.parse(localStorage.getItem('cinema_favorites') || '[]');
    const index = favs.findIndex(i => i.id === activeModalItem.id && i.type === activeModalItem.type);
    const favBtn = document.getElementById('modal-fav-btn');

    if (index > -1) {
        favs.splice(index, 1);
        favBtn.innerHTML = `<span>⭐</span> ${modalLang.save_fav}`;
    } else {
        favs.push(activeModalItem);
        favBtn.innerHTML = `<span>✔️</span> ${modalLang.saved_fav}`;
    }
    localStorage.setItem('cinema_favorites', JSON.stringify(favs));
}

window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('netflix-modal')) closeNetflixModal();
});
</script>

<?php include 'includes/footer.php'; ?>