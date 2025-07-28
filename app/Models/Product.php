<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The possible product status values.
     */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'title',
        'description',
        'price',
        'stock',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->status = $product->stock > 0 
                ? self::STATUS_AVAILABLE 
                : self::STATUS_UNAVAILABLE;
        });

        static::updating(function ($product) {
            $product->status = $product->stock > 0 
                ? self::STATUS_AVAILABLE 
                : self::STATUS_UNAVAILABLE;
        });
    }

    /**
     * Get the status options.
     *
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_UNAVAILABLE => 'Unavailable',
        ];
    }
}