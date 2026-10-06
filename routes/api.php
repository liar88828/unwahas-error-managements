<?php

use Illuminate\Support\Facades\Route;
use Unwahas\ErrorRedirect\Http\Controllers\ErrorLogController;

Route::get('/', [ErrorLogController::class, 'index'])->name('index');
Route::get('/new', [ErrorLogController::class, 'newSince'])->name('new');
Route::get('/latest-id', [ErrorLogController::class, 'latestId'])->name('latest-id');
Route::get('/notifications/count', [ErrorLogController::class, 'notificationCount'])->name('notifications.count');
Route::get('/{id}', [ErrorLogController::class, 'show'])->whereNumber('id')->name('show');
Route::patch('/{id}/resolve', [ErrorLogController::class, 'resolve'])->whereNumber('id')->name('resolve');
Route::delete('/{id}', [ErrorLogController::class, 'destroy'])->whereNumber('id')->name('destroy');
