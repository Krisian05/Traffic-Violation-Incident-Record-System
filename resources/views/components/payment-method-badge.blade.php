@props(['method' => 'cash'])

@php
    $pmDetails = [
        'cash'  => ['label'=>'Cash',         'icon'=>'bi-cash-stack',      'cls'=>'bg-success text-white'],
        'gcash' => ['label'=>'GCash',        'icon'=>'bi-phone-fill',       'cls'=>'bg-primary text-white'],
        'maya'  => ['label'=>'Maya',         'icon'=>'bi-credit-card-fill', 'cls'=>'bg-purple text-white'],
        'bank'  => ['label'=>'Bank Transfer','icon'=>'bi-bank2',            'cls'=>'bg-warning text-dark'],
        'other' => ['label'=>'Other',        'icon'=>'bi-three-dots',       'cls'=>'bg-secondary text-white'],
    ][strtolower($method)] ?? ['label'=>ucfirst($method ?: 'Cash'), 'icon'=>'bi-credit-card', 'cls'=>'bg-light text-dark'];
@endphp

<span class="badge {{ $pmDetails['cls'] }} px-2.5 py-1" style="font-size: .75rem;">
    <i class="{{ $pmDetails['icon'] }} me-1"></i> {{ $pmDetails['label'] }}
</span>
