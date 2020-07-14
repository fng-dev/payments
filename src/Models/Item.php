<?php

namespace Fng\Payments\Models;

use Fng\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = "gux_items_payment";

    protected $fillable = [
        'amount',
        'quantity',
        'unit',
        'name',
        'description',
        'img_url',
        'payment_id'
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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
