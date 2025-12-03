<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHoldRequest;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Hold;
use App\Services\InventoryService;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, InventoryService $inventory)
    {
        $hold = Hold::findOrFail($request->validated()['hold_id']);

        if ($hold->status !== 'valid' || $hold->expires_at->isPast()) {
            return response()->json(['error' => 'Hold expired or invalid'], 400);
        }

        try {
            $order = $inventory->convertHoldToOrder($hold);

            return response()->json([
                'order_id' => $order->id,
                'status' => $order->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
