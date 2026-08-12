<?php

use App\Http\Controllers\CourierPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/couriers');
});

Route::get('/couriers', [CourierPageController::class, 'index'])->name('couriers.page');