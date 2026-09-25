<script>
document.addEventListener('DOMContentLoaded', function () {
    const channel = @json($channelName ?? null);
    if (!channel || !window.Echo) return;

    const text = (selector, value) => document.querySelectorAll(selector).forEach((el) => el.textContent = value ?? '-');
    const statusClass = function (status) {
        if (status === 'Present' || status === 'Joined') return 'present';
        if (status === 'Left') return 'left';
        if (status === 'Revoked') return 'revoked';
        if (status === 'Expired') return 'expired';
        return 'not-joined';
    };

    const statusIcon = function (status) {
        const cls = statusClass(status);
        if (cls === 'present') return 'bi-check-circle';
        if (cls === 'left') return 'bi-box-arrow-right';
        if (cls === 'revoked' || cls === 'expired') return 'bi-x-circle';
        return 'bi-dash-circle';
    };

    const row = (selector, data) => document.querySelectorAll(selector).forEach(function (el) {
        const status = data.status || 'Not Joined';
        const cls = statusClass(status);

        el.querySelectorAll('[data-presence-status]').forEach(function (statusEl) {
            statusEl.textContent = status;
        });
        el.querySelector('[data-presence-joined-at]')?.replaceChildren(document.createTextNode(data.joined_at || 'Not joined'));
        el.querySelector('[data-presence-left-at]')?.replaceChildren(document.createTextNode(data.left_at || '-'));

        el.classList.remove('present', 'left', 'revoked', 'expired', 'not-joined', 'invited', 'joined');
        el.classList.add(cls);

        el.querySelectorAll('.family-status-pill, .pandit-live-pill[data-presence-status]').forEach(function (pill) {
            pill.classList.remove('present', 'left', 'revoked', 'expired', 'not-joined', 'invited', 'joined');
            pill.classList.add(cls);
        });

        el.querySelectorAll('[data-family-status-icon]').forEach(function (icon) {
            icon.className = 'bi ' + statusIcon(status);
        });
    });
    const apply = function (snapshot) {
        if (!snapshot) return;
        text('[data-live-session-status]', snapshot.session?.status);
        text('[data-live-started-at]', snapshot.session?.started_at || 'Not started');
        text('[data-live-ended-at]', snapshot.session?.ended_at || 'Not ended');
        text('[data-live-total-present]', snapshot.counts?.total_present ?? 0);
        text('[data-live-joined-count]', snapshot.counts?.joined ?? 0);
        text('[data-live-left-count]', snapshot.counts?.left ?? 0);
        text('[data-family-top-count], [data-live-family-present]', snapshot.counts?.family_present ?? 0);
        text('[data-family-count]', (snapshot.counts?.family_present ?? 0) + ' joined');
        if (snapshot.session?.status === 'Ended') {
            document.querySelectorAll('[data-completion-after-ended]').forEach((el) => el.style.display = '');
        }
        row('[data-presence-person="user"]', snapshot.people?.user || {});
        row('[data-presence-person="pandit"]', snapshot.people?.pandit || {});
        (snapshot.family || []).forEach(function (item) {
            row('[data-family-presence-id="' + item.id + '"]', item);
            document.querySelector('[data-invite-row="' + item.id + '"] [data-invite-status]')?.replaceChildren(document.createTextNode(item.status));
        });
    };

    window.Echo.private(channel)
        .listen('.LiveSessionUpdated', apply)
        .listen('.FamilyMemberStatusChanged', (event) => apply(event.snapshot));
});
</script>
