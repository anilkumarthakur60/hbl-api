<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HblResponse extends Model
{
    /** @use HasFactory<\Database\Factories\HblResponseFactory> */
    use HasFactory;

    protected $fillable = ['response', 'status', 'order_no'];

    protected $casts = [
        'response' => 'json',
    ];
}
