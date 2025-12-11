// --- Lightweight logger ---
const logger = (() =>
{
        const ctx = window.APP_CONTEXT || {};
        const dataFlag = () =>
        {
                const body = document.body;
                if (!body) return false;
                return body.getAttribute('data-logging-enabled') === 'true';
        };

        const enabled = Boolean(ctx.developerMode || ctx.loggingEnabled || dataFlag());
        const prefix  = '[LC]';

        return {
                enabled,
                debug: (...args) =>
                {
                        if (!enabled) return;
                        console.debug(prefix, ...args);
                },
        };
})();

// --- Navbar: active state ---
function initializeNav()
{
        logger.debug('initializeNav: start');
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
                                logger.debug('initializeNav: set active nav link', linkHref);
                        }
                });
}

// --- Form: run native HTML5 validation + scroll to errors ---
function initializeForm(formId, statusId)
{
    logger.debug('initializeForm: start', { formId, statusId });
    const formElement   = document.getElementById(formId);
    const statusElement = document.getElementById(statusId);

    if (!formElement || !statusElement) return;
	
    // Helper to show user-friendly status in the same alert area used for success/error messages.	
    const setStatus = (message, type) =>	
    {		
        statusElement.textContent = message;		
        statusElement.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-primary');		
        statusElement.classList.add('alert', `alert-${type}`);		
	};	
	
    const hideStatus = () =>	
    {		
        statusElement.textContent = '';		
        statusElement.classList.add('d-none');		
        statusElement.classList.remove('alert-success', 'alert-danger', 'alert-primary');		
	};	
	
    // Clear status when the user edits any field
    formElement.addEventListener('input', (event) =>
                {
                        if (event.target.matches('input, select, textarea')) hideStatus();
                });
    logger.debug('initializeForm: input listener attached', formId);

                // Also clear on 'invalid' events (just for the alert box)
                formElement.addEventListener('invalid', () =>
                        {
                                hideStatus();
                        }, true);
    logger.debug('initializeForm: invalid listener attached', formId);
			
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
                                                                // Surface an immediate, blue Bootstrap alert so the user knows the submit is in progress.
                                                                setStatus('Submitting, please wait…', 'primary');

                                                                // Prevent duplicate submissions while the page posts back.
                                                                submitButtons.forEach((button) => button.setAttribute('disabled', 'disabled'));
                                                                formElement.submit(); // we already validated
                                                                logger.debug('initializeForm: submitted valid form', formId);
                                                                return;
                                                        }

                                                        // Form is invalid: find the first invalid field
                                                        const firstInvalid = formElement.querySelector(':invalid');
                                                        if (!firstInvalid) return;

                                                        logger.debug('initializeForm: invalid form, focusing first error', { formId, fieldId: firstInvalid.id || firstInvalid.name || '' });
							
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
                        logger.debug('initializeForm: submit listeners attached', formId, submitButtons.length);
}



/* ============================================================================	
	* NEW: Gallery + Testimonial Rotators	
* ==========================================================================*/

// Shared fetch helper so gallery + testimonials can reuse the same payload
const galleryPayloadCache = new Map();

const fetchGalleryPayload = (endpoint) =>
{
	const resolvedEndpoint = endpoint || '/galleryData.php';
	
	if (galleryPayloadCache.has(resolvedEndpoint)) return galleryPayloadCache.get(resolvedEndpoint);
	
	const controller = new AbortController();
	const timeoutId  = setTimeout(() => controller.abort(), 8000);
	
	const request = fetch(resolvedEndpoint, { credentials: 'same-origin', signal: controller.signal })
	.then((resp) =>
		{
			if (!resp.ok) throw new Error('Gallery endpoint error');
			return resp.json();
		})
		.catch(() => ({ images: [], testimonials: [] }))
		.finally(() => clearTimeout(timeoutId));
		
		galleryPayloadCache.set(resolvedEndpoint, request);
		return request;
};


