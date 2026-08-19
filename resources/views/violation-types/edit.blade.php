@extends('layouts.app')
@section('title', 'Edit Violation Type')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('violation-types.index') }}" style="color:#dc2626;text-decoration:none;">Violation Types</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $violationType->name }}</li>
@endsection

@section('content')

<div class="vt-form-card">

    <div class="vt-form-header">
        <span class="vt-form-icon" style="background:linear-gradient(135deg,#d97706,#b45309);box-shadow:0 3px 10px rgba(180,83,9,.35);">
            <i class="bi bi-pencil-fill" style="color:#fff;font-size:.9rem;"></i>
        </span>
        <div>
            <div class="vt-form-title">Edit Violation Type</div>
            <div class="vt-form-sub">{{ $violationType->name }}</div>
        </div>
    </div>

    <div class="vt-form-body">
        <form method="POST" action="{{ route('violation-types.update', $violationType) }}">
            @csrf @method('PUT')

            @if(Auth::user()->isSuperAdmin())
            <div class="mb-3">
                <label class="vt-label">Assign to LGU <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text vt-ig-icon" style="background:#f0f9ff;border-color:#bae6fd;">
                        <i class="bi bi-building text-primary"></i>
                    </span>
                    <select name="lgu_id" class="form-select vt-input @error('lgu_id') is-invalid @enderror" required>
                        <option value="">— Select Target LGU —</option>
                        @foreach($lgus as $lguItem)
                            <option value="{{ $lguItem->id }}" {{ old('lgu_id', $violationType->lgu_id) == $lguItem->id ? 'selected' : '' }}>
                                {{ $lguItem->name }} ({{ $lguItem->province }})
                            </option>
                        @endforeach
                    </select>
                    @error('lgu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            @endif

            <div class="mb-3">
                <label class="vt-label">Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text vt-ig-icon" style="background:#fff1f2;border-color:#fecdd3;">
                        <i class="bi bi-tag-fill" style="color:#dc2626;"></i>
                    </span>
                    <input type="text" name="name"
                           class="form-control vt-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $violationType->name) }}"
                           required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="vt-label">Description</label>
                <div class="input-group align-items-start">
                    <span class="input-group-text vt-ig-icon" style="background:#eff6ff;border-color:#bfdbfe;">
                        <i class="bi bi-card-text" style="color:#1d4ed8;"></i>
                    </span>
                    <textarea name="description"
                              class="form-control vt-input @error('description') is-invalid @enderror"
                              rows="3">{{ old('description', $violationType->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="vt-label">1st Offense Fine (₱) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text vt-ig-icon" style="background:#f0fdf4;border-color:#86efac;">
                            <i class="bi bi-cash-coin" style="color:#15803d;"></i>
                        </span>
                        <input type="number" name="fine_amount"
                               class="form-control vt-input @error('fine_amount') is-invalid @enderror"
                               value="{{ old('fine_amount', $violationType->fine_amount) }}"
                               min="0" step="0.01">
                        @error('fine_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="vt-label">2nd Offense Fine (₱) <span class="text-muted fw-normal" style="font-size:.72rem;">(Optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text vt-ig-icon" style="background:#fef3c7;border-color:#fde047;">
                            <i class="bi bi-arrow-repeat" style="color:#b45309;"></i>
                        </span>
                        <input type="number" name="fine_amount_2nd"
                               class="form-control vt-input @error('fine_amount_2nd') is-invalid @enderror"
                               value="{{ old('fine_amount_2nd', $violationType->fine_amount_2nd) }}"
                               min="0" step="0.01" placeholder="Leave blank if flat">
                        @error('fine_amount_2nd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="vt-label">3rd Offense Fine (₱) <span class="text-muted fw-normal" style="font-size:.72rem;">(Optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text vt-ig-icon" style="background:#fee2e2;border-color:#fca5a5;">
                            <i class="bi bi-exclamation-octagon text-danger"></i>
                        </span>
                        <input type="number" name="fine_amount_3rd"
                               class="form-control vt-input @error('fine_amount_3rd') is-invalid @enderror"
                               value="{{ old('fine_amount_3rd', $violationType->fine_amount_3rd) }}"
                               min="0" step="0.01" placeholder="Leave blank if flat">
                        @error('fine_amount_3rd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="vt-label">Late Penalty (₱)</label>
                    <div class="input-group">
                        <span class="input-group-text vt-ig-icon" style="background:#fff7ed;border-color:#ffedd5;">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                        </span>
                        <input type="number" name="late_penalty_amount"
                               class="form-control vt-input @error('late_penalty_amount') is-invalid @enderror"
                               value="{{ old('late_penalty_amount', $violationType->late_penalty_amount) }}"
                               min="0" step="0.01">
                        @error('late_penalty_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 pt-2">
                <button type="submit" class="vt-submit-btn">
                    <i class="bi bi-check-lg"></i> Update Type
                </button>
                <a href="{{ route('violation-types.index') }}"
                   class="btn d-inline-flex align-items-center gap-2 rounded-pill"
                   style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;">
                    <i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.vt-form-card {
    max-width: 560px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 6px 24px rgba(0,0,0,.06);
    overflow: hidden;
}
.vt-form-header {
    display: flex; align-items: center; gap: 1rem;
    padding: 1.1rem 1.4rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
    border-bottom: 1.5px solid #ece5da;
}
.vt-form-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.vt-form-title { font-size: .95rem; font-weight: 700; color: #1c1917; }
.vt-form-sub   { font-size: .74rem; color: #a8a29e; margin-top: .1rem; }
.vt-form-body  { padding: 1.4rem; }

.vt-label {
    font-size: .72rem;
    font-weight: 700;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .4rem;
    display: block;
}
.vt-ig-icon {
    border-right: none;
    padding: .45rem .75rem;
    border-radius: 10px 0 0 10px !important;
}
.vt-input {
    border-left: none;
    border-radius: 0 10px 10px 0 !important;
    font-size: .875rem;
}
.vt-input:focus { box-shadow: none; border-color: #e2d9cf; }

.vt-submit-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1.25rem;
    border-radius: 10px;
    font-size: .84rem; font-weight: 700;
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff;
    border: none;
    box-shadow: 0 2px 8px rgba(180,83,9,.3);
    cursor: pointer;
    transition: all .15s;
}
.vt-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(180,83,9,.45); }
</style>

@endsection
