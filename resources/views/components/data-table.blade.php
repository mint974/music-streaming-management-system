{{--
    Reusable data table component — Bootstrap 5 dark theme.

    Usage:
        <x-data-table
            :headers="[
                ['label'=>'#',       'class'=>'ps-3', 'style'=>'width:46px'],
                ['label'=>'Tiêu đề'],
                ['label'=>'Hành động', 'class'=>'text-end pe-3', 'style'=>'width:120px'],
            ]"
            :isEmpty="$items->isEmpty()"
            emptyIcon="fa-table"
            emptyText="Không có dữ liệu."
        >
            @foreach($items as $item)
                <tr class="border-secondary border-opacity-25">
                    ...
                </tr>
            @endforeach

            <x-slot:pagination>
                {{ $items->links('pagination::bootstrap-5') }}
            </x-slot:pagination>
        </x-data-table>
--}}
@props([
    'headers'   => [],
    'isEmpty'   => false,
    'emptyIcon' => 'fa-table',
    'emptyText' => 'Không có dữ liệu nào.',
])

<div class="card bg-dark border-secondary border-opacity-25">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    @foreach($headers as $h)
                        <th class="text-muted fw-normal small {{ $h['class'] ?? '' }}"
                            @isset($h['style']) style="{{ $h['style'] }}" @endisset>
                            {{ $h['label'] ?? '' }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if($isEmpty)
                    <tr>
                        <td colspan="{{ count($headers) }}" class="text-center text-muted py-5">
                            <i class="fa-solid {{ $emptyIcon }} fa-2x mb-3 opacity-25 d-block"></i>
                            {{ $emptyText }}
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    @isset($pagination)
        <div class="card-footer bg-transparent border-secondary border-opacity-25 py-3">
            {{ $pagination }}
        </div>
    @endisset
</div>
