// --- Navbar: active state ---
function initializeNav() 
{
	const normalizePathname = (path) => path.replace(/\/$/, '') || '/index.php';
	const currentPathname   = normalizePathname(location.pathname);
	const navLinkNodeList = document.querySelectorAll('#primaryNav .nav-link');
	
	navLinkNodeList.forEach((navLinkElement) => 
	{
		const linkHref = navLinkElement.getAttribute('href') || '';
		
		// Skip non-page links
		if (linkHref.startsWith('mailto:') || linkHref.startsWith('tel:') || linkHref.startsWith('#')) return;
		
		const targetPathname = normalizePathname(new URL(linkHref, location.origin).pathname);
		if (targetPathname === currentPathname) 
		{
			navLinkElement.classList.add('active');
			navLinkElement.setAttribute('aria-current', 'page');
		}
	});
}

// --- Form: run native HTML5 validation + scroll to errors ---
function initializeForm(formId, statusId)
{
    const formElement   = document.getElementById(formId);
    const statusElement = document.getElementById(statusId);

    if (!formElement || !statusElement) return;

    const hideStatus = () =>
    {
        statusElement.textContent = '';
        statusElement.classList.add('d-none');
        statusElement.classList.remove('alert-success', 'alert-danger');
    };

    // Clear status when the user edits any field
    formElement.addEventListener('input', (event) =>
    {
        if (event.target.matches('input, select, textarea')) hideStatus();
    });

    // Also clear on 'invalid' events (just for the alert box)
    formElement.addEventListener('invalid', () =>
    {
        hideStatus();
    }, true);

    // Handle submit buttons explicitly
    const submitButtons = formElement.querySelectorAll('button[type="submit"], input[type="submit"]');

    submitButtons.forEach((btn) =>
    {
        btn.addEventListener('click', (event) =>
        {
            // Always take over submit behavior
            event.preventDefault();
            event.stopPropagation();

            // If the form is valid, submit immediately
            if (formElement.checkValidity())
            {
                hideStatus();
                formElement.submit(); // we already validated
                return;
            }

            // Form is invalid: find the first invalid field
            const firstInvalid = formElement.querySelector(':invalid');
            if (!firstInvalid) return;

            // Use a higher-level container for better scroll positioning
            const container = firstInvalid.closest('.col-md-6, .col-12, .form-group') || firstInvalid;

            // Compute a scroll position that centers the container in the viewport
            const rect           = container.getBoundingClientRect();
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            let   targetY        = window.scrollY + rect.top - (viewportHeight / 2);

            if (targetY < 0) targetY = 0;

            window.scrollTo({
                top: targetY,
                behavior: 'smooth'
            });

            // After scroll has (mostly) finished, focus and show the native popup
            setTimeout(() =>
            {
                if (typeof firstInvalid.focus === 'function')
                {
                    firstInvalid.focus();
                }

                // This shows "Please fill out this field."
                firstInvalid.reportValidity();
            }, 300); // adjust delay if needed
        });
    });
}




// Run immediately because <script> is deferred
initializeNav();
initializeForm('contactForm', 'contactStatus');
initializeForm('applyForm', 'applyStatus');