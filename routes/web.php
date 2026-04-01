<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\WorkEntryController;



use App\Http\Controllers\AttendanceController;



Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/{id}/work', [\App\Http\Controllers\WorkController::class, 'index']);
Route::get('/work-calendar', [WorkController::class, 'calendar']);
Route::get('/test-attendance/{employeeId}', [AttendanceController::class, 'test']);


Route::get('/employees/create', [EmployeeController::class, 'create']);
Route::get('/work-types', [\App\Http\Controllers\WorkEntryTypeController::class, 'index']);
Route::get('/work-entries/day', function (Illuminate\Http\Request $request) {

    return \App\Models\WorkEntry::with('type')
        ->where('employee_id', $request->employee_id)
        ->whereDate('date', $request->date)
        ->get();

});

Route::post('/work-entries', [WorkEntryController::class, 'store']);
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::post('/work-types', [\App\Http\Controllers\WorkEntryTypeController::class, 'store']);





Route::get('/employees/{id}', [EmployeeController::class, 'show'])->where('id', '[0-9]+');

// HOME
Route::get('/', function () {
    return view('welcome');
});
