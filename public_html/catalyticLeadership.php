<?php
    $container = require_once __DIR__ . '/app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];

    $pageTitle = 'Catalytic Leadership – The Luke Center';
    $activeNav = 'catalytic';

    // Insert HTML header.
    require app_public_path('header.php', APP_ENVIRONMENT);
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-4">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Catalytic Leadership</h1>
        <p class="fs-5 mb-0">Navigating our interconnected world through strategy.</p>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <p>In the late 1980's, Dr. Jeff Luke of the University of Oregon undertook a research project to find out why some communities were more successful dealing with difficult issues and solving complex problems.</p>
                <p>His answer came after he studied community challenges in which multiple groups came together, ones in which no one group had clear ownership over the problem or the process.</p>
                <p>Jeff found the primary factor for success was a certain type of leadership, which he called Catalytic Leadership. Catalytic Leaders engage and motivate others to take on leadership roles and work toward a shared vision.</p>
                <p>Dr. Luke decided that the skills of Catalytic Leadership are teachable. In 1989, he brought together Catalytic Leaders from around the country to train Pacific Northwest leadership. Thus The Pacific Program was born.</p>
                <h2 class="h4 mt-4">The Catalytic Leadership Skill Set</h2>
                <ul>
                    <li><strong>Raising Awareness:</strong> Effective leadership involves focusing public attention on the issue.</li>
                    <li><strong>Forming Work Groups:</strong> Bringing people together to address the problem is essential for lasting solutions.</li>
                    <li><strong>Creating Strategies:</strong> We aim to stimulate multiple strategies and options for action.</li>
                    <li><strong>Sustaining Action:</strong> Strong implementation strategies keeps momentum alive.</li>
                    <li><strong>Thinking and Acting Strategically:</strong> We frame challenges in ways that reveal leverage points and pathways for impact.</li>
                    <li><strong>Facilitating Productive Work Groups:</strong> Productive work groups thrive when conflict is managed and progress is sustained.</li>
                    <li><strong>Leading from Personal Passion and Strength of Character:</strong> Character and personal commitment inspire trust and drive results.</li>
                </ul>
                <div class="col-12 col-md-6 mx-auto">
                    <div class="card h-100 shadow-sm">
                        <a class="btn btn-outline-brand" href="https://drive.google.com/file/d/1lb5MosJYTY04dtvBjPfwaDjMDEn182xh/view" target="_blank" rel="noopener">Download the Catalytic Leadership Overview</a>                        
                    </div>
                </div>
                <?php 
                    // Insert HTML lower main section.
                    require app_public_path('lowerMainSection.php', APP_ENVIRONMENT);
                ?>                
            </div>
        </div>
    </div>
</section>
<?php
    // Insert HTML footer.
    require app_public_path('footer.php', APP_ENVIRONMENT);
?>
