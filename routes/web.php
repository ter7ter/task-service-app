<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\DispatcherController; // Added
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Added for Auth::user() in routes

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('requests.create');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Dispatcher Routes
    Route::middleware(['role:dispatcher'])->group(function () {
        Route::get('/dispatcher', [DispatcherController::class, 'index'])->name('dispatcher.dashboard');
        Route::post('/dispatcher/requests/{repairRequest}/assign', [DispatcherController::class, 'assign'])->name('dispatcher.requests.assign');
        Route::post('/dispatcher/requests/{repairRequest}/cancel', [DispatcherController::class, 'cancel'])->name('dispatcher.requests.cancel');
    });

    // Master Routes (Placeholder for now)
    Route::get('/master', function () {
        if (Auth::user()->role !== 'master') {
            abort(403);
        }
        return view('master.dashboard');
    })->name('master.dashboard');
});

// Request management routes (publicly accessible for creation)
Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
