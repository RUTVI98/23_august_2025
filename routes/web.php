<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectEmployeeController;


Route::middleware('guestuser:admin')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'createlogin')->name('login');
        Route::post('/login', 'login_error')->name('login_error');
    });
});

Route::middleware(['authuser:admin'])->group(function () {

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/counts', 'getCounts')->name('dashboard_getCounts');
    });

    Route::controller(DesignationController::class)->group(function () {
        Route::get('/designations', 'index')->name('designation_index');
        Route::get('/designations/list', 'list')->name('designation_list');
        Route::post('/designations', 'store')->name('designation_store');
        Route::post('/designations/update', 'update')->name('designation_update');
        Route::post('/designations/delete', 'destroy')->name('designation_delete');
        Route::get('/designations/{id}', 'show')->name('designation_show');
        Route::post('/designations/datatable', 'datatable')->name('designation_datatable');
    });

    Route::controller(EmployeeController::class)->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employee_index');
        Route::post('/employees/datatable', 'datatable')->name('employee_datatable');
        Route::get('/employees/{id}', 'show')->name('employee_show');
        Route::post('/employees/store', 'store')->name('employee_store');
        Route::post('/employees/update', 'update')->name('employee_update');
        Route::post('/employees/delete', 'destroy')->name('employee_delete');
    });

    Route::controller(ProjectController::class)->group(function () {
        Route::get('/projects', 'index')->name('project_index');
        Route::post('/projects/datatable', 'datatable')->name('project_datatable');
        Route::get('/projects/{id}', 'show')->name('project_show');
        Route::post('/projects/store', 'store')->name('project_store');
        Route::post('/projects/update', 'update')->name('project_update');
        Route::post('/projects/delete', 'delete')->name('project_delete');
    });

    Route::controller(ProjectEmployeeController::class)->group(function () {
        Route::get('/employee-projects', 'index')->name('employee_project_index');
        Route::post('/employee-projects/datatable', 'datatable')->name('employee_project_datatable');
        Route::post('/employee-projects/store', 'store')->name('employee_project_store');
        Route::post('/employee-projects/delete', 'delete')->name('employee_project_delete');
    });

});