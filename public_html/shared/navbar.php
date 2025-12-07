<!-- BEGIN NAV BAR -->
<nav class="navbar navbar-expand-lg container py-2 flex-column" aria-label="Primary navigation">
        <a class="navbar-brand d-flex align-items-center gap-2 mx-auto" href="/index.php">
		<img id="logo-image" class="img-fluid" src="/images/lukeCenterLogoTransparent512.png" alt="The Luke Center for Catalytic Leadership Logo.">
	</a>
	
	<button class="navbar-toggler mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNav"
	aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	
	<div class="collapse navbar-collapse mt-3 mt-lg-0" id="primaryNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fs-5">
                        <li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="/pacificProgram.php">Pacific Program</a></li>
                        <li class="nav-item"><a class="nav-link" href="/catalyticLeadership.php">Catalytic Leadership</a></li>
                        <li class="nav-item"><a class="nav-link" href="/alumni.php">Alumni</a></li>
                        <li class="nav-item"><a class="nav-link" href="/board.php">Board of Directors</a></li>
                        <li class="nav-item"><a class="nav-link" href="/apply.php">Apply</a></li>
                        <li class="nav-item"><a class="nav-link" href="/contact.php">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-developer-login>Administration</a></li>
                </ul>
        </div>
</nav>
<!-- BEGIN Developer Login Modal -->
<div class="modal fade" id="developerLoginModal" tabindex="-1" aria-labelledby="developerLoginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="developerLoginLabel">Developer Login</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form data-developer-login-form>
                    <div class="mb-3">
                        <label class="form-label" for="developerUsername">Username</label>
                        <input type="text" class="form-control" id="developerUsername" name="username" required autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="developerPassword">Password</label>
                        <input type="password" class="form-control" id="developerPassword" name="password" required autocomplete="current-password">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-brand">Sign In</button>
                        <div class="alert alert-danger d-none mb-0" role="alert" data-login-status></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Developer Login Modal -->
<!-- END NAV BAR -->