<?php

namespace App\Http\Controllers;

use App\Models\PharmacyStock;
use Illuminate\Http\Request;

class PharmacyStockController extends Controller
{
    public function index()
    {
        return PharmacyStock::with('medicine')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer',
        ]);

        $stock = PharmacyStock::where('medicine_id', $request->medicine_id)->first();
        if ($stock) {
            $stock->quantity += $request->quantity;
            $stock->save();
        } else {
            $stock = PharmacyStock::create($request->all());
        }

        return $stock;
    }

    public function show(PharmacyStock $pharmacyStock)
    {
        $pharmacyStock->load('medicine');
        $this->setExpiryStatus($pharmacyStock->medicine);

        return $pharmacyStock;
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

    public function update(Request $request, PharmacyStock $pharmacyStock)
    {
        $request->validate([
            'quantity' => 'required|integer',
        ]);

        $pharmacyStock->update($request->all());

        return $pharmacyStock;
    }

    public function destroy(PharmacyStock $pharmacyStock)
    {
        $pharmacyStock->delete();

        return response()->noContent();
    }
}
