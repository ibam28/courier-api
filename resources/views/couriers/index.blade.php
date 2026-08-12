<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Couriers — Courier API</title>
    @vite(['resources/css/app.css'])
    <style>
        /* small inline overrides agar tidak butuh Tailwind v4 plugins */
        .lvl-1 { background:#fef3c7; color:#92400e; }
        .lvl-2 { background:#dbeafe; color:#1e40af; }
        .lvl-3 { background:#dcfce7; color:#166534; }
        .lvl-4 { background:#ede9fe; color:#5b21b6; }
        .lvl-5 { background:#fce7f3; color:#9d174d; }
        .status-active   { background:#dcfce7; color:#166534; }
        .status-inactive { background:#f3f4f6; color:#374151; }
        .status-suspended{ background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <header class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📦 Courier Management</h1>
        <p class="mt-1 text-sm text-gray-600">Master data kurir — CRUD via REST API. Backend: Laravel 11 + SQLite.</p>
    </header>

    {{-- Toolbar --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-700 mb-1">Search by name</label>
                <input id="f-search" type="text" placeholder="e.g. budi agung"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Level</label>
                <select id="f-level" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All levels</option>
                    <option value="1">Level 1</option>
                    <option value="2">Level 2</option>
                    <option value="3">Level 3</option>
                    <option value="4">Level 4</option>
                    <option value="5">Level 5</option>
                    <option value="2,3">Level 2 or 3</option>
                    <option value="1,2,3">Level 1, 2, or 3</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Sort by</label>
                <select id="f-sort" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="name">Name (default)</option>
                    <option value="created_at">Date registered</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Order</label>
                <select id="f-order" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
            </div>
            <button onclick="applyFilters()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium">
                Apply
            </button>
            <button onclick="resetFilters()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md font-medium">
                Reset
            </button>
            <button onclick="openCreate()" class="ml-auto px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium">
                + New Courier
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="couriers-tbody" class="bg-white divide-y divide-gray-200">
                    {{-- Pre-rendered initial rows (will be replaced by JS after first fetch) --}}
                    @foreach ($initialCouriers as $c)
                        @include('couriers.partials.row', ['c' => $c])
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div id="pagination" class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between text-sm">
            <span id="pagination-info" class="text-gray-600">
                Showing {{ $initialCouriers->count() }} of {{ $initialCouriers->total() }}
            </span>
            <div id="pagination-links" class="flex gap-1">
                @if ($initialCouriers->onFirstPage())
                    <span class="px-3 py-1 text-gray-400">‹ Previous</span>
                @else
                    <a href="{{ $initialCouriers->previousPageUrl() }}" class="px-3 py-1 text-blue-600 hover:underline">‹ Previous</a>
                @endif
                <span class="px-3 py-1 font-medium">{{ $initialCouriers->currentPage() }} / {{ $initialCouriers->lastPage() }}</span>
                @if ($initialCouriers->hasMorePages())
                    <a href="{{ $initialCouriers->nextPageUrl() }}" class="px-3 py-1 text-blue-600 hover:underline">Next ›</a>
                @else
                    <span class="px-3 py-1 text-gray-400">Next ›</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="fixed top-4 right-4 z-50 hidden">
        <div id="toast-msg" class="px-4 py-3 rounded-md shadow-lg text-white font-medium"></div>
    </div>
</div>

{{-- Modal Create/Edit --}}
<div id="modal" class="fixed inset-0 bg-gray-900/50 hidden z-40 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 id="modal-title" class="text-lg font-semibold">New Courier</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form id="courier-form" onsubmit="submitForm(event)" class="px-6 py-4 space-y-3">
            <input type="hidden" id="f-id">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Code</label>
                    <input id="f-code" type="text" maxlength="32" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Name *</label>
                    <input id="f-name" type="text" required maxlength="120" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                    <input id="f-phone" type="text" maxlength="32" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                    <input id="f-email" type="email" maxlength="120" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Vehicle type</label>
                    <input id="f-vehicle_type" type="text" maxlength="32" placeholder="motor / mobil / van" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Plate</label>
                    <input id="f-vehicle_plate" type="text" maxlength="32" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Level (1-5) *</label>
                    <select id="f-form-level" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="1">1 — Junior</option>
                        <option value="2">2</option>
                        <option value="3" selected>3</option>
                        <option value="4">4</option>
                        <option value="5">5 — Senior</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select id="f-status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="active">active</option>
                        <option value="inactive">inactive</option>
                        <option value="suspended">suspended</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                <textarea id="f-address" maxlength="1000" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>
            <div id="form-errors" class="text-sm text-red-600 hidden"></div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
const API = '/api/couriers';

function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function rowHtml(c) {
    const lvl = c.level ?? '-';
    const status = c.status || 'active';
    const joined = c.joined_at ? c.joined_at.substring(0,10) : '-';
    return `
        <tr data-id="${c.id}">
            <td class="px-4 py-3 text-sm font-mono text-gray-600">${escapeHtml(c.code || '')}</td>
            <td class="px-4 py-3 text-sm font-medium text-gray-900">${escapeHtml(c.name)}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(c.phone || '-')}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(c.vehicle_type || '-')} <span class="text-xs text-gray-400">${escapeHtml(c.vehicle_plate || '')}</span></td>
            <td class="px-4 py-3"><span class="px-2 py-1 text-xs font-semibold rounded lvl-${lvl}">L${lvl}</span></td>
            <td class="px-4 py-3"><span class="px-2 py-1 text-xs font-semibold rounded status-${status}">${status}</span></td>
            <td class="px-4 py-3 text-sm text-gray-600">${joined}</td>
            <td class="px-4 py-3 text-right text-sm">
                <button onclick='openEdit(${JSON.stringify(c)})' class="text-blue-600 hover:text-blue-800 font-medium mr-2">Edit</button>
                <button onclick="deleteCourier(${c.id})" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
            </td>
        </tr>`;
}

function renderList(data) {
    const tbody = document.getElementById('couriers-tbody');
    if (!data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No couriers found.</td></tr>';
    } else {
        tbody.innerHTML = data.data.map(rowHtml).join('');
    }
    const from = data.from || 0, to = data.to || 0, total = data.total || 0;
    document.getElementById('pagination-info').textContent =
        total === 0 ? 'No results' : `Showing ${from}–${to} of ${total}`;
    const links = document.getElementById('pagination-links');
    links.innerHTML = `
        ${data.prev_page_url ? `<button onclick="loadUrl('${data.prev_page_url}')" class="px-3 py-1 text-blue-600 hover:underline">‹ Previous</button>` : '<span class="px-3 py-1 text-gray-400">‹ Previous</span>'}
        <span class="px-3 py-1 font-medium">${data.current_page} / ${data.last_page}</span>
        ${data.next_page_url ? `<button onclick="loadUrl('${data.next_page_url}')" class="px-3 py-1 text-blue-600 hover:underline">Next ›</button>` : '<span class="px-3 py-1 text-gray-400">Next ›</span>'}
    `;
}

function loadUrl(url) {
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(renderList)
        .catch(err => toast('Error loading: ' + err.message, 'error'));
}

function applyFilters() {
    const params = new URLSearchParams();
    const s = document.getElementById('f-search').value.trim();
    const l = document.getElementById('f-level').value;
    const sort = document.getElementById('f-sort').value;
    const order = document.getElementById('f-order').value;
    if (s) params.set('search', s);
    if (l) params.set('level', l);
    if (sort) { params.set('sort', sort); params.set('order', order); }
    loadUrl(API + '?' + params.toString());
}

function resetFilters() {
    document.getElementById('f-search').value = '';
    document.getElementById('f-level').value = '';
    document.getElementById('f-sort').value = 'name';
    document.getElementById('f-order').value = 'asc';
    loadUrl(API);
}

function openCreate() {
    document.getElementById('modal-title').textContent = 'New Courier';
    document.getElementById('courier-form').reset();
    document.getElementById('f-id').value = '';
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modal').classList.add('flex');
}

function openEdit(c) {
    document.getElementById('modal-title').textContent = 'Edit Courier #' + c.id;
    document.getElementById('f-id').value = c.id;
    document.getElementById('f-code').value = c.code || '';
    document.getElementById('f-name').value = c.name || '';
    document.getElementById('f-phone').value = c.phone || '';
    document.getElementById('f-email').value = c.email || '';
    document.getElementById('f-vehicle_type').value = c.vehicle_type || '';
    document.getElementById('f-vehicle_plate').value = c.vehicle_plate || '';
    document.getElementById('f-form-level').value = c.level || 3;
    document.getElementById('f-status').value = c.status || 'active';
    document.getElementById('f-address').value = c.address || '';
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modal').classList.add('flex');
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('modal').classList.remove('flex');
}

function submitForm(ev) {
    ev.preventDefault();
    const id = document.getElementById('f-id').value;
    const payload = {
        code: document.getElementById('f-code').value || null,
        name: document.getElementById('f-name').value,
        phone: document.getElementById('f-phone').value || null,
        email: document.getElementById('f-email').value || null,
        vehicle_type: document.getElementById('f-vehicle_type').value || null,
        vehicle_plate: document.getElementById('f-vehicle_plate').value || null,
        level: parseInt(document.getElementById('f-form-level').value, 10),
        status: document.getElementById('f-status').value,
        address: document.getElementById('f-address').value || null,
    };
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API}/${id}` : API;
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify(payload)
    }).then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) {
            const errs = data.errors ? Object.values(data.errors).flat().join(' | ') : (data.message || 'Error');
            document.getElementById('form-errors').textContent = errs;
            document.getElementById('form-errors').classList.remove('hidden');
            return;
        }
        closeModal();
        toast(id ? 'Courier updated' : 'Courier created', 'success');
        applyFilters();
    }).catch(err => {
        document.getElementById('form-errors').textContent = err.message;
        document.getElementById('form-errors').classList.remove('hidden');
    });
}

function deleteCourier(id) {
    if (!confirm('Delete courier #' + id + '?')) return;
    fetch(`${API}/${id}`, {
        method: 'DELETE',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(r => r.json()).then(data => {
        toast('Deleted: ' + (data.message || 'OK'), 'success');
        applyFilters();
    }).catch(err => toast('Error: ' + err.message, 'error'));
}

function toast(msg, kind) {
    const t = document.getElementById('toast');
    const m = document.getElementById('toast-msg');
    m.textContent = msg;
    m.className = 'px-4 py-3 rounded-md shadow-lg text-white font-medium ' + (kind === 'error' ? 'bg-red-600' : 'bg-green-600');
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 2500);
}

// initial fetch to get latest data (replaces SSR rows with live API data)
applyFilters();
</script>
</body>
</html>