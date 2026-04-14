<?php
use App\Models\NotificationModel;

$userNavNotificationItems = [];
$userNavUnreadCount = 0;
if (function_exists('auth') && auth()->loggedIn()) {
    $userNavCurrentUser = auth()->user();
    $notificationModel = new NotificationModel();
    $userNavUnreadCount = $notificationModel->where('user_id', $userNavCurrentUser->id)->where('is_read', 0)->countAllResults();
    $userNavNotificationItems = $notificationModel->where('user_id', $userNavCurrentUser->id)->orderBy('created_at', 'DESC')->findAll(5);
}
?>
<nav class="navbar navbar-expand-lg navbar-dark glass-navbar py-3">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <div class="d-flex align-items-center">
                <div class="brand-logo me-2"><i class="bi bi-building-fill text-white fs-4"></i></div>
                <div><span class="text-white">SLAMS</span><span class="text-light small opacity-75"> | FKMP</span></div>
            </div>
        </a>

        <button class="navbar-toggler glass-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar" aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item me-3"><a class="nav-link position-relative" href="/"><i class="bi bi-house-door me-1"></i> Home<span class="nav-indicator"></span></a></li>
                <li class="nav-item me-3"><a class="nav-link position-relative" href="/laboratories"><i class="bi bi-building me-1"></i> Laboratories<span class="nav-indicator"></span></a></li>
                <li class="nav-item me-3"><a class="nav-link position-relative" href="/assets"><i class="bi bi-box-seam me-1"></i> Assets<span class="nav-indicator"></span></a></li>
                <li class="nav-item me-3"><a class="nav-link position-relative" href="/contact"><i class="bi bi-envelope me-1"></i> Contact<span class="nav-indicator"></span></a></li>

                <?php if (auth()->loggedIn()): ?>
                    <?php $user = auth()->user(); ?>
                    <li class="nav-item me-3 dropdown notification-nav-item">
                        <a class="nav-link position-relative notification-nav-link" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell me-1"></i> Notifications
                            <?php if ($userNavUnreadCount > 0): ?><span class="notification-bubble"><?= esc($userNavUnreadCount > 99 ? '99+' : (string) $userNavUnreadCount) ?></span><?php endif; ?>
                            <span class="nav-indicator"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold text-dark">Notifications</div>
                                    <div class="small text-muted"><?= esc((int) $userNavUnreadCount) ?> unread</div>
                                </div>
                                <a href="/dashboard/notifications" class="small text-decoration-none">View all</a>
                            </div>
                            <?php if (empty($userNavNotificationItems)): ?>
                                <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
                            <?php else: ?>
                                <?php foreach ($userNavNotificationItems as $item): ?>
                                    <a href="/dashboard/notifications" class="notification-item text-decoration-none">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge <?= (int) ($item['is_read'] ?? 0) === 0 ? 'bg-primary' : 'bg-secondary' ?> mt-1"><?= (int) ($item['is_read'] ?? 0) === 0 ? 'New' : 'Read' ?></span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small text-dark"><?= esc($item['title'] ?? 'Notification') ?></div>
                                                <div class="small text-muted"><?= esc($item['message'] ?? '') ?></div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </li>

                    <?php if (!($user->inGroup('pic') || $user->inGroup('manager'))): ?>
                        <li class="nav-item me-3"><a class="nav-link position-relative" href="/dashboard/profile"><i class="bi bi-person-circle me-1"></i> Profile<span class="nav-indicator"></span></a></li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-3">
                        <form action="/logout" method="post" class="d-inline"><button class="btn btn-glass btn-sm"><i class="bi bi-box-arrow-right me-1"></i> Logout</button></form>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3"><a class="btn btn-glow btn-sm px-3 py-2" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
