<?php

Route::group(['namespace' => 'Fng\Payments\Http\Controllers', 'middleware' => ['auth']], function () {

    // Mercado Pago
    Route::post('/payments/mercadopago/create', 'MercadoPagoPaymentController@createPayment');

    // Transbank
    Route::post('/payments/webpay/create', 'WebPayPaymentController@createPayment');

    // Payments
    Route::get('/payments', 'PaymentController@getPayments');
    Route::get('/payments/{id}', 'PaymentController@getPaymentsById');

});

Route::group(['namespace' => 'Fng\Payments\Http\Controllers'], function () {
    //Mercado Pago
    Route::post('/payments/mercadopago/webhook', 'MercadoPagoPaymentController@updateStatusWebHook');
    Route::get('/payments/mercadopago/failure', 'MercadoPagoPaymentController@failure');
    Route::get('/payments/mercadopago/success', 'MercadoPagoPaymentController@success');
    Route::get('/payments/mercadopago/pending', 'MercadoPagoPaymentController@pending');

    // Transbank
    Route::post('/payments/webpay/return', 'WebPayPaymentController@returnTransbank');
    Route::post('/payments/webpay/final', 'WebPayPaymentController@finalTransaction');
});




