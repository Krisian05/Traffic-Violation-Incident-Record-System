@extends('layouts.app')
@section('title', 'Online GCash Claims')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Online GCash Claims</li>
@endsection

@section('content')
<div class="container-fluid py-4" style="max-width: 1100px; margin: 0 auto;">

    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div style="flex: 1 1 auto; max-width: 760px; min-width: 0;">
            <h4 class="fw-800 text-stone-900 mb-1" style="color: #1c1917; font-family: 'Instrument Sans', sans-serif;">Online GCash Payment Claims</h4>
            <p class="text-muted mb-0" style="font-size: .85rem; line-height: 1.5;">Violators scan their ticket's QR code and submit a GCash reference number after paying. Cross-check each claim against the LGU's actual GCash transaction history before verifying — verifying settles the citation.</p>
        </div>
        <div class="flex-shrink-0">
            <span class="badge d-inline-flex align-items-center gap-1.5 shadow-sm" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: .85rem; font-weight: 700; padding: .55rem 1.1rem; border-radius: 9999px; white-space: nowrap;">
                <i class="bi bi-hourglass-split"></i> <span>{{ $claims->total() }} Awaiting Review</span>
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px; background-color: #f0fdf4; color: #15803d;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px; background-color: #fef2f2; color: #b91c1c;">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1px solid #e7e2db;">
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size: .87rem;">
                <thead style="background-color: #faf9f6; border-bottom: 1px solid #e7e2db;">
                    <tr>
                        <th class="p-3 fw-700 text-muted">Ticket #</th>
                        <th class="p-3 fw-700 text-muted">Violator</th>
                        <th class="p-3 fw-700 text-muted">Claimed Reference</th>
                        <th class="p-3 fw-700 text-muted">Claimed Amount</th>
                        <th class="p-3 fw-700 text-muted">Submitted</th>
                        <th class="p-3 fw-700 text-muted text-center">Proof</th>
                        <th class="p-3 fw-700 text-muted text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr style="border-bottom: 1px solid #f5f0e8;">
                            <td class="p-3 fw-700" style="font-family: ui-monospace, monospace;">{{ $claim->violation->ticket_number }}</td>
                            <td class="p-3 fw-600">{{ $claim->violation->violator?->full_name }}</td>
                            <td class="p-3" style="font-family: ui-monospace, monospace;">{{ $claim->claimed_reference }}</td>
                            <td class="p-3 fw-700 text-success">₱{{ number_format($claim->claimed_amount, 2) }}</td>
                            <td class="p-3 text-muted" style="font-size: .78rem;">{{ $claim->created_at->format('M d, Y g:i A') }}</td>
                            <td class="p-3 text-center">
                                @if($claim->screenshot_path)
                                    <a href="{{ uploaded_file_url($claim->screenshot_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-image"></i>
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <form action="{{ route('payment-claims.verify', $claim) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm you have checked the actual GCash transaction and it matches this claim?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success fw-700 me-1">
                                        <i class="bi bi-check-circle me-1"></i> Verify &amp; Settle
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger fw-700" onclick="openRejectModal('{{ route('payment-claims.reject', $claim) }}')">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-5 text-center text-muted">No online payment claims are currently awaiting review.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($claims->hasPages())
            <div class="p-3 border-top bg-light">
                {{ $claims->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="rejectClaimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="rejectClaimForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-danger text-white border-0 p-4">
                    <h5 class="modal-title fw-800"><i class="bi bi-x-circle me-2"></i> Reject Payment Claim</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size: .82rem;">Reason <span class="text-danger">*</span></label>
                        <textarea name="review_notes" class="form-control" rows="3" placeholder="e.g., no matching transaction found in GCash account" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-700 px-4">Reject Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openRejectModal(actionUrl) {
        document.getElementById('rejectClaimForm').action = actionUrl;
        const modal = new bootstrap.Modal(document.getElementById('rejectClaimModal'));
        modal.show();
    }
</script>
@endpush
@endsection
