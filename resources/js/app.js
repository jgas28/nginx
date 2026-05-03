import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function getFieldLabel(field) {
    const explicitLabel = field.id
        ? document.querySelector(`label[for="${CSS.escape(field.id)}"]`)
        : null;

    const containerLabel = field.closest('label');
    const labelText = explicitLabel?.textContent || containerLabel?.textContent || field.getAttribute('aria-label') || field.name || 'This field';

    return labelText.replace(/\s+/g, ' ').trim().replace(/[:*]\s*$/, '');
}

function getFieldErrorKey(field) {
    if (!field.dataset.validationKey) {
        const baseKey = field.name || field.id || 'field';
        field.dataset.validationKey = `${baseKey}-${Math.random().toString(36).slice(2, 10)}`;
    }

    return field.dataset.validationKey;
}

function getFieldMessage(field) {
    if (field.validity.valueMissing) {
        return `${getFieldLabel(field)} is required.`;
    }

    if (field.validity.typeMismatch || field.validity.badInput) {
        return `Please enter a valid ${getFieldLabel(field).toLowerCase()}.`;
    }

    return field.validationMessage || `Please review ${getFieldLabel(field).toLowerCase()}.`;
}

function getErrorAnchor(field) {
    if (field.type === 'checkbox' || field.type === 'radio') {
        return field.closest('div, fieldset, label') || field;
    }

    return field;
}

function showFieldError(field) {
    field.classList.add('app-invalid-field', 'is-invalid');
    field.setAttribute('aria-invalid', 'true');

    const anchor = getErrorAnchor(field);
    const message = getFieldMessage(field);
    const errorKey = getFieldErrorKey(field);
    let errorElement = field.form?.querySelector(`[data-field-error-for="${errorKey}"]`);

    if (!errorElement) {
        errorElement = document.createElement('p');
        errorElement.className = 'app-field-error';
        errorElement.dataset.fieldErrorFor = errorKey;
        anchor.insertAdjacentElement('afterend', errorElement);
    }

    errorElement.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('app-invalid-field', 'is-invalid');
    field.removeAttribute('aria-invalid');

    const errorKey = field.dataset.validationKey;
    const errorElement = errorKey
        ? field.form?.querySelector(`[data-field-error-for="${errorKey}"]`)
        : null;

    if (errorElement) {
        errorElement.remove();
    }
}

function validateField(field) {
    if (!(field instanceof HTMLElement) || !(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
        return true;
    }

    if (field.disabled || field.type === 'hidden') {
        clearFieldError(field);
        return true;
    }

    const isValid = field.checkValidity();

    if (isValid) {
        clearFieldError(field);
        return true;
    }

    showFieldError(field);
    return false;
}

function attachFormValidation(form) {
    if (!(form instanceof HTMLFormElement) || form.dataset.validationBound === 'true') {
        return;
    }

    if (!form.querySelector('[required]')) {
        return;
    }

    form.dataset.validationBound = 'true';

    const getRequiredFields = () => Array.from(form.querySelectorAll('input, select, textarea'))
        .filter((field) => field.required);

    form.addEventListener('input', (event) => {
        const field = event.target;
        if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
            if (field.required) {
                validateField(field);
            }
        }
    });

    form.addEventListener('change', (event) => {
        const field = event.target;
        if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
            if (field.required) {
                validateField(field);
            }
        }
    });

    form.addEventListener('focusout', (event) => {
        const field = event.target;
        if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
            if (field.required) {
                validateField(field);
            }
        }
    });

    form.addEventListener('submit', (event) => {
        const invalidFields = getRequiredFields().filter((field) => !validateField(field));

        if (invalidFields.length === 0) {
            return;
        }

        event.preventDefault();

        const firstInvalidField = invalidFields[0];
        firstInvalidField.focus();
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

function initializeRequiredFieldValidation(root = document) {
    root.querySelectorAll('form').forEach((form) => attachFormValidation(form));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initializeRequiredFieldValidation(), { once: true });
} else {
    initializeRequiredFieldValidation();
}
