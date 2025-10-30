<?php

declare(strict_types=1);

$container = require __DIR__ . '/bootstrap.php';
$config = $container['config'];

$programName = site_content_program_name($config);
$programLocation = site_content_program_location($config);
$programDates = site_content_program_dates($config);
$applicationOpen = site_content_application_open($config);

$pageTitle = 'Alumni – The Luke Center';
$activeNav = 'alumni';

require __DIR__ . '/includes/layout/header.php';
?>
    <section class="hero text-center text-hero border-bottom">
      <div class="container py-5">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Alumni</h1>
        <p class="fs-5 mb-0">Welcome Coyotes!</p>
      </div>
    </section>
    <section class="py-4 py-md-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-9">
            <p>For over 30 years, the transformative teachings of The Pacific Program has been moving and growing the skills and passion of leaders. Each cohort, a unique and diverse community, developing connections and friendships that last for many years.</p>
            <p>As the Luke Center creates new opportunities to reconnect and recharge catalytic energy with alumni, we'll post upcoming events and information here.</p>
            <div class="row g-4 mt-1">
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <img class="card-img-top" src="https://lh3.googleusercontent.com/d/1FjWxxv6hJkoL9TnyKdmxLnWEGGNS2xwB=w800" srcset="https://lh3.googleusercontent.com/d/1FjWxxv6hJkoL9TnyKdmxLnWEGGNS2xwB=w400 400w, https://lh3.googleusercontent.com/d/1FjWxxv6hJkoL9TnyKdmxLnWEGGNS2xwB=w800 800w, https://lh3.googleusercontent.com/d/1FjWxxv6hJkoL9TnyKdmxLnWEGGNS2xwB=w1200 1200w" sizes="(max-width: 768px) 100vw, 800px" alt="Stay connected" loading="lazy">
                  <div class="card-body">
                    <h3 class="h5 text-brand">Update Your Contact Information</h3>
                    <p class="mb-3">Ensure you receive alumni updates and invitations to upcoming events.</p>
                    <a class="btn btn-brand" href="/contact.php">Share Your Info</a>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <div class="card-body text-center">
                    <h3 class="h5 text-brand mb-1"><?= htmlspecialchars($programName, ENT_QUOTES) ?></h3>
                    <p class="mb-1 fw-semibold text-secondary"><?= htmlspecialchars($programLocation, ENT_QUOTES) ?></p>
                    <p class="mb-3"><?= htmlspecialchars($programDates, ENT_QUOTES) ?></p>
                    <a class="btn btn-outline-brand me-2" href="/pacific-program.php">View Program Details</a>
                    <a class="btn btn-brand" href="/apply.php" data-application-button <?= $applicationOpen ? '' : 'disabled' ?>>Apply Now</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/includes/layout/footer.php';
?>
