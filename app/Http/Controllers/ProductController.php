<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function show($id)
    {
        $key = 'product:' . $id;

        $product = Cache::remember($key, 5, function () use ($id) {
            return Product::findOrFail($id);
        });

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'stock_total' => $product->stock_total,
            'stock_reserved' => $product->stock_reserved,
            'stock_committed' => $product->stock_committed,
            'stock_sold' => $product->stock_sold,
            'available' => $product->available(),
        ]);
    }
}
