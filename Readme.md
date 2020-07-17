# WEBPAY - MERCADO PAGO PAYMENTS

## Class Features
- Integration with laravel / lumen
- Automatic table creation in database
- Automatic route configurations

## Installation

```sh
composer require fng-dev/gux-payments
```

## Installation Errors

If the version of the doctrine/inflector is greater than 1.4, it will be necessary to make a dowgrade for this version for the sdk of the mercadopago work.

```composer require doctrine/inflector:^1  ```

## Configs

### ENV

laravel / lumen

MERCADO PAGO ENVIROMENT

The return url for the webhook, as well as the response url, use the laravel/lumen ```APP_URL``` variable to build the final url. Don't forget to add it to the Mercado Pago panel.

```
APP_URL=https://api.your-site.com
```

If you need to take any action after a ```Mercado Pago Webhook``` or ```Transbank Response``` request, such as sending an email for example, you can enter an internal endpoint to be called at the end of the ```MERCADO PAGO``` or ```TRANSBANK``` request. Keep in mind that the first request from the ```Mercado Pago Webhook``` waits 22 seconds for a valid response, and the next only 5 seconds.

inform variable ```MERCADO_PAGO_INTERNAL_WEBHOOK``` in your .env file with the endpoint
inform variable ```TRANSBANK_INTERNAL_WEBHOOK``` in your .env file with the endpoint

This hook is made in POST format, and sends a ```JSON``` object as a parameter

```
{
    "id": 2,
    "buy_order": "202007141317288298MP", //External reference
    "amount": "350000.00",
    "shipping_amount": null,
    "status": "approved",
    "payment_type": "credit_card",
    "payment_company": "mercado_pago",
    "session_id": 27959191, // Paid purchase id in Mercado Pago | token transbank
    "collection_id": "27966139",
    "preference_id": "567061042-138f6a5f-2553-4542-a2f2-0e2a1618b5cb",
    "merchant_order_id": "1594255766",
    "share_number": null,
    "details": "Transaction details,
    "user_id": 1, // Id of the user who made the purchase
    "created_at": "2020-07-14 16:53:38",
    "updated_at": "2020-07-14 17:36:07"
}
```

The response of this internal hook must be made using response laravel method:

```php
return response()->json($response);
```

VARIABLES

```
PAYMENT_ENVIROMENT='SANDBOX|PRODUCTION'
FRONT_RETURN_PAYMENT='https://www.your-site.com/responses'

MERCADO_PAGO_PUBLIC_KEY_SANDBOX=SANDBOX PUBLIC KEY
MERCADO_PAGO_ACCESS_TOKEN_SANDBOX=SANDBOX ACCESS TOKEN

MERCADO_PAGO_PUBLIC_KEY=PRODUCTION PUBLIC KEY
MERCADO_PAGO_ACCESS_TOKEN=PRODUCTION ACCESS TOKEN
MERCADO_PAGO_CLIENT_ID=CLIENT ID
MERCADO_PAGO_CLIENT_SECRET=CLIENT SECRET

MP_WEBHOOK=true
MERCADO_PAGO_INTERNAL_WEBHOOK=false|/your/endpoint/to/your/hook
TRANSBANK_INTERNAL_WEBHOOK=false|/your/endpoint/to/your/hook

TRANSBANK_CODIGO_COMERCIO=35669965
TRANSBANK_PRIVATE_KEY='url/to/key'
TRANSBANK_PUBLIC_CERT='url/to/key'
TRANSBANK_WEBPAY_CERT='url/to/key'
```

### Lumen

Uncomment the lines

```sh
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);
```
and

```sh
$app->register(App\Providers\AuthServiceProvider::class);
```

and add

```sh
$app->register(Fng\Payments\PaymentServiceProvider::class);
```
below the last register inside the file

```sh
    bootstrap/app.php
```

## Migration

Run migrations command

```sh
    php artisan migration
```

## EndPoints

After configuring everything, you can initiate a payment by accessing the following routes via post.

```[POST] /payments/mercadopago/create```

```[POST] /payments/webpay/create```

These routes wait as parameters the following data:

```json
{
    "items": [
        {
            "id": 1,
            "name": "Item 1",
            "quantity": 1,
            "price_unit": 200000, // without dots - just numbers
            "img_url": "https://path/to/img", // Optional
            "description": "description", // Optional
            "unit": "ml", // Optional
        },
        {
            "id": 2,
            "name": "Item 2",
            "quantity": 3,
            "price_unit": 50000 // without dots - just numbers
            "img_url": "https://path/to/img", // Optional
            "description": "description", // Optional
            "unit": "ml", // Optional
        }
    ],
    "payer": {
        "name": "Franco",
        "surname": "Nascimento"
    },
    "amount": 350000
}
```

 If the field does not have an 'optional' flag, it is required.

 ```[POST] /payments/mercadopago/create```
 
 If everything went well, and the request was successful, the mercadopago method will return a url to go to the payment methods.

 ```json
{
    "url": "https://sandbox.mercadopago.cl/checkout/v1/redirect?pref_id=567061042-a0d6bf78-c9db-4743-bb01-ee4fb31699"
}
```

 ```[POST] /payments/mercadopago/create```
 
 If everything went well, and the request was successful, the transbank method will return a url and a token, to go to the payment methods.

 In this case, the request must be made through a POST, sending the token as a parameter.

 ```json
{
    "token_ws": "e974070a11ba9d117d9f6f85d576d87fb1efb2cf05962b366cb35544ab1fc116",
    "url": "https://webpay3gint.transbank.cl/webpayserver/initTransaction"
}
```



