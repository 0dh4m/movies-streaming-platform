/**
 * adh.che Futuristic Interaction Engine 2026
 * Ultra-Smooth 60FPS Sci-Fi UI Handler with Hologram Modals & Cyber Notifications
 */

document.addEventListener('DOMContentLoaded', () => {
    initCursorAndSpotlight();
    init3DCardTiltEngine();
    initParticleMesh();
    initReadingProgressBar();
    initModalSystem();
});

// ==========================================
// 1. مصفوفة الصوت السايبراني التوليدي (Web Audio Synthesizer)
// ==========================================
const SoundFX = {
    ctx: null,
    init() {
        if (!this.ctx) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) this.ctx = new AudioCtx();
        }
    },
    playBeep(freq = 520, type = 'sine', duration = 0.08) {
        try {
            this.init();
            if (!this.ctx) return;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
            gain.gain.setValueAtTime(0.04, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + duration);
            osc.connect(gain);
            gain.connect(this.ctx.destination);
            osc.start();
            osc.stop(this.ctx.currentTime + duration);
        } catch (e) {}
    },
    success() {
        this.playBeep(440, 'triangle', 0.06);
        setTimeout(() => this.playBeep(880, 'sine', 0.12), 60);
    },
    modalOpen() {
        this.playBeep(260, 'sine', 0.1);
        setTimeout(() => this.playBeep(520, 'sine', 0.15), 50);
    },
    modalClose() {
        this.playBeep(480, 'sine', 0.08);
        setTimeout(() => this.playBeep(240, 'sine', 0.12), 40);
    }
};

// ==========================================
// 2. نظام التنبيهات السايبرانية (Cyber Toast Notifications)
// ==========================================
window.showCyberToast = function(message, type = 'cyan', duration = 3500) {
    SoundFX.success();
    let container = document.getElementById('cyber-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'cyber-toast-container';
        container.className = 'fixed bottom-6 left-6 z-[10000] flex flex-col gap-3 pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const borderColors = {
        cyan: 'border-cyan-500/50 shadow-[0_0_25px_rgba(6,182,212,0.35)]',
        amber: 'border-amber-500/50 shadow-[0_0_25px_rgba(245,158,11,0.35)]',
        purple: 'border-purple-500/50 shadow-[0_0_25px_rgba(168,85,247,0.35)]'
    };
    const textColors = {
        cyan: 'text-cyan-400',
        amber: 'text-amber-400',
        purple: 'text-purple-400'
    };

    toast.className = `lux-glass px-5 py-3 rounded-2xl border ${borderColors[type] || borderColors.cyan} pointer-events-auto flex items-center gap-3 transform translate-y-8 opacity-0 scale-95 transition-all duration-300 backdrop-blur-2xl bg-[#090d16]/90`;
    toast.innerHTML = `
        <span class="w-2.5 h-2.5 rounded-full bg-current ${textColors[type] || textColors.cyan} animate-pulse"></span>
        <p class="text-xs font-bold text-gray-200 tracking-wide">${message}</p>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-8', 'opacity-0', 'scale-95');
        toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'scale-90', '-translate-x-6');
        setTimeout(() => toast.remove(), 350);
    }, duration);
};

// ==========================================
// 3. أنيميشن فتح وإغلاق النوافذ المنبثقة (Hologram Modal Engine)
// ==========================================
function initModalSystem() {
    const originalOpenModal = window.openNetflixModal;
    const originalCloseModal = window.closeNetflixModal;

    window.openNetflixModal = function(id, type) {
        SoundFX.modalOpen();
        const modal = document.getElementById('netflix-modal');
        if (modal) {
            const modalBox = modal.querySelector('div.relative.w-full');
            modal.classList.remove('hidden');
            modal.style.opacity = '0';
            modal.style.transition = 'opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1)';
            
            if (modalBox) {
                modalBox.style.transform = 'scale(0.88) translateY(30px)';
                modalBox.style.opacity = '0';
                modalBox.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
            }

            requestAnimationFrame(() => {
                modal.style.opacity = '1';
                if (modalBox) {
                    modalBox.style.transform = 'scale(1) translateY(0)';
                    modalBox.style.opacity = '1';
                }
            });
        }
        if (typeof originalOpenModal === 'function') {
            originalOpenModal(id, type);
        }
    };

    window.closeNetflixModal = function() {
        SoundFX.modalClose();
        const modal = document.getElementById('netflix-modal');
        if (modal) {
            const modalBox = modal.querySelector('div.relative.w-full');
            modal.style.opacity = '0';
            if (modalBox) {
                modalBox.style.transform = 'scale(0.92) translateY(20px)';
                modalBox.style.opacity = '0';
            }
            setTimeout(() => {
                modal.classList.add('hidden');
                if (typeof originalCloseModal === 'function') {
                    originalCloseModal();
                }
            }, 300);
        }
    };

    const originalToggleFav = window.toggleModalFavorite;
    window.toggleModalFavorite = function() {
        if (typeof originalToggleFav === 'function') {
            originalToggleFav();
            const favBtn = document.getElementById('modal-fav-btn');
            const isAdded = favBtn && favBtn.innerText.includes('محفوظ') || (favBtn && favBtn.innerText.includes('Saved')) || (favBtn && favBtn.innerText.includes('Enregistré')) || (favBtn && favBtn.innerText.includes('Salvato'));
            
            // قراءة الرسائل من كائن اللغات إذا كان متاحاً، أو استخدام العربية كافتراضي
            const msgAdded = (window.langData && window.langData.added_fav_toast) ? window.langData.added_fav_toast : 'تمت إضافة العمل إلى المفضلة ⭐';
            const msgRemoved = (window.langData && window.langData.removed_fav_toast) ? window.langData.removed_fav_toast : 'تمت إزالة العمل من المفضلة';
            
            showCyberToast(isAdded ? msgAdded : msgRemoved, isAdded ? 'amber' : 'purple');
        }
    };
}

// ==========================================
// 4. المؤشر التفاعلي وضوء الـ Spotlight
// ==========================================
function initCursorAndSpotlight() {
    // إيقاف التتبع في الهواتف وشاشات اللمس لمنع استهلاك الموارد وتداخل الـ UI
    if (window.matchMedia("(pointer: coarse)").matches) return;

    const cursor = document.getElementById('custom-cursor');
    const follower = document.getElementById('custom-cursor-follower');
    const spotlight = document.getElementById('spotlight-glow');

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let cursorX = mouseX, cursorY = mouseY;
    let followerX = mouseX, followerY = mouseY;

    window.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;

        if (spotlight) {
            spotlight.style.left = `${mouseX}px`;
            spotlight.style.top = `${mouseY}px`;
        }
    });

    function renderCursor() {
        cursorX += (mouseX - cursorX) * 0.55;
        cursorY += (mouseY - cursorY) * 0.55;
        followerX += (mouseX - followerX) * 0.16;
        followerY += (mouseY - followerY) * 0.16;

        if (cursor) cursor.style.transform = `translate(${cursorX}px, ${cursorY}px) translate(-50%, -50%)`;
        if (follower) follower.style.transform = `translate(${followerX}px, ${followerY}px) translate(-50%, -50%)`;

        requestAnimationFrame(renderCursor);
    }
    renderCursor();

    const interactiveElements = document.querySelectorAll('a, button, input, select, .sci-card, .interactive-hover');
    interactiveElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            if (follower) {
                follower.style.width = '62px';
                follower.style.height = '62px';
                follower.style.borderColor = '#06b6d4';
                follower.style.backgroundColor = 'rgba(6, 182, 212, 0.08)';
            }
            if (cursor) {
                cursor.style.transform += ' scale(1.4)';
            }
        });
        el.addEventListener('mouseleave', () => {
            if (follower) {
                follower.style.width = '38px';
                follower.style.height = '38px';
                follower.style.borderColor = 'rgba(6, 182, 212, 0.45)';
                follower.style.backgroundColor = 'transparent';
            }
        });
    });
}

// ==========================================
// 5. محرك الميلان ثلاثي الأبعاد لكروت العرض (إصلاح فوري ودقيق 60FPS)
// ==========================================
function init3DCardTiltEngine() {
    // إيقاف التأثير في الهواتف وشاشات اللمس
    if (window.matchMedia("(pointer: coarse)").matches) return;

    const cards = document.querySelectorAll('.sci-card');

    cards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            this.style.setProperty('--mouse-x', `${x}px`);
            this.style.setProperty('--mouse-y', `${y}px`);

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = ((y - centerY) / centerY) * -14;
            const rotateY = ((x - centerX) / centerX) * 14;

            this.style.transition = 'none';
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px) scale3d(1.04, 1.04, 1.04)`;
        });

        card.addEventListener('mouseleave', function() {
            this.style.transition = 'transform 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
            this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0) scale3d(1, 1, 1)';
        });
    });
}

