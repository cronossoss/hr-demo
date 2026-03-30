<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;


// LISTA
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/{id}/work', [\App\Http\Controllers\WorkController::class, 'index']);

// CREATE
Route::get('/employees/create', [EmployeeController::class, 'create']);
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::post('/work-entries', [\App\Http\Controllers\WorkController::class, 'store']);
Route::get('/work-types', [\App\Http\Controllers\WorkEntryTypeController::class, 'index']);
Route::post('/work-types', [\App\Http\Controllers\WorkEntryTypeController::class, 'store']);

// SHOW
Route::get('/employees/{id}', [EmployeeController::class, 'show'])->where('id', '[0-9]+');

// HOME
Route::get('/', function () {
    return view('welcome');
});