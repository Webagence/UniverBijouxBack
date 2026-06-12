<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingCarrier;

class CarrierController extends Controller
{
    public function index()
    {
        $carriers = ShippingCarrier::active()->get()->map(function ($carrier) {
            return [
                'id' => $carrier->id,
                'name' => $carrier->name,
                'carrier_name' => $carrier->carrier_name,
                'price' => (float) $carrier->price,
                'delay' => $carrier->delay,
            ];
        });

        return response()->json($carriers);
    }
}