function initializeGalleryRotator()
{
        logger.debug('initializeGalleryRotator: start');
        const root = document.getElementById('galleryRoot');
        if (!root) return;
	
	const endpoint = root.getAttribute('data-gallery-endpoint') || '/galleryData.php';	
	const imgEl    = document.getElementById('galleryImage');	
	const prevBtn  = document.getElementById('galleryPrev');	
	const nextBtn  = document.getElementById('galleryNext');	
	
	if (!imgEl || !prevBtn || !nextBtn) return;	
	
	const imageIntervalMs = 5500;	// 5.5 seconds
	const idleResumeMs    = 54500; // resume time is 54.5 seconds + imageInterval of 5.5 Seconds
        const gallerySizes    = "(min-width: 1200px) 1100px, (min-width: 992px) 900px, (min-width: 768px) 700px, 90vw";
	
        // Reusable 1px transparent gif so we never render the broken-image icon.
        const fallbackImage = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

        // Off-screen image to prefetch the next gallery item.
        const prefetchImg = new Image();
        prefetchImg.decoding = 'async';
        prefetchImg.loading = 'lazy';
        let prefetchedTargetId = '';
        prefetchImg.addEventListener('error', () =>
        {
                if (prefetchedTargetId) failedImageIds.add(prefetchedTargetId);
        });

        // NEW: session storage key (allow override if you ever need it)
        const storageKey =
        root.getAttribute('data-gallery-storage-key') ||
        'lc_gallery_index';
	
	const readStoredIndex = () =>
	{
		try {
			const raw = sessionStorage.getItem(storageKey);
			const idx = Number.parseInt(raw ?? '', 10);
			return Number.isFinite(idx) ? idx : 0;
			} catch (_) {
			return 0;
		}
	};
	
	const persistIndex = (idx) =>
	{
		try {
			sessionStorage.setItem(storageKey, String(idx));
		} catch (_) {}
	};
	
	
        let images = [];

        // Track images that failed to load so we can skip them on rotation and avoid an infinite loop.
        const failedImageIds = new Set();

        let imageIndex = 0;

        let imageTimer  = null;
        let resumeTimer = null;

        const buildImageVariants = (item) =>
        {
                const widths = [400, 800, 1200];
                const id     = item.id || '';
                const url    = item.url || '';

                const buildUrlForWidth = (width) =>
                {
                        if (id) return `https://lh3.googleusercontent.com/d/${id}=w${width}`;
                        if (url.includes('=w')) return url.replace(/=w\d+/, `=w${width}`);
                        return url;
                };

                const srcset = widths
                .map((width) =>
                        {
                                const candidate = buildUrlForWidth(width);
                                return candidate ? `${candidate} ${width}w` : '';
                        })
                .filter(Boolean)
                .join(', ');

                const src = buildUrlForWidth(1200) || url || fallbackImage;

                return { src, srcset, key: id || url || src };
        };

        const prefetchNextImage = () =>
        {
                if (!images.length) return;

                const targetIndex = (imageIndex + 1) % images.length;
                const nextItem    = images[targetIndex];
                const variants    = buildImageVariants(nextItem);

                prefetchedTargetId = variants.key;
                prefetchImg.srcset = variants.srcset;
                prefetchImg.sizes  = gallerySizes;
                prefetchImg.src    = variants.src;
        };

        const setImage = (index) =>
        {
                if (!images.length) return;
		
		// Skip any URLs that failed previously.		
		let nextIndex = (index + images.length) % images.length;		
		let guard = 0;		
		while (guard < images.length && failedImageIds.has(images[nextIndex].id || images[nextIndex].url)) 
		{			
			nextIndex = (nextIndex + 1) % images.length;			
			guard += 1;			
		}		
		
                imageIndex = nextIndex;

                persistIndex(imageIndex); // NEW: save on every successful set

                const item = images[imageIndex];

                const prefetchedMatch = (prefetchedTargetId && prefetchedTargetId === (item.id || item.url))
                        ? {
                                src: prefetchImg.currentSrc || prefetchImg.src,
                                srcset: prefetchImg.srcset,
                                key: prefetchedTargetId,
                        }
                        : null;

                const variants = (prefetchedMatch && prefetchedMatch.src)
                        ? prefetchedMatch
                        : buildImageVariants(item);

                imgEl.dataset.imageId = item.id || item.url;
                imgEl.srcset = variants.srcset;
                imgEl.sizes  = gallerySizes;
                imgEl.src = variants.src;
                imgEl.alt = item.name || 'Gallery image';
                logger.debug('initializeGalleryRotator: set image', { index: imageIndex, id: imgEl.dataset.imageId });

                prefetchNextImage();
        };
	
	const nextImage = () => setImage(imageIndex + 1);	
	const prevImage = () => setImage(imageIndex - 1);	
	
        const startImageAuto = () =>
        {
                if (imageTimer || images.length <= 1) return;
                imageTimer = setInterval(nextImage, imageIntervalMs);
                logger.debug('initializeGalleryRotator: auto-rotation started');
        };
	
        const stopImageAuto = () =>
        {
                if (imageTimer)
                {
                        clearInterval(imageTimer);
                        imageTimer = null;
                        logger.debug('initializeGalleryRotator: auto-rotation stopped');
                }
        };
	
        const scheduleResume = () =>
        {
                if (resumeTimer) clearTimeout(resumeTimer);
                resumeTimer = setTimeout(() =>
                        {
                                startImageAuto();
                        }, idleResumeMs);
                logger.debug('initializeGalleryRotator: auto-rotation scheduled to resume');
        };
	
        const manualAdvance = (fn) =>
        {
                stopImageAuto();
                fn();
                scheduleResume();
                logger.debug('initializeGalleryRotator: manual advance invoked');
        };
	
	// Wire controls	
        prevBtn.addEventListener('click', () => manualAdvance(prevImage));
        nextBtn.addEventListener('click', () => manualAdvance(nextImage));
        logger.debug('initializeGalleryRotator: navigation listeners attached');
	
	// Show a lightweight loading placeholder so the user sees immediate feedback.	
	imgEl.src = fallbackImage;	
	imgEl.alt = 'Loading gallery…';	
	
	// Load data	
	fetchGalleryPayload(endpoint)	
	.then((data) =>			
		{				
			// Filter out empty or malformed items to avoid broken thumbnails.				
			images = (Array.isArray(data.images) ? data.images : []).filter((item) => item && item.url);				
			
                        if (images.length)
                        {
                                // NEW: initialize from stored index
                                setImage(readStoredIndex());
                                startImageAuto();
                                logger.debug('initializeGalleryRotator: images loaded', images.length);
                        }
			else 
			{					
				imgEl.alt = 'No gallery images available';					
			}				
		})			
                .catch(() =>
                        {
                                imgEl.alt = 'Failed to load gallery';
                                imgEl.src = fallbackImage;
                                logger.debug('initializeGalleryRotator: failed to load gallery payload');
                        });
			
			// Skip broken images automatically instead of showing missing icons.				
			imgEl.addEventListener('error', () =>					
				{						
					const failedId = imgEl.dataset.imageId || imgEl.src;						
					if (failedId) failedImageIds.add(failedId);						
					
                        if (failedImageIds.size >= images.length)
                        {
                                imgEl.src = fallbackImage;
                                imgEl.srcset = '';
                                imgEl.alt = 'Unable to load gallery images';
                                stopImageAuto();
                                return;
                        }

                                        manualAdvance(nextImage);
                                        logger.debug('initializeGalleryRotator: image failed, advancing', imgEl.dataset.imageId);
                                });
				
				// Clean up on page hide					
				window.addEventListener('pagehide', () =>						
					{							
						stopImageAuto();							
						if (resumeTimer) clearTimeout(resumeTimer);							
					});						
}

