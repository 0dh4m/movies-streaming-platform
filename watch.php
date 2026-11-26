<?php
/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

require_once 'includes/db.php';
$database = getDatabase();

$id = $_GET['id'] ?? '';
$type = $_GET['type'] ?? 'movie';
$season = $_GET['season'] ?? 1;
$episode = $_GET['episode'] ?? 1;

$item_info = null;
$collection = ($type === 'tv') ? ($database['series'] ?? []) : ($database['movies'] ?? []);
foreach ($collection as $item) {
    if (strval($item['tmdb_id']) === strval($id)) {
        $item_info = $item;
        break;
    }
}

$title = $item_info['title'] ?? 'مشاهدة العمل';
$poster = $item_info['poster'] ?? '';
$rating = $item_info['rating'] ?? '0.0';
$year = $item_info['year'] ?? '';
$genres = $item_info['genre'] ?? [];

$api_key = TMDB_API_KEY;
$details = [];

if (!empty($id)) {
    $endpoint = ($type === 'tv') ? "tv/{$id}" : "movie/{$id}";
    $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
    $response = @file_get_contents("https://api.themoviedb.org/3/{$endpoint}?api_key={$api_key}&language=ar", false, $ctx);
    if ($response) {
        $details = json_decode($response, true);
        if (empty($poster) && isset($details['poster_path'])) {
            $poster = "https://image.tmdb.org/t/p/w500" . $details['poster_path'];
        }
        if (empty($title)) {
            $title = ($type === 'tv') ? ($details['name'] ?? 'مشاهدة العمل') : ($details['title'] ?? 'مشاهدة العمل');
        }
    }
}

$seasons_count = $details['number_of_seasons'] ?? 1;

// سيرفرات البث المستقرة والحديثة (تم تعويض AutoEmbed بـ SuperEmbed)
if ($type === 'tv') {
    $servers = [
        'server1' => [
            'url' => "https://vidlink.pro/tv/{$id}/{$season}/{$episode}?sub.lang=ar&primaryColor=06b6d4&secondaryColor=a855f7",
            'name' => 'VidLink (Ultra HD)'
        ],
        'server2' => [
            'url' => "https://vidsrc.me/embed/tv?tmdb={$id}&season={$season}&episode={$episode}",
            'name' => 'VidSrc Pro'
        ],
        'server3' => [
            'url' => "https://embed.su/embed/tv/{$id}/{$season}/{$episode}",
            'name' => 'SuperEmbed (VIP)'
        ],
        'server4' => [
            'url' => "https://multiembed.mov/?video_id={$id}&tmdb=1&s={$season}&e={$episode}",
            'name' => 'MultiEmbed (Fast)'
        ]
    ];
} else {
    $servers = [
        'server1' => [
            'url' => "https://vidlink.pro/movie/{$id}?sub.lang=ar&primaryColor=06b6d4&secondaryColor=a855f7",
            'name' => 'VidLink (Ultra HD)'
        ],
        'server2' => [
            'url' => "https://vidsrc.me/embed/movie?tmdb={$id}",
            'name' => 'VidSrc Pro'
        ],
        'server3' => [
            'url' => "https://embed.su/embed/movie/{$id}",
            'name' => 'SuperEmbed (VIP)'
        ],
        'server4' => [
            'url' => "https://multiembed.mov/?video_id={$id}&tmdb=1",
            'name' => 'MultiEmbed (Fast)'
        ]
    ];
}

include 'includes/header.php';
$t = $trans ?? []; 
?>

<canvas id="space-canvas" class="fixed inset-0 pointer-events-none z-0"></canvas>

