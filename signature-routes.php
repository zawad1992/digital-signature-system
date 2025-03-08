<?php
// routes/web.php

use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;

Route::get('/signature', [SignatureController::class, 'create'])->name('signatures.create');
Route::post('/signature/store', [SignatureController::class, 'store'])->name('signatures.store');
Route::get('/signature/success', [SignatureController::class, 'success'])->name('signatures.success');
