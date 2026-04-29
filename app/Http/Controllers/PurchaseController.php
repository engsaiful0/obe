<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase as PurchaseModel;
use App\Models\PurchaseUniqueId as PurchaseUniqueIdModel;
use App\Models\PurchaseItem as PurchaseItemModel;
use App\Models\Supplier;
use App\Models\PaymentMethod;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function addPurchase()
    {
        $suppliers = Supplier::all();
        $payment_methods = PaymentMethod::all();
        $items = Item::orderBy('item_name')->get();
        $units = Unit::orderBy('unit_name')->get();
        // Get the last serial
        $latest = PurchaseUniqueIdModel::latest('serial')->first();
        $serial = $latest ? $latest->serial + 1 : 1;

        // Format as S-0001, S-0002, ...
        $purchase_unique_id = 'P-' . str_pad($serial, 4, '0', STR_PAD_LEFT);
        return view('content.purchase.add', compact('suppliers', 'payment_methods', 'items', 'units', 'purchase_unique_id', 'serial'));
    }
    // PurchaseController.php
    public function productRow(Request $request)
    {
        $items = Item::orderBy('item_name')->get();
        $units = Unit::orderBy('unit_name')->get();
        $rowIndex = $request->get('row_index', 0);
        return view('content.purchase.product_row', compact('items', 'units', 'rowIndex'));
    }


    public function viewPurchase(Request $request)
    {
        $payment_methods = PaymentMethod::all();
        $query = PurchaseModel::with(['supplier', 'purchaseItems.item', 'purchaseItems.unit', 'paymentMethod'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Get suppliers for filter dropdown
        $suppliers = Supplier::where('user_id', Auth::id())->get();

        // Paginate results
        $purchases = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('content.purchase.view', compact('purchases', 'suppliers', 'payment_methods'));
    }

    public function getPurchase(Request $request)
    {
        $purchases = PurchaseModel::with(['supplier', 'purchaseItems.item', 'purchaseItems.unit'])
            ->where('user_id', Auth::id())
            ->get();
        return response()->json([
            'data' => $purchases,
        ]);
    }

    public function getSuppliers()
    {
        $suppliers = Supplier::where('user_id', Auth::id())->get();
        return response()->json($suppliers);
    }

    public function getItems()
    {
        $items = Item::where('user_id', Auth::id())->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_number' => 'required|string|max:255|unique:purchases,purchase_number',
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'paid' => 'required|numeric|min:0',
            'due' => 'required|numeric|min:0',
            'net_total' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'serial' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $userId = $user->id;

            $purchase = PurchaseModel::create([
                'purchase_number' => $request->purchase_number,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'paid' => $request->paid,
                'due' => $request->due,
                'net_total' => $request->net_total,
                'payment_method_id' => $request->payment_method_id,
                'remarks' => $request->remarks,
                'user_id' => $userId,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            // Create purchase items
            foreach ($request->items as $item) {
                PurchaseItemModel::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }
            PurchaseUniqueIdModel::create([
                'serial' => $request->serial,
                'purchase_number' => $purchase->purchase_number,
                'purchase_id' => $purchase->id,
                'user_id' => $userId,
                'created_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Purchase created successfully.', 'data' => $purchase], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error creating purchase: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $userId = $user->id;
        $request->validate([
            'purchase_number' => 'required|string|max:255|unique:purchases,purchase_number,' . $id,
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'paid' => 'required|numeric|min:0',
            'due' => 'required|numeric|min:0',
            'net_total' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'remarks' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',

        ]);

        DB::beginTransaction();
        try {
            $purchase = PurchaseModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

            $purchase->update([
                'purchase_number' => $request->purchase_number,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'paid' => $request->paid,
                'due' => $request->due,
                'net_total' => $request->net_total,
                'payment_method_id' => $request->payment_method_id,
                'remarks' => $request->remarks,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

            // Update existing purchase items or create new ones
            $existingItems = $purchase->purchaseItems;
            $requestItemIds = collect($request->items)->pluck('item_id')->toArray();

            // Update existing items
            foreach ($request->items as $index => $item) {
                $existingItem = $existingItems->where('item_id', $item['item_id'])->first();

                if ($existingItem) {
                    // Update existing item
                    $existingItem->update([
                        'unit_id' => $item['unit_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'updated_by' => $userId,
                        'updated_at' => now(),
                    ]);
                } else {
                    // Create new item (only if it doesn't exist)
                    PurchaseItemModel::create([
                        'purchase_id' => $purchase->id,
                        'item_id' => $item['item_id'],
                        'unit_id' => $item['unit_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'user_id' => $purchase->user_id, // Use the original user_id
                        'created_by' => $purchase->created_by, // Use the original created_by
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Remove items that are no longer in the request
            $existingItems->whereNotIn('item_id', $requestItemIds)->each(function ($item) {
                $item->delete();
            });

            DB::commit();
            return response()->json(['message' => 'Purchase updated successfully.', 'data' => $purchase]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error updating purchase: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $purchase = PurchaseModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        DB::beginTransaction();
        try {
            // Delete purchase items first
            $purchase->purchaseItems()->delete();
            // Delete purchase
            $purchase->delete();

            DB::commit();
            return response()->json(['message' => 'Purchase deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error deleting purchase: ' . $e->getMessage()], 500);
        }
    }
    public function viewDetails($id)
    {
        $purchase = PurchaseModel::with(['supplier', 'purchaseItems.item', 'purchaseItems.unit', 'paymentMethod'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('content.purchase.view_details', compact('purchase'));
    }

    public function edit($id)
    {
        $purchase = PurchaseModel::with(['supplier', 'purchaseItems.item', 'purchaseItems.unit', 'paymentMethod'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $suppliers = Supplier::where('user_id', Auth::id())->get();
        $payment_methods = PaymentMethod::all();
        $items = Item::orderBy('item_name')->get(); 
        $units = Unit::orderBy('unit_name')->get();

        // Get the serial number from PurchaseUniqueId
        $purchaseUniqueId = PurchaseUniqueIdModel::where('purchase_id', $id)->first();
        $serial = $purchaseUniqueId ? $purchaseUniqueId->serial : 1;

        return view('content.purchase.edit', compact('purchase', 'suppliers', 'payment_methods', 'items', 'units', 'serial'));
    }
    public function printPurchaseList(Request $request)
    {
        $query = PurchaseModel::with(['supplier', 'purchaseItems.item', 'purchaseItems.unit', 'paymentMethod'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Get all purchases (no pagination for print)
        $purchases = $query->orderBy('created_at', 'desc')->get();

        return view('content.purchase.print_list', compact('purchases'));
    }

    public function printPurchase($id)
    {
        $purchase = PurchaseModel::with(['supplier', 'purchaseItems.item', 'purchaseItems.unit', 'paymentMethod'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('content.purchase.print_purchase', compact('purchase'));
    }

    public function exportPdf(Request $request)
    {
        $query = PurchaseModel::with(['supplier', 'purchaseItems.item', 'purchaseItems.unit'])
            ->where('user_id', Auth::id());

        // Apply same filters as view
        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $purchases = $query->orderBy('created_at', 'desc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('content.purchase.pdf', compact('purchases'));
        return $pdf->download('purchases-' . date('Y-m-d') . '.pdf');
    }
}
