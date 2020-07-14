<?php

namespace Fng\Payments\Http\Controllers;

use Exception;
use MercadoPago\SDK;
use MercadoPago\Item;
use MercadoPago\Payer;
use MercadoPago\Preference;
use Illuminate\Http\Request;
use Fng\Payments\Models\Payment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Fng\Payments\Models\Item as PaymentItem;
use Illuminate\Support\Facades\Route;

class MercadoPagoPaymentController extends Controller
{

    private $CONFIG;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setupMercadoPago();
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

        $user = Auth::user();
        $buyOrder = date("YmdHis") . rand(0, 9999) . "MP";

        try {
            return DB::transaction(function () use ($request, $buyOrder, $user) {
                $payment = Payment::create([
                    "amount" => $request->amount,
                    "shipping_amount" => $request->shipping_amount,
                    "status" => "initiated", // initiated, approved, failed, in_proccess,
                    "payment_type" => $request->payment_type,
                    "payment_company" => "mercado_pago",
                    "buy_order" => $buyOrder,
                    "user_id" => $user->id,
                ]);

                $preference = new Preference();

                $itemsMP = [];
                foreach ($request->items as $item) {
                    $item = (object) $item;
                    $itemMP = new Item();
                    $itemMP->id = $item->id;
                    $itemMP->title = $item->name;
                    $itemMP->quantity = $item->quantity;
                    $itemMP->currency_id = "CLP";
                    $itemMP->unit_price = $item->price_unit;

                    PaymentItem::create([
                        "price_unit" => $item->price_unit,
                        "quantity" => $item->quantity,
                        "unit" => isset($item->unit) ? $item->unit : null,
                        "name" => $item->name,
                        "description" => isset($item->description) ? $item->description : null,
                        "img_url" => isset($item->img_url) ? $item->img_url : null,
                        "payment_id" => $payment->id,
                    ]);

                    $itemsMP[] = $itemMP;
                }

                $preference->items = $itemsMP;

                $preference->back_urls = [
                    "success" => env('APP_URL') . "/payments/mercadopago/success",
                    "pending" => env('APP_URL') . "/payments/mercadopago/pending",
                    "failure" => env('APP_URL') . "/payments/mercadopago/failure",
                ];

                $preference->auto_return = "all";

                $payerData = (object) $request->payer;
                $payer = new Payer();
                $payer->name = $payerData->name;
                $payer->surname = $payerData->surname;

                $preference->payer = $payer;

                $webHook = env('MP_WEBHOOK', true);

                if ($webHook) {
                    $preference->notification_url =  env('APP_URL') . "/payments/mercadopago/webhook";
                }

                $preference->external_reference = $buyOrder;
                $preference->save();

                $initPoint = getenv("PAYMENT_ENVIROMENT") === "PRODUCTION" ? $preference->init_point : $preference->sandbox_init_point;

                return response()->json([
                    "url" => $initPoint
                ]);
            });
        } catch (Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ]);
        }
    }

    public function failure(Request $request)
    {
        $payment = Payment::where('buy_order', $request->external_reference)->get()->first();
        $payment->update([
            "status" => "failed",
            "collection_id" => $request->collection_id,
            "payment_type" => $request->payment_type,
            "merchant_order_id" => $request->merchant_order_id,
            "preference_id" => $request->preference_id,
        ]);
        $payment->makeHidden('user_id');
        return redirect()->to(env('FRONT_RETURN_PAYMENT') . '?' . http_build_query($payment->toArray()));
    }

    public function success(Request $request)
    {
        $payment = Payment::where('buy_order', $request->external_reference)->get()->first();
        $payment->update([
            "status" => $request->collection_status,
            "collection_id" => $request->collection_id,
            "payment_type" => $request->payment_type,
            "merchant_order_id" => $request->merchant_order_id,
            "preference_id" => $request->preference_id,
        ]);
        $payment->makeHidden('user_id');
        return redirect()->to(env('FRONT_RETURN_PAYMENT') . '?' . http_build_query($payment->toArray()));
    }

    public function pending(Request $request)
    {
        $payment = Payment::where('buy_order', $request->external_reference)->get()->first();
        $payment->update([
            "status" => $request->collection_status,
            "collection_id" => $request->collection_id,
            "payment_type" => $request->payment_type,
            "merchant_order_id" => $request->merchant_order_id,
            "preference_id" => $request->preference_id,
        ]);
        $payment->makeHidden('user_id');
        return redirect()->to(env('FRONT_RETURN_PAYMENT') . '?' . http_build_query($payment->toArray()));
    }

    public function updateStatusWebHook(Request $request)
    {
        if (!$request->has('type')) {
            return response()->json(['message' => 'There is no type parameter'], 400);
        }

        if ($request->type == 'payment') {
            $getRequest = SDK::get("/v1/payments/" . $request->data['id']);
            $response = $getRequest['body'];
            $payment = Payment::where('buy_order', $response['external_reference'])->get()->first();
            $payment->status = $response['status'];
            $payment->session_id = $request->data['id'];
            $payment->save();

            $internalWebHook = env('MERCADO_PAGO_INTERNAL_WEBHOOK', false);

            if (!$internalWebHook) {
                return response()->json(['message' => 'success'], 200);
            }

            $internalRequest = Request::create($internalWebHook, 'POST', $payment->toArray());
            $response = app()->handle($internalRequest);
            return response()->json($response, 200)->original;
        }

        return response()->json(['message' => 'Type not configured'], 400);
    }

    private function setupMercadoPago()
    {
        SDK::initialize();

        $this->CONFIG = SDK::config();

        $ACCESS_TOKEN = (env("PAYMENT_ENVIROMENT") == 'PRODUCTION') ? env("MERCADO_PAGO_ACCESS_TOKEN") : env("MERCADO_PAGO_ACCESS_TOKEN_SANDBOX");

        $this->CONFIG->set("ACCESS_TOKEN", $ACCESS_TOKEN);
    }
}
