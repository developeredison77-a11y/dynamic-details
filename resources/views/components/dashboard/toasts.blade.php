@php
    $toastMessages = collect([
        session('status') ? ['type' => 'success', 'message' => session('status')] : null,
        session('success') ? ['type' => 'success', 'message' => session('success')] : null,
        session('error') ? ['type' => 'error', 'message' => session('error')] : null,
        session('warning') ? ['type' => 'warning', 'message' => session('warning')] : null,
        session('info') ? ['type' => 'info', 'message' => session('info')] : null,
    ])->filter();
@endphp

@if ($toastMessages->isNotEmpty())
    <div class="toast-stack" data-toast-stack aria-live="polite" aria-atomic="true">
        @foreach ($toastMessages as $toast)
            <div class="toast toast-{{ $toast['type'] }}" data-toast>
                <span class="toast-mark">
                    <x-dashboard.icon :name="$toast['type'] === 'success' ? 'dashboard' : 'settings'" />
                </span>
                <div class="toast-copy">
                    <strong>{{ ucfirst($toast['type']) }}</strong>
                    <p>{{ $toast['message'] }}</p>
                </div>
                <button type="button" class="toast-close action-icon-btn action-icon-neutral" data-toast-close aria-label="Close notification" data-tooltip="Close"><x-dashboard.icon name="x" /></button>
            </div>
        @endforeach
    </div>
@endif
