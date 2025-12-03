<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Hold;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Create a temporary hold on a product stock
     *
     * @param int $productId
     * @param int $qty
     * @param int $seconds
     * @return Hold
     * @throws \Exception
     */
    public function createHold(int $productId, int $qty, int $seconds = 120): Hold
    {
        return DB::transaction(function () use ($productId, $qty, $seconds) {
            /** @var Product $product */
            $product = Product::lockForUpdate()->findOrFail($productId);

            if ($qty <= 0) {
                throw new \Exception("Quantity must be greater than 0.");
            }

            if ($qty > $product->available()) {
                throw new \Exception("Not enough stock available.");
            }

            $product->stock_reserved += $qty;
            $product->save();

            $hold = Hold::create([
                'product_id' => $product->id,
                'qty' => $qty,
                'status' => 'valid',
                'expires_at' => Carbon::now()->addSeconds($seconds),
            ]);

            return $hold;
        });
    }

    /**
     * Convert a valid hold to an order
     *
     * @param Hold $hold
     * @return Order
     * @throws \Exception
     */
    public function convertHoldToOrder(Hold $hold): Order
    {
        return DB::transaction(function () use ($hold) {
            $hold = Hold::lockForUpdate()->findOrFail($hold->id);

            if ($hold->status !== 'valid' || $hold->expires_at->isPast()) {
                throw new \Exception("Hold expired or invalid.");
            }

            $product = Product::lockForUpdate()->findOrFail($hold->product_id);

            if ($hold->qty > $product->stock_reserved) {
                throw new \Exception("Reserved stock mismatch.");
            }

            $product->stock_reserved -= $hold->qty;
            $product->stock_committed += $hold->qty;
            $product->save();

            $hold->status = 'used';
            $hold->save();

            $order = Order::create([
                'hold_id' => $hold->id,
                'amount' => $product->price * $hold->qty,
                'status' => 'pending',
            ]);

            return $order;
        });
    }

    /**
     * Finalize payment for an order (idempotent)
     *
     * @param Order $order
     * @param bool $success
     * @return void
     */
    public function finalizePayment(Order $order, bool $success): void
    {
        DB::transaction(function () use ($order, $success) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $hold = Hold::lockForUpdate()->findOrFail($order->hold_id);
            $product = Product::lockForUpdate()->findOrFail($order->product_id);

            if ($success) {
                if ($order->status !== 'paid') {
                    $order->status = 'paid';
                    $order->save();

                    $product->stock_committed -= $hold->qty;
                    $product->stock_sold += $hold->qty;
                    $product->save();
                }
            } else {
                if ($order->status !== 'paid') {
                    $order->status = 'cancelled';
                    $order->save();

                    $product->stock_committed -= $hold->qty;
                    $product->stock_reserved += $hold->qty;
                    $product->save();

                    $hold->status = 'cancelled';
                    $hold->save();
                }
            }
        });
    }

    /**
     * Expire a hold (background task)
     *
     * @param Hold $hold
     * @return void
     */
    public function expireHold(Hold $hold): void
    {
        DB::transaction(function () use ($hold) {
            $hold = Hold::lockForUpdate()->findOrFail($hold->id);

            if ($hold->status === 'valid' && $hold->expires_at->isPast()) {
                $product = Product::lockForUpdate()->findOrFail($hold->product_id);

                $product->stock_reserved -= $hold->qty;
                $product->save();

                $hold->status = 'expired';
                $hold->save();
            }
        });
    }
}
