<?php

namespace Fng\Payments\Http\Controllers;

use Fng\Payments\Models\Payment;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getPayments()
    {
        $auth = env('GUX_AUTH', true);

        if ($auth) {
            $user = Auth::user();
        } else {
            $user = (object) ["id" => 1];
        }

        $payments = Payment::where('user_id', $user->id)->with('items')->paginate(12);

        return response()->json($payments);
    }

    public function getPaymentsById($id)
    {
        $auth = env('GUX_AUTH', true);

        if ($auth) {
            $user = Auth::user();
        } else {
            $user = (object) ["id" => 1];
        }

        $payment = Payment::where('id', $id)
            ->where('user_id', $user->id)
            ->with('items')
            ->get()
            ->first();

        if ($payment) {
            return response()->json($payment);
        }

        return response()->json([
            "message" => "Forbidden"
        ], 401);
    }
}
