<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model {
    use HasFactory;
    protected $table = 'products';
    protected $fillable = ['name','stock_total','stock_reserved','stock_committed','stock_sold','price'];
    protected $casts = [
        'stock_total' => 'integer',
        'stock_reserved' => 'integer',
        'stock_committed' => 'integer',
        'stock_sold' => 'integer',
        'price' => 'decimal:2',
    ];


    public function available(): int {
        return max(0, $this->stock_total - $this->stock_reserved - $this->stock_committed - $this->stock_sold);
    }
    public function holds() {
        return $this->hasMany(Hold::class);
    }

}
