<?php

namespace App\Http\Controllers;

use App\Models\WarehouseStock;
use Illuminate\Http\Request;

class WarehouseStockController extends Controller
{
    public function index()
    {
        return WarehouseStock::with('medicine')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer',
        ]);

        $stock = WarehouseStock::where('medicine_id', $request->medicine_id)->first();
        if ($stock) {
            $stock->quantity += $request->quantity;
            $stock->save();
        } else {
            $stock = WarehouseStock::create($request->all());
        }

        return $stock;
    }

    public function show(WarehouseStock $warehouseStock)
    {
        $warehouseStock->load('medicine');
        $this->setExpiryStatus($warehouseStock->medicine);

        return $warehouseStock;
    }

    // Method to set expiry status based on expiry date
    private function setExpiryStatus($medicine)
    {
        $expiryDate = new \DateTime($medicine->expiry_date);
        $now = new \DateTime();
        $diff = $now->diff($expiryDate);

        if ($diff->m >= 5) {
            $medicine->status = 'hijau';
        } elseif ($diff->m >= 3) {
            $medicine->status = 'kuning';
        } elseif ($diff->m > 0) {
            $medicine->status = 'merah';
        } else {
            $medicine->status = 'hitam';
        }
    }

    public function update(Request $request, WarehouseStock $warehouseStock)
    {
        $request->validate([
            'quantity' => 'required|integer',
        ]);

        $warehouseStock->update($request->all());

        return $warehouseStock;
    }

    public function destroy(WarehouseStock $warehouseStock)
    {
        $warehouseStock->delete();

        return response()->noContent();
    }
}
