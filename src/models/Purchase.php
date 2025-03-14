<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}
