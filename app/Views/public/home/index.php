<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<!-- ============================================================
     HERO SECTION WITH AERIAL FOOTAGE
     ============================================================ -->
<div class="home-page">
<section class="hero-section">
    <!-- Video Background -->
    <div class="video-background">
        <video id="uthmVideo" autoplay muted loop playsinline>
            <source src="<?= base_url('images/uthm-aerial.mp4') ?>" type="video/mp4">
            <source src="<?= base_url('images/uthm-aerial.webm') ?>" type="video/webm">
        </video>
    </div>
    
    <!-- Overlay for better text readability -->
    <div class="hero-overlay"></div>
    
    <!-- Video Controls -->
    <div class="video-controls">
        <button class="video-toggle" id="videoPauseBtn" title="Pause Video">
            <i class="bi bi-pause-fill"></i>
        </button>
    </div>
    
    <!-- Hero Content -->
    <div class="container">
        <div class="hero-content">
            <h1>FKMP Smart Laboratory Management System</h1>
            <p class="subtitle">
                Experience UTHM's state-of-the-art facilities. Book laboratories, manage equipment,
                and streamline lab usage efficiently - all in one place.
            </p>
            <div class="hero-buttons">
                <a href="<?= site_url('/laboratories') ?>" class="hero-btn hero-btn-secondary">
                    <i class="bi bi-building"></i>
                    Explore Laboratories
                </a>
                <a href="<?= site_url('/login') ?>" class="hero-btn hero-btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login to SLAMS
                </a>
            </div>
        </div>
    </div>
    
        <!-- Campus Info -->
    <div class="campus-info">
        <i class="bi bi-camera-video-fill"></i>
        <span>UTHM Campus Night Aerial Footage</span>
    </div>
</section>


<div class="container">

    <!-- ============================================================
         FEATURE SECTION
         ============================================================ -->
    <h3 class="feature-section-title">Why Use SLAMS?</h3>

    <div class="row g-4 mb-5">

        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h5 class="fw-semibold">Fast & Easy Booking</h5>
                <p class="small mb-0">
                    A streamlined wizard helps UTHM students and staff submit lab bookings with ease.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <h5 class="fw-semibold">Equipment Availability</h5>
                <p class="small mb-0">
                    View real-time equipment availability and select items required for your task.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5 class="fw-semibold">Secure Approval Flow</h5>
                <p class="small mb-0">
                    Built-in PIC and Manager approval ensures compliance and lab safety.
                </p>
            </div>
        </div>

    </div>


    <!-- ============================================================
         CTA SECTION
         ============================================================ -->
    <section class="cta-section">
        <h4 class="fw-bold mb-3">Ready to get started?</h4>
        <p class="mb-4">
            Log in to submit a booking request at UTHM.
        </p>
        <div class="cta-buttons">
            <a href="<?= site_url('/login') ?>" class="btn btn-glow px-4 py-2">
                <i class="bi bi-person-circle me-1"></i> Login Now
            </a>
        </div>
    </section>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('uthmVideo');
    const videoBtn = document.getElementById('videoPauseBtn');
    const videoIcon = videoBtn.querySelector('i');
    let isPlaying = true;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isSmallScreen = window.matchMedia('(max-width: 768px)').matches;
    
    // Optimize video for night footage
    if (video) {
        // Adjust video settings for night footage
        video.style.filter = 'brightness(0.8) contrast(1.2) saturate(1.1)';
        
        // Ensure video plays
        video.play().catch(error => {
            console.log('Autoplay prevented, showing play button');
            videoBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            isPlaying = false;
        });
        
        // Video controls
        videoBtn.addEventListener('click', function() {
            if (isPlaying) {
                video.pause();
                videoIcon.className = 'bi bi-play-fill';
                videoBtn.title = 'Play Video';
                isPlaying = false;
            } else {
                video.play();
                videoIcon.className = 'bi bi-pause-fill';
                videoBtn.title = 'Pause Video';
                isPlaying = true;
            }
        });
        
        // Add subtle parallax effect on scroll (skip for small screens/reduced motion)
        if (!prefersReducedMotion && !isSmallScreen) {
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const rate = scrolled * 0.1;
                video.style.transform = `translateY(${rate}px)`;
            });
        }
        
        // Handle video loading states
        video.addEventListener('waiting', function() {
            video.style.opacity = '0.8';
        });
        
        video.addEventListener('canplay', function() {
            video.style.opacity = '1';
        });
        
        // Restart video when it ends
        video.addEventListener('ended', function() {
            this.currentTime = 0;
            this.play();
        });
    }
    
    // Add smooth scroll for navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
