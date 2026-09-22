{{--
    REPLACE: resources/views/video-meetings/zoom-client.blade.php
    Standalone Client View document: do NOT extend layouts.app/pandit-live.
    Inputs from existing controller: sdkEndpointUrl, leaveUrl, zoomSdkVersion.
    UI callbacks here do NOT write attendance or mark the booking completed.
--}}
@php
    $version = trim((string) ($zoomSdkVersion
        ?? config('video_meetings.providers.zoom.meeting_sdk_cdn_version')));
    $validVersion = preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
    $hostLabel = !request()->routeIs('live.family.client') && request()->query('mode') === 'host';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="no-referrer">
    <title>BhaktiDeep Zoom Meeting</title>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            min-width: 0;
            margin: 0;
            padding: 0;
            background: #101010;
        }
        /* The SDK owns all in-meeting layout. Do not resize its internal tiles. */
        #zmmtg-root { display: none; }
        #bd-client-lobby {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100%;
            padding: 24px;
            box-sizing: border-box;
            color: white;
            font: 16px/1.5 Arial, sans-serif;
            text-align: center;
        }
        #bd-client-lobby[hidden], #bd-client-error[hidden] { display: none; }
        #bd-client-lobby h1 { margin: 0 0 12px; font-size: 24px; }
        #bd-client-lobby p { max-width: 390px; margin: 0 auto 16px; color: #ddd; }
        #bd-client-start, #bd-client-retry {
            padding: 12px 20px;
            border: 0;
            border-radius: 8px;
            background: #e16a16;
            color: white;
            font: 600 16px Arial, sans-serif;
            cursor: pointer;
        }
        #bd-client-start:disabled { cursor: wait; opacity: .65; }
        #bd-client-error {
            position: fixed;
            top: 12px;
            left: 12px;
            right: 12px;
            z-index: 100000;
            max-width: 540px;
            margin: auto;
            padding: 16px;
            border: 1px solid #d67a5b;
            border-radius: 10px;
            background: #2d1717;
            color: white;
            box-sizing: border-box;
            font: 14px/1.5 Arial, sans-serif;
        }
        #bd-client-error p { margin: 0 0 12px; overflow-wrap: anywhere; }
    </style>
