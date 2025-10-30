<?php

declare(strict_types=1);

$container = require __DIR__ . '/bootstrap.php';
$config = $container['config'];

$programName = site_content_program_name($config);
$programLocation = site_content_program_location($config);
$programDates = site_content_program_dates($config);
$applicationOpen = site_content_application_open($config);

$pageTitle = 'Catalytic Leadership – The Luke Center';
$activeNav = 'catalytic';

require __DIR__ . '/templates/header.php';
?>
    <section class="hero text-center text-hero border-bottom">
      <div class="container py-5">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Catalytic Leadership</h1>
        <p class="fs-5 mb-0">Strategies for an Interconnected World</p>
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
            <div class="row g-4 mt-1">
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <img class="card-img-top" src="https://lh3.googleusercontent.com/d/1lfakBsoEU7Uf4UgrQ0lIH0wIKXRytUaP=w800" srcset="https://lh3.googleusercontent.com/d/1lfakBsoEU7Uf4UgrQ0lIH0wIKXRytUaP=w400 400w, https://lh3.googleusercontent.com/d/1lfakBsoEU7Uf4UgrQ0lIH0wIKXRytUaP=w800 800w, https://lh3.googleusercontent.com/d/1lfakBsoEU7Uf4UgrQ0lIH0wIKXRytUaP=w1200 1200w" sizes="(max-width: 768px) 100vw, 800px" alt="Meeting Picture" loading="lazy">
                  <div class="card-body">
                    <a class="btn btn-outline-brand" href="https://drive.google.com/file/d/1lb5MosJYTY04dtvBjPfwaDjMDEn182xh/view" target="_blank" rel="noopener">Download the Catalytic Leadership Overview</a>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <h3 class="h5 text-brand mb-1"><?= htmlspecialchars($programName, ENT_QUOTES) ?></h3>
                    <p class="mb-1 fw-semibold text-secondary"><?= htmlspecialchars($programLocation, ENT_QUOTES) ?></p>
                    <p class="mb-3"><?= htmlspecialchars($programDates, ENT_QUOTES) ?></p>
                    <a class="btn btn-brand me-2" href="/apply.php" data-application-button <?= $applicationOpen ? '' : 'disabled' ?>>Apply Now</a>
                    <a class="btn btn-outline-brand" href="/pacific-program.php">View the Pacific Program</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/templates/footer.php';
?>
