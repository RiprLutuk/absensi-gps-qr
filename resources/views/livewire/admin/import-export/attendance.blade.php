<div>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:gap-6">
    @if ($mode != 'import')
      <div>
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Ekspor Data Absensi
        </h3>
        <form wire:submit.prevent="export">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 lg:items-end">
            <div class="flex flex-col gap-1">
              <x-label for="year" value="Per Tahun" />
              <x-input type="number" min="1970" max="2099" name="year" id="year" wire:model.live="year" class="w-full" />
            </div>
            <div class="flex flex-col gap-1">
              <x-label for="month" value="Per Bulan" />
              <x-input type="month" name="month" id="month" wire:model.live="month" class="w-full" />
            </div>
            
            <x-tom-select id="division" name="division" wire:model.live="division" :options="$divisions" placeholder="{{ __('Select Division') }}" />

            <x-tom-select id="jobTitle" name="job_title" wire:model.live="job_title" :options="$jobTitles" placeholder="{{ __('Select Job Title') }}" />

            <x-tom-select id="education" name="education" wire:model.live="education" :options="$educations" placeholder="{{ __('Select Education') }}" />
            
            <div class="flex items-center gap-2">
              @if($previewing && $mode == 'export')
                  <x-secondary-button type="button" wire:click="preview" class="justify-center flex-1">
                    {{ __('Batal') }}
                  </x-secondary-button>
              @endif
              <x-button class="justify-center flex-1" wire:loading.attr="disabled">
                {{ __('Export') }}
              </x-button>
            </div>
          </div>
        </form>
      </div>
    @endif
    @if ($mode != 'export')
      <div>
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Impor Data Absensi
        </h3>
        <form x-data="{ file: null }" wire:submit.prevent="import" method="post" enctype="multipart/form-data">
          @csrf
          <div class="mb-4 flex items-center gap-3">
            <x-secondary-button class="me-2" type="button" x-on:click.prevent="$refs.file.click()"
              x-text="file ? 'Ganti File' : 'Pilih File dan Pratinjau'">
              Pilih File
            </x-secondary-button>
            <x-secondary-button class="me-2" type="button" x-show="file"
              x-on:click.prevent="$refs.file.files[0] = null; file = null; $wire.$set('file', null)">
              Hapus File
            </x-secondary-button>
            <h5 class="text-sm dark:text-gray-200" x-text="file ? file.name : 'File Belum Dipilih'"></h5>
            <x-input type="file" class="hidden" name="file" x-ref="file"
              x-on:change="file = $refs.file.files[0]" wire:model.live="file" />
          </div>
          <div class="flex items-center justify-stretch">
            <x-danger-button class="w-full"
              x-text="file ? '{{ __('Import') }} ' + file.name : '{{ __('Import') }}'">
            </x-danger-button>
          </div>
        </form>
      </div>
    @endif
  </div>
  @if ($mode && $previewing)
    <h3 class="mt-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Preview') . ' ' . $mode }}
    </h3>
    <div class="mt-4 w-full overflow-x-scroll text-sm">
      @php
        $trClass = 'divide-x divide-gray-200 dark:divide-gray-700';
        $thClass = 'px-4 py-3 text-left font-semibold dark:text-white';
        $tdClass = 'px-4 py-4 text-sm font-medium text-gray-900 dark:text-white';
      @endphp
      <table class="w-full divide-y divide-gray-200 border dark:divide-gray-700 dark:border-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr class="{{ $trClass }}">
            <th scope="col" class="px-2 py-3 text-left font-semibold dark:text-white">
              No
            </th>
            <th class="{{ $thClass }}">Date</th>
            <th class="{{ $thClass }}">Name</th>
            <th class="{{ $thClass }}">NIP</th>
            <th class="{{ $thClass }} text-nowrap">Time In</th>
            <th class="{{ $thClass }} text-nowrap">Time Out</th>
            <th class="{{ $thClass }}">Shift</th>
            <th class="{{ $thClass }} text-nowrap">Barcode Id</th>
            <th class="{{ $thClass }}">Coordinates</th>
            <th class="{{ $thClass }}">Status</th>
            <th class="{{ $thClass }}">Note</th>
            <th class="{{ $thClass }}">Attachment</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
          @foreach ($attendances as $attendance)
            <tr class="{{ $trClass }}">
              <td class="px-2 py-4 text-center text-sm font-medium text-gray-900 dark:text-white">
                {{ $loop->iteration }}
              </td>
              <td class="{{ $tdClass }} text-nowrap">{{ $attendance->date?->format('Y-m-d') }}</td>
              <td class="{{ $tdClass }}">{{ $attendance->user?->name }}</td>
              <td class="{{ $tdClass }}">{{ $attendance->user?->nip }}</td>
              <td class="{{ $tdClass }}">{{ $attendance->time_in?->format('H:i:s') }}</td>
              <td class="{{ $tdClass }}">{{ $attendance->time_out?->format('H:i:s') }}</td>
              <td class="{{ $tdClass }} text-nowrap">{{ $attendance->shift?->name }}</td>
              <td class="{{ $tdClass }}">{{ $attendance->barcode_id }}</td>
              <td class="{{ $tdClass }}">
                @if($attendance->latitude_in && $attendance->longitude_in)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_in }},{{ $attendance->longitude_in }}" target="_blank" class="text-blue-600 hover:text-blue-900 underline">IN</a>
                @endif
                @if($attendance->latitude_out && $attendance->longitude_out)
                    {{ ($attendance->latitude_in ? ' | ' : '') }}
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_out }},{{ $attendance->longitude_out }}" target="_blank" class="text-blue-600 hover:text-blue-900 underline">OUT</a>
                @endif
              </td>
              <td class="{{ $tdClass }} text-nowrap">{{ __($attendance->status) }}</td>
              <td class="{{ $tdClass }}">
                <div class="w-48">{{ Str::limit($attendance->note, 30, '...') }}</div>
              </td>
              <td class="{{ $tdClass }}">
                @if ($attendance->attachment_url)
                    <a href="{{ $attendance->attachment_url }}" target="_blank">
                        <img src="{{ $attendance->attachment_url }}" class="max-h-16 object-contain rounded">
                    </a>
                @else
                    -
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
