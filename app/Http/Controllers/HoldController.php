<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHoldRequest;
use Illuminate\Http\Request;
use App\Services\InventoryService;

class HoldController extends Controller
{
    public function store(CreateHoldRequest $request, InventoryService $inventory)
    {
        $data = $request->validated();

        $holdSeconds = $request->header('X-Hold-Seconds', 120);

        try {
            $hold = $inventory->createHold($data['product_id'], $data['qty'], $holdSeconds);

            return response()->json([
                'hold_id' => $hold->id,
                'expires_at' => $hold->expires_at,
            ]);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
