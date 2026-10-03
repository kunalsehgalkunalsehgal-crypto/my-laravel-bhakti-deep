@extends('layouts.app')

@section('title', 'Live Session - BhaktiDeep')
@section('description', 'Immersive live pooja and hawan session with mantras, aarti, family join and donations in one screen.')

@push('styles')
<style>

/*
|--------------------------------------------------------------------------
| ZOOM TOP + CARDS BELOW
|--------------------------------------------------------------------------
*/

.live-page {
    overflow-x: hidden;
}


.live-page .container {
    min-width: 0;
}


/* =========================================================
   USER LIVE SESSION CARDS
   Match the Pandit live-card layout/design.
   Scoped only to this live page.
   ========================================================= */

.live-page .live-bottom-grid {

    display: grid !important;

    grid-template-columns:
        repeat(3, minmax(0, 1fr)) !important;

    gap: 20px !important;

    align-items: start !important;

    width: 100%;

    min-width: 0;
}


.live-page .live-bottom-grid > * {

    min-width: 0 !important;

    margin-top: 0 !important;
    margin-bottom: 0 !important;

    box-sizing: border-box;
}


/* Same visual language as the Pandit cards.
   Scoped so other .side-panel cards remain untouched. */

.live-page .live-bottom-grid > .side-panel {

    width: 100% !important;

    border: 1px solid rgba(199, 141, 34, .22) !important;

    border-radius: 20px !important;

    background: rgba(255, 255, 255, .68) !important;

    box-shadow:
        0 20px 60px -54px rgba(63, 36, 23, .7) !important;

    backdrop-filter: blur(14px);

    -webkit-backdrop-filter: blur(14px);

    padding: 24px !important;

    overflow: hidden;

    box-sizing: border-box;
}


.live-page .live-bottom-grid > .side-panel h3 {

    margin-top: 0;

    margin-bottom: 18px;
}


/* Tablet */

@media (max-width: 1199.98px) {

    .live-page .live-bottom-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr)) !important;

        gap: 20px !important;
    }

}


/* Mobile */

@media (max-width: 767.98px) {

    .live-page .live-bottom-grid {

        grid-template-columns: 1fr !important;

        gap: 14px !important;
    }


    .live-page .live-bottom-grid > .side-panel {

        padding: 18px !important;

        border-radius: 18px !important;
    }


    .live-page
    .container {

        max-width: 100%;

        padding-left:
            12px;

        padding-right:
            12px;

    }

}

