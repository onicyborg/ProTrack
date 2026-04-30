<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DailyReportController as AdminDailyReportController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\PM\DashboardController as PMDashboardController;
use App\Http\Controllers\PM\CalendarController as PMCalendarController;
use App\Http\Controllers\PM\ProjectController as PMProjectController;
use App\Http\Controllers\PM\DailyReportController;
use App\Http\Controllers\PM\ProjectTaskController;
use App\Http\Controllers\ProfileController;

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

Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'pm'    => redirect()->route('pm.dashboard'),
        default => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes (Placeholder)
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('calendar/download-daily-reports', [CalendarController::class, 'downloadDailyReportsByDate'])->name('calendar.download-daily-reports');

    Route::get('daily-reports/{dailyReport}', [AdminDailyReportController::class, 'show'])->name('daily-reports.show');
    Route::get('daily-report/{dailyReport}/download-pdf', [AdminDailyReportController::class, 'downloadPdf'])->name('daily-reports.download-pdf');

    Route::resource('clients', ClientController::class)->except(['show', 'create', 'edit']);

    Route::resource('employees', EmployeeController::class)->except(['show', 'create', 'edit']);

    Route::resource('projects', ProjectController::class)->except(['create', 'edit']);

    Route::post('projects/{project}/roles', [ProjectController::class, 'storeRole'])->name('projects.roles.store');
    Route::put('projects/{project}/roles/{role}', [ProjectController::class, 'updateRole'])->name('projects.roles.update');
    Route::delete('projects/{project}/roles/{role}', [ProjectController::class, 'destroyRole'])->name('projects.roles.destroy');

    Route::post('projects/{project}/assign-employee', [ProjectController::class, 'assignEmployee'])->name('projects.assign-employee');
    Route::delete('projects/{project}/team/{projectEmployee}', [ProjectController::class, 'unassignEmployee'])->name('projects.team.destroy');
});

// PM Routes
Route::prefix('pm')->middleware(['auth', 'role:pm'])->name('pm.')->group(function () {
    Route::get('/dashboard', [PMDashboardController::class, 'index'])->name('dashboard');

    Route::get('calendar', [PMCalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [PMCalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('calendar/check-report', [PMCalendarController::class, 'checkReport'])->name('calendar.check');
    Route::get('calendar/download-daily-reports', [PMCalendarController::class, 'downloadDailyReportsByDate'])->name('calendar.download-daily-reports');

    Route::get('daily-reports/tasks-by-date', [DailyReportController::class, 'tasksByDate'])->name('daily-reports.tasks-by-date');
    Route::get('daily-report/{dailyReport}/download-pdf', [DailyReportController::class, 'downloadPdf'])->name('daily-reports.download-pdf');
    Route::resource('daily-reports', DailyReportController::class);

    Route::get('projects', [PMProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{project}', [PMProjectController::class, 'show'])->name('projects.show');

    Route::post('projects/{project}/materials', [PMProjectController::class, 'storeMaterial'])->name('projects.materials.store');
    Route::put('projects/{project}/materials/{material}', [PMProjectController::class, 'updateMaterial'])->name('projects.materials.update');
    Route::delete('projects/{project}/materials/{material}', [PMProjectController::class, 'destroyMaterial'])->name('projects.materials.destroy');

    Route::post('projects/{project}/equipments', [PMProjectController::class, 'storeEquipment'])->name('projects.equipments.store');
    Route::put('projects/{project}/equipments/{equipment}', [PMProjectController::class, 'updateEquipment'])->name('projects.equipments.update');
    Route::delete('projects/{project}/equipments/{equipment}', [PMProjectController::class, 'destroyEquipment'])->name('projects.equipments.destroy');

    Route::post('projects/{project}/tasks', [ProjectTaskController::class, 'store'])->name('projects.tasks.store');
    Route::put('projects/{project}/tasks/{task}', [ProjectTaskController::class, 'update'])->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [ProjectTaskController::class, 'destroy'])->name('projects.tasks.destroy');

    Route::post('projects/{project}/tasks/{task}/assignments', [ProjectTaskController::class, 'syncAssignments'])->name('projects.tasks.assignments.sync');

    Route::post('projects/{project}/tasks/{task}/subtasks', [ProjectTaskController::class, 'storeSubtask'])->name('projects.tasks.subtasks.store');
    Route::delete('projects/{project}/tasks/{task}/subtasks/{subtask}', [ProjectTaskController::class, 'destroySubtask'])->name('projects.tasks.subtasks.destroy');
});