<main class="min-h-screen pt-28 pb-20 px-4 sm:px-12 max-w-[1500px] mx-auto relative z-10">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-4 lux-glass px-6 py-3.5 rounded-2xl">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-none w-full md:w-auto">
            <span class="text-xs font-black text-gray-400 ms-2 whitespace-nowrap"><?php echo $t['streaming_servers'] ?? '⚡ سيرفرات البث:'; ?></span>
            <?php $i = 1; foreach ($servers as $key => $srv): ?>
                <button onclick="changeServer('<?php echo $srv['url']; ?>', this)" class="server-btn px-4 py-2 rounded-xl text-xs font-black transition-all <?php echo $i === 1 ? 'btn-neon-cyan text-black' : 'lux-glass text-gray-300 hover:text-white'; ?> flex items-center gap-2 whitespace-nowrap">
                    <span><?php echo $srv['name']; ?></span>
                    <span class="ping-badge text-[9px] px-1.5 py-0.5 rounded-md <?php echo $i === 1 ? 'bg-black/30' : 'bg-white/10'; ?> font-mono" data-url="<?php echo $srv['url']; ?>">
                        <span class="animate-pulse text-gray-400">⏳</span>
                    </span>
                </button>
            <?php $i++; endforeach; ?>
        </div>

        <div class="flex items-center gap-2">
            <span class="bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 px-3.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-2 shadow-[0_0_15px_rgba(6,182,212,0.3)]">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span> <?php echo $t['quality'] ?? 'Dolby Vision • Ultra HD'; ?>
            </span>
        </div>
    </div>

    <div class="relative mb-8">
        <div class="w-full aspect-video rounded-3xl overflow-hidden bg-black border border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.95)]">
            <iframe id="main-player" src="<?php echo $servers['server1']['url']; ?>" class="w-full h-full border-0" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>
        </div>
    </div>

    <div class="lux-glass p-6 sm:p-8 rounded-3xl">
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <?php if (!empty($poster)): ?>
            <div class="w-24 sm:w-32 aspect-[2/3] rounded-2xl overflow-hidden border border-white/10 flex-shrink-0 shadow-xl mx-auto sm:mx-0">
                <img src="<?php echo $poster; ?>" class="w-full h-full object-cover" alt="">
            </div>
            <?php endif; ?>
            
            <div class="flex-1 text-center sm:text-start">
                <div class="flex items-center justify-center sm:justify-start gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase <?php echo $type === 'tv' ? 'bg-purple-500/20 text-purple-300' : 'bg-cyan-500/20 text-cyan-300'; ?>">
                        <?php echo $type === 'tv' ? ($t['exclusive_series'] ?? 'مسلسل حصري') : ($t['cinematic_movie'] ?? 'فيلم سينمائي'); ?>
                    </span>
                    <span class="text-amber-300 text-xs font-bold">★ <?php echo $rating; ?></span>
                    <?php if (!empty($year)): ?>
                    <span class="text-gray-400 text-xs">• <?php echo $year; ?></span>
                    <?php endif; ?>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-white mb-4"><?php echo htmlspecialchars($title); ?></h1>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 w-full">
                    <button onclick="openShareModal()" class="px-5 py-2.5 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                        <span>📱</span> <?php echo $t['qr_phone'] ?? 'رمز QR للهاتف'; ?>
                    </button>
                    <button id="btn-fav" onclick="toggleFavoriteCurrent()" class="px-5 py-2.5 lux-glass hover:border-amber-400 text-amber-300 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                        <span id="fav-icon">⭐</span> <span id="fav-text"><?php echo $t['add_to_fav'] ?? 'إضافة للمفضلة'; ?></span>
                    </button>
                    <a href="index.php" class="px-5 py-2.5 lux-glass hover:border-cyan-400 text-gray-300 rounded-xl text-xs font-bold transition-all">
                        <?php echo $t['back_home'] ?? 'العودة للرئيسية'; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($type === 'tv'): ?>
    <div class="mt-8 lux-glass p-6 sm:p-8 rounded-3xl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-white/10">
            <h3 class="text-lg font-black text-white"><?php echo $t['episodes_list'] ?? 'قائمة الحلقات والمواسم'; ?></h3>
            <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-xl text-xs font-bold"><?php echo ($t['season'] ?? 'الموسم') . ' ' . $season . ' • ' . ($t['episode'] ?? 'الحلقة') . ' ' . $episode; ?></span>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-4 scrollbar-none w-full">
            <?php for ($s = 1; $s <= $seasons_count; $s++): ?>
                <a href="watch.php?id=<?php echo $id; ?>&type=tv&season=<?php echo $s; ?>&episode=1" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?php echo intval($season) === $s ? 'bg-purple-600 text-white font-black' : 'lux-glass text-gray-300 hover:text-white'; ?>">
                    <?php echo ($t['season'] ?? 'الموسم') . ' ' . $s; ?>
                </a>
            <?php endfor; ?>
        </div>

        <div class="grid grid-cols-4 sm:grid-cols-8 md:grid-cols-12 gap-2.5 max-h-56 overflow-y-auto p-1 scrollbar-none">
            <?php for ($ep = 1; $ep <= 24; $ep++): ?>
                <a href="watch.php?id=<?php echo $id; ?>&type=tv&season=<?php echo $season; ?>&episode=<?php echo $ep; ?>" class="py-2.5 rounded-xl text-center text-xs transition-all <?php echo intval($episode) === $ep ? 'btn-neon-cyan text-black font-black' : 'lux-glass text-gray-300 hover:text-white'; ?>">
                    <?php echo $ep; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>

