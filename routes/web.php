<?php

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

Route::group(['middleware' => ['CaptureRedirectURI']], function() {
    // Login
    Route::get('/', 'LoginController@index')->name('home');
    Route::get('/login', 'LoginController@index')->name('login');
    Route::post('/login', 'LoginController@login')->name('submitLogin');

    // Create New Password
    Route::group(['middleware' => ['VerifySession']], function() {
        Route::get('/new-password', 'NewPasswordController@index')->name('newPassword')->middleware('VerifySession');
        Route::post('/new-password', 'NewPasswordController@updatePassword')->name('submitNewPassword')->middleware('VerifySession');
    });

    // Forgot Password
    Route::get('/forgot-password', 'ForgotPasswordController@index')->name('forgotPassword.index');
    Route::post('/forgot-password', 'ForgotPasswordController@sendVerificationCode')->name('forgotPassword.sendVerificationCode');
    Route::get('/forgot-password/verify', 'ForgotPasswordController@enterVerificationCode')->name('forgotPassword.enterVerificationCode');
    Route::post('/update-password', 'ForgotPasswordController@updatePassword')->name('forgotPassword.updatePassword');

    // Register new user and verify account
    Route::group(['prefix' => 'register'], function() {
        Route::get('/', 'RegisterController@index')->name('register.index');
        Route::post('/', 'RegisterController@submitRegistration')->name('register.submitRegistration');
        Route::get('/verify', 'RegisterController@enterVerificationCode')->name('register.enterVerificationCode');
        Route::post('/verify', 'RegisterController@submitVerificationCode')->name('register.submitVerificationCode');
    });

    // Return to the verification page through a link in the Verification Confirmation email template
    // Note: the same link /verify is used for all Verification Confirmation email templates... which means both account verification, and password reset verification
    Route::group(['prefix' => 'verify'], function() {
        Route::get('/', 'VerificationController@index')->name('verification.index');
        Route::get('/resend-verification-code', 'VerificationController@requestVerificationCode')->name('verification.requestVerificationCode');
        Route::post('/resend-verification-code', 'VerificationController@resendVerificationCode')->name('verification.resendVerificationCode');
    });
});
