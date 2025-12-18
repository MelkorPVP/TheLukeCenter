<?php
    $container = require_once __DIR__ . '/app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];
    
    $pageTitle = 'Apply – The Luke Center';
    $activeNav = 'apply';
    
    // Check for headers
    if (!headers_sent())
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    // Prepair message(s)
    $message = $_SESSION['message'] ?? '';
    $messageType = $_SESSION['messageType'] ?? ''; // 'success' | 'error'
    unset($_SESSION['message'], $_SESSION['messageType']); // one-time
    
    $hasMessage   = $message !== '';
    $alertClass = $hasMessage ? ($messageType === 'success' ? 'alert-success' : 'alert-danger') : 'd-none';
    
    // Insert HTML header.
    require app_public_path('header.php', APP_ENVIRONMENT);
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-4">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Participate in the Pacific Program</h1>
        <p class="fs-5 mb-0">Fill out the form below to take the first step on your leadership journey.</p>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <p class="mb-4">Please complete the application form below. Required fields are marked with an asterisk (*). You will receive a confirmation email once your application has been submitted.</p>
                <form id="applyForm" class="needs-validation" action="handleApplyForm.php" method="post">
                    <input type="text" class="d-none" tabindex="-1" autocomplete="off" data-honeypot>
                    <div class="row g-3">
                        <div class="col-12 pt-2">
                            <h2 class="h5 text-uppercase text-secondary">Personal Information</h2>
                        </div>                             
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
                        <div class="col-12 pt-2">
                            <h2 class="h5 text-uppercase text-secondary">Contact Information</h2>
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
                        <div class="col-12 pt-2">
                            <h2 class="h5 text-uppercase text-secondary">Mailing Information</h2>
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
                            <label id="stateLabel" name="stateLabel" class="form-label" for="state" >State/Province</label>
                            <select id="state" name="state" class="form-select" required>
                                <option value="" selected disabled>Select...</option>
                                <?php
                                    // Insert States.
                                    require app_public_path('states.php', APP_ENVIRONMENT);
                                ?>
                            </select>                            
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="zipCode">Zip / Postal Code *</label>
                            <input class="form-control" type="text" id="zipCode" name="zipCode" required>
                        </div>
                        <div class="col-12 pt-2">
                            <h2 class="h5 text-uppercase text-secondary">Other</h2>
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
                            <label class="form-label" for="dietaryRestrictions">Other dietary restrictions</label>
                            <textarea class="form-control" id="dietaryRestrictions" name="dietaryRestrictions" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="accessibilityNeeds">Accessibility needs</label>
                            <textarea class="form-control" id="accessibilityNeeds" name="accessibilityNeeds" rows="2"></textarea>
                        </div>
                        <div class="col-12 pt-2">
                            <h2 class="h5 text-uppercase text-secondary">Background Information</h2>
                        </div>                          
                        <div class="col-md-6">
                            <label class="form-label" for="organiaztion">Organization / Agency *</label>
                            <input class="form-control" type="text" id="organiaztion" name="organiaztion" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="currentTitle">Current Title / Role *</label>
                            <input class="form-control" type="text" id="currentTitle" name="currentTitle" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="sponsorName">Sponsor / Supervisor Name *</label>
                            <input class="form-control" type="text" id="sponsorName" name="sponsorName" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="sponsorEmail">Sponsor / Supervisor Email *</label>
                            <input class="form-control" type="email" id="sponsorEmail" name="sponsorEmail" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="sponsorPhone">Sponsor / Supervisor Phone *</label>
                            <input class="form-control" type="tel" id="sponsorPhone" name="sponsorPhone" required>
                        </div>
                        <div class="col-12 pt-2">
                            <h2 class="h5 text-uppercase text-secondary">Background Questions</h2>
                        </div>                          
                        <div class="col-12">
                            <label class="form-label" for="questionOne">How did you learn about the Pacific Program? *</label>
                            <textarea class="form-control" id="questionOne" name="questionOne" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="questionTwo">Describe your leadership responsibilities *</label>
                            <textarea class="form-control" id="questionTwo" name="questionTwo" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="questionThree">Describe your experience working across sectors *</label>
                            <textarea class="form-control" id="questionThree" name="questionThree" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="questionFour">Describe a professional challenge you'd like to discuss *</label>
                            <textarea class="form-control" id="questionFour" name="questionFour" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="questionFive">How will the program support your goals? *</label>
                            <textarea class="form-control" id="questionFive" name="questionFive" rows="3" required></textarea>
                        </div>
                        <div class="col-12 pt-2">
                            <h2 class="h5 text-uppercase text-secondary">Scholarship Information</h2>
                        </div>                          
                        <div class="col-md-6">
                            <label class="form-label" for="scholarshipQuestion">Will a partial scholarship impact your ability to attend?</label>
                            <select class="form-select" id="scholarshipQuestion" name="scholarshipQuestion" required>
                                <option value="" selected disabled>Select an option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="scholarshipAmount">What amount of financial assistance are you requesting?</label>
                            <input class="form-control" type="text" id="scholarshipAmount" name="scholarshipAmount">
                        </div>
                    </div>
                    <div class="d-grid d-sm-flex gap-3 mt-4">
                        <button class="btn btn-brand" type="submit" data-application-button <?= $applicationOpen ? '' : 'disabled' ?>>Submit Application</button>
                        <a class="btn btn-outline-brand" href="/pacificProgram.php">Back to Program Overview</a>
                    </div>
                    <div id="applyStatus" class="alert mt-3 <?php echo $alertClass; ?>" role="status" data-status>
                        <?php if ($hasMessage) 
                            {
                                // If you ever include <br> in messages, allow only <br> tags:
                                echo strip_tags($message, '<br>');
                            } 
                        ?>
                    </div>
                </form>
            </div>
        </div>       
    </div>
</section>
<?php
    // Insert HTML footer.
    require app_public_path('footer.php', APP_ENVIRONMENT);
?>
