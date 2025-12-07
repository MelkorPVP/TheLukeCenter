function createClientLogger(options = {})
{
    const enabled = Boolean(options.enabled);
    const environment = (options.environment || '').toUpperCase();
    const requestId = options.requestId || 'browser';

    const prefix = [
        environment ? `[${environment}]` : '',
        requestId ? `[RID:${requestId}]` : '',
    ].filter(Boolean).join(' ');

    const emit = (consoleMethod, level, message, meta = {}) =>
    {
        if (!enabled) return;

        const payload = Object.keys(meta).length ? meta : undefined;
        const args = [prefix, `[${level}]`, message];

        if (payload) {
            args.push(payload);
        }

        (console[consoleMethod] || console.log).apply(console, args);
    };

    return {
        info: (message, meta) => emit('info', 'INFO', message, meta),
        warn: (message, meta) => emit('warn', 'WARN', message, meta),
        error: (message, meta) => emit('error', 'ERROR', message, meta),
    };
}

const appLogger = createClientLogger({
    enabled: document.body?.dataset.loggingEnabled === 'true' || Boolean((window.APP_CONTEXT || {}).loggingEnabled),
    environment: document.body?.dataset.appEnvironment || (window.APP_CONTEXT || {}).environment,
    requestId: document.body?.dataset.requestId || (window.APP_CONTEXT || {}).requestId,
});

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
                        appLogger.info('Activated navigation link', { href: linkHref });
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

            appLogger.warn('Form validation failed', {
                formId,
                field: firstInvalid.getAttribute('id') || firstInvalid.getAttribute('name') || 'unknown',
            });

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

function initializeDeveloperLogin()
{
    const trigger = document.querySelector('[data-developer-login]');
    const modalElement = document.getElementById('developerLoginModal');

    if (!trigger || !modalElement || !window.bootstrap) return;

    const loginForm = modalElement.querySelector('[data-developer-login-form]');
    const statusBox = modalElement.querySelector('[data-login-status]');
    const modal = new window.bootstrap.Modal(modalElement);

    const resetStatus = () => {
        if (!statusBox) return;
        statusBox.textContent = '';
        statusBox.classList.add('d-none');
        statusBox.classList.remove('alert-success', 'alert-danger');
    };

    const showStatus = (message, isSuccess) => {
        if (!statusBox) return;
        statusBox.textContent = message;
        statusBox.classList.remove('d-none');
        statusBox.classList.toggle('alert-success', isSuccess);
        statusBox.classList.toggle('alert-danger', !isSuccess);
    };

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        resetStatus();
        modal.show();
    });

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            resetStatus();

            const formData = new FormData(loginForm);
            const payload = {
                username: formData.get('username') || '',
                password: formData.get('password') || '',
            };

            try {
                const response = await fetch('/developer-login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showStatus('Login successful. Redirecting…', true);
                    appLogger.info('Developer login successful');
                    setTimeout(() => { window.location.href = '/developer.php'; }, 400);
                } else {
                    showStatus(result.error || 'Login failed.', false);
                    appLogger.warn('Developer login failed', { message: result.error || 'Login failed' });
                }
            } catch (error) {
                appLogger.error('Unexpected developer login error', { error: String(error) });
                showStatus('Unexpected error during login.', false);
            }
        });
    }
}

function initializeDeveloperTestData()
{
    const ctx = window.APP_CONTEXT || {};
    if (!ctx.developerMode || !ctx.developerSession) return;

    const sampleData = {
        contactForm: {
            contactFirstName: 'Testy',
            contactLastName: 'McTester',
            contactEmail: 'tester@example.com',
            contactPhone: '555-0100',
            contactPhoneType: 'Mobile',
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
            applicantOrganiaztion: 'Luke Center QA',
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

    const fillForm = (formElement, values) => {
        Object.entries(values).forEach(([id, value]) => {
            const field = formElement.querySelector('#' + id);
            if (!field) return;

            if (field.tagName === 'SELECT') {
                field.value = value;
            } else if (field.tagName === 'TEXTAREA' || field.tagName === 'INPUT') {
                field.value = value;
            }
        });
    };

    Object.entries(sampleData).forEach(([formId, values]) => {
        const formElement = document.getElementById(formId);
        if (!formElement) return;

        const submitButtons = formElement.querySelectorAll('button[type="submit"], input[type="submit"]');

        submitButtons.forEach((button) => {
            if (button.dataset.testFillAttached === 'true') return;

            const fillButton = document.createElement('button');
            fillButton.type = 'button';
            fillButton.className = 'btn btn-outline-secondary';
            fillButton.textContent = 'Fill with test data';
            fillButton.addEventListener('click', () => {
                fillForm(formElement, values);
                appLogger.info('Developer test data populated', { formId });
            });

            if (button.parentNode) {
                button.parentNode.insertBefore(fillButton, button);
            }
            button.dataset.testFillAttached = 'true';
        });
    });
}




// Run immediately because <script> is deferred
initializeNav();
initializeForm('contactForm', 'contactStatus');
initializeForm('applyForm', 'applyStatus');
initializeDeveloperLogin();
initializeDeveloperTestData();
appLogger.info('Frontend bootstrap complete', { path: location.pathname });