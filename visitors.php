<?php
/**
 * Movies & Series Streaming Platform
 * Engineered & Developed by 0dh4m (Adham Cherkaoui Eddeqaqi)
 * Contact: 0dh4mm@gmail.com | Portfolio: https://0dh4m.github.io/portfolio
 * Copyright (c) 2026 0dh4m. All rights reserved.
 */

require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";

// 1. جلب كلمة السر من جدول الإعدادات مباشرة
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$stmt->execute(['admin_password']);
$admin_pass_row = $stmt->fetch(PDO::FETCH_ASSOC);
$admin_password = $admin_pass_row['setting_value'] ?? 'adhub_secure_2026';

// 2. جلب حالة الإعلانات المزعجة مباشرة من القاعدة (0 يعني معطلة)
$stmt2 = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$stmt2->execute(['show_annoying_ads']);
$ads_row = $stmt2->fetch(PDO::FETCH_ASSOC);
$show_annoying_ads = ($ads_row['setting_value'] ?? '0') === '1';

// التحقق من إرسال كلمة السر عبر النموذج
if (isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === $admin_password) {
        $_SESSION['is_admin'] = true;
        header("Location: visitors.php");
        exit();
    } else {
        $error = "كلمة السر غير صحيحة، حاول مرة أخرى!";
    }
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header("Location: visitors.php");
    exit();
}

include 'includes/header.php';
$t = $trans ?? [];
?>

<!-- الإعلانات المزعجة معطلة نهائياً لأن القيمة في القاعدة 0 -->
<?php if ($show_annoying_ads): ?>
    <!-- مكان الإعلانات -->
<?php endif; ?>

<canvas id="space-canvas" class="fixed inset-0 pointer-events-none z-0"></canvas>

<main class="min-h-screen pt-36 pb-32 px-6 sm:px-12 max-w-[1500px] mx-auto relative z-10">

    <?php if (!isset($_SESSION['is_admin'])): ?>
        <!-- نموذج تسجيل الدخول -->
        <div class="max-w-md mx-auto lux-glass p-8 rounded-3xl border border-cyan-500/30 text-center shadow-2xl mt-12 backdrop-blur-2xl">
            <div class="w-16 h-16 bg-cyan-500/10 border border-cyan-500/30 rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl shadow-[0_0_20px_rgba(6,182,212,0.3)]">
                🔒
            </div>
            <h1 class="text-2xl font-black text-white mb-2">منطقة محمية أمنياً</h1>
            <p class="text-xs text-gray-400 mb-6">أدخل كلمة المرور الخاصة للوصول إلى لوحة تحليل الزوار.</p>

            <?php if (!empty($error)): ?>
                <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold p-3 rounded-xl mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="visitors.php" class="space-y-4">
                <input type="password" name="admin_pass" placeholder="أدخل كلمة المرور..." required class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-cyan-400 transition-all text-center">
                <button type="submit" class="w-full py-3 btn-neon-cyan rounded-xl text-black font-black text-xs transition-all shadow-lg">
                    دخول لوحة التحكم ⚡
                </button>
            </form>
        </div>
    <?php else: ?>
        <?php
        // جلب قائمة الزوار والعدد الإجمالي باستعلام مباشر
        $stmt_visitors = $pdo->query("SELECT * FROM visitors_log ORDER BY visit_time DESC");
        $visitors = $stmt_visitors->fetchAll(PDO::FETCH_ASSOC);
        $total_visitors = count($visitors);
        ?>
        <!-- لوحة تحكم الزوار -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 border-b border-white/10 pb-6 gap-6">
            <div>
                <span class="text-cyan-400 text-[11px] font-black tracking-widest uppercase">System Analytics</span>
                <h1 class="text-2xl sm:text-4xl font-black text-white mt-1">سجل زوار الموقع 📊</h1>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="lux-glass px-5 py-2.5 rounded-2xl border border-cyan-500/30 flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span class="text-xs font-bold text-gray-300">إجمالي الزيارات: <strong class="text-cyan-400 text-sm"><?php echo $total_visitors; ?></strong></span>
                </div>
                <a href="visitors.php?logout=1" class="lux-glass text-rose-400 hover:border-rose-500 px-4 py-2.5 rounded-xl text-xs font-bold transition-all">
                    تسجيل الخروج 🚪
                </a>
            </div>
        </div>

        <div class="lux-glass rounded-3xl overflow-hidden border border-white/10 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="p-4 text-xs font-black text-gray-400 uppercase"># ID</th>
                            <th class="p-4 text-xs font-black text-gray-400 uppercase">عنوان IP</th>
                            <th class="p-4 text-xs font-black text-gray-400 uppercase">المتصفح</th>
                            <th class="p-4 text-xs font-black text-gray-400 uppercase">نظام التشغيل</th>
                            <th class="p-4 text-xs font-black text-gray-400 uppercase">وقت الدخول</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-gray-200 divide-y divide-white/5">
                        <?php if (!empty($visitors)): ?>
                            <?php foreach ($visitors as $v): ?>
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="p-4 font-mono text-cyan-400"><?php echo htmlspecialchars($v['id']); ?></td>
                                    <td class="p-4 font-mono"><?php echo htmlspecialchars($v['ip_address']); ?></td>
                                    <td class="p-4 font-bold text-amber-300"><?php echo htmlspecialchars($v['browser']); ?></td>
                                    <td class="p-4 text-purple-300"><?php echo htmlspecialchars($v['os']); ?></td>
                                    <td class="p-4 text-gray-400 font-mono"><?php echo htmlspecialchars($v['visit_time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-400">لا توجد زيارات مسجلة حتى الآن.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>