function initializeTestimonialRotator()
{
        logger.debug('initializeTestimonialRotator: start');
        const quoteEl = document.getElementById('testimonialRotator');
        if (!quoteEl) return;
	
	const galleryRoot = document.getElementById('galleryRoot');
	
	const endpoint =
	quoteEl.getAttribute('data-testimonial-endpoint') ||
	quoteEl.getAttribute('data-gallery-endpoint') ||
	(galleryRoot ? galleryRoot.getAttribute('data-gallery-endpoint') : '') ||
	'/galleryData.php';
	
	const quoteIntervalMs = 9000;
	
	// NEW: session storage key (allow override)
	const storageKey =
	quoteEl.getAttribute('data-testimonial-storage-key') ||
	'lc_testimonial_index';
	
	const readStoredIndex = () =>
	{
		try {
			const raw = sessionStorage.getItem(storageKey);
			const idx = Number.parseInt(raw ?? '', 10);
			return Number.isFinite(idx) ? idx : 0;
			} catch (_) {
			return 0;
		}
	};
	
	const persistIndex = (idx) =>
	{
		try {
			sessionStorage.setItem(storageKey, String(idx));
		} catch (_) {}
	};
	
	let testimonials = [];
	let quoteIndex = 0;
	let quoteTimer = null;
	
        const setQuote = (index) =>
        {
                if (!testimonials.length) return;

                quoteIndex = (index + testimonials.length) % testimonials.length;
                persistIndex(quoteIndex); // NEW: save on every successful set
                quoteEl.textContent = testimonials[quoteIndex];
                logger.debug('initializeTestimonialRotator: set quote', { index: quoteIndex });
        };
	
	const nextQuote = () => setQuote(quoteIndex + 1);
	
        const startQuoteAuto = () =>
        {
                if (quoteTimer || testimonials.length <= 1) return;
                quoteTimer = setInterval(nextQuote, quoteIntervalMs);
                logger.debug('initializeTestimonialRotator: auto-rotation started');
        };
	
        const stopQuoteAuto = () =>
        {
                if (quoteTimer)
                {
                        clearInterval(quoteTimer);
                        quoteTimer = null;
                        logger.debug('initializeTestimonialRotator: auto-rotation stopped');
                }
        };
	
	quoteEl.textContent = 'Loading testimonials…';
	
	fetchGalleryPayload(endpoint)
	.then((data) =>
		{
			testimonials = Array.isArray(data.testimonials) ? data.testimonials : [];
			
                        if (!testimonials.length)
                        {
                                quoteEl.textContent = '';
                                return;
                        }

                        // NEW: initialize from stored index
                        setQuote(readStoredIndex());
                        startQuoteAuto();
                        logger.debug('initializeTestimonialRotator: testimonials loaded', testimonials.length);
                })
                .catch(() =>
                        {
                                quoteEl.textContent = '';
                                logger.debug('initializeTestimonialRotator: failed to load testimonials');
                        });
			
			window.addEventListener('pagehide', () =>
				{
					stopQuoteAuto();
				});
}