.glass-navbar { background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95)) !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-bottom: 1px solid rgba(59, 130, 246, 0.3); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); position: relative; z-index: 1000; transition: all 0.3s ease; }
.glass-navbar.scrolled { background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.98)) !important; box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25); }
.brand-logo { width: 40px; height: 40px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(30, 64, 175, 0.4)); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); transition: all 0.3s ease; }
.brand-logo:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3); }
.navbar-brand { font-size: 1.4rem; transition: all 0.3s ease; }
.navbar-brand:hover { transform: translateY(-1px); }
.nav-link { color: rgba(255, 255, 255, 0.9) !important; font-weight: 500; padding: 8px 16px !important; border-radius: 8px; transition: all 0.3s ease; position: relative; margin: 0 4px; }
.nav-link:hover { color: white !important; background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(30, 64, 175, 0.3)); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); }
.nav-link.active { color: white !important; background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(30, 64, 175, 0.4)); font-weight: 600; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
.nav-indicator { position: absolute; bottom: 0; left: 50%; transform: translateX(-50%) scaleX(0); width: 24px; height: 3px; background: linear-gradient(90deg, #60a5fa, #3b82f6); border-radius: 2px; transition: transform 0.3s ease; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4); }
.nav-link:hover .nav-indicator, .nav-link.active .nav-indicator { transform: translateX(-50%) scaleX(1); }
.notification-bubble { position: absolute; top: 2px; right: 6px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: #ef4444; color: white; font-size: 0.68rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.notification-menu { width: 340px; border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.24); }
.notification-item { display: block; padding: 12px 16px; color: inherit; border-bottom: 1px solid #eef2f7; }
.notification-item:hover { background: #f8fafc; }
.notification-item:last-child { border-bottom: 0; }
.btn-glow { background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; border: none; border-radius: 10px; font-weight: 600; position: relative; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4); }
.btn-glow:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(59, 130, 246, 0.6); color: white; }
.btn-glow::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent); transition: left 0.5s ease; }
.btn-glow:hover::before { left: 100%; }
.btn-glass { background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05)); color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; backdrop-filter: blur(5px); transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
.btn-glass:hover { background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.1)); border-color: rgba(255,255,255,0.3); transform: translateY(-2px); box-shadow: 0 6px 25px rgba(0,0,0,0.25); }
.glass-toggler { border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.2); backdrop-filter: blur(5px); padding: 6px 10px; border-radius: 8px; }
.glass-toggler:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3); outline: none; }
.glass-toggler .navbar-toggler-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e"); width: 24px; height: 24px; }
@media (max-width: 991.98px) {
    .glass-navbar { padding: 12px 0; }
    .navbar-collapse { background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.98)); backdrop-filter: blur(15px); border-radius: 0 0 20px 20px; padding: 20px; margin-top: 15px; border: 1px solid rgba(59, 130, 246, 0.2); border-top: none; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3); }
    .nav-link { padding: 12px 0 !important; margin: 4px 0; border-radius: 8px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .nav-link:last-child { border-bottom: none; }
    .nav-link:hover { background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(30, 64, 175, 0.2)); transform: translateX(5px); }
    .nav-indicator { display: none; }
    .btn-glow, .btn-glass { width: 100%; justify-content: center; margin-top: 10px; }
    .notification-menu { width: min(92vw, 340px); }
}
@media (min-width: 992px) {
    .nav-item { position: relative; }
    .nav-link::after { content: ''; position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%) scaleX(0); width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-bottom: 6px solid #3b82f6; transition: all 0.3s ease; opacity: 0; }
    .nav-link:hover::after { transform: translateX(-50%) scaleX(1); opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.glass-navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) { navbar.classList.add('scrolled'); } else { navbar.classList.remove('scrolled'); }
    });
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        const isHomepage = (currentPath === '/' || currentPath === '') && (href === '/' || href === '/');
        const isActive = isHomepage || href === currentPath;
        if (isActive) { link.classList.add('active'); } else { link.classList.remove('active'); }
    });
    if (window.innerWidth > 992) {
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                const indicator = this.querySelector('.nav-indicator');
                if (indicator) { indicator.style.transition = 'transform 0.3s ease'; indicator.style.transform = 'translateX(-50%) scaleX(1)'; }
            });
            item.addEventListener('mouseleave', function() {
                const indicator = this.querySelector('.nav-indicator');
                const isActive = this.querySelector('.nav-link.active');
                if (indicator && !isActive) { indicator.style.transform = 'translateX(-50%) scaleX(0)'; }
            });
        });
    }
    const buttons = document.querySelectorAll('.btn-glow, .btn-glass');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            ripple.style.cssText = `position:absolute;border-radius:50%;background:rgba(255,255,255,0.3);transform:scale(0);animation:ripple 0.6s linear;width:${size}px;height:${size}px;top:${y}px;left:${x}px;`;
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
});
const style = document.createElement('style');
style.textContent = `@keyframes ripple { to { transform: scale(4); opacity: 0; } }`;
document.head.appendChild(style);
</script>
