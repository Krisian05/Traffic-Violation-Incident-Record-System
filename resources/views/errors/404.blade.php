@extends('layouts.app')

@section('title', '404 — Page Not Found')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="card shadow-sm border-0 text-center" style="max-width: 540px; border-radius: 16px; background: #fffdf9; border: 1px solid #ddd0be;">
        <div class="card-body p-5">
            {{-- Search/File Icon Badge --}}
            <div class="mb-4 d-inline-flex align-items-center justify-content-center" 
                 style="width: 84px; height: 84px; border-radius: 50%; background: #fef3c7; border: 2px solid #fde68a; color: #d97706;">
                <i class="bi bi-file-earmark-x-fill" style="font-size: 2.5rem;"></i>
            </div>

            {{-- Title & Message --}}
            <h1 class="fw-800 text-dark mb-2" style="font-size: 2.25rem; letter-spacing: -0.02em;">404 Not Found</h1>
            <h5 class="fw-700 text-amber mb-3" style="color: #b45309;">Page or Record Not Found</h5>
            
            <p class="text-secondary mb-4" style="font-size: 0.95rem; line-height: 1.6;">
                The page, ticket, or record you are looking for does not exist, was moved, or has been removed.
            </p>

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
