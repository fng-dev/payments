<?php

namespace Fng\Payments\Http\Controllers;

use Transbank\Webpay\Webpay;
use Transbank\Webpay\Configuration;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebPayPaymentController extends Controller
{

    protected $webpay, $configuration;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setupWebPay();
    }

    public function createPayment(Request $request)
    {

        $validations = [
            // Items validations
            "items" => "required|array",
            "items.*.id" => "required|integer",
            "items.*.name" => "required|string",
            "items.*.quantity" => "required|numeric",
            "items.*.price_unit" => "required|numeric",

            //Payer validations
            "payer.name" => "required|string",
            "payer.surname" => "required|string",
            "amount" => "required|numeric"
        ];

        $this->validate($request, $validations);

        $buyOrder = date("YmdHis") . rand(0, 9999) . "WP";

        $returnUrl = env('APP_URL') . "/payments/webpay/return";
        $finalUrl = env('APP_URL') . "/payments/webpay/final";

        $transaction = $this->webpay->getNormalTransaction();

        $initResult = $transaction->initTransaction(
            $request->amount,
            $buyOrder,
            $buyOrder,
            $returnUrl,
            $finalUrl
        );

        $token = $initResult->token;
        $url = $initResult->url;

        $params = [
            "token_ws" => $token,
            "url" => $url
        ];

        return response()->json($params);

    }

    public function setupWebPay()
    {
        if (env('PAYMENT_ENVIROMENT') == 'PRODUCTION') {

            $privateKey = file_get_contents(env("TRANSBANK_PRIVATE_KEY"));
            $publicCert = file_get_contents(env("TRANSBANK_PUBLIC_CERT"));
            $webpayCert = file_get_contents(env("TRANSBANK_WEBPAY_CERT"));
            $commerceCode = env("TRANSBANK_CODIGO_COMERCIO");

            $this->configuration = new Configuration();
            $this->configuration->setCommerceCode($commerceCode);
            $this->configuration->setEnvironment('PRODUCCION');
            $this->configuration->setPrivateKey($privateKey);
            $this->configuration->setPublicCert($publicCert);
            $this->configuration->setWebpayCert($webpayCert);
            $this->webpay = new Webpay($this->configuration);
        } else {
            $this->configuration = Configuration::forTestingWebpayPlusNormal();
            $this->webpay = new Webpay($this->configuration);
        }
    }

}
