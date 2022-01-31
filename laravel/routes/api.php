<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function(){
    //
    Route::resource('products', ProductController::class);
    Route::get('products/search/{keyword}', [ProductController::class, 'searchProductName']);
    Route::resource('users', AuthController::class);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('users/profile/updateProfilePicture/{user_id}', [AuthController::class, 'updateProfilePicture']);
    Route::put('users/profile/updatePersonalInfomation/{user_id}', [AuthController::class, 'updatePersonalInfomation']);
    Route::put('users/profile/changePassword/{user_id}', [AuthController::class, 'changePassword']);
});
