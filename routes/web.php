<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resources([
    'streams'      => StreamController::class,
]);

Auth::routes();

Route::get('/', [StreamController::class, 'index']);
Route::get('/streams',  [StreamController::class, 'index'])->name('streams');
Route::get('/success', [App\Http\Controllers\HomeController::class, 'success'])->name('success');
