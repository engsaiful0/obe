<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Damage;
use App\Models\DamageItem;
use App\Models\Item;
use App\Models\Warehouse;
use App\Exports\DamagesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class DamageController extends Controller
{
    public function add()
    {
        $items = Item::where('user_id', Auth::id())->get();
        $warehouses = Warehouse::where('user_id', Auth::id())->get();
        $date = now()->toDateString();
        return view('content.damage.add', compact('items', 'warehouses', 'date'));
    }

    public function view(Request $request)
    {
        $items = Item::where('user_id', Auth::id())->get();
        $warehouses = Warehouse::where('user_id', Auth::id())->get();
        
        $query = Damage::with(['warehouse', 'damageItems.item'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('item_id') && $request->item_id) {
            $query->whereHas('damageItems', function($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('warehouse', function($wQuery) use ($search) {
                      $wQuery->where('warehouse_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('damageItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('item_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $damages = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(15);
        
        // If AJAX request, return JSON with HTML
        if ($request->ajax()) {
            $html = view('content.damage.table', compact('damages'))->render();
            $pagination = view('content.damage.pagination', compact('damages'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'total' => $damages->total(),
                'showing' => $damages->count()
            ]);
        }
        
        return view('content.damage.index', compact('items', 'warehouses', 'damages'));
    }

    public function edit($id)
    {
        $damage = Damage::with(['warehouse', 'damageItems.item'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $items = Item::where('user_id', Auth::id())->get();
        $warehouses = Warehouse::where('user_id', Auth::id())->get();

        return view('content.damage.edit', compact('damage', 'items', 'warehouses'));
    }

    public function productRow(Request $request)
    {
        $items = Item::where('user_id', Auth::id())->get();
        $rowIndex = $request->get('row_index', 0);
        return view('content.damage.product_row', compact('items', 'rowIndex'));
    }

    public function getDamage(Request $request)
    {
        $query = Damage::with(['warehouse', 'damageItems.item'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('item_id') && $request->item_id) {
            $query->whereHas('damageItems', function($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('warehouse', function($wQuery) use ($search) {
                      $wQuery->where('warehouse_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('damageItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('item_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $damages = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($damages);
    }

    public function getItems()
    {
        $items = Item::where('user_id', Auth::id())->get();
        return response()->json($items);
    }

    public function getWarehouses()
    {
        $warehouses = Warehouse::where('user_id', Auth::id())->get();
        return response()->json($warehouses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'nullable|string|max:255',
            'items.*.approximate' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $damage = Damage::create([
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'remarks' => $request->remarks,
                'user_id' => $userId,
                'created_by' => $userId,
            ]);

            foreach ($request->items as $item) {
                DamageItem::create([
                    'damage_id' => $damage->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'reason' => $item['reason'] ?? null,
                    'approximate' => $item['approximate'] ?? null,
                    'user_id' => $userId,
                    'created_by' => $userId,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Damage saved successfully.', 'data' => $damage], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error saving damage: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $damage = Damage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'nullable|string|max:255',
            'items.*.approximate' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $damage->update([
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'remarks' => $request->remarks,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

            // Delete existing damage items
            DamageItem::where('damage_id', $damage->id)->delete();

            // Create new damage items
            foreach ($request->items as $item) {
                DamageItem::create([
                    'damage_id' => $damage->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'reason' => $item['reason'] ?? null,
                    'approximate' => $item['approximate'] ?? null,
                    'user_id' => $userId,
                    'created_by' => $userId,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Damage updated successfully.', 'data' => $damage], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating damage: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $damage = Damage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Delete damage items first
            DamageItem::where('damage_id', $damage->id)->delete();
            
            // Delete damage
            $damage->delete();

            DB::commit();
            return response()->json(['message' => 'Damage deleted successfully.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting damage: ' . $e->getMessage()], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $query = Damage::with(['warehouse', 'damageItems.item'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('item_id') && $request->item_id) {
            $query->whereHas('damageItems', function($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('warehouse', function($wQuery) use ($search) {
                      $wQuery->where('warehouse_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('damageItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('item_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $damages = $query->orderBy('date', 'desc')->get();

        return Excel::download(new DamagesExport($damages), 'damages_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = Damage::with(['warehouse', 'damageItems.item'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('item_id') && $request->item_id) {
            $query->whereHas('damageItems', function($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('warehouse', function($wQuery) use ($search) {
                      $wQuery->where('warehouse_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('damageItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('item_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $damages = $query->orderBy('date', 'desc')->get();

        $pdf = Pdf::loadView('content.damage.export-pdf', compact('damages'));
        return $pdf->download('damages_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
