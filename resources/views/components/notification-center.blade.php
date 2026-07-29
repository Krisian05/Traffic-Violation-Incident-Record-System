<div class="dropdown me-2 position-relative" id="notificationCenterApp">
    {{-- Notification Bell Button --}}
    <button class="btn btn-light position-relative d-flex align-items-center justify-content-center border-0 shadow-xs" 
            id="notifBellBtn" 
            data-bs-toggle="dropdown" 
            data-bs-auto-close="outside"
            aria-expanded="false"
            title="System Notifications"
            style="width: 40px; height: 40px; border-radius: 12px; background: #fff; border: 1px solid #e7e2db !important; transition: all .15s;">
        <i class="bi bi-bell fs-5 text-stone-700" id="notifBellIcon" style="color: #44403c;"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" 
              id="notifBadge" 
              style="font-size: 0.68rem; padding: 0.3em 0.55em; box-shadow: 0 2px 6px rgba(220,38,38,0.4); border: 2px solid #fff;">
            0
        </span>
    </button>

    {{-- Dropdown Menu --}}
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" 
         style="width: 380px; max-width: 92vw; border: 1px solid #e7e2db !important; overflow: hidden; z-index: 1050;">
        
        {{-- Header --}}
        <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between" style="border-color: #f0ebe3 !important;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-bell-fill text-danger"></i>
                <h6 class="fw-800 text-stone-900 mb-0" style="font-size: 0.95rem; font-family: 'Instrument Sans', sans-serif;">System Updates</h6>
            </div>
            <div class="d-flex align-items-center gap-1">
                {{-- Audio Mute/Unmute Toggle --}}
                <button type="button" class="btn btn-sm btn-light border-0 text-muted p-1 px-2 rounded-3" 
                        id="notifSoundToggle" 
                        title="Toggle Notification Sound" style="font-size: 0.75rem;">
                    <i class="bi bi-volume-up-fill" id="notifSoundIcon"></i>
                </button>
                {{-- Mark All Read --}}
                <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted p-1 px-2 fw-700" 
                        id="notifMarkAllBtn" style="font-size: 0.75rem;">
                    Mark all read
                </button>
            </div>
        </div>

        {{-- Notifications List --}}
        <div id="notifListWrap" style="max-height: 380px; overflow-y: auto;">
            <div class="text-center py-4 text-muted" id="notifLoading">
                <div class="spinner-border spinner-border-sm text-danger mb-2" role="status"></div>
                <div style="font-size: 0.8rem;">Checking system updates...</div>
            </div>
            <div id="notifListContainer"></div>
        </div>

        {{-- Footer --}}
        <div class="p-2.5 text-center bg-light border-top" style="border-color: #f0ebe3 !important; font-size: 0.75rem;">
            <span class="text-muted fw-600">Real-time system updates &amp; alerts</span>
        </div>
    </div>
</div>

