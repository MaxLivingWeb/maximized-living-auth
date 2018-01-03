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

Route::get('/', 'LoginController@index')->name('home');

Route::get('/login', 'LoginController@index')->name('login')->middleware('VerifySetup');
Route::post('/login', 'LoginController@login')->name('submitLogin')->middleware('VerifySetup');

Route::group(['middleware' => ['VerifySetup', 'VerifySession']], function() {
    Route::get('/new-password', 'NewPasswordController@index')->name('newPassword')->middleware('VerifySession');
    Route::post('/new-password', 'NewPasswordController@updatePassword')->name('submitNewPassword')->middleware('VerifySession');
});

Route::get('/forgot-password', 'ForgotPasswordController@index')->name('forgotPassword')->middleware('VerifySetup');
Route::post('/forgot-password', 'ForgotPasswordController@sendCode')->name('sendCode')->middleware('VerifySetup');
Route::get('/forgot-password/verify', 'ForgotPasswordController@verifyCode')->name('forgotPassword.verifyCode')->middleware('VerifySetup');
Route::post('/update-password', 'ForgotPasswordController@updatePassword')->name('updatePassword')->middleware('VerifySetup');

Route::group(['prefix' => 'register'], function() {
    Route::get('/', 'RegisterController@index')->name('register');
    Route::post('/', 'RegisterController@registerSubmit')->name('registerSubmit');
    Route::get('/verify', 'RegisterController@verify')->name('verify');
    Route::post('/verify', 'RegisterController@verifySubmit')->name('verifySubmit');
});