function initializeDeveloperLogin()
{
        logger.debug('initializeDeveloperLogin: start');
        const trigger = document.querySelector('[data-developer-login]');
        const modalElement = document.getElementById('developerLoginModal');

        if (!trigger || !modalElement || !window.bootstrap) return;
	
	const loginForm = modalElement.querySelector('[data-developer-login-form]');	
	const statusBox = modalElement.querySelector('[data-login-status]');	
	const modal = new window.bootstrap.Modal(modalElement);	
	
	const resetStatus = () =>	
	{		
		if (!statusBox) return;		
		statusBox.textContent = '';		
		statusBox.classList.add('d-none');		
		statusBox.classList.remove('alert-success', 'alert-danger');		
	};	
	
	const showStatus = (message, isSuccess) =>	
	{		
		if (!statusBox) return;		
		statusBox.textContent = message;		
		statusBox.classList.remove('d-none');		
		statusBox.classList.toggle('alert-success', isSuccess);		
		statusBox.classList.toggle('alert-danger', !isSuccess);		
	};	
	
        trigger.addEventListener('click', (event) =>
        {
                        event.preventDefault();
                        resetStatus();
                        modal.show();
                        logger.debug('initializeDeveloperLogin: trigger clicked');
                });
		
        if (loginForm)		
        {			
			loginForm.addEventListener('submit', async (event) =>				
                {					
					event.preventDefault();					
					resetStatus();					
					
					const formData = new FormData(loginForm);					
					const payload = {						
						username: formData.get('username') || '',						
						password: formData.get('password') || '',						
					};					
					
					try {						
						const response = await fetch('/developerLogin.php', {							
							method: 'POST',							
							headers: { 'Content-Type': 'application/json' },							
							body: JSON.stringify(payload),							
						});						
						
						const result = await response.json();						
						
                                                if (response.ok && result.success)
                                                {
                                                        showStatus('Login successful. Redirecting…', true);
                                                        setTimeout(() => { window.location.href = '/developer.php'; }, 400);
                                                        logger.debug('initializeDeveloperLogin: success');
                                                }
                                                else
                                                {
                                                        showStatus(result.error || 'Login failed.', false);
                                                        logger.debug('initializeDeveloperLogin: failure');
                                                }
                        } catch (error) {
                                                console.error(error);
                                                showStatus('Unexpected error during login.', false);
                                                logger.debug('initializeDeveloperLogin: exception');
                                        }
                                });
                }
}

