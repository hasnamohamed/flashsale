<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookIdempotency extends Model {
    protected $fillable = ['key','payload_hash','result','processed_at'];
    protected $casts = ['result' => 'array','processed_at' => 'datetime'];
}
