<?php
/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io/portfolio
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

require_once 'includes/db.php';
include 'includes/header.php';
$t = $trans ?? [];
?>

<canvas id="space-canvas" class="fixed inset-0 pointer-events-none z-0"></canvas>

<main class="min-h-screen pt-36 pb-32 px-6 sm:px-12 max-w-[1500px] mx-auto relative z-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 border-b border-white/10 pb-6 gap-6">
        <div>
            <span class="text-amber-400 text-[11px] font-black tracking-widest uppercase"><?php echo $t['saved_content'] ?? 'Saved Content'; ?></span>
            <h1 class="text-2xl sm:text-4xl font-black text-white mt-1"><?php echo $t['my_favorites_title'] ?? 'قائمتي المفضلة ⭐'; ?></h1>
        </div>
        
        <button onclick="clearAllFavorites()" class="lux-glass text-rose-400 hover:border-rose-500 hover:text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
            <span>🗑️</span> <?php echo $t['clear_all'] ?? 'مسح كافة العناصر'; ?>
        </button>
    </div>

    <div id="favorites-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6"></div>

    <div id="empty-favorites" class="text-center py-28 lux-glass rounded-3xl hidden">
        <p class="text-gray-400 text-lg font-bold mb-2"><?php echo $t['empty_fav_title'] ?? 'قائمتك المفضلة فارغة حالياً 🎬'; ?></p>
        <p class="text-xs text-gray-500"><?php echo $t['empty_fav_desc'] ?? 'قم بحفظ الأفلام والمسلسلات التي تعجبك للرجوع إليها لاحقاً.'; ?></p>
        <a href="index.php" class="btn-neon-cyan px-6 py-2.5 rounded-xl font-black text-black text-xs inline-block mt-6"><?php echo $t['explore_now'] ?? 'استكشف الأعمال الآن'; ?></a>
    </div>
</main>

<script>
const favLang = <?php echo json_encode([
    'series' => $t['series_badge'] ?? 'مسلسل',
    'movie' => $t['movie_badge'] ?? 'فيلم',
    'watch_now' => $t['watch_now'] ?? 'مشاهدة الآن',
    'confirm_clear' => $t['confirm_clear_all'] ?? 'هل أنت متأكد من رغبتك في مسح كافة العناصر المحفوظة؟',
    'cleared' => $t['cleared_successfully'] ?? 'تم مسح كافة عناصر المفضلة بنجاح'
]); ?>;

function renderFavorites() {
    const favs = JSON.parse(localStorage.getItem('cinema_favorites') || '[]');
    const grid = document.getElementById('favorites-grid');
    const emptyState = document.getElementById('empty-favorites');

    if (favs.length === 0) {
        grid.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');
    grid.innerHTML = favs.map(item => `
        <div class="sci-card block group cursor-pointer" onclick="window.location.href='watch.php?id=${item.id}&type=${item.type}'">
            <div class="aspect-[2/3] w-full relative">
                <img src="${item.poster}" alt="${item.title}" class="w-full h-full object-cover pointer-events-none">
                
                <div class="absolute top-2.5 inset-x-2.5 flex justify-between items-center z-10 pointer-events-none">
                    <span class="bg-black/80 backdrop-blur-md text-amber-300 border border-amber-300/30 text-[11px] font-bold px-2 py-0.5 rounded-lg">
                        ★ ${item.rating || '0.0'}
                    </span>
                    <span class="bg-black/80 backdrop-blur-md ${item.type === 'tv' ? 'text-purple-300 border-purple-500/30' : 'text-cyan-300 border-cyan-500/30'} border text-[11px] font-bold px-2 py-0.5 rounded-lg">
                        ${item.type === 'tv' ? favLang.series : favLang.movie}
                    </span>
                </div>

                <div class="absolute inset-0 p-4 sci-overlay flex flex-col justify-end pointer-events-none z-10">
                    <h3 class="text-xs font-black text-white line-clamp-1 mb-2.5">${item.title}</h3>
                    <div class="w-full ${item.type === 'tv' ? 'btn-neon-purple text-white' : 'btn-neon-cyan text-black'} py-2 rounded-xl text-xs font-black text-center shadow-lg pointer-events-auto">
                        ${favLang.watch_now}
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    if (typeof init3DCardTiltEngine === 'function') {
        init3DCardTiltEngine();
    }
}

function clearAllFavorites() {
    if (confirm(favLang.confirm_clear)) {
        localStorage.removeItem('cinema_favorites');
        renderFavorites();
        if (typeof showCyberToast === 'function') {
            showCyberToast(favLang.cleared, 'purple');
        }
    }
}

document.addEventListener('DOMContentLoaded', renderFavorites);
</script>

<?php include 'includes/footer.php'; ?>