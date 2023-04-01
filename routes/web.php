<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriberController;
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

Route::get('/', [DashboardController::class, 'index']);
Route::post('/save_key', [DashboardController::class, 'saveAPIKey'])->name('api_key.store');
Route::post('/delete_key', [DashboardController::class, 'deleteAPIKey'])->name('api_key.delete');
Route::get('/subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
Route::get('/subscribers/email/check/{email}', [SubscriberController::class, 'emailCheck'])->name('subscribers.checkemail');
Route::post('/subscribers/new', [SubscriberController::class, 'store'])->name('subscriber.store');
Route::post('/subscribers/update', [SubscriberController::class, 'update'])->name('subscriber.update');
Route::post('/subscribers/delete', [SubscriberController::class, 'delete'])->name('subscriber.delete');