</main>

<div id="share-modal" class="fixed inset-0 bg-black/85 backdrop-blur-xl z-50 hidden flex items-center justify-center p-4">
    <div class="relative w-full max-w-sm bg-[#0d0d18]/95 border border-cyan-500/30 p-6 rounded-3xl text-center shadow-[0_25px_90px_rgba(0,0,0,0.95)]">
        <button onclick="closeShareModal()" class="absolute top-4 end-4 text-gray-400 hover:text-white w-7 h-7 rounded-full bg-white/10 flex items-center justify-center font-bold text-xs transition-all">✕</button>
        <h3 class="text-lg font-black text-white mb-1"><?php echo $t['watch_on_phone'] ?? 'المشاهدة على الهاتف'; ?></h3>
        <p class="text-[11px] text-gray-400 mb-4"><?php echo $t['scan_qr'] ?? 'امسح الكود بكاميرا الهاتف للمتابعة فوراً'; ?></p>
        <div class="bg-white p-3.5 rounded-2xl inline-block mb-4 shadow-xl">
            <div id="qrcode-box"></div>
        </div>
        <button onclick="copyCurrentLink()" class="w-full py-2.5 btn-neon-cyan rounded-xl text-black font-black text-xs transition-all flex items-center justify-center gap-2">
            <span>🔗</span> <span id="copy-btn-text"><?php echo $t['copy_link'] ?? 'نسخ الرابط'; ?></span>
        </button>
    </div>
</div>

<script>
const langData = <?php echo json_encode([
    'copied' => $t['copied'] ?? 'تم النسخ!',
    'copy_link' => $t['copy_link'] ?? 'نسخ الرابط',
    'server_changed' => $t['server_changed'] ?? 'تم تبديل السيرفر',
    'link_copied_toast' => $t['link_copied_toast'] ?? 'تم النسخ',
    'added_fav_toast' => $t['added_fav_toast'] ?? 'أضيف للمفضلة',
    'removed_fav_toast' => $t['removed_fav_toast'] ?? 'أزيل من المفضلة',
    'add_to_fav' => $t['add_to_fav'] ?? 'إضافة للمفضلة',
    'saved_to_fav' => $t['saved_to_fav'] ?? 'محفوظ'
]); ?>;

function calculateRealPings() {
    const badges = document.querySelectorAll('.ping-badge');
    
    badges.forEach((badge) => {
        const url = badge.getAttribute('data-url');
        try {
            const urlObj = new URL(url);
            const targetOrigin = urlObj.origin; 
            
            const startTime = performance.now();
            const img = new Image();
            let isResolved = false;

            img.onload = img.onerror = function() {
                if (isResolved) return;
                isResolved = true;
                
                const pingTime = Math.round(performance.now() - startTime);
                
                let colorClass = 'text-cyan-400';
                if (pingTime > 200) colorClass = 'text-amber-400';
                if (pingTime > 500) colorClass = 'text-rose-400';
                
                badge.innerHTML = `<span class="${colorClass}">${pingTime}ms</span>`;
            };

            setTimeout(() => {
                if (!isResolved) {
                    isResolved = true;
                    img.src = ''; 
                    badge.innerHTML = `<span class="text-rose-500">Err</span>`;
                }
            }, 3000);

            img.src = targetOrigin + '/favicon.ico?_=' + new Date().getTime();

        } catch (error) {
            badge.innerHTML = `<span class="text-rose-500">Err</span>`;
        }
    });
}

