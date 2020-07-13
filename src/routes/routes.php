<?php

$status = strtolower(env('GUX_AUTH', true));
if($status) {
    Route::group(['namespace' => 'Fng\CategoryBase\Http\Controllers', 'middleware' => ['auth']], function () {

        //Type routes

        Route::post('/admin/types/create', 'FngTypeController@create');
        Route::put('/admin/types/update/{id}', 'FngTypeController@update');
        Route::delete('/admin/types/delete/{id}', 'FngTypeController@delete');

        //Category Routes

        Route::post('/admin/categories/create', 'FngCategoryController@create');
        Route::put('/admin/categories/update/{id}', 'FngCategoryController@update');
        Route::delete('/admin/categories/delete/{id}', 'FngCategoryController@delete');

        //Product Routes

        Route::post('/admin/products/create', 'FngProductController@create');
        Route::put('/admin/products/update/{id}', 'FngProductController@update');
        Route::delete('/admin/products/delete/{id}', 'FngProductController@delete');

        // Support Data
        Route::get('/admin/support/product', 'SupportInfoController@getInitProductInfo');

    });
} else {
    Route::group(['namespace' => 'Fng\CategoryBase\Http\Controllers'], function () {

        //Type routes

        Route::post('/admin/types/create', 'FngTypeController@create');
        Route::put('/admin/types/update/{id}', 'FngTypeController@update');
        Route::delete('/admin/types/delete/{id}', 'FngTypeController@delete');

        //Category Routes

        Route::post('/admin/categories/create', 'FngCategoryController@create');
        Route::put('/admin/categories/update/{id}', 'FngCategoryController@update');
        Route::delete('/admin/categories/delete/{id}', 'FngCategoryController@delete');

        //Product Routes

        Route::post('/admin/products/create', 'FngProductController@create');
        Route::put('/admin/products/update/{id}', 'FngProductController@update');
        Route::delete('/admin/products/delete/{id}', 'FngProductController@delete');

        // Support Data
        Route::get('/admin/support/product', 'SupportInfoController@getInitProductInfo');

    });
}

Route::group(['namespace' => 'Fng\CategoryBase\Http\Controllers'], function () {

    //Type routes

    Route::get('/admin/types', 'FngTypeController@getAll');
    Route::get('/admin/types/{id}', 'FngTypeController@getById');

    //Category Routes

    Route::get('/admin/categories', 'FngCategoryController@getAll');
    Route::get('/admin/categories/{id}', 'FngCategoryController@getById');
    Route::get('/admin/categories/get/father', 'FngCategoryController@getFather');

    //Product Routes

    Route::get('/admin/products', 'FngProductController@getAll');
    Route::get('/admin/products/{id}', 'FngProductController@getById');

    // Support Data
    Route::get('/admin/support/product', 'SupportInfoController@getInitProductInfo');

});




