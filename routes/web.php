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

Route::get('/login', 'LoginController@index')->name('login');
Route::post('/login', 'LoginController@login')->name('submitLogin');

Route::group(['middleware' => ['VerifySession']], function() {
    Route::get('/new-password', 'NewPasswordController@index')->name('newPassword')->middleware('VerifySession');
    Route::post('/new-password', 'NewPasswordController@updatePassword')->name('submitNewPassword')->middleware('VerifySession');
});

Route::get('/forgot-password', 'ForgotPasswordController@index')->name('forgotPassword');
Route::post('/forgot-password', 'ForgotPasswordController@sendCode')->name('sendCode');
Route::get('/forgot-password/verify', 'ForgotPasswordController@verifyCode')->name('forgotPassword.verifyCode');
Route::post('/update-password', 'ForgotPasswordController@updatePassword')->name('updatePassword');

Route::group(['prefix' => 'register'], function() {
    Route::get('/', 'RegisterController@index')->name('register');
    Route::post('/', 'RegisterController@registerSubmit')->name('registerSubmit');
    Route::get('/verify', 'RegisterController@verify')->name('verify');
    Route::post('/verify', 'RegisterController@verifySubmit')->name('verifySubmit');
});