.live-session-intro {
        min-height: clamp(300px, 42vw, 520px);
        padding: clamp(22px, 4vw, 46px);
        display: flex;
        align-items: flex-end;
        position: relative;
        overflow: hidden;
    }

    .live-session-intro img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .live-session-intro::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(18, 9, 4, .1), rgba(18, 9, 4, .86));
    }

    .live-session-intro .session-intro-copy {
        position: relative;
        z-index: 2;
        max-width: 680px;
    }

    .live-session-intro h1 {
        font-size: clamp(30px, 5vw, 58px);
        margin-bottom: 10px;
    }

    .live-topbar .container {
        min-width: 0;
    }

    .live-topbar-actions {
        min-width: 0;
    }

    .live-back-btn {
        white-space: nowrap;
        flex-shrink: 0;
    }

    .session-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    .session-meta-grid span {
        border: 1px solid rgba(199, 141, 34, .24);
        border-radius: 14px;
        padding: 12px;
        background: rgba(18, 9, 4, .42);
        color: var(--cream);
        font-size: 13px;
    }

    .session-meta-grid small {
        display: block;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .live-pill.upcoming i {
        background: var(--gold);
        box-shadow: 0 0 0 6px rgba(199, 141, 34, .2);
    }

    .live-pill.completed i {
        background: #78d38a;
        box-shadow: 0 0 0 6px rgba(120, 211, 138, .18);
    }

    .donation-custom {
        display: none;
    }

    .donation-custom.show {
        display: block;
    }

    .donation-panel [data-donation-amount].active {
        border-color: var(--gold);
        background: var(--gold);
        color: #1f1206;
    }

    /* Report card uses the same visual language as the other live side cards. */
    .report-card {
        width: 100%;
        border: 1px solid rgba(199, 141, 34, .22) !important;
        border-radius: 20px !important;
        background: rgba(255, 255, 255, .68) !important;
        box-shadow: 0 20px 60px -54px rgba(63, 36, 23, .7) !important;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        padding: 24px !important;
        overflow: hidden;
        box-sizing: border-box;
    }

    .report-card h3 {
        margin: 0 0 18px;
    }

    .report-issue-form {
        display: grid;
        gap: 12px;
        margin-top: 0;
    }

    .report-card .sacred-input {
        width: 100%;
        min-height: 46px;
        border: 1px solid rgba(199, 141, 34, .24);
        border-radius: 14px;
        background: rgba(251, 244, 223, .58);
        color: var(--cream);
        padding: 10px 14px;
        box-sizing: border-box;
    }

    .report-card .sacred-input:focus {
        border-color: rgba(199, 141, 34, .65);
        box-shadow: 0 0 0 .2rem rgba(199, 141, 34, .12);
        outline: 0;
    }

    .report-card textarea.sacred-input {
        min-height: 126px;
        resize: vertical;
    }

    .report-card input[type="file"].sacred-input {
        padding: 6px 8px;
    }

    .report-card input[type="file"].sacred-input::file-selector-button {
        min-height: 32px;
        margin: -1px 10px -1px -2px;
        border: 0;
        border-right: 1px solid rgba(199, 141, 34, .20);
        background: rgba(255, 255, 255, .66);
        color: var(--cream);
        padding: 7px 12px;
    }

    .report-card .report-submit-btn {
        min-height: 46px;
        border-radius: 12px;
        font-weight: 800;
    }

    @media (max-width: 767.98px) {
        .report-card {
            padding: 18px !important;
            border-radius: 18px !important;
        }

        .report-card h3 {
            margin-bottom: 14px;
        }

        .report-issue-form {
            gap: 10px;
        }

        .report-card textarea.sacred-input {
            min-height: 118px;
        }
    }

    .session-note {
        color: var(--muted);
        font-size: 13px;
        margin-top: 12px;
    }

    .live-selector-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .live-selector-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
        padding: 18px;
        border: 1px solid rgba(199, 141, 34, .22);
        border-radius: 16px;
        background: rgba(251, 244, 223, .08);
        color: var(--cream);
        text-decoration: none;
        transition: border-color .2s ease, background .2s ease, transform .2s ease;
    }

    .live-selector-card:hover,
    .live-selector-card.active {
        border-color: var(--gold);
        background: rgba(199, 141, 34, .14);
        color: var(--cream);
        transform: translateY(-1px);
    }

    .live-selector-card span {
        width: fit-content;
        margin-bottom: 10px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(199, 141, 34, .18);
        color: var(--gold);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .live-selector-card strong {
        font-size: 17px;
        margin-bottom: 5px;
    }

    .live-selector-card p {
        flex: 1;
        margin-bottom: 14px;
        color: var(--muted);
        font-size: 13px;
    }

    .family-invite-form {
        display: grid;
        gap: 10px;
        margin-top: 16px;
    }

    .family-invite-link {
        display: none;
        margin-top: 10px;
        overflow-wrap: anywhere;
    }

    .family-invite-link.show {
        display: block;
    }

    /* =========================================================
       FAMILY SUMMARY / PRESENCE
       Mirrors the Pandit "Who Is Present" card while staying
       compact enough for the user's 3-card layout.
       ========================================================= */

    .family-summary-card {
        width: 100%;
        min-width: 0;
    }

    .family-summary-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .family-summary-head h3 {
        margin: 0;
        color: var(--cream);
        font-family: "Cinzel", serif;
        font-size: 20px;
        line-height: 1.2;
    }

    .family-summary-head p {
        margin: 7px 0 0;
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
    }

    .family-summary-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        flex: 0 0 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--gold), var(--saffron));
        color: #fff;
        box-shadow: 0 12px 24px -18px rgba(232, 91, 33, .85);
    }

    .family-summary-list {
        display: grid;
        gap: 12px;
    }

    .family-summary-person {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 14px;
        border: 1px solid rgba(199, 141, 34, .18);
        border-radius: 16px;
        background: rgba(251, 244, 223, .50);
        box-sizing: border-box;
    }

    .family-summary-person-main {
        min-width: 0;
    }

    .family-summary-person h4 {
        margin: 9px 0 5px;
        color: var(--cream);
        font-family: "Cinzel", serif;
        font-size: 15px;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .family-summary-person p {
        margin: 0;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    .family-summary-person p strong {
        color: var(--cream);
        font-weight: 800;
    }

    .family-summary-person-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        min-width: 0;
    }

    .family-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: fit-content;
        max-width: 100%;
        border: 1px solid rgba(199, 141, 34, .24);
        border-radius: 999px;
        background: rgba(255, 255, 255, .72);
        color: #9b4c14;
        font-size: 10.5px;
        font-weight: 800;
        line-height: 1;
        padding: 7px 10px;
        white-space: nowrap;
    }

    .family-status-pill.present {
        border-color: rgba(47, 182, 109, .24);
        background: rgba(47, 182, 109, .10);
        color: #237a48;
    }

    .family-status-pill.left,
    .family-status-pill.revoked,
    .family-status-pill.expired {
        border-color: rgba(232, 91, 33, .28);
        background: rgba(232, 91, 33, .08);
        color: var(--saffron-dark);
    }

    .family-status-pill.not-joined,
    .family-status-pill.invited {
        color: #9b4c14;
    }

    .family-summary-revoke {
        padding: 6px 9px !important;
        font-size: 10px !important;
        line-height: 1 !important;
    }

    .family-summary-empty {
        margin: 0;
        padding: 16px;
        border: 1px dashed rgba(199, 141, 34, .28);
        border-radius: 14px;
        background: rgba(251, 244, 223, .38);
        color: var(--muted);
        font-size: 12px;
        text-align: center;
    }

    .family-invite-area {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid rgba(199, 141, 34, .16);
    }

    .family-summary-person.revoked,
    .family-summary-person.expired {
        opacity: .68;
    }

    @media (max-width: 575.98px) {
        .family-summary-head {
            margin-bottom: 14px;
        }

        .family-summary-head h3 {
            font-size: 18px;
        }

        .family-summary-icon {
            width: 40px;
            height: 40px;
            flex-basis: 40px;
            border-radius: 12px;
        }

        .family-summary-person {
            grid-template-columns: 1fr;
            align-items: start;
            gap: 10px;
            padding: 12px;
            border-radius: 14px;
        }

        .family-summary-person-actions {
            width: 100%;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .family-summary-person-actions .family-status-pill {
            margin-right: auto;
        }
    }

    @media (max-width: 340px) {
        .family-summary-person p {
            font-size: 10px;
        }

        .family-status-pill {
            font-size: 9.5px;
            padding: 6px 8px;
        }
    }

    .live-page .embedded-meeting-panel {
        margin-top: 0 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE SESSION TOPBAR
    | Desktop keeps the full controls. Mobile becomes one compact status strip.
    |--------------------------------------------------------------------------
    */

    .live-topbar {
        position: relative;
        top: auto;
        z-index: 20;
    }

    .live-mobile-label {
        display: none;
    }

    @media (max-width: 767.98px) {
        /* The main site header stays sticky. This second bar scrolls normally. */
        .live-topbar {
            position: relative !important;
            top: auto !important;
            z-index: 20 !important;
            padding: 6px 0 7px;
            background: transparent;
            border-bottom: 0;
            backdrop-filter: none;
        }

        .live-topbar > .container {
            display: block !important;
            max-width: 100%;
            padding: 0 10px !important;
        }

        /* Back + duplicate BhaktiDeep identity are not needed on phone. */
        .live-topbar-identity {
            display: none !important;
        }

        /* One segmented strip: Status | Family | Present | Report */
        .live-topbar-actions {
            width: 100%;
            min-width: 0;
            display: flex !important;
            align-items: stretch;
            gap: 0 !important;
            overflow: hidden;
            background: rgba(255, 255, 255, .62);
            border: 1px solid rgba(199, 141, 34, .24);
            border-radius: 14px;
            box-shadow: 0 8px 24px -18px rgba(63, 36, 23, .55);
        }

        .live-topbar .live-pill {
            flex: 0 0 auto;
        }

        .live-topbar .live-family-stat,
        .live-topbar .live-present-stat {
            flex: 1 1 0;
            min-width: 0;
        }

        .live-topbar .live-report-btn,
        .live-topbar .live-view-report-btn {
            flex: 0 0 38px;
        }

        .live-topbar .live-pill,
        .live-topbar .family-pill,
        .live-topbar .live-report-btn,
        .live-topbar .live-view-report-btn {
            min-width: 0;
            height: 38px;
            margin: 0;
            padding: 0 8px !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            color: var(--cream);
            font-size: 10.5px;
            line-height: 1;
            white-space: nowrap;
        }

        .live-topbar .live-pill {
            color: var(--saffron);
            font-weight: 900;
        }

        .live-topbar .live-pill i {
            width: 6px;
            height: 6px;
            flex: 0 0 6px;
            box-shadow: 0 0 0 3px rgba(232, 91, 33, .11);
        }

        .live-topbar .live-family-stat,
        .live-topbar .live-present-stat,
        .live-topbar .live-report-btn,
        .live-topbar .live-view-report-btn {
            border-left: 1px solid rgba(199, 141, 34, .16) !important;
        }

        .live-topbar .live-family-stat i,
        .live-topbar .live-present-stat i {
            flex: 0 0 auto;
            font-size: 12px;
        }

        .live-desktop-label {
            display: none !important;
        }

        .live-mobile-label {
            display: inline;
        }

        /* Donation still exists in the page panel; do not crowd the phone strip. */
        .live-topbar .live-donate-btn {
            display: none !important;
        }

        /* Long dispute text cannot fit in the compact strip. */
        .live-topbar .live-issue-status {
            display: none !important;
        }

        .live-topbar .live-report-btn,
        .live-topbar .live-view-report-btn {
            width: 38px;
            min-width: 38px;
            padding: 0 !important;
            color: #9b4c14;
        }

        .live-topbar .live-action-label {
            display: none !important;
        }

        .live-topbar .live-report-btn i,
        .live-topbar .live-view-report-btn i {
            margin: 0;
            font-size: 13px;
        }

        .live-player,
        .live-session-intro {
            min-height: 320px;
        }

        .player-copy {
            padding: 18px;
        }

        .audio-row {
            gap: 10px;
        }

        .audio-wave div {
            max-width: 100%;
            overflow: hidden;
        }

        .session-meta-grid {
            grid-template-columns: 1fr;
        }

        .side-panel,
        .donation-panel {
            padding: 18px;
        }

        .live-selector-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 340px) {
        .live-topbar > .container {
            padding: 0 6px !important;
        }

        .live-topbar .live-pill,
        .live-topbar .family-pill {
            padding: 0 5px !important;
            font-size: 9.6px;
        }

        .live-topbar .live-report-btn,
        .live-topbar .live-view-report-btn {
            width: 34px;
            min-width: 34px;
        }
    }
    
            /* =========================================================
   REVIEW PANDIT CARD
   Same card language as Family / Booking / Report cards.
   Scoped only to the user live page.
   ========================================================= */

.review-pandit-card {
    width: 100%;
    border: 1px solid rgba(199, 141, 34, .22) !important;
    border-radius: 20px !important;
    background: rgba(255, 255, 255, .68) !important;
    box-shadow: 0 20px 60px -54px rgba(63, 36, 23, .7) !important;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    padding: 24px !important;
    overflow: hidden;
    box-sizing: border-box;
}

.review-pandit-card .review-panel-content {
    width: 100%;
    min-width: 0;
}

.review-pandit-card .review-panel-heading {
    margin: 0 0 18px;
}

.review-pandit-card .review-panel-heading h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 6px;
    color: var(--cream);
    font-family: "Cinzel", serif;
    font-size: 24px;
    line-height: 1.25;
}

.review-pandit-card .review-panel-heading h3 i {
    color: var(--gold);
    font-size: 20px;
}

.review-pandit-card .review-panel-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.55;
}

/* OTP verification is a soft inner section, not a second heavy card. */
.review-pandit-card .review-verification {
    margin: 0 0 18px;
    padding: 16px;
    border: 1px solid rgba(199, 141, 34, .18);
    border-radius: 16px;
    background: rgba(251, 244, 223, .46);
}

.review-pandit-card .review-field-heading {
    margin-bottom: 12px;
}

.review-pandit-card .review-field-heading small {
    display: block;
    margin-bottom: 4px;
    color: var(--gold);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
}

.review-pandit-card .review-field-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
}

.review-pandit-card .review-action-form {
    margin-top: 12px;
}

.review-pandit-card .review-otp-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: end;
    gap: 10px;
    margin-top: 12px;
}

.review-pandit-card .review-input-group {
    width: 100%;
    min-width: 0;
}

.review-pandit-card .review-input-group label,
.review-pandit-card .review-form-field label {
    display: block;
    margin: 0 0 7px;
    color: var(--muted);
    font-size: 12px;
    font-weight: 700;
}

.review-pandit-card .review-submit-form {
    display: grid;
    gap: 14px;
    margin-top: 0;
}

.review-pandit-card .review-form-field {
    margin: 0;
}

/* Match Report card inputs. */
.review-pandit-card .sacred-input {
    width: 100%;
    min-width: 0;
    min-height: 46px;
    border: 1px solid rgba(199, 141, 34, .24);
    border-radius: 14px;
    background: rgba(251, 244, 223, .58);
    color: var(--cream);
    padding: 10px 14px;
    box-sizing: border-box;
    outline: 0;
}