function initializeDeveloperTestData()
{
        logger.debug('initializeDeveloperTestData: start');
        const ctx = window.APP_CONTEXT || {};
        if (!ctx.developerMode || !ctx.developerSession) return;
	
	const sampleData = {		
		contactForm: {			
			contactFirstName: 'Testy',			
			contactLastName: 'McTester',			
			contactEmail: 'tester@example.com',			
			phone: '555-0100',			
			phoneType: 'Mobile',			
			currentOrganization: 'Test Org',			
			yearAttended: '2024',			
		},		
		applyForm: {			
			applicantFirstName: 'Alex',			
			applicantLastName: 'Developer',			
			applicantPreferredName: 'Alex',			
			applicantPronouns: 'they/them',			
			applicantEmail: 'alex.developer@example.com',			
			applicantPhone: '555-0101',			
			applicantPhoneType: 'Mobile',			
			addressOne: '123 Test Lane',			
			addressTwo: 'Suite 100',			
			city: 'Testville',			
			state: 'CA',			
			zipCode: '90001',			
			vegan: 'No',			
			vegetarian: 'Yes',			
			dietaryRestrictions: 'Peanuts',			
			accessibilityNeeds: 'None',			
			organiaztion: 'Luke Center QA',			
			currentTitle: 'Engineer',			
			sponsorName: 'Pat Manager',			
			sponsorEmail: 'pat.manager@example.com',			
			sponsorPhone: '555-0102',			
			questionOne: 'From colleagues.',			
			questionTwo: 'Lead teams across regions.',			
			questionThree: 'Partnered with public/private orgs.',			
			questionFour: 'Improving onboarding.',			
			questionFive: 'Expand leadership toolkit.',			
			scholarshipQuestion: 'No',			
			scholarshipAmount: '0',			
		},		
	};	
	
        const fillForm = (formElement, values) =>
        {
                Object.entries(values).forEach(([id, value]) =>
                        {
                                const field = formElement.querySelector('#' + id);
				if (!field) return;				
				
				if (field.tagName === 'SELECT') {					
					field.value = value;					
					} else if (field.tagName === 'TEXTAREA' || field.tagName === 'INPUT') {					
                                        field.value = value;
                                }
                        });
                logger.debug('initializeDeveloperTestData: form filled', formElement.id);
        };
	
	Object.entries(sampleData).forEach(([formId, values]) =>		
        {			
			const formElement = document.getElementById(formId);			
			if (!formElement) return;			
			
			const submitButtons = formElement.querySelectorAll('button[type="submit"], input[type="submit"]');			
			
			submitButtons.forEach((button) =>				
                {					
					if (button.dataset.testFillAttached === 'true') return;					
					
					const fillButton = document.createElement('button');					
                                        fillButton.type = 'button';
                                        fillButton.className = 'btn btn-outline-brand';
                                        fillButton.textContent = 'Fill with test data';
                                        fillButton.addEventListener('click', () => fillForm(formElement, values));
					
                                        if (button.parentNode) {
                                                button.parentNode.insertBefore(fillButton, button);
                                        }
                                        button.dataset.testFillAttached = 'true';
                                        logger.debug('initializeDeveloperTestData: fill button attached', formElement.id);
                                });
                });
}

// Run immediately because <script> is deferred
initializeNav();
initializeForm('contactForm', 'contactStatus');
initializeForm('applyForm', 'applyStatus');
initializeGalleryRotator();
initializeTestimonialRotator();
initializeDeveloperLogin();
initializeDeveloperTestData();