<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index()
    {
        return Medicine::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'expiry_date' => 'required|date',
        ]);

        return Medicine::create($request->all());
    }

    public function show($id)
    {
        $medicine = Medicine::findOrFail($id);
        $this->setExpiryStatus($medicine);

        return $medicine;
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

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'name' => 'required',
            'expiry_date' => 'required|date',
        ]);

        $medicine->update($request->all());

        return $medicine;
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return response()->noContent();
    }

    public function checkExpiryStatus($medicineId)
    {
        $medicine = Medicine::find($medicineId);

        if (!$medicine) {
            return response()->json(['error' => 'Obat tidak ditemukan'], 404);
        }

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

        $medicine->save();

        return response()->json(['status' => $medicine->status]);
    }
}
