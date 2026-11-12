<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::match(['get', 'post'], '/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:admin,student'])->get('/students', function () {
    return response()->json([
        ['id' => 1, 'name' => 'Earl Justin'],
        ['id' => 2, 'name' => 'John Doe'],
    ]);
});

Route::middleware(['auth:sanctum', 'admin'])->get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome Admin!']);
});

Route::middleware(['auth:sanctum', 'role:student'])->get('/student/dashboard', function () {
    return response()->json(['message' => 'Welcome Student!']);
});
