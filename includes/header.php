<?php
/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تبديل اللغة (تمت إضافة الفرنسية والإيطالية)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en', 'fr', 'it'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$current_lang = $_SESSION['lang'] ?? 'ar';
$lang_file = __DIR__ . "/../lang/{$current_lang}.php";
$trans = file_exists($lang_file) ? require $lang_file : [];
$dir = ($current_lang === 'ar') ? 'rtl' : 'ltr';

// Record one visit per session for visitors.php (fails silently).
if (isset($pdo) && $pdo instanceof PDO && function_exists('logVisitor')) {
    logVisitor($pdo);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>adh.che // Cinema Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#020204] text-slate-100 min-h-screen selection:bg-cyan-500 selection:text-black">

    <div id="custom-cursor" class="hidden md:block"></div>
    <div id="custom-cursor-follower" class="hidden md:block"></div>
    <div id="spotlight-glow"></div>

    <header class="fixed top-0 inset-x-0 z-50 px-4 sm:px-12 py-4">
        <div class="max-w-[1500px] mx-auto lux-glass rounded-2xl px-6 py-3.5 flex flex-wrap items-center justify-between gap-4">
            
            <div class="flex items-center gap-6">
                <!-- اللوغو -->
                <a href="index.php" class="text-xl font-extrabold text-white flex items-center gap-2.5 tracking-tight" style="direction: ltr; font-family: 'Space Grotesk', monospace;">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_12px_#06b6d4]"></span>
                    <span>adh<span class="text-cyan-400">.</span>che</span>
                </a>

                <!-- تبديل اللغة -->
                <div class="flex items-center gap-3 border-s border-white/10 ps-6 ms-2">
                    <a href="?lang=ar" class="<?php echo $current_lang === 'ar' ? 'text-cyan-400 font-bold' : 'text-gray-500 hover:text-white'; ?> text-xs transition">AR</a>
                    <a href="?lang=en" class="<?php echo $current_lang === 'en' ? 'text-cyan-400 font-bold' : 'text-gray-500 hover:text-white'; ?> text-xs transition">EN</a>
                    <a href="?lang=fr" class="<?php echo $current_lang === 'fr' ? 'text-cyan-400 font-bold' : 'text-gray-500 hover:text-white'; ?> text-xs transition">FR</a>
                    <a href="?lang=it" class="<?php echo $current_lang === 'it' ? 'text-cyan-400 font-bold' : 'text-gray-500 hover:text-white'; ?> text-xs transition">IT</a>
                </div>
            </div>

            <!-- روابط التنقل -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold text-gray-300">
                <a href="index.php" class="hover:text-cyan-400 transition-colors"><?php echo $trans['home'] ?? 'الرئيسية'; ?></a>
                <a href="movies.php" class="hover:text-cyan-400 transition-colors"><?php echo $trans['movies'] ?? 'الأفلام'; ?></a>
                <a href="series.php" class="hover:text-purple-400 transition-colors"><?php echo $trans['series'] ?? 'المسلسلات'; ?></a>
                <a href="favorites.php" class="hover:text-amber-400 transition-colors"><?php echo $trans['favorites'] ?? '⭐ المفضلة'; ?></a>
            </nav>

            <!-- حقل البحث -->
            <form action="search.php" method="GET" class="flex items-center bg-black/50 border border-white/10 rounded-xl px-3.5 py-1.5 w-full sm:w-64 focus-within:border-cyan-400 transition-all relative">
                <input type="text" id="global-search-input" name="q" placeholder="<?php echo $trans['search_placeholder'] ?? ''; ?>" class="bg-transparent text-xs text-white placeholder-gray-500 focus:outline-none w-full">
                <button type="button" onclick="startVoiceSearch()" class="text-gray-400 hover:text-cyan-400 text-xs ms-2" title="البحث الصوتي">🎤</button>
                <button type="submit" class="text-gray-400 hover:text-cyan-400 text-xs me-2 ms-2 transition-colors">🔍</button>
            </form>

        </div>
    </header>

    <script>
    const transData = <?php echo json_encode([
        'voice_not_supported' => $trans['voice_not_supported'] ?? '',
        'speak_now' => $trans['speak_now'] ?? ''
    ]); ?>;

    function startVoiceSearch() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            if (typeof showCyberToast === 'function') showCyberToast(transData.voice_not_supported, 'purple');
            return;
        }
        
        const recognition = new SpeechRecognition();
        
        // تحديد لغة البحث الصوتي بناءً على لغة الموقع الحالية
        const langMap = {
            'ar': 'ar-MA',
            'en': 'en-US',
            'fr': 'fr-FR',
            'it': 'it-IT'
        };
        recognition.lang = langMap[document.documentElement.lang] || 'ar-MA';
        
        recognition.start();
        if (typeof showCyberToast === 'function') showCyberToast(transData.speak_now, 'cyan');
        
        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            const input = document.getElementById('global-search-input');
            if (input) {
                input.value = transcript;
                input.form.submit();
            }
        };
    }
    </script>