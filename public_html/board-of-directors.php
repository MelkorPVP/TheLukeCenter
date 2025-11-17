<?php    
    require_once __DIR__ . '/config.php';
    
    /*
        $container = require __DIR__ . '/bootstrap.php';
        $config = $container['config'];
        
        $directors = site_content_directors($config);
        $executiveChair = site_content_role($config, 'executive_chair');
        $executiveViceChair = site_content_role($config, 'executive_vicechair');
        $executiveTreasurer = site_content_role($config, 'executive_treasurer');
        $executiveSecretary = site_content_role($config, 'executive_secretary');
        
        $pageTitle = 'Board of Directors – The Luke Center';
        $activeNav = 'board';
    */
    
    // Insert HTML header.
    require __DIR__ . '/header.php';
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-5">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Board of Directors</h1>
        <p class="fs-5 mb-0">Guiding the mission of the Luke Center.</p>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <h2 class="h5 text-uppercase text-secondary">Executive Officers</h2>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Sara Cromwell, Chair</h3>
                                <p class="mb-0 text-muted">Oregon Employment Department</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Chris Pfannkuch, Vice-Chair</h3>
                                <p class="mb-0 text-muted">Oregon Department of Transportation</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Parm Kaur, Treasurer</h3>
                                <p class="mb-0 text-muted">Oregon Department of Corrections</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Lacie Tolle, Secretary</h3>
                                <p class="mb-0 text-muted">City of Eugene</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-12">
                        <h2 class="h5 text-uppercase text-secondary">Directors</h2>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Shannon Bush</h3>
                                <p class="mb-0 text-muted">Benton County</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Seiji Shiratori</h3>
                                <p class="mb-0 text-muted">Oregon Department of Emergency Management</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Shannon Johns</h3>
                                <p class="mb-0 text-muted">Higher Education Coordinating Commission</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Jay Jackson</h3>
                                <p class="mb-0 text-muted">Oregon Department of Administrative Services</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Shamus Hannan</h3>
                                <p class="mb-0 text-muted">Oregon Department of Transportation</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Carroll Cottingham</h3>
                                <p class="mb-0 text-muted">Oregon Department of Transportation</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    // Insert HTML lower main section.
                    require __DIR__ . '/lowerMainSection.php';                 
                ?> 
            </div>
        </div>       
    </div>
</section>
<?php
    // Insert HTML footer.
    require __DIR__ . '/footer.php'; 
?>
