<x-app-layout>


    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
             @livewire('scan-component')
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session()->has('flash.banner'))
                Swal.fire({
                    icon: 'success',
                    title: "{{ __('Success!') }}",
                    text: "{{ session('flash.banner') }}",
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: "{{ __('OK') }}"
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
