@extends('layouts.app')

@section('title', '403 — Access Denied')

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
    border: 1px solid rgba(239, 68, 68, 0.15);
    box-shadow: 0 20px 45px -10px rgba(225, 29, 72, 0.09), 0 4px 16px rgba(15, 23, 42, 0.04);
    padding: 2.25rem 1.75rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.err-hero-glow {
    position: absolute;
    top: -60px;
    left: 50%;
    transform: translateX(-50%);
    width: 220px;
    height: 140px;
    background: radial-gradient(circle, rgba(239, 68, 68, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
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
    background: rgba(239, 68, 68, 0.12);
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
    background: linear-gradient(135deg, #ef4444 0%, #be123c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 8px 24px rgba(225, 29, 72, 0.35);
    position: relative;
    z-index: 1;
}
.err-code-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #fef2f2;
    color: #e11d48;
    border: 1px solid #fecdd3;
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
    margin-bottom: 1.4rem;
}
.err-explainer {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #ef4444;
    border-radius: 16px;
    padding: 1.1rem 1.15rem;
    text-align: left;
    margin-bottom: 1.5rem;
}
.err-explainer-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.6rem;
}
.err-explainer-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.err-explainer-item {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    font-size: 0.82rem;
    color: #475569;
    line-height: 1.45;
}
.err-explainer-item i {
    font-size: 0.95rem;
    color: #ef4444;
    flex-shrink: 0;
    margin-top: 0.1rem;
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
                <i class="bi bi-shield-lock-fill" style="font-size: 2.1rem;"></i>
            </div>
        </div>

        {{-- Status Chip & Header --}}
        <div>
            <div class="err-code-chip">
                <i class="bi bi-lock-fill"></i> Error 403 · Access Denied
            </div>
            <h1 class="err-title">Access Restricted</h1>
            <p class="err-subtitle">
                {{ $exception->getMessage() ?: 'You do not have sufficient permissions or role privileges to view this area or perform this action.' }}
            </p>
        </div>

        {{-- Explainer Box --}}
        <div class="err-explainer">
            <div class="err-explainer-title">
                <i class="bi bi-info-circle-fill" style="color: #d97706;"></i>
                <span>Why am I seeing this?</span>
            </div>
            <ul class="err-explainer-list">
                <li class="err-explainer-item">
                    <i class="bi bi-person-badge"></i>
                    <span>Your current account role (<strong>{{ Auth::check() ? Auth::user()->role_label : 'Guest' }}</strong>) is not authorized for this specific feature or action.</span>
                </li>
                <li class="err-explainer-item">
                    <i class="bi bi-geo-alt"></i>
                    <span>This operation or record may be restricted to a different Municipality/LGU.</span>
                </li>
                <li class="err-explainer-item">
                    <i class="bi bi-eye"></i>
                    <span>Read-only user accounts (e.g. Auditor) cannot modify, edit, or delete records.</span>
                </li>
            </ul>
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