</head>
<body>
    <section id="bd-client-lobby">
        <div>
            <h1>BhaktiDeep Meeting</h1>
            <p id="bd-client-status">Camera aur microphone ki permission browser pooche to Allow karein.</p>
            <button id="bd-client-start" type="button" @disabled(!$validVersion)>
                {{ $hostLabel ? 'Start Meeting' : 'Join Meeting' }}
            </button>
            @unless ($validVersion)
                <p>ZOOM_MEETING_SDK_CDN_VERSION config missing ya invalid hai.</p>
            @endunless
        </div>
    </section>

    <aside id="bd-client-error" role="alert" hidden>
        <p id="bd-client-error-message"></p>
        <button id="bd-client-retry" type="button">Reload meeting page</button>
    </aside>

    @if ($validVersion)
        {{-- SDK styles are supplied by the SDK. Do not load BhaktiDeep/Vite CSS here. --}}
        <script src="https://source.zoom.us/{{ $version }}/lib/vendor/react.min.js"></script>
        <script src="https://source.zoom.us/{{ $version }}/lib/vendor/react-dom.min.js"></script>
        <script src="https://source.zoom.us/{{ $version }}/lib/vendor/redux.min.js"></script>
        <script src="https://source.zoom.us/{{ $version }}/lib/vendor/redux-thunk.min.js"></script>
        <script src="https://source.zoom.us/{{ $version }}/lib/vendor/lodash.min.js"></script>
        {{-- Client View main CDN URL has no /VERSION/ before the filename. --}}
        <script src="https://source.zoom.us/zoom-meeting-{{ $version }}.min.js"></script>
    @endif

    <script>
        (() => {
            const endpointValue = @json($sdkEndpointUrl ?? null);
            const exitValue = @json($leaveUrl ?? null);
            const version = @json($version);
            const csrf = @json(csrf_token());
            const button = document.getElementById('bd-client-start');
            const lobby = document.getElementById('bd-client-lobby');
            const status = document.getElementById('bd-client-status');
            const errorBox = document.getElementById('bd-client-error');
            const errorText = document.getElementById('bd-client-error-message');
            let started = false;

            // Only local UI messages. Never treat these as provider attendance proof.
            const tellParent = (type) => {
                if (window.parent !== window) {
                    window.parent.postMessage({ type }, window.location.origin);
                }
            };

            const sameOriginUrl = (value, label) => {
                if (typeof value !== 'string' || !value) throw new Error(`${label} missing hai.`);
                const url = new URL(value, window.location.href);
                if (url.origin !== window.location.origin) {
                    throw new Error(`${label} ka domain/protocol is BhaktiDeep page se match nahi karta.`);
                }
                return url.href;
            };

            const fail = (error) => {
                const code = error?.errorCode ?? error?.code;
                const detail = typeof error === 'string'
                    ? error : (error?.message || error?.reason || 'Zoom start/join failed.');
                errorText.textContent = `${detail}${code !== undefined ? ` (Code: ${code})` : ''}`;
                errorBox.hidden = false;
                tellParent('bhaktideep:zoom-error');
                // Do not log JWTs, ZAKs, passwords or complete SDK config objects.
                console.warn('BhaktiDeep Zoom setup failed', code === undefined ? '' : `Code ${code}`);
            };

            document.getElementById('bd-client-retry').addEventListener('click', () => {
                window.location.reload(); // Explicit retry only, never on resize.
            });

            tellParent('bhaktideep:zoom-ready');

            button.addEventListener('click', async () => {
                if (started) return;
                started = true;
                button.disabled = true;
                status.textContent = 'Preparing meeting...';
                errorBox.hidden = true;
                const abort = new AbortController();
                let timeout;

                try {
                    if (!window.isSecureContext) {
                        throw new Error('BhaktiDeep aur iframe dono HTTPS par kholen.');
                    }
                    if (!window.ZoomMtg) {
                        throw new Error('Zoom Client View SDK load nahi hua. Network tab me CDN scripts check karein.');
                    }
                    const endpoint = sameOriginUrl(endpointValue, 'SDK endpoint');
                    const exitUrl = sameOriginUrl(exitValue, 'Meeting exit URL');
                    timeout = setTimeout(() => abort.abort(), 30000);
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                        signal: abort.signal,
                    });
                    clearTimeout(timeout);
                    if (!response.ok) {
                        const notes = {
                            401: 'Login dobara karein.',
                            403: 'Booking/host access denied.',
                            419: 'Login session expire hua. Parent page reload karein.',
                            422: 'Server Zoom configuration nahi bana saka. Laravel log check karein.',
                        };
                        throw new Error(`SDK endpoint HTTP ${response.status}. ${notes[response.status] || 'Laravel log check karein.'}`);
                    }
                    if (!(response.headers.get('content-type') || '').includes('application/json')) {
                        throw new Error('SDK endpoint JSON ke badle HTML bhej raha hai. Login/redirect check karein.');
                    }
                    const config = await response.json();
                    if (config.provider !== 'zoom' || !config.signature || !config.meetingNumber || !config.userName) {
                        throw new Error('SDK config me provider/signature/meetingNumber/userName missing hai.');
                    }

                    ZoomMtg.setZoomJSLib(`https://source.zoom.us/${version}/lib`, '/av');
                    ZoomMtg.preLoadWasm();
                    ZoomMtg.prepareWebSDK();

                    // Remove our lobby BEFORE init/join. Never cover Zoom's waiting room,
                    // preview, audio controls or dialogs with a full-screen loading overlay.
                    lobby.hidden = true;
                    document.getElementById('zmmtg-root')?.style.setProperty('display', 'block');
                    tellParent('bhaktideep:zoom-joining');

                    await new Promise((resolve, reject) => {
                        ZoomMtg.init({
                            leaveUrl: exitUrl,
                            patchJsMedia: true,
                            leaveOnPageUnload: true,
                            success: resolve,
                            error: reject,
                        });
                    });
                    document.getElementById('zmmtg-root')?.style.setProperty('display', 'block');

                    const options = {
                        signature: config.signature,
                        meetingNumber: String(config.meetingNumber),
                        userName: config.userName,
                        userEmail: config.userEmail || '',
                        passWord: config.password || '',
                    };
                    // Preserve optional values already issued by your backend.
                    for (const key of ['zak', 'obfToken', 'customerKey', 'tk']) {
                        if (typeof config[key] === 'string' && config[key]) options[key] = config[key];
                    }
                    await new Promise((resolve, reject) => {
                        ZoomMtg.join({ ...options, success: resolve, error: reject });
                    });
                    tellParent('bhaktideep:zoom-joined');
                } catch (error) {
                    clearTimeout(timeout);
                    if (error?.name === 'AbortError') {
                        fail(new Error('SDK configuration request timed out. Server connection check karein.'));
                    } else {
                        fail(error);
                    }
                }
            });
        })();
    </script>
</body>
</html>