<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>
<?php
// Helpers to safely pull a contact setting with a fallback
$cs = function (string $key, string $fallback = '') use ($contactSettings): string {
    return $contactSettings[$key] ?? $fallback;
};
?>
<div class="contact-page">
    <div class="container">

        <!-- Page Header -->
        <div class="contact-header">
            <h1 class="contact-title">Contact Us</h1>
            <p class="contact-subtitle">
                Get in touch with the <?= esc($cs('faculty_name')) ?> at <?= esc($cs('university_name')) ?>.
                Our team is here to assist you with laboratory inquiries and booking support.
            </p>
        </div>

        <div class="row gy-4">

            <!-- =========================================== -->
            <!-- LEFT COLUMN: Faculty Information            -->
            <!-- =========================================== -->
            <div class="col-lg-6">
                <div class="contact-card">
                    <!-- Faculty Building Image -->
                    <img src="<?= base_url('images/fkmp/FKMP.jpeg') ?>"
                         alt="<?= esc($cs('faculty_name')) ?> Building"
                         class="contact-img"
                         onerror="this.src='https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'">

                    <!-- Faculty Information -->
                    <h4 class="faculty-title">
                        <i class="bi bi-building"></i>
                        <?= esc($cs('faculty_name')) ?>
                    </h4>

                    <p class="faculty-info">
                        <strong><?= esc($cs('university_name')) ?></strong><br>
                        <?= esc($cs('address')) ?>
                    </p>

                    <!-- Contact Details -->
                    <div class="contact-details">
                        <?php if ($cs('phone')): ?>
                        <div class="contact-detail">
                            <i class="bi bi-telephone-fill"></i>
                            <div><strong>Phone:</strong> <?= esc($cs('phone')) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($cs('fax')): ?>
                        <div class="contact-detail">
                            <i class="bi bi-printer-fill"></i>
                            <div><strong>Fax:</strong> <?= esc($cs('fax')) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($cs('operating_hours')): ?>
                        <div class="contact-detail">
                            <i class="bi bi-clock-fill"></i>
                            <div><strong>Operating Hours:</strong> <?= esc($cs('operating_hours')) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($cs('location')): ?>
                        <div class="contact-detail">
                            <i class="bi bi-geo-alt-fill"></i>
                            <div><strong>Location:</strong> <?= esc($cs('location')) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($cs('general_note')): ?>
                    <div class="contact-note">
                        <p>
                            <i class="bi bi-info-circle-fill"></i>
                            <?= esc($cs('general_note')) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- =========================================== -->
            <!-- RIGHT COLUMN: Staff Contacts                -->
            <!-- =========================================== -->
            <div class="col-lg-6">
                <div class="contact-card">
                    <h4 class="staff-section-title">
                        <i class="bi bi-people-fill"></i>
                        Key Personnel Contacts
                    </h4>

                    <?php foreach ($personnel as $person): ?>
                    <div class="staff-card">
                        <img src="<?= esc(!empty($person['photo_path']) ? base_url($person['photo_path']) : '') ?>"
                             class="staff-photo"
                             alt="<?= esc($person['name']) ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80'">

                        <div class="staff-info">
                            <div class="staff-name"><?= esc($person['name']) ?></div>
                            <div class="staff-role"><?= esc($person['role']) ?></div>

                            <div class="staff-contact">
                                <?php if (!empty($person['phone'])): ?>
                                <a href="tel:<?= esc(preg_replace('/\s+/', '', $person['phone'])) ?>" class="contact-link">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span><?= esc($person['phone']) ?></span>
                                </a>
                                <?php endif; ?>

                                <?php if (!empty($person['email'])): ?>
                                <a href="mailto:<?= esc($person['email']) ?>" class="contact-link">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span><?= esc($person['email']) ?></span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($cs('personnel_note')): ?>
                    <div class="contact-note">
                        <p>
                            <i class="bi bi-shield-check"></i>
                            <?= esc($cs('personnel_note')) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Map Section -->
        <div class="map-section">
            <h4 class="map-title">
                <i class="bi bi-geo-alt-fill"></i>
                Campus Location
            </h4>

            <?php if ($cs('map_embed_src')): ?>
            <div class="map-container">
                <iframe
                    src="<?= esc($cs('map_embed_src')) ?>"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            <?php endif; ?>

            <!-- Map Action Buttons -->
            <div class="map-actions">
                <?php if ($cs('directions_url')): ?>
                <a href="<?= esc($cs('directions_url')) ?>" target="_blank" class="map-btn">
                    <i class="bi bi-signpost"></i>
                    Get Directions
                </a>
                <?php endif; ?>

                <?php if ($cs('google_maps_url')): ?>
                <a href="<?= esc($cs('google_maps_url')) ?>" target="_blank" class="map-btn">
                    <i class="bi bi-google"></i>
                    Open in Google Maps
                </a>
                <?php endif; ?>

                <?php if ($cs('waze_url')): ?>
                <a href="<?= esc($cs('waze_url')) ?>" target="_blank" class="map-btn">
                    <i class="bi bi-signpost-split"></i>
                    Open in Waze
                </a>
                <?php endif; ?>
            </div>

            <?php if ($cs('coordinates') || $cs('parking_info') || $cs('transport_info')): ?>
            <div class="map-info">
                <p>
                    <?php if ($cs('coordinates')): ?>
                        <strong>Exact Coordinates:</strong> <?= esc($cs('coordinates')) ?><br>
                    <?php endif; ?>
                    <?php if ($cs('parking_info')): ?>
                        <strong>Parking:</strong> <?= esc($cs('parking_info')) ?><br>
                    <?php endif; ?>
                    <?php if ($cs('transport_info')): ?>
                        <strong>Public Transport:</strong> <?= esc($cs('transport_info')) ?>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const staffCards = document.querySelectorAll('.staff-card');
    staffCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
        });
        card.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) return;
        });
    });
});
</script>

<?= $this->endSection() ?>
