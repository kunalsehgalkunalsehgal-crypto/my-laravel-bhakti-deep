<script>
document.addEventListener('DOMContentLoaded', function () {
    const channel = @json($channelName ?? null);
    if (!channel || !window.Echo) return;

    const text = (selector, value) => document.querySelectorAll(selector).forEach((el) => el.textContent = value ?? '-');
    const row = (selector, data) => document.querySelectorAll(selector).forEach(function (el) {
        el.querySelector('[data-presence-status]')?.replaceChildren(document.createTextNode(data.status || 'Not Joined'));
        el.querySelector('[data-presence-joined-at]')?.replaceChildren(document.createTextNode(data.joined_at || 'Not joined'));
        el.querySelector('[data-presence-left-at]')?.replaceChildren(document.createTextNode(data.left_at || '-'));
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
