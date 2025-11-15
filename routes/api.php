<?php

use App\Http\Controllers\Api\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//
Route::get('api/test',[TestController::class,'get']);

Route::middleware('api')->group(function () {
    require base_path('generated/routes.php');
});