{{-- Slide-in Toast Container for Live Alerts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" id="notifToastContainer" style="z-index: 1090;"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const feedUrl = "{{ route('notifications.feed') }}";
    const markAllUrl = "{{ route('notifications.readAll') }}";

    let knownNotifIds = new Set();
    let isFirstFetch = true;
    let isMuted = localStorage.getItem('tvirs_notif_muted') === 'true';

    const notifBadge = document.getElementById('notifBadge');
    const notifListContainer = document.getElementById('notifListContainer');
    const notifLoading = document.getElementById('notifLoading');
    const notifMarkAllBtn = document.getElementById('notifMarkAllBtn');
    const notifSoundToggle = document.getElementById('notifSoundToggle');
    const notifSoundIcon = document.getElementById('notifSoundIcon');
    const toastContainer = document.getElementById('notifToastContainer');

    updateSoundIcon();

    // ── Web Audio Synthesizer (Bell Chime Effect) ──
    function playNotificationBellChime() {
        if (isMuted) return;
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;
            const ctx = new AudioContextClass();

            // First high tone (E6 - 1318Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(1318.51, ctx.currentTime);
            gain1.gain.setValueAtTime(0.2, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.6);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.6);

            // Second harmonic chime tone (B6 - 1975Hz)
            setTimeout(() => {
                try {
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(1975.53, ctx.currentTime);
                    gain2.gain.setValueAtTime(0.18, ctx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.8);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(ctx.currentTime);
                    osc2.stop(ctx.currentTime + 0.8);
                } catch (e) {}
            }, 90);
        } catch (e) {
            console.warn('Notification audio chime error:', e);
        }
    }

    // ── Mute / Unmute Sound Toggle ──
    notifSoundToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        isMuted = !isMuted;
        localStorage.setItem('tvirs_notif_muted', isMuted);
        updateSoundIcon();
        if (!isMuted) {
            playNotificationBellChime();
        }
    });

    function updateSoundIcon() {
        if (isMuted) {
            notifSoundIcon.className = 'bi bi-volume-mute-fill text-danger';
            notifSoundToggle.title = "Unmute Notification Sound";
        } else {
            notifSoundIcon.className = 'bi bi-volume-up-fill text-success';
            notifSoundToggle.title = "Mute Notification Sound";
        }
    }

    // ── Fetch Notifications Feed ──
    function fetchNotifications() {
        fetch(feedUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (notifLoading) notifLoading.style.display = 'none';

            // Update unread count badge
            if (data.unread_count > 0) {
                notifBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                notifBadge.classList.remove('d-none');
            } else {
                notifBadge.classList.add('d-none');
            }

            const items = data.notifications || [];
            let newItemsDetected = false;

            if (items.length === 0) {
                notifListContainer.innerHTML = `
                    <div class="text-center py-4 px-3 text-muted">
                        <i class="bi bi-bell-slash fs-3 d-block mb-1 text-secondary opacity-50"></i>
                        <div class="fw-600" style="font-size: 0.85rem;">No notifications yet</div>
                        <div style="font-size: 0.75rem;">System updates will appear here in real-time.</div>
                    </div>`;
                return;
            }

            let html = '';
            items.forEach(n => {
                const isNew = !knownNotifIds.has(n.id);
                knownNotifIds.add(n.id);

                if (isNew && !isFirstFetch && !n.read) {
                    newItemsDetected = true;
                    showToastAlert(n);
                }

                const d = n.data || {};
                const icon = d.icon || 'bi-info-circle-fill';
                const color = d.color || '#dc2626';
                const bg = d.bg || '#fef2f2';
                const unreadCls = !n.read ? 'bg-light fw-bold border-start border-3 border-danger' : '';

                html += `
                    <a href="${d.url || '#'}" class="d-flex align-items-start gap-2.5 p-3 text-decoration-none border-bottom notif-item ${unreadCls}" 
                       data-id="${n.id}" style="color: #1c1917; transition: background .12s; border-color: #f5f0e8 !important;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle mt-0.5" 
                             style="width: 34px; height: 34px; background-color: ${bg}; color: ${color}; font-size: 0.95rem;">
                            <i class="bi ${icon}"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                <span class="fw-700 text-truncate" style="font-size: 0.82rem; color: #1c1917;">${escapeHtml(d.title || 'Notification')}</span>
                                <span class="text-muted flex-shrink-0" style="font-size: 0.7rem;">${n.created_at}</span>
                            </div>
                            <p class="text-muted mb-0 text-break" style="font-size: 0.78rem; line-height: 1.35;">${escapeHtml(d.message || '')}</p>
                        </div>
                        ${!n.read ? '<span class="flex-shrink-0 rounded-circle bg-danger mt-1.5" style="width: 7px; height: 7px;"></span>' : ''}
                    </a>`;
            });

            notifListContainer.innerHTML = html;

            // Trigger bell audio chime if new items arrived
            if (newItemsDetected) {
                playNotificationBellChime();
            }

            isFirstFetch = false;
        })
        .catch(err => {
            console.error('Notification feed error:', err);
            if (notifLoading) notifLoading.style.display = 'none';
        });
    }

    // ── Slide-in Toast Alert ──
    function showToastAlert(n) {
        const d = n.data || {};
        const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);

        const toastHtml = `
            <div id="${toastId}" class="toast show border-0 shadow-lg rounded-4 overflow-hidden mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="background: #fff; border: 1px solid #e7e2db !important; min-width: 310px;">
                <div class="toast-header border-0 py-2.5 px-3" style="background: ${d.bg || '#fef2f2'};">
                    <i class="bi ${d.icon || 'bi-bell-fill'} me-2 fs-6" style="color: ${d.color || '#dc2626'};"></i>
                    <strong class="me-auto text-dark" style="font-size: 0.85rem; font-family: 'Instrument Sans', sans-serif;">${escapeHtml(d.title || 'New Update')}</strong>
                    <small class="text-muted me-2" style="font-size: 0.7rem;">Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body py-2.5 px-3" style="font-size: 0.8rem; color: #44403c;">
                    ${escapeHtml(d.message || '')}
                    ${d.url ? `<div class="mt-2 text-end"><a href="${d.url}" class="btn btn-sm btn-outline-danger fw-700 py-0.5 px-2" style="font-size: 0.72rem;">View Details</a></div>` : ''}
                </div>
            </div>`;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        setTimeout(() => {
            const toastEl = document.getElementById(toastId);
            if (toastEl) toastEl.remove();
        }, 8000);
    }

    // ── Mark All Read ──
    notifMarkAllBtn.addEventListener('click', function () {
        fetch(markAllUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(() => fetchNotifications());
    });

    // Helper: HTML Escaping
    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    // Initial Fetch & Start 10-Second Auto-Polling Loop
    fetchNotifications();
    setInterval(fetchNotifications, 10000);
});
</script>

<style>
.notif-item:hover { background-color: #faf9f6 !important; }
</style>
