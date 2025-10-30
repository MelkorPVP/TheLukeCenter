<?php

declare(strict_types=1);

/** @var array<string, mixed> $container */
$container = require __DIR__ . '/bootstrap.php';

$siteContentService = $container['siteContentService'];
$programName = $siteContentService->getProgramName();
$programLocation = $siteContentService->getProgramLocation();
$programDates = $siteContentService->getProgramDates();
$applicationOpen = $siteContentService->isApplicationOpen();

$pageTitle = 'Apply – The Luke Center';
$activeNav = 'apply';

require __DIR__ . '/../templates/partials/header.php';
?>
    <section class="hero text-center text-hero border-bottom">
      <div class="container py-5">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Apply to the Pacific Program</h1>
        <p class="fs-5 mb-0"><?= htmlspecialchars($programName, ENT_QUOTES) ?> – <?= htmlspecialchars($programDates, ENT_QUOTES) ?>, <?= htmlspecialchars($programLocation, ENT_QUOTES) ?></p>
      </div>
    </section>
    <section class="py-4 py-md-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-9">
            <p class="mb-4">Please complete the application form below. Required fields are marked with an asterisk (*). You will receive a confirmation email once your application has been submitted.</p>
            <div id="applyFormErrors" class="alert alert-danger d-none" role="alert" data-error-summary></div>
            <form id="applyForm" class="needs-validation" novalidate>
              <input type="text" class="d-none" tabindex="-1" autocomplete="off" data-honeypot>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="applicantFirstName">First Name *</label>
                  <input class="form-control" type="text" id="applicantFirstName" name="applicantFirstName" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="applicantLastName">Last Name *</label>
                  <input class="form-control" type="text" id="applicantLastName" name="applicantLastName" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="applicantPreferredName">Preferred Name</label>
                  <input class="form-control" type="text" id="applicantPreferredName" name="applicantPreferredName">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="applicantPronouns">Pronouns</label>
                  <input class="form-control" type="text" id="applicantPronouns" name="applicantPronouns">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="applicantEmail">Email *</label>
                  <input class="form-control" type="email" id="applicantEmail" name="applicantEmail" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="applicantPhone">Phone *</label>
                  <input class="form-control" type="tel" id="applicantPhone" name="applicantPhone" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="applicantPhoneType">Phone Type *</label>
                  <select class="form-select" id="applicantPhoneType" name="applicantPhoneType" required>
                    <option value="" selected disabled>Select an option</option>
                    <option value="Mobile">Mobile</option>
                    <option value="Home">Home</option>
                    <option value="Work">Work</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="addressOne">Street Address *</label>
                  <input class="form-control" type="text" id="addressOne" name="addressOne" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="addressTwo">Street Address Two</label>
                  <input class="form-control" type="text" id="addressTwo" name="addressTwo">
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="city">City *</label>
                  <input class="form-control" type="text" id="city" name="city" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="state">State / Province *</label>
                  <input class="form-control" type="text" id="state" name="state" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="zip">Zip / Postal Code *</label>
                  <input class="form-control" type="text" id="zip" name="zip" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="vegan">Are you vegan? *</label>
                  <select class="form-select" id="vegan" name="vegan" required>
                    <option value="" selected disabled>Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="vegetarian">Are you vegetarian? *</label>
                  <select class="form-select" id="vegetarian" name="vegetarian" required>
                    <option value="" selected disabled>Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="diet">Other dietary restrictions</label>
                  <textarea class="form-control" id="diet" name="diet" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="accommodations">Accessibility needs</label>
                  <textarea class="form-control" id="accommodations" name="accommodations" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="org">Organization / Agency *</label>
                  <input class="form-control" type="text" id="org" name="org" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="title">Current Title / Role *</label>
                  <input class="form-control" type="text" id="title" name="title" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="supName">Sponsor / Supervisor Name *</label>
                  <input class="form-control" type="text" id="supName" name="supName" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="supEmail">Sponsor / Supervisor Email *</label>
                  <input class="form-control" type="email" id="supEmail" name="supEmail" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="supPhone">Sponsor / Supervisor Phone *</label>
                  <input class="form-control" type="tel" id="supPhone" name="supPhone" required>
                </div>
                <div class="col-12">
                  <label class="form-label" for="refferalQuestion">How did you learn about the Pacific Program? *</label>
                  <textarea class="form-control" id="refferalQuestion" name="refferalQuestion" rows="3" required></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label" for="questionOne">Describe your leadership responsibilities *</label>
                  <textarea class="form-control" id="questionOne" name="questionOne" rows="3" required></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label" for="questionTwo">Describe your experience working across sectors *</label>
                  <textarea class="form-control" id="questionTwo" name="questionTwo" rows="3" required></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label" for="questionThree">Describe a professional challenge you'd like to discuss *</label>
                  <textarea class="form-control" id="questionThree" name="questionThree" rows="3" required></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label" for="questionFour">How will the program support your goals? *</label>
                  <textarea class="form-control" id="questionFour" name="questionFour" rows="3" required></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="partialScholarship">Will a partial scholarship impact your ability to attend?</label>
                  <select class="form-select" id="partialScholarship" name="partialScholarship">
                    <option value="" selected disabled>Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="assistAmount">What amount of financial assistance are you requesting?</label>
                  <input class="form-control" type="text" id="assistAmount" name="assistAmount">
                </div>
              </div>
              <div class="d-grid d-sm-flex gap-3 mt-4">
                <button class="btn btn-brand" type="submit" data-application-button <?= $applicationOpen ? '' : 'disabled' ?>>Submit Application</button>
                <a class="btn btn-outline-brand" href="/pacific-program.php">Back to Program Overview</a>
              </div>
              <div id="applyStatus" class="alert mt-3 d-none" role="status" data-status></div>
            </form>
          </div>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/../templates/partials/footer.php';
