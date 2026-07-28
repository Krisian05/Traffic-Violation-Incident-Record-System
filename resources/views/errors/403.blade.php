@extends('layouts.app')

@section('title', '403 — Access Denied')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="card shadow-sm border-0 text-center" style="max-width: 540px; border-radius: 16px; background: #fffdf9; border: 1px solid #ddd0be;">
        <div class="card-body p-5">
            {{-- Shield Icon Badge --}}
            <div class="mb-4 d-inline-flex align-items-center justify-content-center" 
                 style="width: 84px; height: 84px; border-radius: 50%; background: #fef2f2; border: 2px solid #fecaca; color: #dc2626;">
                <i class="bi bi-shield-lock-fill" style="font-size: 2.5rem;"></i>
            </div>

            {{-- Title & Message --}}
            <h1 class="fw-800 text-dark mb-2" style="font-size: 2.25rem; letter-spacing: -0.02em;">403 Forbidden</h1>
            <h5 class="fw-700 text-danger mb-3">Access Denied</h5>
            
            <p class="text-secondary mb-4" style="font-size: 0.95rem; line-height: 1.6;">
                {{ $exception->getMessage() ?: 'You do not have sufficient permissions or role privileges to access this resource or perform this action.' }}
            </p>

            {{-- Role Context Explainer Box --}}
            <div class="p-3 text-start mb-4" style="background: #fdf8f0; border-radius: 10px; border: 1px solid #ede8df; font-size: 0.85rem; color: #57534e;">
                <div class="d-flex align-items-center gap-2 fw-700 text-dark mb-1">
                    <i class="bi bi-info-circle-fill text-amber" style="color: #d97706;"></i>
                    <span>Why am I seeing this?</span>
                </div>
                <ul class="mb-0 ps-3 mt-1 text-muted" style="line-height: 1.5;">
                    <li>Your account role (<strong>{{ Auth::check() ? Auth::user()->role_label : 'Guest' }}</strong>) may not be authorized for this area.</li>
                    <li>This operation may be restricted to a specific Municipality/LGU.</li>
                    <li>Read-only accounts (Auditor/View-Only) cannot create, edit, or delete records.</li>
                </ul>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex align-items-center justify-content-center gap-3">
                <button onclick="window.history.back()" class="btn btn-outline-secondary px-4 py-2 fw-600" style="border-radius: 8px;">
                    <i class="bi bi-arrow-left me-1"></i> Go Back
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-primary px-4 py-2 fw-600" style="border-radius: 8px; background: #1d4ed8; border-color: #1d4ed8;">
                    <i class="bi bi-house-door-fill me-1"></i> Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
