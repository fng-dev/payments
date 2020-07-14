<?php

Route::group(['namespace' => 'Fng\Payments\Http\Controllers', 'middleware' => ['auth']], function () {

    // Route::post('/payments/mercadopago/create', 'MercadoPagoPaymentController@createPayment');

});

Route::group(['namespace' => 'Fng\Payments\Http\Controllers'], function () {
    Route::post('/payments/mercadopago/webhook', 'MercadoPagoPaymentController@updateStatusWebHook');
    Route::get('/payments/mercadopago/failure', 'MercadoPagoPaymentController@failure');
    Route::get('/payments/mercadopago/success', 'MercadoPagoPaymentController@success');
    Route::get('/payments/mercadopago/pending', 'MercadoPagoPaymentController@pending');
    Route::post('/payments/mercadopago/create', 'MercadoPagoPaymentController@createPayment');
});




