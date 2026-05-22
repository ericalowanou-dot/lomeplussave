@props(['type' => 'error', 'title' => null, 'message', 'solutions' => []])

@php
    $icons = [
        'error' => 'bi-exclamation-circle',
        'warning' => 'bi-exclamation-triangle',
        'info' => 'bi-info-circle',
        'success' => 'bi-check-circle'
    ];
    
    $colors = [
        'error' => ['bg' => 'bg-danger', 'border' => 'border-danger', 'text' => 'text-danger', 'icon' => '#dc3545'],
        'warning' => ['bg' => 'bg-warning', 'border' => 'border-warning', 'text' => 'text-warning', 'icon' => '#ffc107'],
        'info' => ['bg' => 'bg-info', 'border' => 'border-info', 'text' => 'text-info', 'icon' => '#17a2b8'],
        'success' => ['bg' => 'bg-success', 'border' => 'border-success', 'text' => 'text-success', 'icon' => '#28a745']
    ];
    
    $color = $colors[$type] ?? $colors['error'];
    $icon = $icons[$type] ?? $icons['error'];
@endphp

<div class="error-alert alert-{{ $type }}" role="alert">
    <div class="error-alert-header">
        <i class="bi {{ $icon }}"></i>
        @if($title)
            <strong>{{ $title }}</strong>
        @endif
    </div>
    <div class="error-alert-message">
        {{ $message }}
    </div>
    @if(count($solutions) > 0)
        <div class="error-alert-solutions">
            <strong>Solutions :</strong>
            <ul>
                @foreach($solutions as $solution)
                    <li>{{ $solution }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<style>
.error-alert {
    border-radius: 8px;
    padding: 16px 20px;
    margin: 20px 0;
    border-left: 4px solid;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.error-alert-error {
    border-left-color: #dc3545;
    background: #fff5f5;
}

.error-alert-warning {
    border-left-color: #ffc107;
    background: #fffbf0;
}

.error-alert-info {
    border-left-color: #17a2b8;
    background: #f0f9ff;
}

.error-alert-success {
    border-left-color: #28a745;
    background: #f0fff4;
}

.error-alert-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 16px;
    font-weight: 600;
}

.error-alert-header i {
    font-size: 20px;
}

.error-alert-error .error-alert-header {
    color: #dc3545;
}

.error-alert-warning .error-alert-header {
    color: #856404;
}

.error-alert-info .error-alert-header {
    color: #0c5460;
}

.error-alert-success .error-alert-header {
    color: #155724;
}

.error-alert-message {
    color: #333;
    line-height: 1.6;
    margin-bottom: 12px;
}

.error-alert-solutions {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.error-alert-solutions strong {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-size: 14px;
}

.error-alert-solutions ul {
    margin: 0;
    padding-left: 20px;
    color: #555;
    font-size: 14px;
}

.error-alert-solutions li {
    margin: 6px 0;
    line-height: 1.5;
}
</style>

