<?php
/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io/portfolio
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

include 'includes/header.php';
?>

<main class="min-h-[85vh] flex items-center justify-center px-6 relative z-10 pt-28 pb-20">
    <div class="max-w-2xl w-full text-center lux-glass p-12 sm:p-16 rounded-[3rem] border border-cyan-500/30 shadow-[0_0_80px_rgba(6,182,212,0.15)]">
        
        <div class="inline-block mb-6 relative">
            <span class="text-7xl sm:text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 animate-pulse tracking-widest">
                404
            </span>
            <div class="absolute -top-3 -right-3 w-4 h-4 rounded-full bg-rose-500 animate-ping"></div>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-white mb-4">انقطاع الإشارة السينمائية // Signal Lost</h1>
        <p class="text-gray-400 text-xs sm:text-sm font-semibold max-w-md mx-auto mb-10 leading-relaxed">
            يبدو أن الرابط الذي تحاول الوصول إليه غير موجود أو تم نقله إلى فضاء آخر في الأرشيف.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="index.php" class="w-full sm:w-auto btn-neon-cyan px-8 py-3.5 rounded-2xl font-black text-black text-xs tracking-wider inline-flex items-center justify-center gap-2 transition-all">
                &larr; العودة إلى البوابة الرئيسية
            </a>
            <a href="movies.php" class="w-full sm:w-auto lux-glass hover:border-cyan-400 text-gray-300 px-8 py-3.5 rounded-2xl font-bold text-xs transition-all">
                استكشاف الأفلام 🎬
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>