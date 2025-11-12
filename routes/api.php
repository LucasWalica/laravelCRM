<?php

use App\Http\Controllers\Api\{
    UserController,
    BusinessController,
    BusinessServiceController,
    ReservationController,
    BusinessFeedbackController
};


Route::prefix('v1')->group(function () {

    // 🔹 Usuarios públicos
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    // ruta usada por cliente anonimos
    Route::post("/create-client-internal", [UserController::class, 'createClientInternal']);
    // crear y actualizar reservas sin auth siendo cliente sin registrar 
    Route::post("/reservation-public", [ReservationController::class, "storePublic"]);
    // un cron habilita esta url 
    Route::put('/reservations/update/{token}', [ReservationController::class, 'updatePublic']);


    // 🔹 Rutas protegidas por token
    Route::middleware('auth:sanctum')->group(function () {

        // Usuarios
        Route::post('/logout', [UserController::class, 'logout']);
        Route::post('/users/create', [UserController::class, 'createByOwner']);

        // Negocios
        Route::get('/businesses', [BusinessController::class, 'index']);
        Route::post('/businesses', [BusinessController::class, 'store']);
        Route::get('/businesses/{id}', [BusinessController::class, 'show']);
        Route::put('/businesses/{id}', [BusinessController::class, 'update']);
        Route::delete('/businesses/{id}', [BusinessController::class, 'destroy']);

        // Servicios
        Route::get('/services', [BusinessServiceController::class, 'index']);
        Route::post('/services', [BusinessServiceController::class, 'store']);
        Route::get('/services/{id}', [BusinessServiceController::class, 'show']);
        Route::put('/services/{id}', [BusinessServiceController::class, 'update']);
        Route::delete('/services/{id}', [BusinessServiceController::class, 'destroy']);

        // Reservas
        Route::get('/reservations', [ReservationController::class, 'index']);
        
        Route::get('/reservations/{id}', [ReservationController::class, 'show']);
        // posiblemente no dejar update de reservation
        Route::post('/reservations', [ReservationController::class, 'store']);
        Route::put('/reservations/{id}', [ReservationController::class, 'update']);
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
        // endpoint para cancelar siendo propietario 
        Route::put('/reservations/{id}/owner-cancel', [ReservationController::class, 'cancel']);
        
        
        // Feedbacks
        Route::get('/feedbacks', [BusinessFeedbackController::class, 'index']);
        Route::post('/feedbacks', [BusinessFeedbackController::class, 'store']);
        Route::get('/feedbacks/{id}', [BusinessFeedbackController::class, 'show']);
        Route::delete('/feedbacks/{id}', [BusinessFeedbackController::class, 'destroy']);

    });

});
