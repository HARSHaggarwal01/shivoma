<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnquiryController;

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

Route::get('/', function () {
    return view('index');
});

Route::post('/submit-enquiry', [EnquiryController::class, 'submitEnquiry']);
Route::get('/payment/callback', [EnquiryController::class, 'handleCallback'])->name('payment.callback');

