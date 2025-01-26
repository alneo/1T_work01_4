<?php
use App\Jobs\ProcessSendingEmail;
use App\Jobs\ProcessSendingEmail2;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    ProcessSendingEmail::dispatch();
    ProcessSendingEmail2::dispatch();
    return view('welcome');
});