.review-pandit-card .sacred-input:focus {
    border-color: rgba(199, 141, 34, .65);
    background: rgba(251, 244, 223, .72);
    box-shadow: 0 0 0 .2rem rgba(199, 141, 34, .12);
}

.review-pandit-card textarea.sacred-input {
    min-height: 120px;
    resize: vertical;
}

.review-pandit-card input[type="file"].sacred-input {
    padding: 6px 8px;
}

.review-pandit-card input[type="file"].sacred-input::file-selector-button {
    min-height: 32px;
    margin: -1px 10px -1px -2px;
    border: 0;
    border-right: 1px solid rgba(199, 141, 34, .20);
    background: rgba(255, 255, 255, .66);
    color: var(--cream);
    padding: 7px 12px;
}

.review-pandit-card .review-action-form .btn,
.review-pandit-card .review-otp-form .btn {
    min-height: 42px;
    padding: 9px 14px;
    white-space: nowrap;
}

.review-pandit-card .review-submit-button {
    margin: 0;
}

.review-pandit-card .review-submit-button .btn {
    width: 100%;
    min-height: 46px;
    border-radius: 12px !important;
    font-size: 14px;
    font-weight: 800;
}

.review-pandit-card .review-existing {
    display: grid;
    gap: 12px;
    margin: 0;
}

.review-pandit-card .review-existing > div {
    padding: 14px;
    border: 1px solid rgba(199, 141, 34, .18);
    border-radius: 14px;
    background: rgba(251, 244, 223, .46);
}

.review-pandit-card .review-existing small,
.review-pandit-card .review-existing b {
    display: block;
}

.review-pandit-card .review-existing small {
    margin-bottom: 5px;
    color: var(--muted);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.review-pandit-card .review-existing b {
    color: var(--cream);
    font-size: 14px;
}

.review-pandit-card .review-existing .review-comment {
    display: block;
    margin-top: 6px;
    color: var(--muted);
    font-weight: 400;
    line-height: 1.55;
}

@media (max-width: 767.98px) {
    .review-pandit-card {
        padding: 18px !important;
        border-radius: 18px !important;
    }

    .review-pandit-card .review-panel-heading {
        margin-bottom: 14px;
    }

    .review-pandit-card .review-panel-heading h3 {
        font-size: 21px;
    }

    .review-pandit-card .review-verification {
        padding: 14px;
        border-radius: 14px;
    }

    .review-pandit-card .review-otp-form {
        grid-template-columns: 1fr;
    }

    .review-pandit-card .review-action-form .btn,
    .review-pandit-card .review-otp-form .btn {
        width: 100%;
    }

    .review-pandit-card textarea.sacred-input {
        min-height: 112px;
    }
}


/* =========================================================
   BOOKING + PANDIT DETAIL GRIDS
   Two detail tiles per row, matching the compact session
   progress card language. Scoped to these two cards only.
   ========================================================= */

.live-detail-card .live-detail-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 18px;
}

.live-detail-card .live-detail-card-head h3 {
    margin: 0 !important;
}

.live-detail-card .live-detail-card-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: grid;
    place-items: center;
    border-radius: 13px;
    background: linear-gradient(135deg, var(--gold), var(--saffron));
    color: #fff;
    font-size: 17px;
    box-shadow: 0 10px 24px -16px rgba(232, 91, 33, .75);
}

.live-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.live-detail-item {
    min-width: 0;
    min-height: 82px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 13px 14px;
    border: 1px solid rgba(199, 141, 34, .22);
    border-radius: 14px;
    background: rgba(251, 244, 223, .46);
    box-sizing: border-box;
}

.live-detail-item .detail-label {
    display: block;
    margin-bottom: 5px;
    color: var(--muted);
    font-size: 11px;
    font-weight: 700;
    line-height: 1.25;
}

