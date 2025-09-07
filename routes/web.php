<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\admin\AdminAuthController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\AdminUsersController;
use App\Http\Controllers\admin\PaymentsController;
use App\Http\Controllers\admin\ReportController;

Route::get('/', function () {
    return Auth::guard('admin')->check() ? redirect()->route('admin.dashboard') : redirect()->route('admin.login');
});

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

//for admin panel
Route::middleware('auth:admin')->prefix('admin')->group(function () {
//
//
    Route::get('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout')->middleware('auth:admin');
//
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/pending-payments-jobs', [PaymentsController::class, 'postLists'])->name('admin.payments.post.list');
    Route::get('/Transaction-job-posts/{id}', [PaymentsController::class, 'PostTransactionList'])->name('admin.payments.post.view');
////////////////////////////////////////////////////////////Users/////////////////////////////////////////////////
    Route::get('/business-users', [AdminUsersController::class, 'businessList'])->name('admin.business.users.list');
    Route::get('/candidate-users', [AdminUsersController::class, 'candidateList'])->name('admin.candidate.users.list');
    Route::get('/users/{user}', [AdminUsersController::class, 'edit'])->name('admin.users.show');
    Route::post('/users/edit/{user}', [AdminUsersController::class, 'update'])->name('admin.users.edit');
    Route::get('/users/delete/{user}', [AdminUsersController::class, 'destroy'])->name('admin.users.delete');
    Route::get('/users/inactive/{user}', [AdminUsersController::class, 'inactive'])->name('admin.users.inactive');
    Route::get('/users/acitve/{user}', [AdminUsersController::class, 'acitve'])->name('admin.users.acitve');

    Route::get('/users/acitve/{user}', [AdminUsersController::class, 'acitve'])->name('admin.business.reports.list');




    Route::get('/reports', [ReportController::class, 'list'])->name('admin.reports.list');

});

