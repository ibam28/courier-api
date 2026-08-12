<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use Illuminate\Http\Request;

class CourierPageController extends Controller
{
    /**
     * Render halaman UI /couriers.
     * Semua CRUD tetap lewat API di /api/couriers (dipanggil dari JS di view).
     * Halaman ini cuma shell + data awal untuk SSR agar cepat load.
     */
    public function index(Request $request)
    {
        // Pre-fetch data awal agar halaman ter-render dengan isi (bisa di-replace via JS).
        $initial = Courier::orderBy('name')->paginate(10);

        return view('couriers.index', [
            'initialCouriers' => $initial,
        ]);
    }
}
