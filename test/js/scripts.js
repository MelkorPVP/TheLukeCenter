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



/* ============================================================================
	* NEW: Gallery + Testimonial Rotators
* ==========================================================================*/

function initializeGallery()
{
	const root = document.getElementById('galleryRoot');
	if (!root) return;
	
	const endpoint = root.getAttribute('data-gallery-endpoint') || '/galleryData.php';
	const imgEl    = document.getElementById('galleryImage');
	const prevBtn  = document.getElementById('galleryPrev');
	const nextBtn  = document.getElementById('galleryNext');
	const quoteEl  = document.getElementById('testimonialRotator');
	
	if (!imgEl || !prevBtn || !nextBtn) return;
	
	const imageIntervalMs = 6000;
	const quoteIntervalMs = 9000;
	const idleResumeMs    = 180000; // 3 minutes
	
	let images = [];
	let testimonials = [];
	
	let imageIndex = 0;
	let quoteIndex = 0;
	
	let imageTimer = null;
	let quoteTimer = null;
	let resumeTimer = null;
	
	const setImage = (index) =>
	{
		if (!images.length) return;
		
		imageIndex = (index + images.length) % images.length;
		const item = images[imageIndex];
		
		imgEl.src = item.url;
		imgEl.alt = item.name || 'Gallery image';
	};
	
	const setQuote = (index) =>
	{
		if (!quoteEl || !testimonials.length) return;
		
		quoteIndex = (index + testimonials.length) % testimonials.length;
		quoteEl.textContent = testimonials[quoteIndex];
	};
	
	const nextImage = () => setImage(imageIndex + 1);
	const prevImage = () => setImage(imageIndex - 1);
	
	const nextQuote = () => setQuote(quoteIndex + 1);
	
	const startImageAuto = () =>
	{
		if (imageTimer || images.length <= 1) return;
		imageTimer = setInterval(nextImage, imageIntervalMs);
	};
	
	const stopImageAuto = () =>
	{
		if (imageTimer) {
			clearInterval(imageTimer);
			imageTimer = null;
		}
	};
	
	const scheduleResume = () =>
	{
		if (resumeTimer) clearTimeout(resumeTimer);
		resumeTimer = setTimeout(() =>
			{
				startImageAuto();
			}, idleResumeMs);
	};
	
	const manualAdvance = (fn) =>
	{
		stopImageAuto();
		fn();
		scheduleResume();
	};
	
	const startQuoteAuto = () =>
	{
		if (!quoteEl || quoteTimer || testimonials.length <= 1) return;
		quoteTimer = setInterval(nextQuote, quoteIntervalMs);
	};
	
	const stopQuoteAuto = () =>
	{
		if (quoteTimer) {
			clearInterval(quoteTimer);
			quoteTimer = null;
		}
	};
	
	// Wire controls
	prevBtn.addEventListener('click', () => manualAdvance(prevImage));
	nextBtn.addEventListener('click', () => manualAdvance(nextImage));
	
	// Load data
	fetch(endpoint, { credentials: 'same-origin' })
	.then(resp =>
		{
			if (!resp.ok) throw new Error('Gallery endpoint error');
			return resp.json();
		})
		.then(data =>
			{
				images = Array.isArray(data.images) ? data.images : [];
				testimonials = Array.isArray(data.testimonials) ? data.testimonials : [];
				
				if (images.length) {
					setImage(0);
					startImageAuto();
					} else {
					imgEl.alt = 'No gallery images available';
				}
				
				if (quoteEl && testimonials.length) {
					setQuote(0);
					startQuoteAuto();
				}
				
				// If there are no testimonials, hide the block cleanly
				if (quoteEl && !testimonials.length) {
					quoteEl.textContent = '';
				}
			})
			.catch(() =>
				{
					imgEl.alt = 'Failed to load gallery';
					if (quoteEl) {
						quoteEl.textContent = '';
					}
				});
				
				// Clean up on page hide
				window.addEventListener('pagehide', () =>
					{
						stopImageAuto();
						stopQuoteAuto();
						if (resumeTimer) clearTimeout(resumeTimer);
					});
}



// Run immediately because <script> is deferred
initializeNav();
initializeForm('contactForm', 'contactStatus');
initializeForm('applyForm', 'applyStatus');
initializeGallery();