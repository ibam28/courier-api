<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    public const LEVELS = [1, 2, 3, 4, 5];

    public const STATUSES = ['active', 'inactive', 'suspended'];

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'vehicle_type',
        'vehicle_plate',
        'level',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'level' => 'integer',
        'joined_at' => 'datetime',
    ];
}