@php
    $sdkEndpointUrl = $sdkEndpointUrl ?? route('live.session.sdk', [
        'type' => $sessionType,
        'id' => $bookingRecord->id,
        'token' => request('token'),
        'mode' => $canStartProviderMeeting ? 'host' : 'participant',
    ]);
    $zoomSdkVersion = config('video_meetings.providers.zoom.meeting_sdk_cdn_version', '3.13.2');
@endphp

@once
    @push('styles')
        <style>
            .embedded-meeting-panel {
                overflow: hidden;
            }

            .embedded-meeting-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 14px;
            }

            .embedded-meeting-toolbar h3 {
                margin: 0;
            }

            .embedded-meeting-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .embedded-meeting-root {
                width: 100%;
                height: clamp(480px, 68vh, 720px);
                min-height: 480px;
                border: 1px solid rgba(199, 141, 34, .22);
                border-radius: 14px;
                background: rgba(18, 9, 4, .72);
                overflow: hidden;
            }

            .embedded-meeting-message {
                margin: 12px 0 0;
                color: var(--muted);
                font-size: 13px;
            }

            .embedded-meeting-message.error {
                color: #ffd8d8;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://source.zoom.us/{{ $zoomSdkVersion }}/lib/vendor/react.min.js"></script>
        <script src="https://source.zoom.us/{{ $zoomSdkVersion }}/lib/vendor/react-dom.min.js"></script>

<script src="https://source.zoom.us/{{ $zoomSdkVersion }}/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/{{ $zoomSdkVersion }}/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/{{ $zoomSdkVersion }}/lib/vendor/lodash.min.js"></script>



        <script src="https://source.zoom.us/{{ $zoomSdkVersion }}/zoom-meeting-embedded-{{ $zoomSdkVersion }}.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-video-meeting-sdk]').forEach(function (panel) {
                    const startButton = panel.querySelector('[data-sdk-start]');
                    const message = panel.querySelector('[data-sdk-message]');
                    const root = panel.querySelector('[data-sdk-root]');

                    if (!startButton || !root) {
                        return;
                    }

                    const setMessage = function (text, isError) {
                        if (!message) {
                            return;
                        }

                        message.textContent = text;
                        message.classList.toggle('error', Boolean(isError));
                        message.hidden = false;
                    };

                    startButton.addEventListener('click', function () {
                        if (panel.dataset.sdkStarted === 'true') {
                            return;
                        }

                        if (!window.ZoomMtgEmbedded) {
                            setMessage('Meeting SDK could not load. Please use the external meeting link.', true);
                            return;
                        }

                        panel.dataset.sdkStarted = 'true';
                        startButton.disabled = true;
                        setMessage('Preparing your meeting room...', false);

                        fetch(panel.dataset.sdkEndpoint, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': panel.dataset.csrfToken,
                            },
                            credentials: 'same-origin',
                        })
                            .then(function (response) {
                                if (!response.ok) {
                                    throw new Error('Unable to prepare this meeting.');
                                }

                                return response.json();
                            })
                            .then(function (config) {
                                if (config.provider !== 'zoom') {
                                    throw new Error('This meeting provider is not available inside the page.');
                                }

                                const client = window.ZoomMtgEmbedded.createClient();
                                const viewWidth = Math.max(root.clientWidth, 320);
                                const viewHeight = Math.max(root.clientHeight, 480);

                                return Promise.resolve(client.init({
                                    zoomAppRoot: root,
                                    language: 'en-US',
                                    customize: {
                                        video: {
                                            isResizable: true,
                                            viewSizes: {
                                                default: {
                                                    width: viewWidth,
                                                    height: viewHeight,
                                                },
                                            },
                                        },
                                    },
                                })).then(function () {
                                    const joinConfig = {
                                        sdkKey: config.sdkKey,
                                        signature: config.signature,
                                        meetingNumber: config.meetingNumber,
                                        password: config.password || '',
                                        userName: config.userName,
                                        userEmail: config.userEmail || '',
                                    };

                                    if (config.zak) {
                                        joinConfig.zak = config.zak;
                                    }

                                    return client.join(joinConfig);
                                });
                            })
                            .then(function () {
                                setMessage('Meeting opened inside BhaktiDeep.', false);
                            })
                            .catch(function (error) {
                                panel.dataset.sdkStarted = 'false';
                                startButton.disabled = false;
                                setMessage((error && error.message ? error.message : 'Meeting SDK failed.') + ' Please use the external meeting link.', true);
                            });
                    });
                });
            });
        </script>
    @endpush
@endonce

<div class="glass side-panel embedded-meeting-panel mt-4"
     data-video-meeting-sdk
     data-sdk-endpoint="{{ $sdkEndpointUrl }}"
     data-csrf-token="{{ csrf_token() }}">
    <div class="embedded-meeting-toolbar">
        <h3>{{ $providerMeetingAction }} In BhaktiDeep</h3>
        <div class="embedded-meeting-actions">
            <button type="button" class="btn btn-gold btn-sm" data-sdk-start>
                <i class="bi bi-camera-video"></i> Open Here
            </button>
            <a href="{{ $providerMeetingActionUrl }}" target="_blank" rel="noopener" class="btn btn-ghost-gold btn-sm">
                <i class="bi bi-box-arrow-up-right"></i> External Link
            </a>
        </div>
    </div>
    <div class="embedded-meeting-root" data-sdk-root></div>
    <p class="embedded-meeting-message" data-sdk-message hidden></p>
</div>