.live-detail-item .detail-value {
    display: block;
    min-width: 0;
    color: var(--cream);
    font-size: 13px;
    font-weight: 800;
    line-height: 1.35;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.live-detail-item .detail-value .detail-subline {
    display: block;
    margin-top: 2px;
    color: var(--muted);
    font-size: 10px;
    font-weight: 600;
}

@media (max-width: 767.98px) {
    .live-detail-card .live-detail-card-head {
        margin-bottom: 14px;
    }

    .live-detail-card .live-detail-card-icon {
        width: 38px;
        height: 38px;
        flex-basis: 38px;
        border-radius: 12px;
        font-size: 15px;
    }

    .live-detail-grid {
        gap: 9px;
    }

    .live-detail-item {
        min-height: 76px;
        padding: 11px 10px;
        border-radius: 12px;
    }

    .live-detail-item .detail-label {
        font-size: 10px;
        margin-bottom: 4px;
    }

    .live-detail-item .detail-value {
        font-size: 12px;
    }

    .live-detail-item .detail-value .detail-subline {
        font-size: 9.5px;
    }
}

@media (max-width: 340px) {
    .live-detail-grid {
        gap: 7px;
    }

    .live-detail-item {
        padding: 10px 8px;
    }

    .live-detail-item .detail-label {
        font-size: 9.5px;
    }

    .live-detail-item .detail-value {
        font-size: 11px;
    }
}

</style>
@endpush

@section('body')
@php
    $sessionStatus = $sessionStatus ?? request('status', $bookingRecord ? 'live' : 'upcoming');
    $requestedSessionType = $sessionType ?? request()->route('type') ?? request('type', 'aarti');
    $sessionType = in_array($requestedSessionType, ['aarti', 'pooja', 'hawan', 'diya'], true) ? $requestedSessionType : 'aarti';
    $sessionId = $sessionId ?? request()->route('id') ?? 101;
    $bookingRecord = $bookingRecord ?? null;
    if ($bookingRecord && $bookingRecord->status === 'completed' && !request()->has('status')) {
        $sessionStatus = 'completed';
    }
    $embeddedMeetingView = $embeddedMeetingView ?? null;
    $bookingSankalp = $bookingRecord?->sankalp;
    $videoMeeting = $bookingRecord?->videoMeeting;
    $bookingMode = $bookingRecord?->booking_mode ?: 'online';
    $isOfflineBooking = in_array($sessionType, ['pooja', 'hawan'], true) && $bookingMode === 'offline';
    $bookingMeta = [];

    if ($bookingRecord?->admin_note) {
        $bookingMeta = json_decode($bookingRecord->admin_note, true) ?: [];
    }

    $multipleLiveSessions = false;
    $isPrivateBookedSession = in_array($sessionType, ['pooja', 'hawan'], true);
    $bookingIsReadyForMeeting = $bookingRecord
        && !$isOfflineBooking
        && $bookingRecord->payment_status === 'paid'
        && $bookingRecord->status === 'confirmed'
        && $videoMeeting;
    $canStartProviderMeeting = $bookingIsReadyForMeeting
        && \Illuminate\Support\Facades\Auth::guard('pandit')->check()
        && (int) \Illuminate\Support\Facades\Auth::guard('pandit')->id() === (int) $bookingRecord->pandit_id
        && filled($videoMeeting->external_meeting_id);
    $providerMeetingActionUrl = $providerMeetingActionUrl ?? ($bookingIsReadyForMeeting
        ? route($canStartProviderMeeting ? 'live.session.start' : 'live.session.join', ['type' => $sessionType, 'id' => $bookingRecord->id, 'token' => request('token')])
        : null);
    $providerMeetingAction = $providerMeetingAction ?? ($canStartProviderMeeting ? 'Start '.ucfirst($sessionType) : 'Join '.ucfirst($sessionType));
    $hasLiveRoomAccess = $sessionType === 'aarti'
        || !$isPrivateBookedSession
        || $bookingIsReadyForMeeting
        || ($bookingRecord && $bookingRecord->payment_status === 'paid' && $bookingRecord->status === 'completed')
        || ($isOfflineBooking && $bookingRecord->payment_status === 'paid');
    $isPaidSession = $isPrivateBookedSession && $hasLiveRoomAccess;
    $sankalpName = $bookingSankalp?->full_name ?: $bookingRecord?->user?->name ?: 'Devotee';
    $slotTime = $bookingRecord?->slot ?: 'Today - 7:00 PM IST';
    $bookingDateLabel = $bookingRecord?->booking_date ? $bookingRecord->booking_date->format('d M Y') : 'Today';
    $bookingMobile = $bookingSankalp?->mobile ?: '-';
    $bookingPurpose = $bookingSankalp?->purpose ?: '-';
    $bookingPackage = $bookingMeta['package_name'] ?? '-';
    $bookingState = $bookingRecord?->state ?: ($bookingMeta['state'] ?? '-');
    $bookingCity = $bookingRecord?->city ?: ($bookingMeta['city'] ?? '-');
    $bookingDakshina = (float) ($bookingMeta['dakshina'] ?? 0);
    $bookingTotal = (float) ($bookingMeta['total_amount'] ?? 0);

    $backUrl = \Illuminate\Support\Facades\Route::has('live.sessions') ? route('live.sessions') : route('home');
    $bookingRouteName = $sessionType === 'hawan' ? 'hawan.booking' : 'pooja.booking';
    $bookingFallbackRouteName = $sessionType === 'hawan' ? 'hawan' : 'personalized-pooja';
    $bookingUrl = \Illuminate\Support\Facades\Route::has($bookingRouteName)
        ? route($bookingRouteName)
        : (\Illuminate\Support\Facades\Route::has($bookingFallbackRouteName) ? route($bookingFallbackRouteName) : '#');

    $sessionLabels = [
        'aarti' => [
            'title' => 'Live Aarti',
            'completedTitle' => 'Your Aarti Is Completed',
            'completedText' => 'Your aarti has been offered with devotion.',
            'service' => 'Lakshmi Aarti',
            'deity' => 'Maa Lakshmi',
            'section' => 'Aarti',
            'nowPlaying' => 'Om Jai Jagdish Hare',
            'image' => 'assets/live-aarti.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live aarti with sacred diya and temple lamps',
        ],
        'pooja' => [
            'title' => 'Live Pooja',
            'completedTitle' => 'Your Pooja Is Completed',
            'completedText' => 'Blessings have been registered in your name.',
            'service' => 'Lakshmi Pooja',
            'deity' => 'Maa Lakshmi',
            'section' => 'Mantra',
            'nowPlaying' => 'Mahamrityunjaya Mantra',
            'image' => 'assets/live-aarti.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live pooja altar with diya and flowers',
        ],
        'hawan' => [
            'title' => 'Live Hawan',
            'completedTitle' => 'Your Hawan Is Completed',
            'completedText' => 'Your sankalp has been offered through the sacred fire.',
            'service' => 'Mahamrityunjaya Hawan',
            'deity' => 'Lord Shiva',
            'section' => 'Mantra Japa',
            'nowPlaying' => 'Mahamrityunjaya Mantra',
            'image' => 'assets/havan-live.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live havan kund with sacred fire',
        ],
        'diya' => [
            'title' => 'Live Diya',
            'completedTitle' => 'Your Diya Offering Is Completed',
            'completedText' => 'Your diya has been offered with devotion and prayers.',
            'service' => 'Evening Diya Offering',
            'deity' => 'Maa Lakshmi',
            'section' => 'Diya Aarti',
            'nowPlaying' => 'Lakshmi Aarti',
            'image' => 'assets/live-aarti.jpg',
            'fallbackImage' => 'assets/havan-live.jpg',
            'alt' => 'Live diya offering with temple lamps',
        ],
    ];

    $session = $sessionLabels[$sessionType] ?? $sessionLabels['aarti'];
    if ($sessionType === 'pooja' && $bookingRecord) {
        $session['service'] = $bookingMeta['pooja_name'] ?? $session['service'];
        $session['deity'] = str_replace(' Pooja', '', $session['service']);
    }

    if ($sessionType === 'hawan' && $bookingRecord) {
        $session['service'] = $bookingMeta['hawan_name'] ?? $session['service'];
        $session['deity'] = str_contains(strtolower($session['service']), 'lakshmi') ? 'Maa Lakshmi' : $session['deity'];
    }
    $sessionImage = public_path($session['image']);
    $playerImage = file_exists($sessionImage) ? $session['image'] : $session['fallbackImage'];
    $topbarLabel = ['upcoming' => 'Starting Soon', 'live' => 'LIVE', 'completed' => 'Completed'][$sessionStatus] ?? 'LIVE';
    $familyInvites = collect($familyInvites ?? []);
    $liveRealtimeSnapshot = $bookingRecord ? \App\Support\LiveSessionSnapshot::make($bookingRecord) : null;
    $liveRealtimeStatus = $liveRealtimeSnapshot['session']['status'] ?? $topbarLabel;
    $activeFamilyInvite = $activeFamilyInvite ?? null;
    $canManageFamily = $canManageFamily ?? false;
    $activeDispute = $activeDispute ?? null;
    $completionProof = $bookingRecord?->completionProofs()->latest()->first();
    $userConfirmation = $bookingRecord?->userConfirmations()->where('user_id', \Illuminate\Support\Facades\Auth::id())->latest()->first();
    $canConfirmCompletion = $canManageFamily
        && $completionProof
        && $bookingRecord?->status === 'completed'
        && !in_array($userConfirmation?->status, [
            \App\Models\BookingUserConfirmation::STATUS_CONFIRMED,
            \App\Models\BookingUserConfirmation::STATUS_AUTO_CONFIRMED,
        ], true);
    // $canReportIssue = $canManageFamily && $isPrivateBookedSession && in_array($sessionType, ['pooja', 'hawan'], true) && $bookingRecord;
    $canReportIssue = $canManageFamily
    && $isPrivateBookedSession
    && in_array($sessionType, ['pooja', 'hawan'], true)
    && $bookingRecord
    && !in_array($userConfirmation?->status, [
        \App\Models\BookingUserConfirmation::STATUS_CONFIRMED,
        \App\Models\BookingUserConfirmation::STATUS_AUTO_CONFIRMED,
    ], true);
    $disputeStatusLabel = $activeDispute ? \Illuminate\Support\Str::of($activeDispute->status)->replace('_', ' ')->title() : null;
    $reportReasons = [
        'pandit_not_joined' => 'Pandit not joined',
        'pandit_joined_late' => 'Pandit joined late',
        'session_incomplete' => 'Session incomplete',
        'wrong_service' => 'Wrong service',
        'technical_issue' => 'Technical issue',
        'behaviour_issue' => 'Behaviour issue',
        'other' => 'Other',
    ];
    $joinedCount = $familyInvites->filter(fn ($invite) => $invite->statusLabel() === 'Joined')->count();

    $progressByType = [
        'aarti' => [
            ['Sankalp', 'done', 'Completed'],
            ['Mantra / Bhajan', 'active', 'In progress - devotional chanting'],
            ['Live Aarti', 'upcoming', 'Upcoming'],
            ['Blessing', 'upcoming', 'Upcoming'],
        ],
        'pooja' => [
            ['Sankalp', 'done', 'Completed'],
            ['Mantra Chanting', 'active', 'In progress - mantra jaap'],
            ['Aarti', 'upcoming', 'Upcoming'],
            ['Completion Blessing', 'upcoming', 'Upcoming'],
        ],
        'hawan' => [
            ['Kund Sthapana', 'done', 'Completed'],
            ['Sankalp', 'done', 'Completed'],
            ['Mantra Japa', 'active', 'In progress - sacred fire ritual'],
            ['Purna Ahuti', 'upcoming', 'Upcoming'],
            ['Aarti & Blessing', 'upcoming', 'Upcoming'],
        ],
        'diya' => [
            ['Sankalp', 'done', 'Completed'],
            ['Diya Lighting', 'active', 'In progress - offering'],
            ['Aarti', 'upcoming', 'Upcoming'],
            ['Blessing', 'upcoming', 'Upcoming'],
        ],
    ];

    $sessionProgress = $progressByType[$sessionType] ?? $progressByType['aarti'];
    $completedSteps = collect($sessionProgress)->where(1, 'done')->count();

    if ($sessionStatus === 'upcoming') {
        $sessionProgress = collect($sessionProgress)->map(fn ($row, $index) => [$row[0], $index === 0 ? 'active' : 'upcoming', $index === 0 ? 'Starting soon' : 'Upcoming'])->all();
        $completedSteps = 0;
    } elseif ($sessionStatus === 'completed') {
        $sessionProgress = collect($sessionProgress)->map(fn ($row) => [$row[0], 'done', 'Completed'])->all();
        $completedSteps = count($sessionProgress);
    }

    $comingUpItemsByType = [
        'aarti' => [['Live Aarti', '5:30'], ['Blessing', '2:15']],
        'pooja' => [['Aarti', '5:30'], ['Completion Blessing', '2:15']],
        'hawan' => [['Purna Ahuti', '5:30'], ['Aarti & Blessing', '2:15']],
        'diya' => [['Aarti', '5:30'], ['Blessing', '2:15']],
    ];

    $comingUpItems = $comingUpItemsByType[$sessionType] ?? $comingUpItemsByType['aarti'];

    $donationAmounts = [
        ['label' => '&#8377;101', 'value' => 101],
        ['label' => '&#8377;251', 'value' => 251],
        ['label' => '&#8377;501', 'value' => 501],
        ['label' => 'Custom', 'value' => 'custom'],
    ];
@endphp

<header class="site-header live-topbar">
    <div class="container d-flex align-items-center justify-content-between gap-3 py-3">
        <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0 live-topbar-identity">
            <a class="btn btn-ghost-gold btn-sm rounded-pill live-back-btn" href="{{ $backUrl }}" data-live-exit="{{ $sessionStatus === 'live' ? 'true' : 'false' }}"><i class="bi bi-arrow-left"></i> Back</a>
            <a class="brand-wrap min-w-0" href="{{ route('home') }}">
                <span class="brand-icon"><i class="bi bi-fire"></i></span>
                <span class="brand-title gold-text">BhaktiDeep</span>
            </a>
        </div>
        <div class="d-flex align-items-center gap-2 gap-md-3 live-topbar-actions">
            <span class="live-pill {{ $sessionStatus }}" data-live-session-pill>
                <i></i>
                <span data-live-session-status>{{ $liveRealtimeStatus }}</span>
            </span>

            @if ($hasLiveRoomAccess && !$isOfflineBooking)
                <span class="family-pill live-family-stat">
                    <i class="bi bi-people"></i>
                    <span data-family-top-count>{{ $joinedCount }}</span>
                    <span class="live-desktop-label">family joined</span>
                    <span class="live-mobile-label">Family</span>
                </span>

                <span class="family-pill live-present-stat">
                    <i class="bi bi-broadcast"></i>
                    <span data-live-total-present>{{ $liveRealtimeSnapshot['counts']['total_present'] ?? 0 }}</span>
                    <span class="live-desktop-label">present</span>
                    <span class="live-mobile-label">Present</span>
                </span>
            @endif

            @if ($hasLiveRoomAccess && !$isOfflineBooking && $sessionStatus !== 'completed')
                <button class="btn btn-gold btn-sm rounded-pill live-donate-btn" data-scroll-donation>
                    <i class="bi bi-heart"></i> Donate
                </button>
            @endif

            @if ($canReportIssue)
                @if ($activeDispute)
                    <span class="family-pill live-issue-status">
                        <i class="bi bi-exclamation-circle"></i>
                        Issue Reported - Status: {{ $disputeStatusLabel }}
                    </span>
                    <a class="btn btn-ghost-gold btn-sm rounded-pill live-view-report-btn" href="{{ route('user.reports.show', ['dispute' => $activeDispute]) }}" aria-label="View Report" title="View Report">
                        <i class="bi bi-eye"></i>
                        <span class="live-action-label">View Report</span>
                    </a>
                @else
                    <button class="btn btn-ghost-gold btn-sm rounded-pill live-report-btn" data-toggle-report aria-label="Report an Issue" title="Report an Issue">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span class="live-action-label">Report an Issue</span>
                    </button>
                @endif
            @endif
        </div>
    </div>
</header>

<main class="page-shell live-page">
    <section class="container py-4 py-md-5">
        @unless ($hasLiveRoomAccess)
            <div class="glass completion-card">
                <span><i class="bi bi-lock"></i></span>
                <div>
                    <h3>{{ $session['title'] }} Access Required</h3>
                    <p>{{ $session['title'] }} requires a paid, confirmed booking and active video meeting before entering the live room.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $bookingUrl }}" class="btn btn-saffron btn-sm">
                            {{ $sessionType === 'hawan' ? 'Book Hawan' : 'Book Pooja' }} <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ $backUrl }}" class="btn btn-ghost-gold btn-sm"><i class="bi bi-broadcast"></i> Live Sessions</a>
                    </div>
                </div>
            </div>
        @else
        <div class="row g-4">
            <div class="col-12">
            {{-- <div class="col-lg-8"> --}}
                @if ($isOfflineBooking)
                    <div class="glass completion-card">
                        <span><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <h3>{{ $session['service'] }}</h3>
                            <p>This is an offline {{ $sessionType }} booking. No Zoom meeting is needed.</p>
                            <div class="session-meta-grid">
                                <span><small>Pandit</small>{{ $bookingRecord?->pandit?->pandit_name ?: ($bookingRecord?->pandit?->full_name ?: '-') }}</span>
                                <span><small>Sankalp Name</small>{{ $sankalpName }}</span>
                                <span><small>Date and Time</small>{{ $bookingDateLabel }} - {{ $slotTime }}</span>
                                <span><small>State</small>{{ $bookingState }}</span>
                                <span><small>City</small>{{ $bookingCity }}</span>
                                <span><small>Booking Status</small>{{ ucfirst(str_replace('_', ' ', $bookingRecord?->status ?? 'pending')) }}</span>
                                <span><small>Payment Status</small>{{ ucfirst($bookingRecord?->payment_status ?? 'pending') }}</span>
                                <span><small>Package</small>{{ $bookingPackage }}</span>
                                <span><small>Purpose</small>{{ $bookingPurpose }}</span>
                                
                            </div>
                        </div>
                    </div>
                @elseif ($embeddedMeetingView && $bookingIsReadyForMeeting && $sessionStatus !== 'completed')
                    @include($embeddedMeetingView)
                @elseif ($sessionStatus === 'upcoming')
                    <div class="live-player live-session-intro">
                        @if ($sessionType !== 'hawan' && !file_exists(public_path($session['image'])))
                            {{-- TODO: replace with live aarti image. --}}
                        @endif
                        <img src="{{ asset($playerImage) }}" alt="{{ $session['alt'] }}">
                        <div class="particles">@for($i = 0; $i < 28; $i++)<span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * .18 }}s"></span>@endfor</div>
                        <div class="session-intro-copy">
                            <small class="now-playing"><i class="bi bi-clock"></i> Starting Soon</small>
                            <h1>Your {{ $session['title'] }} Begins In</h1>
                            <p>
                                @if ($sessionType === 'pooja')
                                    Your pooja will begin automatically at the selected time.
                                @elseif ($sessionType === 'hawan')
                                    Your sankalp is ready for the sacred fire ritual.
                                @else
                                    Your sankalp is ready. Invite your family before the session starts.
                                @endif
                            </p>
                            <div class="session-meta-grid">
                                <span><small>Selected Service</small>{{ $session['service'] }}</span>
                                <span><small>Sankalp Name</small>{{ $sankalpName }}</span>
                                <span><small>Slot Time</small>{{ $bookingDateLabel }} - {{ $slotTime }}</span>
                                <span><small>Selected Deity</small>{{ $session['deity'] }}</span>
                                @if ($bookingRecord)
                                    <span><small>Package</small>{{ $bookingPackage }}</span>
                                    <span><small>Purpose</small>{{ $bookingPurpose }}</span>
                                    <span><small>Mobile</small>{{ $bookingMobile }}</span>
                                    <span><small>Total Paid</small>Rs.{{ number_format($bookingTotal) }}</span>
                                @endif
                                <span><small>Countdown</small>08:24</span>
                                <span><small>Status</small>{{ $topbarLabel }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @if ($providerMeetingActionUrl)
                                    <a href="{{ $providerMeetingActionUrl }}" target="_blank" rel="noopener" class="btn btn-gold btn-sm">
                                        <i class="bi bi-camera-video"></i> {{ $providerMeetingAction }}
                                    </a>
                                @endif
                                @if ($canManageFamily)
                                    <button class="btn btn-saffron btn-sm" data-focus-invite><i class="bi bi-whatsapp"></i> Invite Family</button>
                                @endif
                                @if ($activeFamilyInvite)
                                    <button class="btn btn-ghost-gold btn-sm" data-copy-current-link><i class="bi bi-link-45deg"></i> Copy Invite Link</button>
                                @endif
                                <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-person-lines-fill"></i> View Sankalp</button>
                                <button class="btn btn-gold btn-sm" data-scroll-donation><i class="bi bi-heart"></i> Add Donation</button>
                            </div>
                        </div>
                    </div>
                @elseif ($sessionStatus === 'live')
                    <div class="live-player">
                        @if ($sessionType !== 'hawan' && !file_exists(public_path($session['image'])))
                            {{-- TODO: replace with live aarti image. --}}
                        @endif
                        <img src="{{ asset($playerImage) }}" alt="{{ $session['alt'] }}">
                        <div class="particles">@for($i = 0; $i < 28; $i++)<span style="left: {{ ($i * 53) % 100 }}%; animation-delay: {{ $i * .18 }}s"></span>@endfor</div>
                        <span class="now-playing"><i class="bi bi-music-note"></i> Now Playing</span>
                        <div class="player-copy">
                            <small>{{ $session['section'] }}</small>
                            <h1>{{ $session['nowPlaying'] }}</h1>
                            <div class="audio-row">
                                <button class="play-round"><i class="bi bi-pause-fill"></i></button>
                                <div class="audio-wave">
                                    <div>@for($i = 0; $i < 60; $i++)<span class="{{ $i < 36 ? 'active' : '' }}" style="height: {{ 6 + abs(sin($i * .6) * 22 + ($i % 5) * 2) }}px"></span>@endfor</div>
                                    <p><span>06:42</span><span>11:08</span></p>
                                </div>
                                <button class="volume-round"><i class="bi bi-volume-up"></i></button>
                            </div>
                            @if ($providerMeetingActionUrl)
                                <a href="{{ $providerMeetingActionUrl }}" target="_blank" rel="noopener" class="btn btn-gold btn-sm mt-3">
                                    <i class="bi bi-camera-video"></i> {{ $providerMeetingAction }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="glass countdown-card mt-4">
                        <span><i class="bi bi-bell"></i></span>
                        <strong>Up next<small>Aarti starts in</small></strong>
                        <em class="gold-text">08:24</em>
                    </div>
                @else
                    <div class="glass completion-card mt-4">
                        <span><i class="bi bi-stars"></i></span>
                        <div>
                            <h3>{{ $session['completedTitle'] }}</h3>
                            <p>{{ $session['completedText'] }} Donation receipt will be available with your booking record.</p>
                            {{-- <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-gold btn-sm"><i class="bi bi-download"></i> Download Receipt</button>
                                <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-play-circle"></i> View Replay</button>
                                <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-share"></i> Share Blessings</button>
                                @if ($sessionType === 'hawan' || ($sessionType === 'pooja' && $isPaidSession))
                                    <button class="btn btn-ghost-gold btn-sm"><i class="bi bi-award"></i> Download Certificate</button>
                                @endif
                            </div> --}}
                            <p class="session-note"><i class="bi bi-people"></i> <span data-family-top-count>{{ $joinedCount }}</span> family members joined this session.</p>
                        </div>
                    </div>
                @endif

                @unless($isOfflineBooking)
                    <div class="glass timeline-card large mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>Session Progress</h3><small><span data-live-joined-count>{{ $liveRealtimeSnapshot['counts']['joined'] ?? 0 }}</span> joined / <span data-live-left-count>{{ $liveRealtimeSnapshot['counts']['left'] ?? 0 }}</span> left</small>
                        </div>
                        @foreach ($sessionProgress as $i => $row)
                            <div class="timeline-row {{ $row[1] }}"><span>{!! $row[1] === 'done' ? '&#10003;' : $i + 1 !!}</span><strong>{{ $row[0] }}<small>{{ $row[2] }}</small></strong></div>
                        @endforeach
                    </div>
                @endunless
            </div>

<aside class="col-12 live-bottom-grid">    
            {{-- <aside class="col-lg-4"> --}}
                @unless($isOfflineBooking)
                    <div class="glass side-panel family-summary-card">
                        <div class="family-summary-head">
                            <div>
                                <h3>{{ $sessionStatus === 'completed' ? 'Family Summary' : 'Family Invites' }}</h3>
                                <p><span data-live-family-present>{{ $liveRealtimeSnapshot['counts']['family_present'] ?? $joinedCount }}</span> present</p>
                            </div>
                            <span class="family-summary-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                        </div>

                        <div class="family-summary-list" data-family-list>
                            @forelse ($familyInvites as $invite)
                                @php
                                    $inviteExpired = $invite->expires_at && $invite->expires_at->isPast();
                                    $familySnapshotRow = collect($liveRealtimeSnapshot['family'] ?? [])->firstWhere('id', $invite->id);

                                    if ($invite->revoked_at) {
                                        $inviteStatus = 'Revoked';
                                    } elseif ($inviteExpired) {
                                        $inviteStatus = 'Expired';
                                    } else {
                                        $inviteStatus = $familySnapshotRow['status'] ?? ($invite->left_at ? 'Left' : ($invite->joined_at ? 'Present' : 'Not Joined'));
                                    }

                                    $familyStatusClass = match ($inviteStatus) {
                                        'Present', 'Joined' => 'present',
                                        'Left' => 'left',
                                        'Revoked' => 'revoked',
                                        'Expired' => 'expired',
                                        default => 'not-joined',
                                    };

                                    $familyStatusIcon = match ($familyStatusClass) {
                                        'present' => 'bi-check-circle',
                                        'left' => 'bi-box-arrow-right',
                                        'revoked', 'expired' => 'bi-x-circle',
                                        default => 'bi-dash-circle',
                                    };
                                @endphp

                                <article class="family-summary-person {{ $familyStatusClass }}" data-invite-row="{{ $invite->id }}" data-family-presence-id="{{ $invite->id }}">
                                    <div class="family-summary-person-main">
                                        <span class="family-status-pill {{ $familyStatusClass }}">
                                            <span data-invite-status data-presence-status>{{ $inviteStatus }}</span>
                                        </span>

                                        <h4>{{ $invite->name }}</h4>
                                        <p>
                                            <strong>Family - {{ $invite->relation }}</strong>
                                            · Joined <span data-presence-joined-at>{{ $invite->joined_at?->format('d M Y, h:i A') ?? 'Not joined' }}</span>
                                            · Left <span data-presence-left-at>{{ $invite->left_at?->format('d M Y, h:i A') ?? '-' }}</span>
                                        </p>
                                    </div>

                                    <div class="family-summary-person-actions">
                                        <span class="family-status-pill {{ $familyStatusClass }}" data-family-status-pill>
                                            <i class="bi {{ $familyStatusIcon }}" data-family-status-icon></i>
                                            <span data-presence-status>{{ $inviteStatus }}</span>
                                        </span>

                                        @if ($canManageFamily && !$invite->revoked_at && !$inviteExpired && $sessionStatus !== 'completed')
                                            <button type="button" class="btn btn-ghost-gold btn-sm family-summary-revoke" data-revoke-invite="{{ route('live.family.revoke', ['type' => $sessionType, 'id' => $bookingRecord->id, 'invite' => $invite]) }}">Revoke</button>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <p class="family-summary-empty" data-empty-family>No family invites yet.</p>
                            @endforelse
                        </div>

                        @if ($canManageFamily && $sessionStatus !== 'completed')
                            <div class="family-invite-area">
                                <form class="family-invite-form" data-invite-form action="{{ route('live.family.store', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}">
                                    <input class="form-control sacred-input" name="name" placeholder="Family member name" required>
                                    <input class="form-control sacred-input" name="relation" placeholder="Relation" required>
                                    <button class="btn btn-saffron w-100" type="submit"><i class="bi bi-person-plus"></i> Create Invite Link</button>
                                </form>
                                <div class="alert alert-success family-invite-link" data-invite-link></div>
                                <p class="session-note"><i class="bi bi-shield-check"></i> Invite links are booking-specific and expire automatically.</p>
                            </div>
                        @elseif ($activeFamilyInvite)
                            <div class="family-invite-area">
                                <p class="session-note mb-2"><i class="bi bi-shield-check"></i> You joined as {{ $activeFamilyInvite->name }}.</p>
                                <button class="btn btn-ghost-gold w-100" type="button" data-leave-session>
                                    <i class="bi bi-box-arrow-left"></i> Leave Session
                                </button>
                            </div>
                        @endif
                    </div>
                @endunless

                @if ($bookingRecord)
                    <div class="glass side-panel mt-4 live-detail-card booking-detail-card">
                        <div class="live-detail-card-head">
                            <h3>Booking Details</h3>
                            <span class="live-detail-card-icon" aria-hidden="true"><i class="bi bi-receipt"></i></span>
                        </div>

                        <div class="live-detail-grid">
                            @if($isOfflineBooking)
                                <div class="live-detail-item">
                                    <span class="detail-label">Pandit</span>
                                    <strong class="detail-value">{{ $bookingRecord?->pandit?->pandit_name ?: ($bookingRecord?->pandit?->full_name ?: '-') }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Date and Time</span>
                                    <strong class="detail-value">{{ $bookingDateLabel }} - {{ $slotTime }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Service Location</span>
                                    <strong class="detail-value">{{ collect([$bookingCity, $bookingState])->filter(fn ($value) => $value !== '-')->join(', ') ?: '-' }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Booking Status</span>
                                    <strong class="detail-value">{{ ucfirst(str_replace('_', ' ', $bookingRecord?->status ?? 'pending')) }}</strong>
                                </div>
                            @else
                                {{-- <div class="live-detail-item">
                                    <span class="detail-label">Meeting Status</span>
                                    <strong class="detail-value" data-live-session-status>{{ $liveRealtimeSnapshot['session']['status'] ?? $topbarLabel }}</strong>
                                </div> --}}
                                <div class="live-detail-item">
                                    <span class="detail-label">Started</span>
                                    <strong class="detail-value" data-live-started-at>{{ $liveRealtimeSnapshot['session']['started_at'] ?? 'Not started' }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Ended</span>
                                    <strong class="detail-value" data-live-ended-at>{{ $liveRealtimeSnapshot['session']['ended_at'] ?? 'Not ended' }}</strong>
                                </div>
                                {{-- <div class="live-detail-item" data-presence-person="user">
                                    <span class="detail-label">User</span>
                                    <strong class="detail-value">
                                        <span data-presence-status>{{ $liveRealtimeSnapshot['people']['user']['status'] ?? 'Not Joined' }}</span>
                                        <span class="detail-subline" data-presence-joined-at>{{ $liveRealtimeSnapshot['people']['user']['joined_at'] ?? 'Not joined' }}</span>
                                    </strong>
                                </div> --}}
                                {{-- <div class="live-detail-item" data-presence-person="pandit">
                                    <span class="detail-label">Pandit</span>
                                    <strong class="detail-value">
                                        <span data-presence-status>{{ $liveRealtimeSnapshot['people']['pandit']['status'] ?? 'Not Joined' }}</span>
                                        <span class="detail-subline" data-presence-joined-at>{{ $liveRealtimeSnapshot['people']['pandit']['joined_at'] ?? 'Not joined' }}</span>
                                    </strong>
                                </div> --}}
                            @endif

                            <div class="live-detail-item">
                                <span class="detail-label">Sankalp Name</span>
                                <strong class="detail-value">{{ $sankalpName }}</strong>
                            </div>
                            <div class="live-detail-item">
                                <span class="detail-label">Purpose</span>
                                <strong class="detail-value">{{ $bookingPurpose }}</strong>
                            </div>
                            <div class="live-detail-item">
                                <span class="detail-label">Package</span>
                                <strong class="detail-value">{{ $bookingPackage }}</strong>
                            </div>
                            {{-- <div class="live-detail-item">
                                <span class="detail-label">Mobile</span>
                                <strong class="detail-value">{{ $bookingMobile }}</strong>
                            </div> --}}
                            <div class="live-detail-item">
                                <span class="detail-label">Total Paid</span>
                                <strong class="detail-value">Rs.{{ number_format($bookingTotal) }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($bookingRecord && $bookingRecord->payment_status === 'paid' && $bookingRecord->pandit)
                        @php
                            $panditInfo = $bookingRecord->pandit;
                        @endphp

                        <div class="glass side-panel mt-4 live-detail-card pandit-detail-card">
                            <div class="live-detail-card-head">
                                <h3>Pandit Details</h3>
                                <span class="live-detail-card-icon" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                            </div>

                            <div class="live-detail-grid">
                                <div class="live-detail-item">
                                    <span class="detail-label">Pandit Name</span>
                                    <strong class="detail-value">{{ $panditInfo->pandit_name ?: $panditInfo->full_name }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Mobile</span>
                                    <strong class="detail-value">{{ $panditInfo->mobile ?: '-' }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Email</span>
                                    <strong class="detail-value">{{ $panditInfo->email ?: '-' }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Address</span>
                                    <strong class="detail-value">{{ $panditInfo->full_address ?: '-' }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Location</span>
                                    <strong class="detail-value">{{ collect([$panditInfo->city, $panditInfo->state])->filter()->join(', ') ?: '-' }}</strong>
                                </div>
                                <div class="live-detail-item">
                                    <span class="detail-label">Experience</span>
                                    <strong class="detail-value">{{ $panditInfo->total_experience_years ?: 0 }} years</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                @if ($completionProof && $bookingRecord)
                    <div class="glass side-panel mt-4">
                        <h3>Pandit has marked this {{ ucfirst($sessionType) }} as completed</h3>
                        @if($completionProof->file_path)
                            <p><a class="btn btn-ghost-gold w-100" href="{{ asset('storage/'.$completionProof->file_path) }}" target="_blank"><i class="bi bi-image"></i> View Completion Image</a></p>
                        @endif
                        <div class="coming-row"><i class="bi bi-card-text"></i><strong>Completion Note<small>{{ $completionProof->notes ?: 'Not added' }}</small></strong><em class="bi bi-check-circle"></em></div>
                        <div class="coming-row"><i class="bi bi-clock"></i><strong>Submitted<small>{{ $completionProof->submitted_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}</small></strong><em class="bi bi-check-circle"></em></div>

                        @if($userConfirmation)
                            <div class="coming-row"><i class="bi bi-info-circle"></i><strong>Confirmation Status<small>{{ ucfirst(str_replace('_', ' ', $userConfirmation->status)) }}</small></strong><em class="bi bi-check-circle"></em></div>
                        @endif

                        @if($canConfirmCompletion)
                            <form method="POST" action="{{ route('live.completion.confirm', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}" class="mt-3">
                                @csrf
                                <button class="btn btn-saffron w-100" type="submit">
                                    <i class="bi bi-check2-circle"></i> Confirm Completed
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($bookingRecord->status === 'completed' && $canManageFamily)
                        <div class="glass side-panel review-pandit-card mt-4">
                            @include('partials.review-form', [
                                'booking' => $bookingRecord,
                                'bookingType' => $sessionType,
                                'reviewBy' => 'user',
                                'title' => 'Review Pandit',
                                'sendOtpRoute' => route('reviews.image-otp'),
                                'verifyOtpRoute' => route('reviews.image-otp.verify'),
                                'storeRoute' => route('reviews.store'),
                            ])
                        </div>
                    @endif
                @endif

                @if ($canReportIssue)
                    <div class="glass side-panel report-card mt-4" data-report-panel>
                        <h3>Report an Issue</h3>

                        @if (session('success'))
                            <div class="alert alert-success mb-3">{{ session('success') }}</div>
                        @endif

                        @if ($activeDispute)
                            <div class="alert alert-warning mb-3">Issue Reported - Status: {{ $disputeStatusLabel }}</div>
                            <a class="btn btn-ghost-gold w-100" href="{{ route('user.reports.show', ['dispute' => $activeDispute]) }}">
                                <i class="bi bi-eye"></i> View Report
                            </a>
                        @else
                            <form method="POST" action="{{ route('live.issue-report.store', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}" enctype="multipart/form-data" class="report-issue-form" data-report-form>
                                @csrf
                                <select name="reason" class="form-control sacred-input" required>
                                    <option value="">Select reason</option>
                                    @foreach ($reportReasons as $value => $label)
                                        <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('reason')
                                    <p class="text-warning small mb-0">{{ $message }}</p>
                                @enderror

                                <textarea name="description" rows="4" class="form-control sacred-input" placeholder="Describe the issue" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-warning small mb-0">{{ $message }}</p>
                                @enderror

                                <input type="file" name="proof" class="form-control sacred-input" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf">
                                @error('proof')
                                    <p class="text-warning small mb-0">{{ $message }}</p>
                                @enderror

                                <button class="btn btn-saffron w-100 report-submit-btn" type="submit">
                                    <i class="bi bi-send"></i> Submit Issue
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                @if ($canManageFamily && !$isOfflineBooking && $sessionStatus !== 'completed')
                    <div class="donation-panel mt-4" data-donation-panel data-dakshina-url="{{ route('live.dakshina.pay', ['type' => $sessionType, 'id' => $bookingRecord->id]) }}" data-csrf-token="{{ csrf_token() }}">
                        <h3><i class="bi bi-heart"></i> Dakshina</h3>
                        <p>Offer dakshina for this {{ $sessionType }} booking.</p>
                        <div class="row g-2">
                            @foreach($donationAmounts as $i => $amount)
                                <div class="col-6 col-sm-3 col-lg-3"><button type="button" class="btn {{ $i === 0 ? 'btn-gold active' : 'btn-ghost-gold' }} w-100" data-donation-amount="{{ $amount['value'] }}">{!! $amount['label'] !!}</button></div>
                            @endforeach
                        </div>
                        <div class="donation-custom mt-3" data-custom-donation>
                            <input type="number" min="1" max="100000" class="form-control sacred-input" placeholder="Enter amount" data-custom-donation-input>
                        </div>
                        <button class="btn btn-light w-100 mt-3" data-donate-button>Pay Dakshina</button>
                        <p class="session-note" data-donation-message>Payment amount is verified on server.</p>
                    </div>
                @endif

                @if (!$isOfflineBooking && $sessionStatus === 'live')
                    <div class="glass side-panel mt-4">
                        <h3>Coming Up</h3>
                        @foreach ($comingUpItems as $item)
                            <div class="coming-row"><i class="bi bi-play-circle"></i><strong>{{ $item[0] }}<small>{{ $item[1] }}</small></strong><em class="bi bi-arrow-right"></em></div>
                        @endforeach
                    </div>
                @elseif (!$isOfflineBooking && $sessionStatus === 'upcoming')
                    <div class="glass side-panel mt-4">
                        <h3>Upcoming Timeline</h3>
                        @foreach ($sessionProgress as $item)
                            <div class="coming-row"><i class="bi bi-clock"></i><strong>{{ $item[0] }}<small>{{ $item[2] }}</small></strong><em class="bi bi-arrow-right"></em></div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
        @endunless
    </section>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const donationPanel = document.querySelector('[data-donation-panel]');
    const liveExitLink = document.querySelector('[data-live-exit="true"]');
    const scrollDonationButtons = document.querySelectorAll('[data-scroll-donation]');
    const inviteForm = document.querySelector('[data-invite-form]');
    const inviteList = document.querySelector('[data-family-list]');
    const inviteLinkBox = document.querySelector('[data-invite-link]');
    const reportPanel = document.querySelector('[data-report-panel]');
    const reportForm = document.querySelector('[data-report-form]');
    const reportButtons = document.querySelectorAll('[data-toggle-report]');
    const csrfToken = @json(csrf_token());
    const familyLeaveUrl = @json($activeFamilyInvite ? route('live.family.leave', ['token' => request()->route('token')]) : null);
    const familyChannel = @json($canManageFamily && $bookingRecord ? 'live-session.'.$sessionType.'.'.$bookingRecord->id : null);

    const setFamilyCount = function (count) {
        document.querySelectorAll('[data-family-count]').forEach(function (item) {
            item.textContent = count + ' joined';
        });

        document.querySelectorAll('[data-family-top-count]').forEach(function (item) {
            item.textContent = count;
        });
    };

    const familyStatusClass = function (status) {
        if (status === 'Present' || status === 'Joined') return 'present';
        if (status === 'Left') return 'left';
        if (status === 'Revoked') return 'revoked';
        if (status === 'Expired') return 'expired';
        return 'not-joined';
    };

    const familyStatusIcon = function (status) {
        const statusClass = familyStatusClass(status);
        if (statusClass === 'present') return 'bi-check-circle';
        if (statusClass === 'left') return 'bi-box-arrow-right';
        if (statusClass === 'revoked' || statusClass === 'expired') return 'bi-x-circle';
        return 'bi-dash-circle';
    };

    const applyFamilyRowStatus = function (row, status) {
        if (!row) return;

        const statusClass = familyStatusClass(status);
        row.classList.remove('present', 'left', 'revoked', 'expired', 'not-joined', 'invited', 'joined');
        row.classList.add(statusClass);

        row.querySelectorAll('.family-status-pill').forEach(function (pill) {
            pill.classList.remove('present', 'left', 'revoked', 'expired', 'not-joined', 'invited', 'joined');
            pill.classList.add(statusClass);
        });

        row.querySelectorAll('[data-presence-status], [data-invite-status]').forEach(function (item) {
            item.textContent = status;
        });

        row.querySelectorAll('[data-family-status-icon]').forEach(function (icon) {
            icon.className = 'bi ' + familyStatusIcon(status);
        });
    };

    const setInviteStatus = function (invite) {
        const row = document.querySelector('[data-invite-row="' + invite.id + '"]');
        applyFamilyRowStatus(row, invite.status || 'Not Joined');
    };

    const copyText = function (text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        }
    };

    const addInviteRow = function (invite) {
        const row = document.createElement('article');
        const main = document.createElement('div');
        const leftStatus = document.createElement('span');
        const leftStatusText = document.createElement('span');
        const name = document.createElement('h4');
        const meta = document.createElement('p');
        const actions = document.createElement('div');
        const rightStatus = document.createElement('span');
        const rightIcon = document.createElement('i');
        const rightStatusText = document.createElement('span');
        const revoke = document.createElement('button');
        const displayStatus = invite.status === 'Joined' ? 'Present' : (invite.status === 'Invited' ? 'Not Joined' : (invite.status || 'Not Joined'));

        row.className = 'family-summary-person ' + familyStatusClass(displayStatus);
        row.dataset.inviteRow = invite.id;
        row.dataset.familyPresenceId = invite.id;

        main.className = 'family-summary-person-main';
        leftStatus.className = 'family-status-pill ' + familyStatusClass(displayStatus);
        leftStatusText.dataset.inviteStatus = '';
        leftStatusText.dataset.presenceStatus = '';
        leftStatusText.textContent = displayStatus;
        leftStatus.appendChild(leftStatusText);

        name.textContent = invite.name;
        meta.innerHTML = '<strong></strong> · Joined <span data-presence-joined-at>Not joined</span> · Left <span data-presence-left-at>-</span>';
        meta.querySelector('strong').textContent = 'Family - ' + invite.relation;
        main.append(leftStatus, name, meta);

        actions.className = 'family-summary-person-actions';
        rightStatus.className = 'family-status-pill ' + familyStatusClass(displayStatus);
        rightStatus.dataset.familyStatusPill = '';
        rightIcon.className = 'bi ' + familyStatusIcon(displayStatus);
        rightIcon.dataset.familyStatusIcon = '';
        rightStatusText.dataset.presenceStatus = '';
        rightStatusText.textContent = displayStatus;
        rightStatus.append(rightIcon, rightStatusText);
        actions.appendChild(rightStatus);

        if (invite.revoke_url) {
            revoke.type = 'button';
            revoke.className = 'btn btn-ghost-gold btn-sm family-summary-revoke';
            revoke.dataset.revokeInvite = invite.revoke_url;
            revoke.textContent = 'Revoke';
            actions.appendChild(revoke);
        }

        row.append(main, actions);
        inviteList.prepend(row);
    };

    if (liveExitLink) {
        liveExitLink.addEventListener('click', function (event) {
            const shouldLeave = window.confirm('Live session chal raha hai. Kya aap bahar jana chahte hain?');

            if (!shouldLeave) {
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll('[data-focus-invite]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (inviteForm) {
                inviteForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                inviteForm.querySelector('input')?.focus();
            }
        });
    });

    reportButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (reportPanel) {
                reportPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            if (reportForm) {
                reportForm.querySelector('select, textarea')?.focus();
            }
        });
    });

    scrollDonationButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const panel = document.querySelector('[data-donation-panel]');

            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    document.querySelectorAll('[data-copy-current-link]').forEach(function (button) {
        button.addEventListener('click', function () {
            copyText(window.location.href);
            button.innerHTML = '<i class="bi bi-check-circle"></i> Link Copied';
            window.setTimeout(function () {
                button.innerHTML = '<i class="bi bi-link-45deg"></i> Copy Invite Link';
            }, 1800);
        });
    });

    if (inviteForm && inviteList) {
        inviteForm.addEventListener('submit', function (event) {
            event.preventDefault();

            fetch(inviteForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(inviteForm),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Invite could not be created.');
                    }

                    return response.json();
                })
                .then(function (result) {
                    const invite = result.invite;
                    document.querySelector('[data-empty-family]')?.remove();
                    addInviteRow(invite);

                    inviteLinkBox.textContent = invite.join_url;
                    inviteLinkBox.classList.add('show');
                    copyText(invite.join_url);
                    inviteForm.reset();
                })
                .catch(function (error) {
                    inviteLinkBox.textContent = error.message || 'Invite could not be created.';
                    inviteLinkBox.classList.add('show');
                });
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-revoke-invite]');

        if (button) {
            fetch(button.dataset.revokeInvite, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Invite could not be revoked.');
                    }

                    return response.json();
                })
                .then(function () {
                    const row = button.closest('[data-invite-row]');
                    applyFamilyRowStatus(row, 'Revoked');
                    button.remove();
                });
        }
    });

    if (familyLeaveUrl) {
        document.querySelector('[data-leave-session]')?.addEventListener('click', function () {
            fetch(familyLeaveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            }).finally(function () {
                window.location.href = @json(route('home'));
            });
        });

        window.addEventListener('beforeunload', function () {
            fetch(familyLeaveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                keepalive: true,
            });
        });
    }

    if (donationPanel) {
        const amountButtons = donationPanel.querySelectorAll('[data-donation-amount]');
        const customWrap = donationPanel.querySelector('[data-custom-donation]');
        const customInput = donationPanel.querySelector('[data-custom-donation-input]');
        const donateButton = donationPanel.querySelector('[data-donate-button]');
        const donationMessage = donationPanel.querySelector('[data-donation-message]');
        let selectedAmount = '101';

        amountButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                amountButtons.forEach(function (item) {
                    item.classList.remove('active', 'btn-gold');
                    item.classList.add('btn-ghost-gold');
                });

                button.classList.add('active', 'btn-gold');
                button.classList.remove('btn-ghost-gold');
                selectedAmount = button.getAttribute('data-donation-amount');

                if (selectedAmount === 'custom') {
                    customWrap.classList.add('show');
                    customInput.focus();
                } else {
                    customWrap.classList.remove('show');
                    customInput.value = '';
                }
            });
        });

        donateButton.addEventListener('click', function () {
            const amount = selectedAmount === 'custom' ? customInput.value : selectedAmount;

            if (!amount || Number(amount) <= 0) {
                donationMessage.textContent = 'Please enter a valid dakshina amount.';
                return;
            }

            donateButton.disabled = true;
            donationMessage.textContent = 'Verifying payment on server...';

            fetch(donationPanel.dataset.dakshinaUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': donationPanel.dataset.csrfToken,
                },
                body: JSON.stringify({ amount: Number(amount) }),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Dakshina payment failed.');
                    }

                    return response.json();
                })
                .then(function (result) {
                    donationMessage.textContent = result.message + ' Receipt: ' + result.receipt_number;
                    customInput.value = '';
                })
                .catch(function (error) {
                    donationMessage.textContent = error.message || 'Dakshina payment failed.';
                })
                .finally(function () {
                    donateButton.disabled = false;
                });
        });
    }
});
</script>
@if($canManageFamily && $bookingRecord && !$isOfflineBooking)
    @include('live-sessions.realtime', ['channelName' => 'live-session.'.$sessionType.'.'.$bookingRecord->id])
@endif
@endpush
@endsection