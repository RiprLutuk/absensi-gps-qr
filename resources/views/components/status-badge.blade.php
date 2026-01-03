@php
    $badgeClass =
        $status == 'late'
            ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
            : 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400';
@endphp
<span class="px-2 py-1 {{ $badgeClass }} text-xs font-semibold rounded-lg">
    {{ $status == 'late' ? 'Late' : 'On Time' }}
</span>
