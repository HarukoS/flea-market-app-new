<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'purchase_id',
        'sender_id',
        'message',
        'image_path',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