// ==========================================
// 6. شريط التمرير الضوئي العلوي (Scroll Progress Glow)
// ==========================================
function initReadingProgressBar() {
    let bar = document.getElementById('cyber-scroll-bar');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'cyber-scroll-bar';
        bar.className = 'fixed top-0 inset-x-0 h-[2.5px] bg-gradient-to-r from-cyan-400 via-purple-500 to-amber-400 z-[99999] opacity-85 shadow-[0_0_12px_#06b6d4]';
        bar.style.transformOrigin = document.documentElement.dir === 'rtl' ? 'right' : 'left'; // توافق مع اتجاه اللغة
        bar.style.transform = 'scaleX(0)';
        document.body.appendChild(bar);
    }

    window.addEventListener('scroll', () => {
        const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = totalHeight > 0 ? window.scrollY / totalHeight : 0;
        bar.style.transform = `scaleX(${progress})`;
    });
}

// ==========================================
// 7. جزيئات الفضاء التفاعلية (Particle Mesh)
// ==========================================
function initParticleMesh() {
    const canvas = document.getElementById('space-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const particles = [];
    const count = 45;

    for (let i = 0; i < count; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.3,
            vy: (Math.random() - 0.5) * 0.3,
            radius: Math.random() * 1.5 + 0.5
        });
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = 'rgba(6, 182, 212, 0.45)';
        ctx.strokeStyle = 'rgba(6, 182, 212, 0.05)';

        for (let i = 0; i < count; i++) {
            const p = particles[i];
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0 || p.x > width) p.vx *= -1;
            if (p.y < 0 || p.y > height) p.vy *= -1;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fill();

            for (let j = i + 1; j < count; j++) {
                const p2 = particles[j];
                const dist = Math.hypot(p.x - p2.x, p.y - p2.y);
                if (dist < 120) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(animate);
    }
    animate();
}