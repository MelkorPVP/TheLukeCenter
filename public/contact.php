<?php

declare(strict_types=1);

$container = require __DIR__ . '/bootstrap.php';
$config = $container['config'];

$programName = site_content_program_name($config);
$programLocation = site_content_program_location($config);
$programDates = site_content_program_dates($config);
$applicationOpen = site_content_application_open($config);

$pageTitle = 'Contact – The Luke Center';
$activeNav = 'contact';

require __DIR__ . '/includes/layout/header.php';
?>
    <section class="hero text-center text-hero border-bottom">
      <div class="container py-5">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Stay Connected</h1>
        <p class="fs-5 mb-0">Update your alumni information to receive news and opportunities.</p>
      </div>
    </section>
    <section class="py-4 py-md-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <p class="mb-4">Alumni and friends of The Luke Center, please share your current contact details so we can keep you informed about upcoming gatherings, volunteer opportunities, and Pacific Program updates.</p>
            <div id="contactFormErrors" class="alert alert-danger d-none" role="alert" data-error-summary></div>
            <form id="contactForm" class="needs-validation" novalidate>
              <input type="text" class="d-none" tabindex="-1" autocomplete="off" data-honeypot>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="contactFirstName">First Name *</label>
                  <input class="form-control" type="text" id="contactFirstName" name="contactFirstName" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contactLastName">Last Name</label>
                  <input class="form-control" type="text" id="contactLastName" name="contactLastName">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contactEmail">Email *</label>
                  <input class="form-control" type="email" id="contactEmail" name="contactEmail" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contactPhone">Phone</label>
                  <input class="form-control" type="tel" id="contactPhone" name="contactPhone">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contactPhoneType">Phone Type</label>
                  <select class="form-select" id="contactPhoneType" name="contactPhoneType">
                    <option value="" selected>Not specified</option>
                    <option value="Mobile">Mobile</option>
                    <option value="Home">Home</option>
                    <option value="Work">Work</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="currentWork">Current Organization</label>
                  <input class="form-control" type="text" id="currentWork" name="currentWork">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="yearAttended">Pacific Program Year</label>
                  <input class="form-control" type="text" id="yearAttended" name="yearAttended" placeholder="e.g., 2018">
                </div>
              </div>
              <div class="d-grid d-sm-flex gap-3 mt-4">
                <button class="btn btn-brand" type="submit" id="contactButton">Submit Information</button>
                <a class="btn btn-outline-brand" href="/alumni.php">Back to Alumni Page</a>
              </div>
              <div id="contactStatus" class="alert mt-3 d-none" role="status" data-status></div>
            </form>
          </div>
          <div class="col-lg-4">
            <div class="card shadow-sm h-100 mt-4 mt-lg-0">
              <div class="card-body">
                <h3 class="h6 text-brand mb-3">Upcoming Program</h3>
                <p class="mb-1"><strong><?= htmlspecialchars($programName, ENT_QUOTES) ?></strong></p>
                <p class="mb-1 text-muted"><?= htmlspecialchars($programDates, ENT_QUOTES) ?></p>
                <p class="mb-3"><?= htmlspecialchars($programLocation, ENT_QUOTES) ?></p>
                <a class="btn btn-brand w-100 mb-2" href="/apply.php" data-application-button <?= $applicationOpen ? '' : 'disabled' ?>>Apply Now</a>
                <a class="btn btn-outline-brand w-100" href="/pacific-program.php">Program Details</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/includes/layout/footer.php';
?>
