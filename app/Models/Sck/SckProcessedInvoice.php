<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;

class SckProcessedInvoice extends Model
{
    protected $guarded = [];
    protected $casts = ['invoice_date' => 'date'];
}
