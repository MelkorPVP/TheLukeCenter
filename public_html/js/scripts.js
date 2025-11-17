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

function initializeForm(formId, statusId)
{
        const formElement = document.getElementById(formId);
        const statusAlertElement = document.getElementById(statusId);

        if (!formElement || !statusAlertElement) return;

        const hideStatus = () =>
        {
                statusAlertElement.textContent = '';
                statusAlertElement.classList.add('d-none');
                statusAlertElement.classList.remove('alert-success', 'alert-danger');
        };

        // 1) Clears when browser blocks submit due to missing required fields
        formElement.addEventListener('invalid', hideStatus, true);

        // 2) Clears as soon as the user starts fixing any field
        formElement.addEventListener('input', (event) =>
                {
                        if (event.target.matches('input, select, textarea')) hideStatus();
                });

        // 3) Force browsers to run native constraint validation before submitting
        formElement.addEventListener('submit', (event) =>
                {
                        if (!formElement.reportValidity())
                        {
                                event.preventDefault();
                                event.stopPropagation();
                                return;
                        }

                        hideStatus();
                });
}

// Run immediately because <script> is deferred
initializeNav();
initializeForm('contactForm', 'contactStatus');
initializeForm('applyForm', 'applyStatus');
