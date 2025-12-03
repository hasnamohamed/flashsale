<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentWebhookRequest;
use Illuminate\Http\Request;
use App\Models\Hold;
use App\Models\Order;
use App\Models\WebhookIdempotency;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(PaymentWebhookRequest $request, InventoryService $inventory)
    {
        $data = $request->validated();

        $key = $data['idempotency_key'];

        return DB::transaction(function () use ($data, $key, $inventory) {
            $record = WebhookIdempotency::where('key', $key)->lockForUpdate()->first();

            if ($record && $record->processed_at) {
                return response()->json($record->result);
            }

            if (!$record) {
                $record = WebhookIdempotency::create(['key' => $key]);
            }

            $hold = Hold::findOrFail($data['hold_id']);
            $order = Order::where('hold_id', $hold->id)->lockForUpdate()->firstOrFail();

            $success = $data['status'] === 'success';
            $inventory->finalizePayment($order, $success);

            $record->result = ['order_status' => $order->status];
            $record->processed_at = now();
            $record->save();

            return response()->json($record->result);
        });
    }
}
