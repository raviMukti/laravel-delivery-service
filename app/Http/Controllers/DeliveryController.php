<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function getDeliveries()
    {
        $deliveries = Delivery::orderBy("created_at","desc")->get();
        return response()->json($deliveries,200);
    }

    private function sendConfirmationEmail($order)
    {
        $toEmail = $order->email;
        $message = "Pesanan Anda dengan ID #" . $order->order_id . " telah dipick up.";
        // Kirim email
        \Mail::to($toEmail)->send(new OrderConfirmation($message));
    }
}
