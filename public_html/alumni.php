<?php
    $container = require_once __DIR__ . '/app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];
    
    $pageTitle = 'Alumni – The Luke Center';
    $activeNav = 'alumni';
    
    // Insert HTML header.
    require app_public_path('header.php', APP_ENVIRONMENT);
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-4">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Alumni</h1>
        <p class="fs-5 mb-0">Welcome Coyotes!</p>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container"> <!-- Open Container -->
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="row justify-content-center">
                    <p>For over 30 years, the transformative teachings of The Pacific Program has been moving and growing the skills and passion of leaders. Each cohort, a unique and diverse community, developing connections and friendships that last for many years.</p>
                    <p>As the Luke Center creates new opportunities to reconnect and recharge catalytic energy with alumni, we'll post upcoming events and information here.</p>
                </div>               
                <div class="row g-4 align-items-stretch mt-1">
                    <div class="col-12 col-md-5 d-flex"> 
                        <!-- IMAGE 1 TOP LEFT -->
                        <div class="card h-100 border-0">
                            <div class="ratio ratio-4x3 overflow-hidden">
                                <img class="w-100 h-100 d-block object-fit-cover rounded-3" src="/images/contactUpdate.png" alt="Image of howling coyote.">
                            </div>
                            <div class="card-body text-center">
                                <h3 class="h5 text-brand">Update Your Contact Information</h3>
                                <p class="mb-3">Ensure you receive alumni updates and invitations to upcoming events.</p>
                                <a class="btn btn-brand" href="/contact.php">Share Your Info</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-7 mx-auto">
                        <div class="card h-100 border-0">
                            <!-- IMAGE 2 TOP RIGHT -->
                            <div id="alumniRoot" class="lc-gallery" data-alumni-endpoint="/handleAlumniGalleryData.php">
                                <div class="lc-gallery-frame">
                                    <img id="alumniImage" class="lc-gallery-img" src="" alt="Alumni image">
                                    <button id="alumniPrev" type="button" class="lc-gallery-arrow lc-gallery-prev" aria-label="Previous image">
                                        &#8249;
                                    </button>
                                    <button id="alumniNext" type="button" class="lc-gallery-arrow lc-gallery-next" aria-label="Next image">
                                        &#8250;
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">        
                                <h3 class="h5 text-brand mb-1 text-center">Past Pacific Programs</h3>                                                                
                            </div>                             
                        </div>
                    </div>            
                </div>           
                <?php 
                    // Insert HTML lower main section.
                    require app_public_path('lowerMainSection.php', APP_ENVIRONMENT);
                ?>
            </div>
        </div>
    </div> <!-- Close Container -->
</section>
<?php
    // Insert HTML footer.
    require app_public_path('footer.php', APP_ENVIRONMENT);
?>
