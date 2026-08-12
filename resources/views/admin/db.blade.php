<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DB Inspector — {{ $driver }} / {{ $database }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .sql-type { background:#eef2ff; color:#3730a3; }
        .pk { background:#fef9c3; color:#854d0e; }
        .null-yes { color:#9ca3af; font-style:italic; }
        .null-no  { color:#16a34a; font-weight:600; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <header class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">🗄️ Database Inspector</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Driver <span class="font-mono px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded">{{ $driver }}</span>
                    Database <span class="font-mono px-2 py-0.5 bg-gray-200 rounded">{{ $database }}</span>
                </p>
                <p class="mt-1 text-xs text-gray-500">Read-only. {{ count($tables) }} tables.</p>
            </div>
            <a href="/couriers" class="text-sm text-blue-600 hover:underline">← back to /couriers</a>
        </div>
    </header>

    @foreach ($tables as $t)
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <div>
                    <h2 class="text-lg font-semibold font-mono">{{ $t['name'] }}</h2>
                    <p class="text-xs text-gray-500">
                        {{ $t['row_count'] }} rows
                        @if ($t['row_count'] > count($t['sample']))
                            <span class="text-amber-600">(showing first {{ count($t['sample']) }})</span>
                        @endif
                    </p>
                </div>
                <span class="text-xs px-2 py-1 bg-gray-200 rounded">{{ count($t['columns']) }} columns</span>
            </div>

            {{-- Columns --}}
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Schema</h3>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Column</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nullable</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($t['columns'] as $col)
                            <tr>
                                <td class="px-3 py-1.5 font-mono">
                                    {{ $col['name'] }}
                                    @if ($col['primary'])
                                        <span class="ml-1 text-xs px-1.5 py-0.5 pk rounded">PK</span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-xs font-mono px-1.5 py-0.5 sql-type rounded">{{ $col['type'] }}</span>
                                </td>
                                <td class="px-3 py-1.5 {{ $col['nullable'] ? 'null-yes' : 'null-no' }}">
                                    {{ $col['nullable'] ? 'yes' : 'no' }}
                                </td>
                                <td class="px-3 py-1.5 font-mono text-gray-600">
                                    {{ $col['default'] === null ? '—' : $col['default'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Sample rows --}}
            <div class="px-4 py-3">
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Sample data</h3>
                @if (empty($t['sample']))
                    <p class="text-sm text-gray-500 italic py-4 text-center">— empty table —</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    @foreach (array_keys($t['sample'][0]) as $colName)
                                        <th class="px-3 py-2 text-left font-mono text-gray-600 whitespace-nowrap">{{ $colName }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($t['sample'] as $row)
                                    <tr class="hover:bg-gray-50">
                                        @foreach ($row as $val)
                                            <td class="px-3 py-1.5 font-mono text-gray-700 whitespace-nowrap">
                                                @if ($val === null)
                                                    <span class="text-gray-400 italic">NULL</span>
                                                @elseif (is_string($val) && strlen($val) > 60)
                                                    <span title="{{ $val }}">{{ substr($val, 0, 60) }}…</span>
                                                @else
                                                    {{ is_scalar($val) ? $val : json_encode($val) }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    @endforeach

</div>

<footer class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 mt-8 border-t border-gray-200 text-center text-sm text-gray-600">
    © 2026 Bambang Saputra Jaya. Seluruh hak cipta dilindungi.
</footer>
</body>
</html>