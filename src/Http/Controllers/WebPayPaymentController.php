<?php

namespace Fng\Payments\Http\Controllers;

use Illuminate\Http\Request;
use Transbank\Webpay\Webpay;
use Fng\Payments\Models\Item;
use Fng\Payments\Models\Payment;
use Illuminate\Support\Facades\DB;
use Transbank\Webpay\Configuration;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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

        $auth = env('GUX_AUTH', true);

        if ($auth) {
            $user = Auth::user();
        } else {
            $user = (object) ["id" => 1];
        }

        try {
            return DB::transaction(function () use ($request, $buyOrder, $user) {
                $payment = Payment::create([
                    "amount" => $request->amount,
                    "shipping_amount" => $request->shipping_amount,
                    "status" => "initiated", // initiated, approved, failed, in_proccess,
                    "payment_type" => $request->payment_type,
                    "payment_company" => "webpay",
                    "buy_order" => $buyOrder,
                    "details" => "Transacción iniciada",
                    "user_id" => $user->id,
                ]);


                foreach ($request->items as $item) {
                    $item = (object) $item;
                    Item::create([
                        "external_id" => $item->id,
                        "price_unit" => $item->price_unit,
                        "quantity" => $item->quantity,
                        "unit" => isset($item->unit) ? $item->unit : null,
                        "name" => $item->name,
                        "description" => isset($item->description) ? $item->description : null,
                        "img_url" => isset($item->img_url) ? $item->img_url : null,
                        "payment_id" => $payment->id,
                    ]);
                }

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
            });
        } catch (Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ]);
        }
    }

    public function returnTransbank(Request $request)
    {
        $response = $this->webpay->getNormalTransaction()->getTransactionResult($request->token_ws);

        if ($response->detailOutput->responseCode === 0) {

            $payment = Payment::where('buy_order', $response->buyOrder)->get()->first();

            $payment->update([
                "status" => "approved",
                "session_id" => $request->token_ws,
                "payment_type" => SELF::paymentType($response->detailOutput->paymentTypeCode),
                "share_number" => $response->detailOutput->sharesNumber,
                "details" => SELF::responseCode($response->detailOutput->responseCode),
            ]);

            $returnUrl = $response->urlRedirection;

            $internalWebHook = env('TRANSBANK_INTERNAL_WEBHOOK', false);

            if (!$internalWebHook) {
                return SELF::redirect('POST', $returnUrl, ['token_ws' => $request->token_ws]);
            }

            $internalRequest = Request::create($internalWebHook, 'POST', $payment->toArray());
            $response = app()->handle($internalRequest);

            return SELF::redirect('POST', $returnUrl, ['token_ws' => $request->token_ws]);



        } else {

            $payment = Payment::where('buy_order', $response->buyOrder)->get()->first();

            $payment->update([
                "status" => "failed",
                "details" => SELF::responseCode($response->detailOutput->responseCode),
            ]);

            $payment->makeHidden('user_id');
            return redirect()->to(env('FRONT_RETURN_PAYMENT') . '?' . http_build_query($payment->toArray()));
        }
    }

    public function finalTransaction(Request $request)
    {
        $payment = Payment::where('session_id', $request->token_ws)->get()->first();
        $payment->makeHidden('user_id');
        return redirect()->to(env('FRONT_RETURN_PAYMENT') . '?' . http_build_query($payment->toArray()));
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

    public static function paymentType($type)
    {
        switch ($type) {
            case 'VD':
                return "Venta Débito";
                break;
            case 'VN':
                return "Venta Normal";
                break;
            case 'VC':
                return "Venta en cuotas";
                break;
            case 'SI':
                return "3 cuotas sin interés";
                break;
            case 'S2':
                return "2 cuotas sin interés";
                break;
            case 'NC':
                return "N Cuotas sin interé";
                break;
            case 'VP':
                return "Venta Prepago";
                break;
            default:
                return "Venta Normal";
        }
    }

    public static function responseCode($code)
    {
        switch ($code) {
            case '0':
                return "Transacción aprobada";
                break;
            case '-1':
                return "Rechazo de transacción - Reintente (Posible error en el ingreso de datos de la transacción)";
                break;
            case '-2':
                return "Rechazo de transacción (Se produjo fallo al procesar la transacción. Este mensaje de rechazo está relacionado a parámetros de la tarjeta y/o su cuenta asociada)";
                break;
            case '-3':
                return "Error en transacción (Interno Transbank)";
                break;
            case '-4':
                return "Rechazo emisor (Rechazada por parte del emisor)";
                break;
            case '-5':
                return "Rechazo - Posible Fraude (Transacción con riesgo de posible fraude)";
                break;
            default:
                return "Transacción aprobada";
        }
    }

    public static function redirect($method, $url, $params)
    {

        $fields = [];
        foreach ($params as $key => $field) {
            $fields[] = [
                "name" => $key,
                "value" => $field,
            ];
        }

        $fields = json_encode($fields);

        $form = <<< EOT
            <body></body>
            <script type='text/javascript'>
                const form = document.createElement('form');
                form.method = '{$method}';
                form.action = "{$url}";
                params = {$fields};
                params.map( (value, index) => {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = value.name;
                    hiddenField.value = value.value;
                    form.appendChild(hiddenField);
                })
                document.body.appendChild(form);
                form.submit();
            </script>

EOT;

        return $form;
    }
}
