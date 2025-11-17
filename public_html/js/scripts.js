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

// --- Contact Form: clear alert on submit ---
function initializeContactForm() 
{
	const contactFormElement = document.getElementById('contactForm');
	const statusAlertElement  = document.getElementById('contactStatus');
	
	if (!contactFormElement || !statusAlertElement) return;
	
	const hideStatus = () => 
	{
		statusAlertElement.textContent = '';
		statusAlertElement.classList.add('d-none');
		statusAlertElement.classList.remove('alert-success', 'alert-danger');
	};
	
	// 1) Clears when browser blocks submit due to missing required fields
	contactFormElement.addEventListener('invalid', hideStatus, true);
	
	// 2) Clears as soon as the user starts fixing any field
	contactFormElement.addEventListener('input', (e) => 
		{
			if (e.target.matches('input, select, textarea')) hideStatus();
		});
		
		// 3) Clears on actual submit when the form is valid
		contactFormElement.addEventListener('submit', hideStatus);
}

// --- Contact Form: clear alert on submit ---
function initializeApplyForm() 
{
	const applyFormElement = document.getElementById('applyForm');
	const statusAlertElement  = document.getElementById('applyStatus');
	
	if (!applyFormElement || !statusAlertElement) return;
	
	const hideStatus = () => 
	{
		statusAlertElement.textContent = '';
		statusAlertElement.classList.add('d-none');
		statusAlertElement.classList.remove('alert-success', 'alert-danger');
	};
	
	// 1) Clears when browser blocks submit due to missing required fields
	applyFormElement.addEventListener('invalid', hideStatus, true);
	
	// 2) Clears as soon as the user starts fixing any field
	applyFormElement.addEventListener('input', (e) => 
		{
			if (e.target.matches('input, select, textarea')) hideStatus();
		});
		
		// 3) Clears on actual submit when the form is valid
		applyFormElement.addEventListener('submit', hideStatus);
}

// Run immediately because <script> is deferred
initializeNav();
initializeContactForm();
initializeApplyForm();
