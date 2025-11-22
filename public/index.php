<?php
    $container = require __DIR__ . '/../app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];

    $pageTitle = 'The Luke Center For Catalytic Leadership';
    $activeNav = 'home';

    // Insert HTML header.
    require __DIR__ . '/header.php';
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-5">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">The Luke Center For Catalytic Leadership</h1>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <h2 class="h2 text-dark mb-2">Welcome to the Luke Center</h2>
                <p class="text-muted fw-semibold mb-3">Home of the Pacific Program Catalytic Leadership Training</p>
                <p>The Luke Center for Catalytic Leadership is Oregon's premier leadership development organization. Established in 1997, The Luke Center seeks to create and support transformational leadership for the public good.</p>
                <p>Catalytic leadership centers around the visionary work of the late Dr. Jeff Luke, and involves the application of strategic leadership skills for addressing complex and interconnected issues of public interest.</p>
                <p>The Pacific Program is The Luke Center's flagship training event. For over 30 years, The Pacific Program has trained nearly one-thousand leaders in the public, nonprofit, and private sectors.</p>
                <p>In addition to the The Pacific Program, The Luke Center has produced other training events including the Cascade Program for Emerging Leaders, partnership training with Leadership Oregon.</p>
                <p>Today's financial, social, and political challenges underscore the need for catalytic leadership here in the beautiful Pacific Northwest.</p>
                <p>The solutions to these challenges will be found through vision, building collaborative relationships across sectors, and unwavering passion. The Luke Center is here to help you meet these challenges.</p>
                <p>Come join us for one of our transformational leadership development trainings!</p>
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
