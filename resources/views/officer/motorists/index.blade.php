@extends('layouts.mobile')
@section('title', 'Motorists')

@push('styles')
<style>
.mot-search-wrap {
    position: relative;
    margin-bottom: 1rem;
}
.mot-search-shell {
    background: #fff;
    border: 1px solid rgba(15,23,42,.05);
    border-radius: 18px;
    padding: .72rem;
    box-shadow: 0 6px 22px rgba(15,23,42,.07);
}
.mot-search-bar {
    display: flex;
    align-items: center;
    gap: .65rem;
    background: #f8fafc;
    border: 1px solid #dbe5f1;
    border-radius: 16px;
    padding: .2rem .22rem .2rem .8rem;
}
.mot-search-icon {
    color: #64748b;
    font-size: 1rem;
    flex-shrink: 0;
}
.mot-search-input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    min-height: 44px;
    font-size: .92rem;
    padding: 0 !important;
}
.mot-search-input::placeholder {
    color: #94a3b8;
}
.mot-search-submit {
    border: none;
    border-radius: 14px;
    min-height: 44px;
    padding: 0 1rem;
    font-size: .84rem;
    font-weight: 800;
    color: #fff;
    background: linear-gradient(135deg,#1d4ed8,#1e40af);
    box-shadow: 0 6px 16px rgba(29,78,216,.25);
    flex-shrink: 0;
}
.mot-search-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .6rem;
    margin-top: .7rem;
    flex-wrap: wrap;
}
.mot-search-pill {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    padding: .24rem .62rem;
    font-size: .68rem;
    font-weight: 700;
}
.mot-search-count {
    font-size: .72rem;
    color: #64748b;
    font-weight: 600;
}
.mot-search-dropdown {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% - .15rem);
    background: #fff;
    border: 1px solid rgba(15,23,42,.06);
    border-radius: 18px;
    box-shadow: 0 14px 38px rgba(15,23,42,.13);
    overflow: hidden;
    z-index: 40;
    display: none;
}
.mot-search-dropdown.open {
    display: block;
}
.mot-suggest-item,
.mot-suggest-empty {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .85rem .95rem;
    text-decoration: none;
}
.mot-suggest-item {
    color: inherit;
    border-bottom: 1px solid #f1f5f9;
}
.mot-suggest-item:last-child {
    border-bottom: none;
}
.mot-suggest-item:hover {
    background: #f8fbff;
}
.mot-suggest-avatar {
    width: 42px;
    height: 42px;
    border-radius: 13px;
    background: linear-gradient(135deg,#1d4ed8,#1e40af);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .88rem;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(29,78,216,.22);
}
.mot-suggest-title {
    font-size: .86rem;
    font-weight: 800;
    color: #0f172a;
}
.mot-suggest-sub {
    font-size: .7rem;
    color: #64748b;
    margin-top: .08rem;
    line-height: 1.45;
}
.mot-suggest-tag {
    display: inline-flex;
    align-items: center;
    gap: .2rem;
    border-radius: 999px;
    padding: .12rem .45rem;
    font-size: .58rem;
    font-weight: 800;
    margin-top: .3rem;
}
.mot-suggest-tag--warn {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fcd34d;
}
.mot-suggest-tag--danger {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}
.mot-suggest-vcount {
    color: #1d4ed8;
    font-size: .66rem;
    font-weight: 800;
    flex-shrink: 0;
    margin-left: auto;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    padding: .16rem .46rem;
}
.mot-result-card {
    display: flex;
    align-items: center;
    gap: .9rem;
    background: #fff;
    border-radius: 18px;
    padding: 1rem 1rem 1rem .95rem;
    text-decoration: none;
    color: inherit;
    margin-bottom: .72rem;
    border: 1px solid rgba(15,23,42,.045);
    box-shadow: 0 3px 14px rgba(15,23,42,.06);
    position: relative;
    overflow: hidden;
}
.mot-result-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg,#1d4ed8,#1e40af);
}
.mot-result-avatar {
    width: 48px;
    height: 48px;
    border-radius: 15px;
    background: linear-gradient(135deg,#1d4ed8,#1e40af);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 5px 16px rgba(29,78,216,.26);
}
.mot-result-name {
    font-size: .92rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
}
.mot-result-meta {
    font-size: .72rem;
    color: #64748b;
    margin-top: .14rem;
    line-height: 1.45;
}
.mot-status-tag {
    display: inline-flex;
    align-items: center;
    gap: .22rem;
    border-radius: 999px;
    padding: .14rem .48rem;
    font-size: .58rem;
    font-weight: 800;
    margin-top: .34rem;
}
.mot-status-tag--repeat {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fcd34d;
}
.mot-status-tag--recidivist {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}
.mot-offline-shell {
    margin-bottom: 1rem;
}
.mot-offline-lead {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .9rem 1rem;
    border-radius: 18px;
    background: #fff7ed;
    border: 1px solid #fdba74;
    color: #9a3412;
    margin-bottom: .75rem;
}
.mot-offline-lead i {
    font-size: 1rem;
    margin-top: .08rem;
    flex-shrink: 0;
}
.mot-offline-title {
    font-size: .8rem;
    font-weight: 800;
    color: #7c2d12;
}
.mot-offline-sub {
    font-size: .7rem;
    line-height: 1.5;
    margin-top: .15rem;
}
.mot-offline-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid rgba(15,23,42,.05);
    box-shadow: 0 4px 18px rgba(15,23,42,.06);
    padding: 1rem;
    margin-bottom: .72rem;
}
.mot-offline-head {
    display: flex;
    align-items: center;
    gap: .85rem;
}
.mot-offline-avatar {
    width: 48px;
    height: 48px;
    border-radius: 15px;
    background: linear-gradient(135deg,#0f766e,#115e59);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    font-weight: 800;
    box-shadow: 0 6px 18px rgba(15,118,110,.22);
    flex-shrink: 0;
}
.mot-offline-name {
    font-size: .92rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
}
.mot-offline-meta {
    font-size: .72rem;
    color: #64748b;
    line-height: 1.45;
    margin-top: .16rem;
}
.mot-offline-tags {
    display: flex;
    flex-wrap: wrap;
    gap: .38rem;
    margin-top: .52rem;
}
.mot-offline-tag {
    display: inline-flex;
    align-items: center;
    gap: .24rem;
    border-radius: 999px;
    padding: .16rem .52rem;
    font-size: .6rem;
    font-weight: 800;
}
.mot-offline-tag--pending {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.mot-offline-tag--failed {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}
.mot-offline-actions {
    display: flex;
    gap: .55rem;
    margin-top: .85rem;
}
.mot-offline-btn {
    flex: 1;
    min-height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    text-decoration: none;
    font-size: .78rem;
    font-weight: 800;
}
.mot-offline-btn--primary {
    background: linear-gradient(135deg,#dc2626,#b91c1c);
    color: #fff;
    box-shadow: 0 8px 18px rgba(220,38,38,.18);
}
.mot-offline-btn--ghost {
    border: 1px solid #dbe5f1;
    background: #f8fafc;
    color: #334155;
}
.mot-pagination-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}
.mot-fab-lower {
    bottom: calc(var(--nav-h) + var(--bot-h) + .55rem);
}
</style>
@endpush

@section('content')

<div class="motshow-section">Quick Search</div>

<div class="mot-search-wrap">
    <form method="GET" action="{{ route('officer.motorists.index') }}" id="motoristSearchForm" class="mot-search-shell">
        <div class="mot-search-bar">
            <i class="ph ph-magnifying-glass mot-search-icon"></i>
            <input
                type="text"
                name="search"
                id="motoristSearchInput"
                value="{{ $search }}"
                class="form-control mot-search-input"
                placeholder="Type name or license number..."
                autocomplete="off"
            >
            <button class="mot-search-submit" type="submit">Search</button>
        </div>

        <div class="mot-search-meta">
            <div class="d-flex gap-2 flex-wrap">
                <span class="mot-search-pill">
                    <i class="ph ph-lightning"></i>
                    Live suggestions
                </span>
                @if($search)
                    <a href="{{ route('officer.motorists.index') }}" class="mot-search-pill" style="text-decoration:none;background:#fff1f2;color:#dc2626;border-color:#fecdd3;">
                        <i class="ph ph-x-circle"></i>
                        Clear “{{ $search }}”
                    </a>
                @endif
            </div>

            <div class="mot-search-count">
                {{ $violators->total() }} motorist{{ $violators->total() !== 1 ? 's' : '' }} matched
            </div>
        </div>
    </form>

<div id="motoristSearchDropdown" class="mot-search-dropdown"></div>
</div>

<div id="offlineMotoristShell" class="mot-offline-shell" style="display:none;">
    <div class="motshow-section">Queued Offline</div>
    <div class="mot-offline-lead">
        <i class="ph-fill ph-wifi-slash"></i>
        <div>
            <div class="mot-offline-title">Unsynced motorists saved on this device</div>
            <div class="mot-offline-sub">You can still record a violation for them right now. The motorist syncs first, then the linked violation follows automatically.</div>
        </div>
    </div>
    <div id="offlineMotoristList"></div>
</div>

<div class="motshow-section">Motorist Records</div>

@if($violators->isEmpty())
    <div class="mob-card">
        <div class="mob-empty">
            <i class="ph ph-users mob-empty-icon"></i>
            <div class="mob-empty-text">No motorists found</div>
            @if($search)
            <div class="mob-empty-sub">
                <a href="{{ route('officer.motorists.index') }}" style="color:#1d4ed8;font-weight:700;text-decoration:none;">Clear search</a>
            </div>
            @endif
        </div>
    </div>
@else
    @foreach($violators as $v)
    <a href="{{ route('officer.motorists.show', $v) }}" class="mot-result-card">
        <div class="mot-result-avatar">
            {{ strtoupper(substr($v->first_name, 0, 1) . substr($v->last_name, 0, 1)) }}
        </div>
        <div style="flex:1;min-width:0;">
            <div class="mot-result-name">
                {{ $v->last_name }}, {{ $v->first_name }}
                @if($v->middle_name) {{ substr($v->middle_name, 0, 1) }}. @endif
            </div>
            <div class="mot-result-meta">
                @if($v->license_number)
                    <i class="ph ph-identification-badge me-1"></i>{{ $v->license_number }}
                @else
                    <span style="color:#94a3b8;">No license on file</span>
                @endif
                <span style="margin:0 .34rem;color:#cbd5e1;">·</span>
                {{ $v->violations_count }} violation{{ $v->violations_count !== 1 ? 's' : '' }}
            </div>

            @if($v->violations_count >= 3)
                <span class="mot-status-tag mot-status-tag--recidivist">
                    <i class="ph-fill ph-fire"></i> Recidivist
                </span>
            @elseif($v->violations_count === 2)
                <span class="mot-status-tag mot-status-tag--repeat">
                    <i class="ph-fill ph-shield-warning"></i> Repeat Offender
                </span>
            @endif
        </div>
        <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.88rem;flex-shrink:0;"></i>
    </a>
    @endforeach

    @if($violators->hasPages())
    <div class="mot-pagination-wrap">
        {{ $violators->links('vendor.pagination.bootstrap-5') }}
    </div>
    @endif
@endif

<a href="{{ route('officer.motorists.create') }}" class="mob-fab mot-fab-lower" title="Add Motorist">
    <i class="ph-bold ph-plus"></i>
</a>

@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('motoristSearchInput');
    const dropdown = document.getElementById('motoristSearchDropdown');
    const url = "{{ route('officer.motorists.suggestions') }}";
    let timer = null;
    let controller = null;

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const hideDropdown = () => {
        dropdown.classList.remove('open');
        dropdown.innerHTML = '';
    };

    const renderSuggestions = (results) => {
        if (!results.length) {
            dropdown.innerHTML = `
                <div class="mot-suggest-empty">
                    <div class="mot-suggest-avatar"><i class="ph ph-magnifying-glass"></i></div>
                    <div>
                        <div class="mot-suggest-title">No motorists found</div>
                        <div class="mot-suggest-sub">Try another name or license number.</div>
                    </div>
                </div>
            `;
            dropdown.classList.add('open');
            return;
        }

        dropdown.innerHTML = results.map((item) => {
            const statusTag = item.status
                ? `<span class="mot-suggest-tag ${item.status === 'Recidivist' ? 'mot-suggest-tag--danger' : 'mot-suggest-tag--warn'}">
                        <i class="ph-fill ${item.status === 'Recidivist' ? 'ph-fire' : 'ph-shield-warning'}"></i>
                        ${escapeHtml(item.status)}
                   </span>`
                : '';

            return `
                <a href="${escapeHtml(item.url)}" class="mot-suggest-item">
                    <div class="mot-suggest-avatar">${escapeHtml(item.initials)}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="mot-suggest-title">${escapeHtml(item.label)}</div>
                        <div class="mot-suggest-sub">${escapeHtml(item.sub)}</div>
                        ${statusTag}
                    </div>
                    <div class="mot-suggest-vcount">${escapeHtml(item.violations_count)} case${item.violations_count === 1 ? '' : 's'}</div>
                </a>
            `;
        }).join('');

        dropdown.classList.add('open');
    };

    const search = (query) => {
        if (controller) controller.abort();
        controller = new AbortController();

        fetch(url + '?q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' },
            signal: controller.signal,
        })
            .then((response) => response.ok ? response.json() : [])
            .then((data) => renderSuggestions(Array.isArray(data) ? data : []))
            .catch((error) => {
                if (error.name !== 'AbortError') hideDropdown();
            });
    };

    input.addEventListener('input', () => {
        const query = input.value.trim();
        clearTimeout(timer);

        if (query.length < 2) {
            hideDropdown();
            return;
        }

        timer = setTimeout(() => search(query), 220);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') {
            dropdown.classList.add('open');
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.mot-search-wrap')) {
            hideDropdown();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideDropdown();
        }
    });
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var shell = document.getElementById('offlineMotoristShell');
    var list = document.getElementById('offlineMotoristList');
    var searchInput = document.getElementById('motoristSearchInput');
    var escapeHtml = function (value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    function renderOfflineMotorists() {
        if (!window.TvirsOffline || typeof window.TvirsOffline.listOfflineMotorists !== 'function') {
            shell.style.display = 'none';
            list.innerHTML = '';
            return;
        }

        window.TvirsOffline.listOfflineMotorists().then(function (motorists) {
            var query = String(searchInput && searchInput.value ? searchInput.value : '').trim().toLowerCase();
            var filtered = (motorists || []).filter(function (motorist) {
                if (!query) {
                    return true;
                }

                return String(motorist.summary && motorist.summary.searchText || '').indexOf(query) !== -1;
            });

            if (!filtered.length) {
                shell.style.display = 'none';
                list.innerHTML = '';
                return;
            }

            shell.style.display = '';
            list.innerHTML = filtered.map(function (motorist) {
                var metaParts = [];
                if (motorist.summary.licenseNumber) metaParts.push('License ' + escapeHtml(motorist.summary.licenseNumber));
                if (motorist.summary.address) metaParts.push(escapeHtml(motorist.summary.address));
                if (!metaParts.length) metaParts.push('Saved only in the local offline queue');

                var stateTag = motorist.state === 'failed'
                    ? '<span class="mot-offline-tag mot-offline-tag--failed"><i class="ph-fill ph-warning-octagon"></i>Needs Review</span>'
                    : '<span class="mot-offline-tag mot-offline-tag--pending"><i class="ph-fill ph-cloud-arrow-up"></i>Pending Sync</span>';

                var violationTag = motorist.queuedViolations > 0
                    ? '<span class="mot-offline-tag mot-offline-tag--pending"><i class="ph-fill ph-files"></i>' + motorist.queuedViolations + ' queued violation' + (motorist.queuedViolations === 1 ? '' : 's') + '</span>'
                    : '';

                var reviewText = motorist.lastError
                    ? '<div class="mot-offline-meta" style="color:#b91c1c;margin-top:.45rem;">' + escapeHtml(motorist.lastError) + '</div>'
                    : '';

                return '' +
                    '<div class="mot-offline-card">' +
                        '<div class="mot-offline-head">' +
                            '<div class="mot-offline-avatar">' + escapeHtml(motorist.summary.initials || 'OF') + '</div>' +
                            '<div style="flex:1;min-width:0;">' +
                                '<div class="mot-offline-name">' + escapeHtml(motorist.summary.displayName || 'Unnamed Motorist') + '</div>' +
                                '<div class="mot-offline-meta">' + metaParts.join(' &bull; ') + '</div>' +
                                '<div class="mot-offline-tags">' + stateTag + violationTag + '</div>' +
                                reviewText +
                            '</div>' +
                        '</div>' +
                        '<div class="mot-offline-actions">' +
                            '<a href="' + escapeHtml(window.TvirsOffline.buildOfflineViolationHref(motorist.offlineMotoristKey)) + '" class="mot-offline-btn mot-offline-btn--primary">' +
                                '<i class="ph-fill ph-file-plus"></i>Record Violation' +
                            '</a>' +
                            '<a href="{{ route('officer.motorists.create') }}" class="mot-offline-btn mot-offline-btn--ghost">' +
                                '<i class="ph ph-user-plus"></i>New Motorist' +
                            '</a>' +
                        '</div>' +
                    '</div>';
            }).join('');
        }).catch(function () {
            shell.style.display = 'none';
            list.innerHTML = '';
        });
    }

    renderOfflineMotorists();
    window.addEventListener('tvirs-offline-updated', renderOfflineMotorists);

    if (searchInput) {
        searchInput.addEventListener('input', renderOfflineMotorists);
    }
});
</script>
@endpush
