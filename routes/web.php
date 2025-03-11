<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemController;

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

// A rota principal é gerenciada pelo Filament
/* Route::get('/', function () {
    return redirect('/');
}); */

Route::post('/update-system', [SystemController::class, 'update'])
    ->middleware(['auth'])
    ->name('update-system');