<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json(Customer::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string',
            'address'        => 'nullable|string',
            'customer_group' => 'sometimes|string',
            'loyalty_points' => 'sometimes|integer',
            'balance'        => 'sometimes|numeric',
        ]);

        $customer = Customer::create($data);
        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string',
            'address'        => 'nullable|string',
            'customer_group' => 'sometimes|string',
            'loyalty_points' => 'sometimes|integer',
            'balance'        => 'sometimes|numeric',
        ]);

        $customer->update($data);
        return response()->json($customer);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['message' => 'Customer profile removed.']);
    }

    /**
     * Retrieve transaction ledger history for the customer.
     */
    public function ledger($id)
    {
        $customer = Customer::findOrFail($id);
        $sales = \App\Models\Sale::where('customer_id', $customer->id)
            ->with(['items.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'customer' => $customer,
            'sales'    => $sales
        ]);
    }
}
