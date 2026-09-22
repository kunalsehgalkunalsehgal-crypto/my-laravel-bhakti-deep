{{--
    REPLACE: resources/views/video-meetings/zoom-sdk.blade.php
    Parent page only. No Zoom SDK scripts or Component View code belongs here.
    Reuses live.session.client / live.family.client and the existing SDK endpoints.
--}}
@php
    $familyToken = request()->route('token');
    $isFamily = ($activeFamilyInvite ?? null) && filled($familyToken);
    $isHost = !$isFamily && (bool) ($canStartProviderMeeting ?? false);
    $clientRoute = $isFamily ? 'live.family.client' : 'live.session.client';
    $clientUrl = null;

    if (\Illuminate\Support\Facades\Route::has($clientRoute)) {
        $params = $isFamily
            ? ['token' => $familyToken]
            : array_filter([
                'type' => $sessionType,
                'id' => $bookingRecord->id,
                'mode' => $isHost ? 'host' : 'participant',
                'token' => request()->query('token'),
            ], fn ($value) => $value !== null && $value !== '');

        // Relative URL keeps the iframe on the current HTTPS/ngrok origin.
        $clientUrl = route($clientRoute, $params, false);
    }
@endphp

@once
    @push('styles')
        <style>
            .bd-iframe-meeting {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                box-sizing: border-box;
            }

            .bd-iframe-meeting [hidden] { display: none !important; }

            .bd-iframe-meeting__actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
                margin-bottom: 12px;
            }

            .bd-iframe-meeting__stage {
                position: relative;
                width: 100%;
                max-width: 100%;
                min-width: 0;
                min-height: 0;
                height: var(--bd-frame-height, 70vh);
                box-sizing: border-box;
                overflow: hidden;
                border: 1px solid rgba(199, 141, 34, .25);
                border-radius: 14px;
                background: #101010;
                isolation: isolate;
            }

            .bd-iframe-meeting__frame {
                display: block;
                position: static;
                width: 100%;
                max-width: 100%;
                min-width: 0;
                height: 100%;
                min-height: 0;
                margin: 0;
                padding: 0;
                border: 0;
                background: #101010;
            }

            .bd-iframe-meeting__message {
                margin: 10px 0 0;
                font-size: 13px;
                line-height: 1.5;
                overflow-wrap: anywhere;
            }

            .bd-iframe-meeting__message.is-error { color: #b42318; }

            /* Native fullscreen preserves this same iframe and document. */
            .bd-iframe-meeting:fullscreen {
                display: flex;
                flex-direction: column;
                width: 100%;
                height: 100%;
                min-height: 0;
                padding: 12px;
                background: #fff8e8;
            }

            .bd-iframe-meeting:fullscreen .bd-iframe-meeting__actions,
            .bd-iframe-meeting:fullscreen .bd-iframe-meeting__message {
                flex: 0 0 auto;
            }

            .bd-iframe-meeting:fullscreen .bd-iframe-meeting__stage {
                flex: 1 1 0;
                height: auto;
                min-height: 0;
            }

            @media (max-width: 575.98px) {
                .bd-iframe-meeting__actions { gap: 8px; }
                .bd-iframe-meeting__actions .btn { flex: 1 1 auto; }
                .bd-iframe-meeting__stage { border-radius: 10px; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const initialise = () => {
                    document.querySelectorAll('[data-bd-iframe-meeting]').forEach((panel) => {
                        if (panel.dataset.initialised === 'true') return;
                        panel.dataset.initialised = 'true';

                        const frame = panel.querySelector('[data-bd-frame]');
                        const stage = panel.querySelector('[data-bd-stage]');
                        const openButton = panel.querySelector('[data-bd-open]');
                        const expandButton = panel.querySelector('[data-bd-expand]');
                        const message = panel.querySelector('[data-bd-message]');
                        if (!frame || !stage || !openButton || !expandButton) return;

                        let frameLoaded = false;
                        let meetingBusy = false;
                        let expandedInPage = false;
                        let resizeTimer;
                        let readyTimer;

                        const say = (text, error = false) => {
                            message.textContent = text;
                            message.classList.toggle('is-error', error);
                        };

                        const syncHeight = () => {
                            const visual = window.visualViewport;
                            // Do not shrink controls just because the user pinch-zooms.
                            const height = visual && Math.abs(visual.scale - 1) < .02
                                ? visual.height
                                : window.innerHeight;
                            const actionHeight = panel.querySelector('[data-bd-actions]')
                                .getBoundingClientRect().height;
                            const pixels = expandedInPage
                                ? Math.max(280, Math.floor(height - actionHeight - 56))
                                : Math.min(740, Math.max(320, Math.floor(height * .74)));

                            panel.style.setProperty('--bd-frame-height', `${pixels}px`);
                            const large = document.fullscreenElement === panel || expandedInPage;
                            expandButton.textContent = large ? 'Normal view' : 'Bada view';
                            expandButton.setAttribute('aria-pressed', String(large));
                        };

                        const scheduleHeight = () => {
                            clearTimeout(resizeTimer);
                            resizeTimer = setTimeout(syncHeight, 100);
                        };

                        openButton.addEventListener('click', () => {
                            if (frameLoaded) return;
                            const url = new URL(frame.dataset.src, window.location.href);
                            if (url.origin !== window.location.origin) {
                                say('Meeting page must use the same BhaktiDeep domain.', true);
                                return;
                            }

                            stage.hidden = false;
                            expandButton.hidden = false;
                            syncHeight();
                            // Set src only on an explicit open/rejoin, NEVER on resize.
                            frameLoaded = true;
                            frame.src = url.href;
                            openButton.disabled = true;
                            say('Meeting page khul raha hai. Andar Start/Join dabao.');
                            readyTimer = setTimeout(() => {
                                say('Page ready na ho to "Meeting page kholen" se check karo.', true);
                            }, 20000);
                        });

                        expandButton.addEventListener('click', async () => {
                            if (document.fullscreenElement === panel) {
                                await document.exitFullscreen().catch(() => {});
                                return;
                            }
                            if (!expandedInPage && panel.requestFullscreen && document.fullscreenEnabled) {
                                try {
                                    await panel.requestFullscreen();
                                    return;
                                } catch (_) {
                                    // Some mobile browsers do not allow element fullscreen.
                                }
                            }
                            // In-flow fallback: no DOM move, iframe replacement or CSS transform.
                            expandedInPage = !expandedInPage;
                            syncHeight();
                            if (expandedInPage) panel.scrollIntoView({ block: 'start', behavior: 'smooth' });
                        });

                        panel.querySelectorAll('[data-bd-alternative]').forEach((link) => {
                            link.addEventListener('click', (event) => {
                                if (!meetingBusy) return;
                                event.preventDefault();
                                say('Dusra meeting page kholne se pehle Zoom toolbar se Leave karo.');
                            });
                        });

                        window.addEventListener('message', (event) => {
                            if (event.origin !== window.location.origin || event.source !== frame.contentWindow) return;
                            const type = event.data?.type;
                            if (type === 'bhaktideep:zoom-ready') {
                                clearTimeout(readyTimer);
                                say('Meeting frame ke andar Start/Join dabao.');
                            } else if (type === 'bhaktideep:zoom-joining') {
                                meetingBusy = true;
                                say('Zoom join process chal raha hai. Waiting room aaye to host ka wait karo.');
                            } else if (type === 'bhaktideep:zoom-joined') {
                                meetingBusy = true;
                                say('Meeting controls Zoom frame ke andar hain.');
                            } else if (type === 'bhaktideep:zoom-error') {
                                meetingBusy = false;
                                clearTimeout(readyTimer);
                                say('Zoom frame me error dikh raha hai. Uska code/message check karo.', true);
                            } else if (type === 'bhaktideep:zoom-left') {
                                clearTimeout(readyTimer);
                                meetingBusy = false;
                                frameLoaded = false;
                                expandedInPage = false;
                                if (document.fullscreenElement === panel) {
                                    document.exitFullscreen().catch(() => {});
                                }
                                frame.src = 'about:blank';
                                stage.hidden = true;
                                expandButton.hidden = true;
                                openButton.disabled = false;
                                say('Meeting se bahar aa gaye. Dobara kholne ke liye button dabao.');
                                syncHeight();
                            }
                        });

                        window.addEventListener('resize', scheduleHeight, { passive: true });
                        window.visualViewport?.addEventListener('resize', scheduleHeight, { passive: true });
                        document.addEventListener('fullscreenchange', syncHeight);
                        syncHeight();
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initialise, { once: true });
                } else {
                    initialise();
                }
            })();
        </script>
    @endpush
@endonce

<section class="bd-iframe-meeting" data-bd-iframe-meeting>
    @if ($clientUrl)
        <div class="bd-iframe-meeting__actions" data-bd-actions>
            <button type="button" class="btn btn-gold" data-bd-open>Meeting kholen</button>
            <button type="button" class="btn btn-ghost-gold" data-bd-expand aria-pressed="false" hidden>Bada view</button>
            <a href="{{ $clientUrl }}" class="btn btn-ghost-gold" target="_blank" rel="noopener noreferrer" data-bd-alternative>
                Meeting page kholen
            </a>
            @if ($providerMeetingActionUrl ?? null)
                <a href="{{ $providerMeetingActionUrl }}" class="btn btn-ghost-gold" target="_blank" rel="noopener noreferrer" data-bd-alternative>
                    Open in Zoom
                </a>
            @endif
        </div>

        <div class="bd-iframe-meeting__stage" data-bd-stage hidden>
            <iframe
                class="bd-iframe-meeting__frame"
                data-bd-frame
                data-src="{{ $clientUrl }}"
                title="BhaktiDeep Zoom Meeting"
                allow="camera; microphone; display-capture; autoplay; fullscreen"
                allowfullscreen
                referrerpolicy="no-referrer"
            ></iframe>
        </div>
        <p class="bd-iframe-meeting__message" role="status" aria-live="polite" data-bd-message>
            Zoom yahin khulega. Session details neeche rahengi.
        </p>
    @else
        <p class="bd-iframe-meeting__message is-error" role="alert">
            Meeting route missing: {{ $clientRoute }}. Existing iframe route setup check karein.
        </p>
    @endif
</section>
