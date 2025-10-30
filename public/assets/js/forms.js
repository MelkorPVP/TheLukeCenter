class FormHandler {
  constructor(form, endpoint) {
    this.form = form;
    this.endpoint = endpoint;
    this.status = form.querySelector('[data-status]');
    this.errorSummary = form.querySelector('[data-error-summary]');
    this.honeypot = form.querySelector('[data-honeypot]');
    form.addEventListener('submit', (event) => this.onSubmit(event));
  }

  async onSubmit(event) {
    event.preventDefault();
    event.stopPropagation();

    if (this.honeypot && this.honeypot.value.trim() !== '') {
      return;
    }

    if (!this.form.checkValidity()) {
      this.form.classList.add('was-validated');
      this.showErrors();
      return;
    }

    this.setStatus('Sending…', 'info');
    this.clearErrors();

    const payload = Object.fromEntries(new FormData(this.form).entries());

    try {
      const response = await fetch(this.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();

      if (!response.ok || !data.ok) {
        throw new Error(data.error || 'Submission failed.');
      }

      this.setStatus('Sent. Thank you.', 'success');
      this.form.reset();
      this.form.classList.remove('was-validated');
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Submission failed.';
      this.setStatus(message, 'danger');
    }
  }

  showErrors() {
    if (!this.errorSummary) {
      return;
    }

    const invalid = Array.from(this.form.querySelectorAll(':invalid'));
    if (invalid.length === 0) {
      this.clearErrors();
      return;
    }

    const items = invalid.map((field) => {
      const label = this.form.querySelector(`label[for="${field.id}"]`);
      const name = label ? label.textContent?.trim() : field.name;
      return `<li>${name || field.name}: ${field.validationMessage}</li>`;
    });

    this.errorSummary.classList.remove('d-none');
    this.errorSummary.innerHTML = `<p><strong>Please correct the following:</strong></p><ul class="mb-0">${items.join('')}</ul>`;
  }

  clearErrors() {
    if (this.errorSummary) {
      this.errorSummary.classList.add('d-none');
      this.errorSummary.innerHTML = '';
    }
  }

  setStatus(message, type) {
    if (!this.status) {
      return;
    }

    this.status.textContent = message;
    this.status.className = `alert mt-3 alert-${type}`;
    this.status.classList.remove('d-none');
  }
}

function initForms() {
  const applyForm = document.querySelector('#applyForm');
  if (applyForm) {
    new FormHandler(applyForm, '/api/application.php');
  }

  if (window.APP_CONTEXT?.applicationOpen === false) {
    document
      .querySelectorAll('[data-application-button]')
      .forEach((button) => button.setAttribute('disabled', 'disabled'));
  }

  const contactForm = document.querySelector('#contactForm');
  if (contactForm) {
    new FormHandler(contactForm, '/api/contact.php');
  }
}

document.addEventListener('DOMContentLoaded', initForms);
