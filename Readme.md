# WEBPAY - MERCADO PAGO PAYMENTS

## Class Features
- Integration with laravel / lumen
- Automatic table creation in database
- Automatic route configurations

## Installation

```sh
composer require fng-dev/gux-payments:dev-master
```

## Installation Errors

If the version of the doctrine/inflector is greater than 1.4, it will be necessary to make a dowgrade for this version for the sdk of the mercadopago work.

```composer require doctrine/inflector:^1  ```

## Configs

### ENV

laravel / lumen

MERCADO PAGO ENVIROMENT

The return url for the webhook, as well as the response url, use the laravel / lumen ```APP_URL``` variable to build the final url. Don't forget to add it to the Mercado Pago panel.

```
APP_URL=https://www.your-site.com
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
    "session_id": 27959191, // Paid purchase id in Mercado Pago
    "collection_id": "27966139",
    "preference_id": "567061042-138f6a5f-2553-4542-a2f2-0e2a1618b5cb",
    "merchant_order_id": "1594255766",
    "share_number": null,
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

TRANSBANK_CODIGO_COMERCIO=35669965
TRANSBANK_PRIVATE_KEY='url/to/key'
TRANSBANK_PUBLIC_CERT='url/to/key'
TRANSBANK_WEBPAY_CERT='url/to/key'
TRANSBANK_INTERNAL_WEBHOOK=false|/your/endpoint/to/your/hook
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
