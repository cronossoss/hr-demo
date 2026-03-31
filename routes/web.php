<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\AttendanceController;



Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/{id}/work', [\App\Http\Controllers\WorkController::class, 'index']);
Route::get('/work-calendar', [WorkController::class, 'calendar']);
Route::get('/test-attendance/{employeeId}', [AttendanceController::class, 'test']);


Route::get('/employees/create', [EmployeeController::class, 'create']);
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::post('/work-entries', [\App\Http\Controllers\WorkController::class, 'store']);
Route::get('/work-types', [\App\Http\Controllers\WorkEntryTypeController::class, 'index']);
Route::post('/work-types', [\App\Http\Controllers\WorkEntryTypeController::class, 'store']);
Route::post('/work-entry/update-or-create', [WorkController::class, 'updateOrCreate']);


Route::get('/employees/{id}', [EmployeeController::class, 'show'])->where('id', '[0-9]+');

// HOME
Route::get('/', function () {
    return view('welcome');
});
