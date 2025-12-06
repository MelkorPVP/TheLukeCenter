<?php
    $container = require __DIR__ . '/../app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];

    $pageTitle = 'Contact – The Luke Center';
    $activeNav = 'contact';

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
    require __DIR__ . '/header.php';
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-4">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Coyotes! Stay with your pack!</h1>
        <p class="fs-5 mb-0">Update your alumni information to recieve news and opportunities.</p>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <p class="mb-4">Alumni and friends of The Luke Center, please share your current contact details so we can keep you informed about upcoming gatherings, volunteer opportunities, and Pacific Program updates.</p>
                <form id="contactForm" class="needs-validation" action="handleContactForm.php" method="post">
                    <input type="text" class="d-none" tabindex="-1" autocomplete="off" data-honeypot>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="contactFirstName">First Name *</label>
                            <input class="form-control" type="text" id="contactFirstName" name="contactFirstName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="contactLastName">Last Name *</label>
                            <input class="form-control" type="text" id="contactLastName" name="contactLastName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="contactEmail">Email *</label>
                            <input class="form-control" type="email" id="contactEmail" name="contactEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone *</label>
                            <input class="form-control" type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phoneType">Phone Type *</label>
                            <select class="form-select" id="phoneType" name="phoneType" required>
                                <option value="" selected disabled>Select...</option>
                                <option value="Mobile">Mobile</option>
                                <option value="Home">Home</option>
                                <option value="Work">Work</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="currentOrganization">Current Organization</label>
                            <input class="form-control" type="text" id="currentOrganization" name="currentOrganization">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="yearAttended">Pacific Program Year *</label>
                            <input class="form-control" type="text" id="yearAttended" name="yearAttended" placeholder="e.g., 2018" required>
                        </div>
                    </div>
                    <div class="d-grid d-sm-flex gap-3 mt-4">
                        <button class="btn btn-brand" type="submit" id="contactButton">Submit Information</button>
                        <a class="btn btn-outline-brand" href="/alumni.php">Back to Alumni Page</a>
                    </div>
                    <div id="contactStatus" class="alert mt-3 <?php echo $alertClass; ?>" role="status" data-status>
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
    require __DIR__ . '/footer.php'; 
?>
