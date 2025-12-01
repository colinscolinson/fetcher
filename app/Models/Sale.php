<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_id',
        'g_number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'total_price',
        'discount_percent',
        'is_supply',
        'is_realization',
        'warehouse_name',
        'country_name',
        'oblast_okrug_name',
        'region_name',
        'income_id',
        'odid',
        'spp',
        'for_pay',
        'finished_price',
        'price_with_disc',
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_storno',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
