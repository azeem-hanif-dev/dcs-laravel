<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Material\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    use ApiResponse, DistributorVisibility;

    // ─────────────────────────────────────────────────
    //  STOCK OVERVIEW — detailed list
    // ─────────────────────────────────────────────────

    /**
     * GET /api/v1/stock/overview
     *
     * Query params:
     *   warehouse_id  — filter by warehouse
     *   product_id    — filter by product
     *   category_id   — filter by category
     *   supplier_id   — filter by supplier
     *   status        — in_stock | low_stock | out_of_stock | all_reserved
     *   search        — search product name / SKU
     *   sort_by       — product_name | total_quantity | available_quantity | stock_value | updated_at (default)
     *   sort_dir      — asc | desc
     *   per_page      — pagination
     */
    public function overview(Request $request)
    {
        $query = Stock::where('stocks.company_id', $request->company_id)
            ->with([
                'product' => fn($q) => $q->select('id', 'material_name', 'sku', 'price', 'category_id', 'supplier_id', 'unit', 'reorder_level')
                    ->with(['category:id,name', 'supplier:id,name']),
                'warehouse:id,name,location',
                'movements' => fn($q) => $q->latest()->limit(1),
            ]);

        // Distributor filter: only show stocks for products they can see
        $query = $this->applyStockVisibility($query, $request);

        // ── Filters ──────────────────────────────────
        if ($request->warehouse_id)  $query->where('warehouse_id', $request->warehouse_id);
        if ($request->product_id)    $query->where('product_id', $request->product_id);
        if ($request->supplier_id) {
            $query->whereHas('product', fn($q) => $q->where('supplier_id', $request->supplier_id));
        }
        if ($request->category_id) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $request->category_id));
        }
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('product', fn($q) => $q->where('material_name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }
        if ($request->status) {
            $query->where(function ($q) use ($request) {
                match ($request->status) {
                    'in_stock'      => $q->whereRaw('total_quantity - reserved_quantity > COALESCE(reorder_level, 10)'),
                    'low_stock'     => $q->whereRaw('(total_quantity - reserved_quantity) > 0')
                                         ->whereRaw('(total_quantity - reserved_quantity) <= COALESCE(reorder_level, 10)'),
                    'out_of_stock'  => $q->whereRaw('total_quantity <= 0'),
                    'all_reserved'  => $q->whereRaw('total_quantity > 0 AND total_quantity - reserved_quantity <= 0'),
                    default         => null,
                };
            });
        }

        // ── Sorting ──────────────────────────────────
        $sortBy  = $request->sort_by ?? 'updated_at';
        $sortDir = $request->sort_dir ?? 'desc';
        $allowedSorts = ['product_name', 'total_quantity', 'available_quantity', 'stock_value', 'updated_at', 'reserved_quantity'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'updated_at';

        if ($sortBy === 'product_name') {
            $query->join('materials', 'stocks.product_id', '=', 'materials.id')
                  ->orderBy('materials.material_name', $sortDir)
                  ->select('stocks.*');
        } elseif ($sortBy === 'available_quantity') {
            $query->orderByRaw("(total_quantity - reserved_quantity) {$sortDir}");
        } elseif ($sortBy === 'stock_value') {
            $query->join('materials', 'stocks.product_id', '=', 'materials.id')
                  ->orderByRaw("(stocks.total_quantity * materials.price) {$sortDir}")
                  ->select('stocks.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $this->paginatedResponse($query, $request, 'Stock overview retrieved');
    }

    // ─────────────────────────────────────────────────
    //  STOCK SUMMARY — aggregate statistics
    // ─────────────────────────────────────────────────

    /**
     * GET /api/v1/stock/summary
     *
     * Returns aggregate statistics plus optional per-warehouse breakdown.
     */
    public function summary(Request $request)
    {
        $companyId = $request->company_id;

        // Base stock query (prefix table to avoid ambiguous column with joins)
        $baseQuery = Stock::where('stocks.company_id', $companyId);
        $baseQuery = $this->applyStockVisibility($baseQuery, $request);

        // ── Aggregate totals ─────────────────────────
        $totals = (clone $baseQuery)
            ->join('materials', 'stocks.product_id', '=', 'materials.id')
            ->selectRaw("
                COUNT(DISTINCT stocks.id)                                          as total_stock_records,
                COUNT(DISTINCT CASE WHEN stocks.total_quantity > 0 THEN stocks.product_id END) as total_unique_products,
                COALESCE(SUM(stocks.total_quantity), 0)                            as total_units,
                COALESCE(SUM(stocks.reserved_quantity), 0)                         as total_reserved,
                COALESCE(SUM(stocks.total_quantity - stocks.reserved_quantity), 0)  as total_available,
                COALESCE(SUM(stocks.total_quantity * materials.price), 0)           as total_value
            ")->first();

        // ── Status counts ────────────────────────────
        $statusCounts = (clone $baseQuery)->selectRaw("
            COUNT(CASE WHEN total_quantity > 0 AND total_quantity - reserved_quantity > COALESCE(reorder_level, 10) THEN 1 END) as in_stock,
            COUNT(CASE WHEN total_quantity > 0 AND total_quantity - reserved_quantity > 0 AND total_quantity - reserved_quantity <= COALESCE(reorder_level, 10) THEN 1 END) as low_stock,
            COUNT(CASE WHEN total_quantity <= 0 THEN 1 END) as out_of_stock,
            COUNT(CASE WHEN total_quantity > 0 AND total_quantity - reserved_quantity <= 0 THEN 1 END) as all_reserved
        ")->first();

        // ── By warehouse breakdown ───────────────────
        $byWarehouse = (clone $baseQuery)
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('materials', 'stocks.product_id', '=', 'materials.id')
            ->selectRaw("
                warehouses.id   as warehouse_id,
                warehouses.name as warehouse_name,
                COUNT(DISTINCT stocks.id)                                          as stock_records,
                COALESCE(SUM(stocks.total_quantity), 0)                            as total_units,
                COALESCE(SUM(stocks.reserved_quantity), 0)                         as reserved_units,
                COALESCE(SUM(stocks.total_quantity - stocks.reserved_quantity), 0)  as available_units,
                COALESCE(SUM(stocks.total_quantity * materials.price), 0)           as total_value
            ")
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();

        // ── Top 5 low stock ──────────────────────────
        $lowStock = (clone $baseQuery)
            ->with(['product:id,material_name,sku,price,reorder_level', 'warehouse:id,name'])
            ->whereRaw('(total_quantity - reserved_quantity) <= COALESCE(reorder_level, 10)')
            ->where('total_quantity', '>', 0)
            ->orderByRaw('(total_quantity - reserved_quantity) ASC')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'product_name'       => $s->product?->material_name,
                'sku'                => $s->product?->sku,
                'warehouse'          => $s->warehouse?->name,
                'available'          => $s->available_quantity,
                'total'              => $s->total_quantity,
                'reserved'           => $s->reserved_quantity,
                'reorder_level'      => $s->reorder_level,
                'status'             => $s->status,
            ]);

        return $this->successResponse([
            'totals'        => $totals,
            'status_counts' => $statusCounts,
            'by_warehouse'  => $byWarehouse,
            'low_stock_alert' => $lowStock,
        ], 'Stock summary retrieved');
    }

    // ─────────────────────────────────────────────────
    //  STOCK MOVEMENTS — history log
    // ─────────────────────────────────────────────────

    /**
     * GET /api/v1/stock/movements
     *
     * Query params:
     *   product_id   — filter by product
     *   warehouse_id — filter by warehouse
     *   type         — movement type (purchase_received, sales_shipped, etc.)
     *   date_from / date_to
     *   per_page
     */
    public function movements(Request $request)
    {
        $query = StockMovement::where('company_id', $request->company_id)
            ->with([
                'product:id,material_name,sku',
                'warehouse:id,name',
                'user:id,name,username',
            ]);

        // Distributor filter for movements too
        $user = $request->auth_user;
        if ($user && $user->role === 'distributor' && $user->distributor_id) {
            $distributor = \App\Models\Material\Distributor::find($user->distributor_id);
            $supplierId = $distributor?->supplier_id;
            $query->whereHas('product', function ($q) use ($user, $supplierId) {
                $q->where(function ($sub) use ($user, $supplierId) {
                    $sub->where('user_id', $user->id);
                    if ($supplierId) $sub->orWhere('supplier_id', $supplierId);
                });
            });
        }

        if ($request->product_id)   $query->where('product_id', $request->product_id);
        if ($request->warehouse_id) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->type)         $query->where('type', $request->type);
        if ($request->date_from)    $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to)      $query->whereDate('created_at', '<=', $request->date_to);

        $query->latest();
        return $this->paginatedResponse($query, $request, 'Stock movements retrieved');
    }

    // ─────────────────────────────────────────────────
    //  STOCK ADJUSTMENT — manual add/remove
    // ─────────────────────────────────────────────────

    /**
     * POST /api/v1/stock/adjust
     */
    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id'   => 'required|exists:materials,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity'     => 'required|integer|not_in:0',
            'type'         => 'required|in:add,remove',
            'reason'       => 'nullable|string|max:500',
        ]);

        $stock = Stock::firstOrCreate(
            [
                'product_id'   => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'company_id'   => $request->company_id,
            ],
            ['total_quantity' => 0, 'reserved_quantity' => 0, 'reorder_level' => 10]
        );

        $movementType = $data['type'] === 'add' ? 'manual_addition' : 'manual_removal';
        $change       = $data['type'] === 'add' ? abs($data['quantity']) : -abs($data['quantity']);

        // Apply the change
        if ($data['type'] === 'add') {
            $stock->increment('total_quantity', abs($data['quantity']));
        } else {
            $stock->decrement('total_quantity', abs($data['quantity']));
            if ($stock->total_quantity < 0) $stock->update(['total_quantity' => 0]);
        }

        // Log movement
        $stock->refresh();
        $movement = $stock->logMovement(
            type:         $movementType,
            change:       $change,
            userId:       $request->auth_user->id,
            refType:      'Manual',
            refId:        null,
            refNumber:    null,
            notes:        $data['reason'] ?? 'Manual stock adjustment'
        );

        // Also update material total_quantity
        $material = Material::find($data['product_id']);
        if ($material) {
            if ($data['type'] === 'add') $material->increment('total_quantity', abs($data['quantity']));
            else $material->decrement('total_quantity', abs($data['quantity']));
        }

        return $this->successResponse([
            'stock'    => $stock->load('product', 'warehouse'),
            'movement' => $movement,
        ], 'Stock adjusted successfully');
    }

    // ─────────────────────────────────────────────────
    //  PRODUCT STOCK DETAIL — all warehouses for a product
    // ─────────────────────────────────────────────────

    /**
     * GET /api/v1/stock/product/{productId}
     */
    public function byProduct(Request $request, $productId)
    {
        $stocks = Stock::where('company_id', $request->company_id)
            ->where('product_id', $productId)
            ->with(['warehouse:id,name,location'])
            ->get();

        $product = Material::with(['category:id,name', 'supplier:id,name'])
            ->findOrFail($productId);

        $totalAcrossWarehouses = $stocks->sum('total_quantity');
        $reservedAcrossWarehouses = $stocks->sum('reserved_quantity');
        $availableAcrossWarehouses = $totalAcrossWarehouses - $reservedAcrossWarehouses;

        return $this->successResponse([
            'product'  => $product,
            'summary'  => [
                'total_quantity'     => $totalAcrossWarehouses,
                'reserved_quantity'  => $reservedAcrossWarehouses,
                'available_quantity' => $availableAcrossWarehouses,
                'stock_value'        => round($totalAcrossWarehouses * ($product->price ?? 0), 2),
            ],
            'warehouses' => $stocks->map(fn($s) => [
                'warehouse_id'       => $s->warehouse_id,
                'warehouse_name'     => $s->warehouse?->name,
                'total_quantity'     => (int)$s->total_quantity,
                'reserved_quantity'  => (int)$s->reserved_quantity,
                'available_quantity' => $s->available_quantity,
                'status'             => $s->status,
                'status_color'       => $s->status_color,
                'reorder_level'      => (int)$s->reorder_level,
                'stock_value'        => $s->stock_value,
            ]),
        ]);
    }

    // ─────────────────────────────────────────────────
    //  MOVEMENT TYPES — for dropdowns
    // ─────────────────────────────────────────────────

    /**
     * GET /api/v1/stock/movement-types
     */
    public function movementTypes()
    {
        return $this->successResponse(
            collect(StockMovement::typeLabels())->map(fn($label, $key) => [
                'value' => $key,
                'label' => $label,
            ])->values()
        );
    }

    // ─────────────────────────────────────────────────
    //  DISTRIBUTOR VISIBILITY
    // ─────────────────────────────────────────────────

    /**
     * Distributor sees stocks only for products they own or from their linked supplier.
     */
    private function applyStockVisibility($query, Request $request)
    {
        $user = $request->auth_user;
        if (!$user || in_array($user->role, ['superadmin', 'admin'])) return $query;
        if ($user->role !== 'distributor') return $query;

        $distributor = \App\Models\Material\Distributor::find($user->distributor_id);
        $supplierId = $distributor?->supplier_id;

        return $query->whereHas('product', function ($q) use ($user, $supplierId) {
            $q->where(function ($sub) use ($user, $supplierId) {
                $sub->where('user_id', $user->id);
                if ($supplierId) $sub->orWhere('supplier_id', $supplierId);
            });
        });
    }
}
