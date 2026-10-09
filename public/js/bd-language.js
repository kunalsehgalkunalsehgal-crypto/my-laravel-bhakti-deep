/* BhaktiDeep language reset: one loader, fresh page per language choice. */
(function () {
    'use strict';
    if (window.__bdLanguageResetLoaded) return;
    window.__bdLanguageResetLoaded = true;

    const languages = ['en', 'hi', 'ta', 'te', 'ml', 'bn', 'kn', 'th', 'ne'];
    const storageKey = 'bhaktideep_language';
    const preferenceCookie = 'bd_language';
    const valid = code => languages.includes(code);
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    let changing = false;
    let widgetStarted = false;
    let readinessTimer;
    let pollTimer;

    function readCookie(name) {
        const entry = document.cookie.split(';').map(v => v.trim())
            .find(v => v.startsWith(name + '='));
        return entry ? entry.slice(name.length + 1) : '';
    }

    function readPreference() {
        try {
            const saved = localStorage.getItem(storageKey);
            if (valid(saved)) return saved;
        } catch (_) { /* Cookie fallback for blocked localStorage. */ }
        const saved = readCookie(preferenceCookie);
        return valid(saved) ? saved : 'en';
    }

    function writeCookie(name, value) {
        // Host-only: do not create a new parent-domain translation cookie.
        document.cookie = name + '=' + value +
            '; Path=/; Max-Age=31536000; SameSite=Lax' + secure;
    }

    function savePreference(code) {
        writeCookie(preferenceCookie, code);
        try { localStorage.setItem(storageKey, code); } catch (_) { /* Cookie fallback. */ }
        return readPreference() === code;
    }

    async function clearTranslationCookies() {
        // Where supported, delete exactly the visible googtrans scopes.
        if (window.cookieStore && typeof window.cookieStore.getAll === 'function') {
            try {
                const entries = await window.cookieStore.getAll('googtrans');
                await Promise.all(entries.map(entry => window.cookieStore.delete({
                    name: 'googtrans',
                    domain: entry.domain || null,
                    path: entry.path || '/',
                    partitioned: Boolean(entry.partitioned)
                })));
            } catch (_) { /* The document.cookie fallback below also runs. */ }
        }

        const host = location.hostname;
        const domains = new Set(['']);
        if (host.includes('.') && !/^\d+(\.\d+){3}$/.test(host) && !host.includes(':')) {
            domains.add(host);
        }
        // This project's known parent domain; never guess eTLDs such as co.uk.
        if (host === 'itsoftexpert.net' || host.endsWith('.itsoftexpert.net')) {
            domains.add('itsoftexpert.net');
        }

        const paths = new Set(['/']);
        let path = '';
        location.pathname.split('/').filter(Boolean).forEach(part => {
            path += '/' + part;
            paths.add(path);
            paths.add(path + '/');
        });

        domains.forEach(domain => paths.forEach(cookiePath => {
            document.cookie = 'googtrans=; Max-Age=0; Path=' + cookiePath +
                (domain ? '; Domain=' + domain : '') + '; SameSite=Lax' + secure;
        }));
    }

    function updateUI(code) {
        document.querySelectorAll('[data-bd-language-label]').forEach(label => {
            label.textContent = code.toUpperCase();
        });
        document.querySelectorAll('.bd-language-option').forEach(button => {
            const active = button.dataset.bdLang === code;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
            if (active) button.setAttribute('aria-current', 'true');
            else button.removeAttribute('aria-current');
        });
    }

    function status(message) {
        let node = document.querySelector('[data-bd-language-status]');
        if (!node) {
            const menu = document.querySelector('.bd-language-menu');
            if (!menu) return;
            const item = document.createElement('li');
            item.className = 'bd-language-footer notranslate';
            node = document.createElement('p');
            node.dataset.bdLanguageStatus = '';
            node.setAttribute('role', 'status');
            node.setAttribute('aria-live', 'polite');
            item.appendChild(node);
            menu.appendChild(item);
        }
        node.textContent = message;
    }

    function fail(message) {
        clearTimeout(readinessTimer);
        clearTimeout(pollTimer);
        // Do not label a failed translation with the requested language.
        status(message);
        console.warn('[BhaktiDeep language]', message);
    }

    function applyRequestedLanguage(code) {
        const select = document.querySelector('#bd-google-translate .goog-te-combo');
        const supported = select && [...select.options].some(option => option.value === code);
        if (!supported) {
            pollTimer = setTimeout(() => applyRequestedLanguage(code), 100);
            return;
        }
        clearTimeout(readinessTimer);
        select.value = code;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        updateUI(code);
        // A request is not a verified translation result.
        status('Automatic translation requested.');
    }

    function startWidget() {
        if (widgetStarted || readPreference() === 'en') return;
        const TranslateElement = window.google?.translate?.TranslateElement;
        if (typeof TranslateElement !== 'function') return;
        widgetStarted = true;
        try {
            new TranslateElement({
                pageLanguage: 'en',
                includedLanguages: languages.join(','),
                autoDisplay: false
            }, 'bd-google-translate');
            applyRequestedLanguage(readPreference());
        } catch (_) {
            fail('Translation could not start. Select a language to retry.');
        }
    }

    // This file owns Google loading. Do not also include element.js in the layout.
    window.googleTranslateElementInit = startWidget;

    async function boot() {
        const code = readPreference();
        updateUI('en');
        await clearTranslationCookies();
        if (document.cookie.split(';').some(v => v.trim().startsWith('googtrans='))) {
            fail('An old translation cookie could not be cleared. Check its Domain and Path.');
            return;
        }

        // Critical fix: EN uses the fresh original page and DOES NOT start Google.
        if (code === 'en') {
            status('Original English.');
            return;
        }

        writeCookie('googtrans', '/en/' + code);
        if (readCookie('googtrans') !== '/en/' + code) {
            fail('Translation cookies are blocked in this browser.');
            return;
        }

        let container = document.getElementById('bd-google-translate');
        if (!container) {
            container = document.createElement('div');
            container.id = 'bd-google-translate';
            container.className = 'bd-google-translate-engine';
            document.body.appendChild(container);
        }

        status('Loading translation...');
        readinessTimer = setTimeout(() => {
            fail('Translation unavailable. Select a language to retry.');
        }, 15000);

        if (typeof window.google?.translate?.TranslateElement === 'function') {
            startWidget();
        } else {
            const script = document.createElement('script');
            script.id = 'bd-google-translate-script';
            script.async = true;
            script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
            script.onerror = () => fail('Google Translate did not load. Check the connection.');
            document.body.appendChild(script);
        }
    }

    document.addEventListener('click', event => {
        const button = event.target instanceof Element
            ? event.target.closest('.bd-language-option') : null;
        if (!button || !valid(button.dataset.bdLang)) return;
        event.preventDefault();
        if (changing) return;
        const code = button.dataset.bdLang;
        if (!savePreference(code)) {
            fail('The language choice could not be saved. Check browser storage settings.');
            return;
        }
        changing = true;
        // Remove only a legacy Google language hash, not normal page anchors.
        if (/^#googtrans(?:\(|[=/])/.test(location.hash)) {
            history.replaceState(null, '', location.pathname + location.search);
        }
        // One user-triggered reload. Old translated DOM is never reused.
        location.reload();
    });

    const pagePreference = readPreference();
    window.addEventListener('pageshow', event => {
        if (event.persisted && readPreference() !== pagePreference) location.reload();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => void boot(), { once: true });
    } else {
        void boot();
    }
})();
