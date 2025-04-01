<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a product entity in the database.
 *
 * This class interacts with the 'tblProductData' table and
 * defines the structure of a product's data including its
 * attributes and data types.
 */
class Product extends Model
{
    protected $table = 'tblProductData';

    protected $primaryKey = 'intProductDataId';

    public $timestamps = false;

    protected $fillable = [
        'strProductName',
        'strProductDesc',
        'strProductCode',
        'strProductStock',
        'strProductCost',
        'dtmAdded',
        'dtmDiscontinued',
    ];

    protected $casts = [
        'dtmAdded' => 'datetime',
        'dtmDiscontinued' => 'datetime',
    ];
}
