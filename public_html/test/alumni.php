<?php
    $container = require dirname(__DIR__, 2) . '/app/bootstrap.php';
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
                <div class="row g-4 mt-1">
                    <div class="col-12 col-md-6">
                        <!-- IMAGE 1 TOP LEFT -->
                        <div class="card h-100 shadow-sm">
                            <div class="ratio ratio-4x3 overflow-hidden">
                                <img class="w-100 h-100 d-block object-fit-cover" src="/images/contactUpdate.png" alt="Image of howling coyote.">
                            </div>
                            <div class="card-body">
                                <h3 class="h5 text-brand text-center">Update Your Contact Information</h3>
                                <p class="mb-3">Ensure you receive alumni updates and invitations to upcoming events.</p>
                                <a class="btn btn-brand" href="/contact.php">Share Your Info</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- IMAGE 2 TOP RIGHT -->
                        <div class="card h-100 shadow-sm">
                            <div class="ratio ratio-4x3 overflow-hidden">
                                <img class="w-100 h-100 d-block object-fit-cover" src="/images/alumni2022.png" alt="Image of the class of 2022.">
                            </div>
                            <div class="card-body">
                                <h3 class="h5 text-brand text-center">Class of 2022</h3>
                            </div>
                        </div>                
                    </div>            
                </div>
                <div class="row g-4 mt-1">
                    <div class="col-12 col-md-6">
                        <!-- IMAGE 3 BOTTOM LEFT -->
                        <div class="card h-100 shadow-sm">
                            <div class="ratio ratio-4x3 overflow-hidden">
                                <img class="w-100 h-100 d-block object-fit-cover" src="/images/alumni1995.png" alt="Image of the class of 1995.">
                            </div>
                            <div class="card-body">
                                <h3 class="h5 text-brand text-center">Class of 1995</h3>
                                <!--<p class="mb-3 text-center">Class of 1995</p>-->
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- IMAGE 4 BOTTOM RIGHT -->
                        <div class="card h-100 shadow-sm">
                            <div class="ratio ratio-4x3 overflow-hidden">
                                <img class="w-100 h-100 d-block object-fit-cover" src="/images/alumniGolfCourse.png" alt="Image of alumni.">
                            </div>
                            <div class="card-body">
                                <h3 class="h5 text-brand text-center">Past Alumni</h3>
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
