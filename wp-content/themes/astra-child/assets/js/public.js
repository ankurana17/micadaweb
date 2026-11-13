/* Native equivalents of the public Angular header and history actions. */
(() => {
    'use strict';
    const select = document.querySelector('[data-micada-language]');
    const switchLanguage = (code) => {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', code);
        window.location.assign(url.href);
    };
    if (select) {
        try {
            const saved = JSON.parse(localStorage.getItem('lang'));
            if (!new URL(window.location.href).searchParams.has('lang') && ['en', 'hd'].includes(saved?.code) && saved.code !== select.value) {
                switchLanguage(saved.code);
            }
        } catch (_) { /* Storage may be unavailable; the selector still works. */ }
        select.addEventListener('change', () => {
            const code = select.value;
            try {
                localStorage.setItem('lang', JSON.stringify({ name: code === 'hd' ? 'Hindi' : 'English', code }));
            } catch (_) { /* Continue without persistent preference. */ }
            switchLanguage(code);
        });
    }
    document.querySelectorAll('[data-micada-back]').forEach((button) => {
        button.addEventListener('click', () => window.history.back());
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
})();
