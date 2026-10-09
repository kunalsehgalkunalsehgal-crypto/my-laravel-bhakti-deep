{{-- @once
@php
    $bdLanguages = [
        'en' => ['English', 'English'],
        'hi' => ['हिन्दी', 'Hindi'],
        'ta' => ['தமிழ்', 'Tamil'],
        'te' => ['తెలుగు', 'Telugu'],
        'ml' => ['മലയാളം', 'Malayalam'],
        'bn' => ['বাংলা', 'Bengali'],
        'kn' => ['ಕನ್ನಡ', 'Kannada'],
        'th' => ['ไทย', 'Thai'],
        'ne' => ['नेपाली', 'Nepali'],
    ];
@endphp

<div class="dropdown bd-language notranslate" translate="no" data-bd-language>
    <button class="btn btn-outline-saffron dropdown-toggle bd-language-toggle"
            id="bdLanguageToggle" type="button" data-bs-toggle="dropdown"
            data-bs-auto-close="outside" aria-expanded="false"
            aria-label="Choose website language">
        <i class="bi bi-translate" aria-hidden="true"></i>
        <span data-bd-language-label>English</span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end bd-language-menu"
        aria-labelledby="bdLanguageToggle">
        <li class="bd-language-heading">Choose your language</li>
        @foreach($bdLanguages as $code => [$native, $english])
            <li>
                <button type="button" class="dropdown-item bd-language-option"
                        data-bd-lang="{{ $code }}" data-bd-label="{{ $native }}"
                        aria-pressed="{{ $code === 'en' ? 'true' : 'false' }}">
                    <span>
                        <span lang="{{ $code }}">{{ $native }}</span>
                        <small>{{ $english }}</small>
                    </span>
                    <i class="bi bi-check2" aria-hidden="true"></i>
                </button>
            </li>
        @endforeach
        <li class="bd-language-footer">
            <p data-bd-language-status role="status" aria-live="polite">
                Automatic text translation.
            </p>
            <div id="bd-google-translate"></div>
        </li>
    </ul>
</div>

@endonce --}}

<div class="dropdown bd-language notranslate" translate="no">
    <button
        class="btn btn-outline-saffron dropdown-toggle bd-language-button"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="Change language"
    >
        <i class="bi bi-translate"></i>
        <span data-bd-language-label>EN</span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end bd-language-menu">
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="en"><span class="bd-language-name">🇬🇧 English</span><span class="bd-language-code">EN</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="hi"><span class="bd-language-name">🇮🇳 हिन्दी</span><span class="bd-language-code">HI</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="ta"><span class="bd-language-name">🇮🇳 தமிழ்</span><span class="bd-language-code">TA</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="te"><span class="bd-language-name">🇮🇳 తెలుగు</span><span class="bd-language-code">TE</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="ml"><span class="bd-language-name">🇮🇳 മലയാളം</span><span class="bd-language-code">ML</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="bn"><span class="bd-language-name">🇮🇳 বাংলা</span><span class="bd-language-code">BN</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="kn"><span class="bd-language-name">🇮🇳 ಕನ್ನಡ</span><span class="bd-language-code">KN</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="th"><span class="bd-language-name">🇹🇭 ไทย</span><span class="bd-language-code">TH</span></button></li>
        <li><button type="button" class="dropdown-item bd-language-option" data-bd-lang="ne"><span class="bd-language-name">🇳🇵 नेपाली</span><span class="bd-language-code">NE</span></button></li>
    </ul>

    <div id="bd-google-translate" class="bd-google-translate" aria-hidden="true"></div>
</div>
