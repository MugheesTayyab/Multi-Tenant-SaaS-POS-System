<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json(Supplier::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|string',
            'address'      => 'nullable|string',
            'balance'      => 'sometimes|numeric',
        ]);

        $supplier = Supplier::create($data);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|string',
            'address'      => 'nullable|string',
            'balance'      => 'sometimes|numeric',
        ]);

        $supplier->update($data);
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json(['message' => 'Supplier removed.']);
    }

    /**
     * Retrieve transaction ledger history for the supplier.
     */
    public function ledger($id)
    {
        $supplier = Supplier::findOrFail($id);
        $purchases = \App\Models\Purchase::where('supplier_id', $supplier->id)
            ->with(['items.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'supplier'  => $supplier,
            'purchases' => $purchases
        ]);
    }
}
