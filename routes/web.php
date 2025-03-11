<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemController;
use App\Models\Equipment;
use Illuminate\Http\Request;

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
    
// Rota para visualização pública de equipamento via QR code
Route::get('/equipment/{equipment}', function (Equipment $equipment) {
    return view('equipment.show', compact('equipment'));
})->name('equipment.show');

// Rota para download do QR code
Route::get('/equipment/{equipment}/qrcode', function (Equipment $equipment, Request $request) {
    $size = $request->get('size', 200);
    return response($equipment->getQrCode($size))->header('Content-Type', 'image/svg+xml');
})->name('equipment.qrcode');