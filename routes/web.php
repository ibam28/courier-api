<?php

use App\Http\Controllers\CourierPageController;
use App\Http\Controllers\DbInspectorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/couriers');
});

Route::get('/couriers', [CourierPageController::class, 'index'])->name('couriers.page');
Route::get('/admin/db', [DbInspectorController::class, 'index'])->name('admin.db');