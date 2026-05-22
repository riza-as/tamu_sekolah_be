<?php

use App\Http\Controllers\V1\SubdistrictVisitorController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\DistrictController;
use App\Http\Controllers\V1\ObjectiveController;
use App\Http\Controllers\V1\ProfileController;
use App\Http\Controllers\V1\ProvinceController;
use App\Http\Controllers\V1\QrCodeController;
use App\Http\Controllers\V1\SubdistrictController;
use App\Http\Controllers\V1\SubdistrictQrCodeController;
use App\Http\Controllers\V1\SchoolController;
use App\Http\Controllers\V1\TotalDataController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\VillageController;
use App\Http\Controllers\V1\VisitorController;
use App\Http\Controllers\V1\VisitorTypeController;
use Illuminate\Support\Facades\Route;

// Unauthenticate Route
Route::middleware('cors')->prefix('v1')->group(function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
    });
    // Address
    Route::group(['prefix' => 'address'], function () {
        Route::get('province', [ProvinceController::class, 'index']);
        Route::get('district', [DistrictController::class, 'index']);
        Route::get('subdistrict', [SubdistrictController::class, 'index']);
        Route::get('village', [VillageController::class, 'index']);
        Route::get('village/{id}', [VillageController::class, 'show']);
        Route::get('school', [SchoolController::class, 'index']);
        Route::get('school/{id}', [SchoolController::class, 'show']);
    });

    // Qr Code
    Route::get('qr-code/{id}', [QrCodeController::class, 'show']);
    Route::get('subdistrict-qr-code/{subdistrict_code}', [SubdistrictQrCodeController::class, 'show']);

    // Visitor Type
    Route::get('visitor-type', [VisitorTypeController::class, 'index']);

    // Objective
    Route::get('objective', [ObjectiveController::class, 'index']);

    // Send Visitor
    Route::post('visitor/{school_id}', [VisitorController::class, 'store']);

    // Subdistrict Visitor
    Route::post('subdistrict-visitor/{subdistrict_code}', [SubdistrictVisitorController::class, 'store']);
});

// Authenticate Route
Route::middleware('jwt_verify')->prefix('v1')->group(function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::delete('logout', [AuthController::class, 'logout']);
    });

    // Total Data
    Route::get('total-data', [TotalDataController::class, 'index']);

    // User
    Route::group(['prefix' => 'user'], function () {
        Route::get('', [UserController::class, 'index']);
        Route::get('me', [UserController::class, 'me']);
        Route::post('', [UserController::class, 'store']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::post('{id}/update', [UserController::class, 'update']);
        Route::post('{id}/activate', [UserController::class, 'activate']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });


    // Profile
    Route::group(['prefix' => 'profile'], function () {
        Route::get('', [ProfileController::class, 'index']);
        Route::get('me', [ProfileController::class, 'me']);
        Route::get('{id}', [ProfileController::class, 'show']);
        Route::post('{id}', [ProfileController::class, 'store']);
        Route::post('{id}/update', [ProfileController::class, 'update']);
        Route::delete('{id}', [ProfileController::class, 'destroy']);
    });

    // Qr Code
    Route::group(['prefix' => 'qr-code'], function () {
        Route::get('', [QrCodeController::class, 'index']);
        Route::post('', [QrCodeController::class, 'store']);
        Route::post('/school', [QrCodeController::class, 'storeSchoolQrCode']);
        Route::post('{id}/update', [QrCodeController::class, 'update']);
        Route::delete('{id}', [QrCodeController::class, 'destroy']);
    });
    // Qr Code Subdistrict
    Route::group(['prefix' => 'subdistrict-qr-code'], function () {
        Route::get('', [SubdistrictQrCodeController::class, 'index']);
        Route::post('', [SubdistrictQrCodeController::class, 'store']);
        Route::post('{id}/update', [SubdistrictQrCodeController::class, 'update']);
        Route::delete('{id}', [SubdistrictQrCodeController::class, 'destroy']);
    });

    // Visitor Type
    Route::group(['prefix' => 'visitor-type'], function () {
        Route::get('{id}', [VisitorTypeController::class, 'show']);
        Route::post('', [VisitorTypeController::class, 'store']);
        Route::post('{id}/update', [VisitorTypeController::class, 'update']);
        Route::delete('{id}', [VisitorTypeController::class, 'destroy']);
    });

    // Objective
    Route::group(['prefix' => 'objective'], function () {
        Route::get('{id}', [ObjectiveController::class, 'show']);
        Route::post('', [ObjectiveController::class, 'store']);
        Route::post('{id}/update', [ObjectiveController::class, 'update']);
        Route::delete('{id}', [ObjectiveController::class, 'destroy']);
    });

    // Visitor
    Route::group(['prefix' => 'visitor'], function () {
        Route::get('total', [VisitorController::class, 'total']);
        Route::get('chart', [VisitorController::class, 'chart']);
        Route::get('', [VisitorController::class, 'index']);
        Route::get('{id}', [VisitorController::class, 'show']);
        // Route::get('school/{school_code}', [VisitorController::class, 'getVisitorSchool']);
        Route::delete('{id}', [VisitorController::class, 'destroy']);
    });

    // Subdistrict Visitor
    Route::group(['prefix' => 'subdistrict-visitor'], function () {
        Route::get('total', [SubdistrictVisitorController::class, 'total']);
        Route::get('chart', [SubdistrictVisitorController::class, 'chart']);
        Route::get('', [SubdistrictVisitorController::class, 'index']);
        Route::get('{id}', [SubdistrictVisitorController::class, 'show']);
        Route::delete('{id}', [SubdistrictVisitorController::class, 'destroy']);
    });

    // School
    Route::group(['prefix' => 'schools'], function () {
        Route::get('', [SchoolController::class, 'index']);
        Route::post('', [SchoolController::class, 'store']);
        Route::get('{id}', [SchoolController::class, 'show']);
        Route::put('{school_code}', [SchoolController::class, 'update']);
        Route::delete('{school_code}', [SchoolController::class, 'destroy']);
    });
});
