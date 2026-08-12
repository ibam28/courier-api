@php
    $lvl = $c->level ?? '-';
    $status = $c->status ?? 'active';
    $joined = $c->joined_at ? $c->joined_at->format('Y-m-d') : '-';
@endphp
<tr data-id="{{ $c->id }}">
    <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $c->code ?? '' }}</td>
    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $c->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $c->phone ?: '-' }}</td>
    <td class="px-4 py-3 text-sm text-gray-600">
        {{ $c->vehicle_type ?: '-' }}
        @if ($c->vehicle_plate)
            <span class="text-xs text-gray-400">{{ $c->vehicle_plate }}</span>
        @endif
    </td>
    <td class="px-4 py-3"><span class="px-2 py-1 text-xs font-semibold rounded lvl-{{ $lvl }}">L{{ $lvl }}</span></td>
    <td class="px-4 py-3"><span class="px-2 py-1 text-xs font-semibold rounded status-{{ $status }}">{{ $status }}</span></td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $joined }}</td>
    <td class="px-4 py-3 text-right text-sm">
        <button onclick="openEdit({{ json_encode($c) }})" class="text-blue-600 hover:text-blue-800 font-medium mr-2">Edit</button>
        <button onclick="deleteCourier({{ $c->id }})" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
    </td>
</tr>