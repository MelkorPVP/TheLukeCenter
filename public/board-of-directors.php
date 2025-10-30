<?php

declare(strict_types=1);

/** @var array<string, mixed> $container */
$container = require __DIR__ . '/bootstrap.php';

$siteContentService = $container['siteContentService'];
$directors = $siteContentService->getDirectors();
$executiveChair = $siteContentService->getExecutiveRole('executive_chair');
$executiveViceChair = $siteContentService->getExecutiveRole('executive_vicechair');
$executiveTreasurer = $siteContentService->getExecutiveRole('executive_treasurer');
$executiveSecretary = $siteContentService->getExecutiveRole('executive_secretary');

$pageTitle = 'Board of Directors – The Luke Center';
$activeNav = 'board';

require __DIR__ . '/../templates/partials/header.php';
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
          <div class="col-lg-10">
            <div class="row g-4 mb-4">
              <div class="col-12">
                <h2 class="h5 text-uppercase text-secondary">Executive Officers</h2>
              </div>
              <?php
                $executives = [
                  $executiveChair,
                  $executiveViceChair,
                  $executiveTreasurer,
                  $executiveSecretary,
                ];
                foreach ($executives as $executive):
                  if (empty($executive[0])) {
                    continue;
                  }
              ?>
                <div class="col-sm-6 col-lg-3">
                  <div class="card h-100 shadow-sm">
                    <div class="card-body">
                      <h3 class="h6 mb-1"><?= htmlspecialchars($executive[0], ENT_QUOTES) ?></h3>
                      <p class="mb-0 text-muted"><?= htmlspecialchars($executive[1] ?? '', ENT_QUOTES) ?></p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="row g-4">
              <div class="col-12">
                <h2 class="h5 text-uppercase text-secondary">Directors</h2>
              </div>
              <?php foreach ($directors as $director): ?>
                <div class="col-sm-6 col-lg-3">
                  <div class="card h-100 shadow-sm">
                    <div class="card-body">
                      <h3 class="h6 mb-1"><?= htmlspecialchars($director[0] ?? '', ENT_QUOTES) ?></h3>
                      <p class="mb-0 text-muted"><?= htmlspecialchars($director[1] ?? '', ENT_QUOTES) ?></p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/../templates/partials/footer.php';
