<?php
    $container = require __DIR__ . '/../app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];

    $pageTitle = 'Pacific Program – The Luke Center';
    $activeNav = 'pacific';

    // Insert HTML header.
    require __DIR__ . '/header.php';
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-5">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Pacific Program</h1>
        <p class="fs-5 mb-0">Perfecting catalytic leadership training.</p>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <p>Founded by Jeffrey Luke, PhD, the Pacific Program has long been the premiere resource for cultivating catalytic leadership across state and local government, nonprofit and private sectors in the Pacific Northwest.</p>
                <p>Join us at the beautiful Oregon Garden in Silverton for an energizing, immersive training series that opens the door to a modern approach to leadership.</p>
                <p>You'll become a member of an influential association of alumni (Coyotes) and cohorts who share a special, career-defining bond across decades of specialized instruction. Availability is limited, register soon to ensure your place in the program.</p>
                <div class="row g-4 align-items-stretch mt-1">
                    <div class="col-12 col-md-7 mx-auto">
                        <div class="card h-100 shadow-sm">
                            <img class="card-img-top" src="/images/meetingStockImage.png" alt="Meeting Picture">                        
                            <div class="card-body">
                                <!--
                                    <h3 class="h5 text-brand mb-1">The Pacific Program</h3>
                                    <p class="mb-1 fw-semibold text-secondary">The Oregon Gardens</p>
                                    <p class="mb-3">October 12th - 16th</p>
                                    <a class="btn btn-brand me-2 mb-1" href="/apply.php" data-application-button>Apply Now</a>
                                    <a class="btn btn-outline-brand mb-1" href="/catalyticLeadership.php">Explore Catalytic Leadership</a>
                                -->
                                <h3 class="h5 text-brand mb-1"><?= htmlspecialchars($programName, ENT_QUOTES) ?></h3>
                                <p class="mb-1 fw-semibold text-secondary"><?= htmlspecialchars($programLocation, ENT_QUOTES) ?></p>
                                <p class="mb-3"><?= htmlspecialchars($programDates, ENT_QUOTES) ?></p>
                                <a class="btn btn-brand me-2" href="/apply.php" data-application-button <?= $applicationOpen ? '' : 'disabled' ?>>Apply Now</a>
                                <a class="btn btn-outline-brand" href="/catalyticLeadership.php">Explore Catalytic Leadership</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5 d-flex">
                        <div class="card h-100 shadow-sm flex-fill">
                            <div class="card-body">
                                <h4 class="h5 text-center mb-2">What You'll Learn</h4>
                                <ul class="mb-3">
                                    <li class="fw-bold">Leading from Personal Passion and Strength of Character</li>
                                    <p>A passion for results, a sense of connectedness and relatedness, and exemplary personal integrity.</p>
                                    <li class="fw-bold">Thinking and Acting Strategically</li>
                                    <p>Reframe issues and your responses to them. Map the network. Focus on end results. Convert strategy into action. Frame the real problem to set clear outcomes. Find leverage. Act, learn, adjust.</p>
                                    <li class="fw-bold">Facilitating Productive Work Groups</li>
                                    <p>Generating fresh ideas and new insights, coping with conflict, getting unstuck and moving forward, and forging agreements.</p>
                                </ul>
                                <div class="text-center">
                                    <a class="btn btn-outline-brand" href="https://drive.google.com/file/d/1NgsWUEDqezEXYFyYgmO5Ik-70roVdqvI/view" target="_blank" rel="noopener">Program Flyer</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
    // Insert HTML footer.
    require __DIR__ . '/footer.php'; 
?>
