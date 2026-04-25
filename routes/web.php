<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\EmployeeController;

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
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'pm'    => redirect()->route('pm.dashboard'),
        default => redirect()->route('login'),
    };
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard Routes (Placeholder)
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['show', 'create', 'edit']);

    Route::resource('employees', EmployeeController::class)->except(['show', 'create', 'edit']);
});

// PM Routes
Route::prefix('pm')->middleware(['auth', 'role:pm'])->name('pm.')->group(function () {
    Route::get('/dashboard', function () {
        return view('pm.dashboard');
    })->name('dashboard');
});
