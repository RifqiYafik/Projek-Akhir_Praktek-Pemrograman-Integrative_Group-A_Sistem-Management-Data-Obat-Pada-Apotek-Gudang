<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\WarehouseStock;
use App\Models\PharmacyStock;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::with('medicine')->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer',
        ]);

        // Determine transaction type based on quantity
        $type = 'in'; // default to 'in'
        if ($request->quantity < 0) {
            $type = 'return'; // if quantity is negative, it's a return
        } elseif ($request->quantity > 0) {
            $type = 'out'; // if quantity is positive, it's an out
        }

        // Create transaction
        $transaction = Transaction::create([
            'medicine_id' => $request->medicine_id,
            'type' => $type,
            'quantity' => $request->quantity,
        ]);

        // Update stocks accordingly
        if ($type === 'in') {
            // Increase warehouse stock
            $stock = WarehouseStock::where('medicine_id', $request->medicine_id)->first();
            if ($stock) {
                $stock->quantity += $request->quantity;
                $stock->save();
            } else {
                WarehouseStock::create([
                    'medicine_id' => $request->medicine_id,
                    'quantity' => $request->quantity,
                ]);
            }
        } elseif ($type === 'out') {
            // Decrease warehouse stock and increase pharmacy stock
            $stock = WarehouseStock::where('medicine_id', $request->medicine_id)->first();
            if ($stock && $stock->quantity >= $request->quantity) {
                $stock->quantity -= $request->quantity;
                $stock->save();

                $pharmacyStock = PharmacyStock::where('medicine_id', $request->medicine_id)->first();
                if ($pharmacyStock) {
                    $pharmacyStock->quantity += $request->quantity;
                    $pharmacyStock->save();
                } else {
                    PharmacyStock::create([
                        'medicine_id' => $request->medicine_id,
                        'quantity' => $request->quantity,
                    ]);
                }
            } else {
                return response()->json(['error' => 'Insufficient stock in warehouse'], 400);
            }
        } elseif ($type === 'return') {
            // Decrease pharmacy stock and increase warehouse stock
            $pharmacyStock = PharmacyStock::where('medicine_id', $request->medicine_id)->first();
            if ($pharmacyStock && $pharmacyStock->quantity >= abs($request->quantity)) {
                $pharmacyStock->quantity -= abs($request->quantity);
                $pharmacyStock->save();

                $warehouseStock = WarehouseStock::where('medicine_id', $request->medicine_id)->first();
                if ($warehouseStock) {
                    $warehouseStock->quantity += abs($request->quantity);
                    $warehouseStock->save();
                } else {
                    WarehouseStock::create([
                        'medicine_id' => $request->medicine_id,
                        'quantity' => abs($request->quantity),
                    ]);
                }
            } else {
                return response()->json(['error' => 'Insufficient stock in pharmacy'], 400);
            }
        }

        return $transaction;
    }


    public function show(Transaction $transaction)
    {
        return $transaction->load('medicine');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'quantity' => 'required|integer',
        ]);

        $transaction->update($request->all());

        return $transaction;
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response()->noContent();
    }
}
