<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'expiry_date', 'status'
    ];
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function pharmacyStocks()
    {
        return $this->hasMany(PharmacyStock::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
