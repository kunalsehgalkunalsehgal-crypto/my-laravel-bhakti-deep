// (function () {
//     'use strict';

//     const languages = {
//         en: 'English',
//         hi: 'हिन्दी',
//         ta: 'தமிழ்',
//         te: 'తెలుగు',
//         ml: 'മലയാളം',
//         bn: 'বাংলা',
//         kn: 'ಕನ್ನಡ',
//         th: 'ไทย',
//         ne: 'नेपाली'
//     };

//     const storageKey = 'bhaktideep_language';

//     /*
//     |--------------------------------------------------------------------------
//     | GOOGLE TRANSLATE INIT
//     |--------------------------------------------------------------------------
//     */

//     window.googleTranslateElementInit = function () {

//         if (
//             !window.google ||
//             !google.translate ||
//             !google.translate.TranslateElement
//         ) {
//             return;
//         }

//         new google.translate.TranslateElement({
//             pageLanguage: 'en',
//             includedLanguages: 'en,hi,ta,te,ml,bn,kn,th,ne',
//             autoDisplay: false
//         }, 'bd-google-translate');
//     };


//     /*
//     |--------------------------------------------------------------------------
//     | REMOVE OLD COOKIE
//     |--------------------------------------------------------------------------
//     */

//     function clearGoogleCookie() {

//         document.cookie =
//             'googtrans=; Max-Age=0; path=/;';

//         document.cookie =
//             'googtrans=; Max-Age=0; path=/; domain=' +
//             location.hostname + ';';
//     }


//     /*
//     |--------------------------------------------------------------------------
//     | CHANGE LANGUAGE
//     |--------------------------------------------------------------------------
//     */

//     function changeLanguage(code) {

//         if (!languages[code]) {
//             return;
//         }

//         localStorage.setItem(storageKey, code);

//         clearGoogleCookie();

//         if (code !== 'en') {

//             document.cookie =
//                 'googtrans=/en/' + code +
//                 '; path=/; Max-Age=31536000; SameSite=Lax';
//         }

//         window.location.reload();
//     }


//     /*
//     |--------------------------------------------------------------------------
//     | BUTTON CLICK
//     |--------------------------------------------------------------------------
//     */

//     document.addEventListener('click', function (event) {

//         const button =
//             event.target.closest('.bd-language-option');

//         if (!button) {
//             return;
//         }

//         event.preventDefault();
//         event.stopPropagation();

//         const code = button.dataset.bdLang;

//         changeLanguage(code);
//     });


//     /*
//     |--------------------------------------------------------------------------
//     | SHOW CURRENT LANGUAGE
//     |--------------------------------------------------------------------------
//     */

//     function showCurrentLanguage() {

//         const saved =
//             localStorage.getItem(storageKey) || 'en';

//         const label =
//             document.querySelector(
//                 '[data-bd-language-label]'
//             );

//         if (label && languages[saved]) {
//             label.textContent = languages[saved];
//         }

//         document
//             .querySelectorAll('.bd-language-option')
//             .forEach(function (button) {

//                 button.setAttribute(
//                     'aria-pressed',
//                     button.dataset.bdLang === saved
//                         ? 'true'
//                         : 'false'
//                 );
//             });
//     }


//     if (document.readyState === 'loading') {

//         document.addEventListener(
//             'DOMContentLoaded',
//             showCurrentLanguage
//         );

//     } else {

//         showCurrentLanguage();
//     }

// })();
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

    window.googleTranslateElementInit = function () {
        if (!window.google || !window.google.translate || !window.google.translate.TranslateElement) {
            return;
        }

        new window.google.translate.TranslateElement(
            {
                pageLanguage: 'en',
                includedLanguages: 'en,hi,ta,te,ml,bn,kn,th,ne',
                autoDisplay: false
            },
            'bd-google-translate'
        );
    };

    function clearTranslateCookies() {
        const hostname = window.location.hostname;

        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;';

        if (hostname && hostname !== 'localhost' && hostname !== '127.0.0.1') {
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=' + hostname + ';';
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=.' + hostname + ';';
        }
    }

    function setTranslateCookie(languageCode) {
        document.cookie = 'googtrans=/en/' + languageCode + '; path=/; Max-Age=31536000; SameSite=Lax';
    }

    function changeLanguage(languageCode) {
        if (!languages[languageCode]) {
            return;
        }

        localStorage.setItem(storageKey, languageCode);
        clearTranslateCookies();

        if (languageCode !== 'en') {
            setTranslateCookie(languageCode);
        }

        window.location.reload();
    }

    function getCurrentLanguage() {
        const savedLanguage = localStorage.getItem(storageKey);
        return savedLanguage && languages[savedLanguage] ? savedLanguage : 'en';
    }

    function updateLanguageButton() {
        const currentLanguage = getCurrentLanguage();
        const language = languages[currentLanguage];
        const label = document.querySelector('[data-bd-language-label]');

        if (label && language) {
            label.textContent = language.short;
        }

        document.querySelectorAll('.bd-language-option').forEach(function (button) {
            const isActive = button.dataset.bdLang === currentLanguage;
            button.classList.toggle('active', isActive);

            if (isActive) {
                button.setAttribute('aria-current', 'true');
            } else {
                button.removeAttribute('aria-current');
            }
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.bd-language-option');

        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        changeLanguage(button.dataset.bdLang);
    });

    function resetGooglePagePosition() {
        if (document.body) {
            document.body.style.top = '0px';
        }
        document.documentElement.style.marginTop = '0px';
    }

    function initLanguageSwitcher() {
        updateLanguageButton();
        resetGooglePagePosition();
        setTimeout(resetGooglePagePosition, 400);
        setTimeout(resetGooglePagePosition, 1000);
        setTimeout(resetGooglePagePosition, 2000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLanguageSwitcher);
    } else {
        initLanguageSwitcher();
    }
})();
