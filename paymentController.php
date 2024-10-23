<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function createOrder(Request $request)
    {
        $amount = $request->input('amount') * 100; // Razorpay expects amount in the smallest currency unit
        $currency = $request->input('currency');

        // Razorpay API credentials
        $apiKey = env('RAZORPAY_KEY_ID');
        $apiSecret = env('RAZORPAY_KEY_SECRET');

        $api = new Api($apiKey, $apiSecret);

        $orderData = [
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => 'receipt#1',
        ];

        try {
            $order = $api->order->create($orderData);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