function changeServer(url, btn) {
    const player = document.getElementById('main-player');
    if (player) player.src = url;
    
    document.querySelectorAll('.server-btn').forEach(b => {
        b.className = 'server-btn px-4 py-2 rounded-xl text-xs font-black transition-all lux-glass text-gray-300 hover:text-white flex items-center gap-2 whitespace-nowrap';
        const badge = b.querySelector('.ping-badge');
        if (badge) {
            badge.classList.remove('bg-black/30');
            badge.classList.add('bg-white/10');
        }
    });
    
    btn.className = 'server-btn px-4 py-2 rounded-xl text-xs font-black transition-all btn-neon-cyan text-black flex items-center gap-2 whitespace-nowrap';
    const activeBadge = btn.querySelector('.ping-badge');
    if (activeBadge) {
        activeBadge.classList.remove('bg-white/10');
        activeBadge.classList.add('bg-black/30');
    }
    
    if (typeof showCyberToast === 'function') {
        showCyberToast(langData.server_changed, 'cyan', 2500);
    }
}

function openShareModal() {
    const modal = document.getElementById('share-modal');
    const qrBox = document.getElementById('qrcode-box');
    qrBox.innerHTML = '';
    new QRCode(qrBox, { text: window.location.href, width: 140, height: 140 });
    modal.classList.remove('hidden');
}

function closeShareModal() {
    document.getElementById('share-modal').classList.add('hidden');
}

function copyCurrentLink() {
    navigator.clipboard.writeText(window.location.href);
    const copyText = document.getElementById('copy-btn-text');
    copyText.innerText = langData.copied;
    if (typeof showCyberToast === 'function') showCyberToast(langData.link_copied_toast, 'cyan');
    setTimeout(() => { copyText.innerText = langData.copy_link; }, 2000);
}

const currentItem = {
    id: '<?php echo $id; ?>',
    type: '<?php echo $type; ?>',
    title: <?php echo json_encode($title); ?>,
    poster: '<?php echo $poster; ?>',
    rating: '<?php echo $rating; ?>',
    season: '<?php echo $season; ?>',
    episode: '<?php echo $episode; ?>'
};

function trackContinueWatching() {
    let list = JSON.parse(localStorage.getItem('cinema_continue_watching') || '[]');
    list = list.filter(item => !(item.id === currentItem.id && item.type === currentItem.type));
    list.unshift(currentItem);
    if (list.length > 8) list.pop();
    localStorage.setItem('cinema_continue_watching', JSON.stringify(list));
}

function updateFavoriteButtonState() {
    const favs = JSON.parse(localStorage.getItem('cinema_favorites') || '[]');
    const isFav = favs.some(item => item.id === currentItem.id && item.type === currentItem.type);
    const favText = document.getElementById('fav-text');
    const favIcon = document.getElementById('fav-icon');
    if (favText) favText.innerText = isFav ? langData.saved_to_fav : langData.add_to_fav;
    if (favIcon) favIcon.innerText = isFav ? '✔️' : '⭐';
}

function toggleFavoriteCurrent() {
    let favs = JSON.parse(localStorage.getItem('cinema_favorites') || '[]');
    const index = favs.findIndex(item => item.id === currentItem.id && item.type === currentItem.type);
    const isAdding = index === -1;
    
    if (!isAdding) favs.splice(index, 1);
    else favs.push(currentItem);
    
    localStorage.setItem('cinema_favorites', JSON.stringify(favs));
    updateFavoriteButtonState();

    if (typeof showCyberToast === 'function') {
        showCyberToast(isAdding ? langData.added_fav_toast : langData.removed_fav_toast, isAdding ? 'amber' : 'purple');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    calculateRealPings();
    trackContinueWatching();
    updateFavoriteButtonState();
});

window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('share-modal')) closeShareModal();
});
</script>

<?php include 'includes/footer.php'; ?>