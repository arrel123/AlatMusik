<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\OTPController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth Routes
// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    
    // Registration Routes
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    
    // Password Reset Flow with WhatsApp OTP
    Route::get('lupa-sandi', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    
    Route::post('lupa-sandi', [ForgotPasswordController::class, 'sendResetLinkPhone'])
        ->name('password.email');
    
    // OTP Verification Routes
    Route::get('verify-otp/{phone}', [ForgotPasswordController::class, 'showVerifyForm'])
        ->name('password.verify')
        ->middleware('throttle:3,1');
    
    Route::post('verify-otp/{phone}', [ForgotPasswordController::class, 'verifyOTP'])
        ->name('password.verify.submit')
        ->middleware('throttle:5,1');
        
    Route::post('resend-otp', [ForgotPasswordController::class, 'resendOTP'])
        ->name('password.resend')
        ->middleware('throttle:1,60');
    
    // Password Reset
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

// Add OTP routes that might be used by authenticated users
Route::middleware('auth')->group(function () {
    Route::post('send-whatsapp-otp', [OTPController::class, 'sendOTP'])
        ->name('send.otp');
});
// Add authenticated routes for OTP verification if needed
Route::middleware('auth')->group(function () {
    Route::get('verify-otp', [OTPController::class, 'showVerifyForm'])
        ->name('otp.verify');
    
    Route::post('verify-otp', [OTPController::class, 'verify'])
        ->name('otp.verify.submit');
    
    Route::post('resend-otp', [OTPController::class, 'resend'])
        ->name('otp.resend');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    Route::get('/profil', [ProfileController::class, 'index'])->name('profile');
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart');
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders');
    
    // Protected OTP Routes
    Route::post('verify-otp', [OTPController::class, 'verify'])
        ->name('otp.verify');
});
Route::get('/test-fonnte', function() {
    $testPhone = '6285271901194'; // Ganti dengan nomor test
    $otp = rand(100000, 999999);
    
    try {
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.token')
        ])->post('https://api.fonnte.com/send', [
            'target' => $testPhone,
            'message' => "Test OTP: $otp"
        ]);
        
        return [
            'status' => $response->status(),
            'response' => $response->body(),
            'success' => $response->successful()
        ];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
});