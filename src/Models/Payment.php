<?php

namespace Fng\Payments\Models;

use Fng\Payments\Models\Item;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = "gux_payments";

    protected $fillable = [
        'buy_order',
        'amount',
        'shipping_amount',
        'status',
        'payment_type',
        'payment_company',
        'session_id',
        'collection_id',
        'preference_id',
        'merchant_order_id',
        'share_number',
        'details',
        'user_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    protected $hidden = [];

    /**
     *
     * Array with validation rules
     *
     * @var array
     *
     */

    protected static $rules = [];


    /**
     * Validation Rules
     *
     * @var array
     */

    static public function getRules(): array
    {
        return self::$rules;
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
