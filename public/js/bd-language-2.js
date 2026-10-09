(function () {
    'use strict';

    const languages = {
        en: { short: 'EN', name: 'English' },
        hi: { short: 'HI', name: 'हिन्दी' },
        ta: { short: 'TA', name: 'தமிழ்' },
        te: { short: 'TE', name: 'తెలుగు' },
        ml: { short: 'ML', name: 'മലയാളം' },
        bn: { short: 'BN', name: 'বাংলা' },
        kn: { short: 'KN', name: 'ಕನ್ನಡ' },
        th: { short: 'TH', name: 'ไทย' },
        ne: { short: 'NE', name: 'नेपाली' }
    };

    const storageKey = 'bhaktideep_language';


    /*
    |--------------------------------------------------------------------------
    | GET SAVED LANGUAGE
    |--------------------------------------------------------------------------
    */

    function getCurrentLanguage() {

        const saved =
            localStorage.getItem(storageKey);

        if (saved && languages[saved]) {
            return saved;
        }

        return 'en';
    }


    /*
    |--------------------------------------------------------------------------
    | COOKIE DOMAINS
    |--------------------------------------------------------------------------
    |
    | Live:
    | bhaktideep.itsoftexpert.net
    |
    | Google can leave googtrans on:
    | bhaktideep.itsoftexpert.net
    | .bhaktideep.itsoftexpert.net
    | itsoftexpert.net
    | .itsoftexpert.net
    |
    */

    function getCookieDomains() {

        const hostname =
            window.location.hostname;

        const domains = [''];

        if (
            hostname &&
            hostname !== 'localhost' &&
            hostname !== '127.0.0.1'
        ) {

            domains.push(hostname);
            domains.push('.' + hostname);


            const parts =
                hostname.split('.');

            if (parts.length >= 2) {

                const parentDomain =
                    parts.slice(-2).join('.');

                if (parentDomain !== hostname) {

                    domains.push(parentDomain);
                    domains.push('.' + parentDomain);
                }
            }
        }

        return [...new Set(domains)];
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR ALL GOOGLE TRANSLATE COOKIES
    |--------------------------------------------------------------------------
    */

    function clearTranslateCookies() {

        const domains =
            getCookieDomains();

        domains.forEach(function (domain) {

            const domainPart =
                domain
                    ? '; domain=' + domain
                    : '';

            document.cookie =
                'googtrans=;' +
                ' Max-Age=0;' +
                ' path=/' +
                domainPart +
                '; SameSite=Lax';

            document.cookie =
                'googtrans=;' +
                ' expires=Thu, 01 Jan 1970 00:00:00 GMT;' +
                ' path=/' +
                domainPart +
                '; SameSite=Lax';
        });
    }


    /*
    |--------------------------------------------------------------------------
    | SET GOOGLE TRANSLATE COOKIE
    |--------------------------------------------------------------------------
    */

    function setTranslateCookie(languageCode) {

        if (languageCode === 'en') {
            return;
        }

        const secure =
            window.location.protocol === 'https:'
                ? '; Secure'
                : '';

        document.cookie =
            'googtrans=/en/' +
            languageCode +
            '; path=/' +
            '; Max-Age=31536000' +
            '; SameSite=Lax' +
            secure;
    }


    /*
    |--------------------------------------------------------------------------
    | SYNC GOOGLE COOKIE WITH SAVED LANGUAGE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | This runs BEFORE Google Translate initializes.
    |
    | So if localStorage = th but old cookie = hi,
    | Hindi cookie is removed and Thai cookie is created.
    |
    */

    function syncLanguageCookie() {

        const languageCode =
            getCurrentLanguage();

        clearTranslateCookies();

        if (languageCode !== 'en') {

            setTranslateCookie(
                languageCode
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RUN COOKIE SYNC IMMEDIATELY
    |--------------------------------------------------------------------------
    */

    syncLanguageCookie();


    /*
    |--------------------------------------------------------------------------
    | GOOGLE TRANSLATE INITIALIZATION
    |--------------------------------------------------------------------------
    */

    window.googleTranslateElementInit =
        function () {

            if (
                !window.google ||
                !window.google.translate ||
                !window.google.translate.TranslateElement
            ) {
                return;
            }

            new window.google.translate.TranslateElement(
                {
                    pageLanguage: 'en',

                    includedLanguages:
                        'en,hi,ta,te,ml,bn,kn,th,ne',

                    autoDisplay: false
                },

                'bd-google-translate'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | CHANGE LANGUAGE
    |--------------------------------------------------------------------------
    */

    function changeLanguage(languageCode) {

        if (!languages[languageCode]) {
            return;
        }


        /*
         * Save selected language
         */

        localStorage.setItem(
            storageKey,
            languageCode
        );


        /*
         * Completely remove previous Google language
         */

        clearTranslateCookies();


        /*
         * Set new language
         */

        if (languageCode !== 'en') {

            setTranslateCookie(
                languageCode
            );
        }


        /*
         * Reload original Laravel page
         * Google will translate using the new cookie
         */

        window.location.reload();
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE LANGUAGE BUTTON
    |--------------------------------------------------------------------------
    */

    function updateLanguageButton() {

        const currentLanguage =
            getCurrentLanguage();

        const language =
            languages[currentLanguage];

        const label =
            document.querySelector(
                '[data-bd-language-label]'
            );


        if (label && language) {

            label.textContent =
                language.short;
        }


        document
            .querySelectorAll(
                '.bd-language-option'
            )
            .forEach(function (button) {

                const active =
                    button.dataset.bdLang ===
                    currentLanguage;

                button.classList.toggle(
                    'active',
                    active
                );

                button.setAttribute(
                    'aria-pressed',
                    active
                        ? 'true'
                        : 'false'
                );
            });
    }


    /*
    |--------------------------------------------------------------------------
    | LANGUAGE CLICK
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.bd-language-option'
                );

            if (!button) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const languageCode =
                button.dataset.bdLang;

            changeLanguage(
                languageCode
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | REMOVE GOOGLE PAGE SHIFT
    |--------------------------------------------------------------------------
    */

    function resetGooglePagePosition() {

        if (document.body) {
            document.body.style.top =
                '0px';
        }

        document.documentElement.style.marginTop =
            '0px';
    }


    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */

    function init() {

        updateLanguageButton();

        resetGooglePagePosition();

        setTimeout(
            resetGooglePagePosition,
            500
        );

        setTimeout(
            resetGooglePagePosition,
            1500
        );
    }


    if (
        document.readyState === 'loading'
    ) {

        document.addEventListener(
            'DOMContentLoaded',
            init
        );

    } else {

        init();
    }

})();