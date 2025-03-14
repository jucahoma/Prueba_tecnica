<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    // Definicion de los campos
    protected $fillable = ['name', 'email', 'password'];

    //relacionde compras
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}

