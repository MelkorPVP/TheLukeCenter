<?php
    $container = require dirname(__DIR__, 2) . '/app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];
    
    $pageTitle = 'The Luke Center For Catalytic Leadership';
    $activeNav = 'home';
    
    // Insert HTML header.
    require app_public_path('header.php', APP_ENVIRONMENT);
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-4">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">The Luke Center For Catalytic Leadership</h1>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            
            <div class="mb-4">
                <h1 class="text-brand">Gallery</h1>
                <?php if ($programDates): ?>
                <div class="text-muted"><?php echo htmlspecialchars($programDates, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
            
            <div
            id="galleryRoot"
            class="lc-gallery"
            data-gallery-endpoint="/galleryData.php"
            >
                <div class="lc-gallery-frame">
                    <img
                    id="galleryImage"
                    class="lc-gallery-img"
                    src=""
                    alt="Gallery image"
                    loading="lazy"
                    >
                    
                    <button
                    id="galleryPrev"
                    type="button"
                    class="lc-gallery-arrow lc-gallery-prev"
                    aria-label="Previous image"
                    >
                        &#8249;
                    </button>
                    
                    <button
                    id="galleryNext"
                    type="button"
                    class="lc-gallery-arrow lc-gallery-next"
                    aria-label="Next image"
                    >
                        &#8250;
                    </button>
                </div>
            </div>
            
            <div class="mt-4 p-4 rounded-3 section-accent">
                <h2 class="h5 text-brand mb-3">What people are saying</h2>
                <div
                id="testimonialRotator"
                class="lc-testimonial fs-5"
                aria-live="polite"
                ></div>
            </div>
            
        </div>
    </div>   
</section>
<?php
    // Insert HTML footer.
    require app_public_path('footer.php', APP_ENVIRONMENT);
?>
