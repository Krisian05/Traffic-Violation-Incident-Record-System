@extends('layouts.app')

@section('title', '404 — Page Not Found')

@section('content')
<style>
.err-wrapper {
    min-height: 75vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem;
    position: relative;
}
.err-card {
    width: 100%;
    max-width: 520px;
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid rgba(217, 119, 6, 0.18);
    box-shadow: 0 20px 45px -10px rgba(217, 119, 6, 0.09), 0 4px 16px rgba(15, 23, 42, 0.04);
    padding: 2.25rem 1.75rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.err-hero-glow {
    position: absolute;
    top: -60px;
    left: 50%;
    transform: translateX(-50%);
    width: 220px;
    height: 140px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}
.err-badge-container {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.err-pulse-ring {
    position: absolute;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.15);
    animation: err-pulse 2.2s ease-out infinite;
}
@keyframes err-pulse {
    0% { transform: scale(0.85); opacity: 0.8; }
    50% { transform: scale(1.15); opacity: 0.35; }
    100% { transform: scale(1.35); opacity: 0; }
}
.err-icon-box {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 8px 24px rgba(217, 119, 6, 0.35);
    position: relative;
    z-index: 1;
}
.err-code-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 0.65rem;
}
.err-title {
    font-size: 1.65rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin-bottom: 0.4rem;
}
.err-subtitle {
    font-size: 0.9rem;
    color: #64748b;
    line-height: 1.55;
    margin-bottom: 1.5rem;
}
.err-btn-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}
.err-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.75rem 1rem;
    border-radius: 14px;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
    cursor: pointer;
    border: none;
}
.err-btn-secondary {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.err-btn-secondary:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.err-btn-secondary:active {
    transform: scale(0.97);
}
.err-btn-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.32);
}
.err-btn-primary:hover {
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.42);
}
.err-btn-primary:active {
    transform: scale(0.97);
}

@media (max-width: 480px) {
    .err-card { padding: 1.75rem 1.25rem; }
    .err-btn-grid { grid-template-columns: 1fr; }
}
</style>

<div class="err-wrapper">
    <div class="err-card">
        <div class="err-hero-glow"></div>

        {{-- Icon --}}
        <div class="err-badge-container">
            <div class="err-pulse-ring"></div>
            <div class="err-icon-box">
                <i class="bi bi-file-earmark-x-fill" style="font-size: 2.1rem;"></i>
            </div>
        </div>

        {{-- Status Chip & Header --}}
        <div>
            <div class="err-code-chip">
                <i class="bi bi-exclamation-circle-fill"></i> Error 404 · Page Not Found
            </div>
            <h1 class="err-title">Record or Page Not Found</h1>
            <p class="err-subtitle">
                The page, ticket, or record you are looking for does not exist, was moved, or has been removed from the system.
            </p>
        </div>

        {{-- Actions --}}
        <div class="err-btn-grid">
            <button onclick="window.history.back()" class="err-btn err-btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Go Back
            </button>
            <a href="{{ route('dashboard') }}" class="err-btn err-btn-primary">
                <i class="bi bi-house-door-fill me-1"></i> Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
