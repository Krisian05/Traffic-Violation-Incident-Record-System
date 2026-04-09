@if ($paginator->hasPages())
<nav aria-label="Pagination">
    <style>
        .mob-pager {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .65rem;
            padding: .5rem 0 1rem;
        }
        .mob-pager-info {
            font-size: .72rem;
            color: #64748b;
            font-weight: 600;
        }
        .mob-pager-row {
            display: flex;
            align-items: center;
            gap: .35rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .mob-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 700;
            text-decoration: none;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            color: #475569;
            transition: all .15s;
            padding: 0 .55rem;
        }
        .mob-page-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }
        .mob-page-btn.active {
            background: linear-gradient(135deg,#2563eb,#1d4ed8);
            border-color: #1d4ed8;
            color: #fff;
            box-shadow: 0 3px 10px rgba(37,99,235,.3);
        }
        .mob-page-btn.disabled {
            opacity: .38;
            pointer-events: none;
        }
        .mob-page-btn--nav {
            gap: .3rem;
            padding: 0 .75rem;
            font-size: .8rem;
            font-weight: 700;
        }
        .mob-page-btn-label {
            white-space: nowrap;
        }
        .mob-page-dots {
            font-size: .82rem;
            color: #94a3b8;
            padding: 0 .15rem;
            line-height: 38px;
        }
        @media (max-width: 420px) {
            .mob-pager-row {
                gap: .28rem;
            }
            .mob-page-btn {
                min-width: 34px;
                height: 34px;
                padding: 0 .45rem;
                font-size: .76rem;
            }
            .mob-page-btn--nav {
                gap: 0;
                padding: 0;
            }
            .mob-page-btn-label {
                display: none;
            }
        }
    </style>

    <div class="mob-pager">
        <div class="mob-pager-info">
            Showing <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> results
        </div>

        <div class="mob-pager-row">
            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span class="mob-page-btn mob-page-btn--nav disabled" aria-label="@lang('pagination.previous')">
                    <i class="ph ph-caret-left"></i>
                    <span class="mob-page-btn-label">Prev</span>
                </span>
            @else
                <a class="mob-page-btn mob-page-btn--nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                    <i class="ph ph-caret-left"></i>
                    <span class="mob-page-btn-label">Prev</span>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="mob-page-dots">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="mob-page-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="mob-page-btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a class="mob-page-btn mob-page-btn--nav" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                    <span class="mob-page-btn-label">Next</span>
                    <i class="ph ph-caret-right"></i>
                </a>
            @else
                <span class="mob-page-btn mob-page-btn--nav disabled" aria-label="@lang('pagination.next')">
                    <span class="mob-page-btn-label">Next</span>
                    <i class="ph ph-caret-right"></i>
                </span>
            @endif
        </div>
    </div>
</nav>
@endif
