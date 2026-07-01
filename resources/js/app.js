import './bootstrap';

import 'bootstrap/dist/js/bootstrap.bundle.min.js';

document.addEventListener('click', async (event) => {
    const button = event.target.closest('.js-copy-text');

    if (!button) {
        return;
    }

    const targetSelector = button.getAttribute('data-copy-target');
    const target = document.querySelector(targetSelector);

    if (!target) {
        return;
    }

    let text = '';

    if (target.tagName === 'TEXTAREA' || target.tagName === 'INPUT') {
        text = target.value;
    } else {
        text = target.textContent || target.innerText || '';
    }

    text = text.replace(/\r\n/g, '\n').trim();

    if (!text) {
        return;
    }

    const originalText = button.innerHTML;

    try {
        await navigator.clipboard.writeText(text);
        showCopySuccess(button, originalText);
    } catch (error) {
        fallbackCopyText(text, button, originalText);
    }
});

function showCopySuccess(button, originalText) {
    button.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied';
    button.classList.remove('btn-outline-secondary');
    button.classList.remove('btn-primary');
    button.classList.add('btn-success');

    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');

        if (button.dataset.copyTarget && button.dataset.copyTarget.includes('full')) {
            button.classList.add('btn-primary');
        } else {
            button.classList.add('btn-outline-secondary');
        }
    }, 1500);
}

function fallbackCopyText(text, button, originalText) {
    const tempTextarea = document.createElement('textarea');

    tempTextarea.value = text;
    tempTextarea.setAttribute('readonly', '');
    tempTextarea.style.position = 'fixed';
    tempTextarea.style.left = '-9999px';
    tempTextarea.style.top = '-9999px';

    document.body.appendChild(tempTextarea);

    tempTextarea.focus();
    tempTextarea.select();

    try {
        document.execCommand('copy');
        showCopySuccess(button, originalText);
    } catch (fallbackError) {
        button.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Failed';

        setTimeout(() => {
            button.innerHTML = originalText;
        }, 1500);
    }

    document.body.removeChild(tempTextarea);
}