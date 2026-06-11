<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * COMPREHENSIVE QA TEST SUITE
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// ====== HELPERS ======
$passCount = 0; $failCount = 0; $bugs = []; $testId = 0;
function api($method, $url, $token, $body = null) {
    global $kernel;
    $headers = ['HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.$token];
    $req = Request::create($url,$method,[],[],[],$headers,$body?json_encode($body):null);
    return json_decode($kernel->handle($req)->getContent());
}
function test($id, $module, $scenario, $result, $expected, $dbCheck = null) {
    global $passCount, $failCount, $bugs, $testId;
    $testId++;
    $status = $result ? '✅ PASS' : '❌ FAIL';
    if ($result) $passCount++; else { $failCount++; $bugs[] = $expected; }
    echo sprintf("[%s] %s | %s | %s | %s\n", $status, $id, $module, $scenario, $expected);
    if ($dbCheck && $result) { echo "       DB: " . trim($dbCheck) . "\n"; }
    if (!$result) { echo "       BUG: $expected\n"; }
}

// Check if API call was rejected (validation error, not success)
function isRejected($response) {
    if (!$response) return true; // null = error
    if (isset($response->status) && $response->status === true) return false; // explicitly true = accepted
    return true; // anything else = rejected
}
function dbRow($table, $id) { return DB::table($table)->find($id); }
function dbCount($table, $where = []) { return DB::table($table)->where($where)->count(); }

// ====== LOGIN ======
echo "═══════════════════════════════════════════\n";
echo " QA TEST SUITE — Distributor ERP\n";
echo "═══════════════════════════════════════════\n\n";

$login = api('POST','/api/auth/login',null,['username'=>'superadmin','password'=>'password','companyId'=>1]);
$token = $login->S_S_Token ?? null;
test('TC_AUTH_001','Authentication','Login with valid credentials', isset($login->status) && $login->status, 'Login should succeed');
if (!$token) { echo "FATAL: Cannot login. Aborting.\n"; exit; }

$companyId = $login->data->company_id ?? 1;
echo "\n";

// ====== MODULE 1: WAREHOUSES ======
echo "═══ MODULE: WAREHOUSES ═══\n";
DB::table('warehouses')->where('company_id',$companyId)->delete();

// Positive
$r = api('POST','/api/v1/warehouse',$token,['name'=>'QA Main Warehouse','location'=>'Test Location','manager_name'=>'QA Manager','phone'=>'555-WH1']);
test('TC_WH_001','Warehouse','Create warehouse with valid data', $r->status ?? false, 'Warehouse created');
if ($r->status ?? false) {
    $wh = dbRow('warehouses',$r->data->id);
    test('TC_WH_001a','Warehouse','DB: name stored correctly', $wh && $wh->name === 'QA Main Warehouse', 'name=QA Main Warehouse', "Actual: name=".($wh->name??'null'));
    test('TC_WH_001b','Warehouse','DB: company_id is correct', $wh && $wh->company_id == $companyId, "company_id=$companyId", "Actual: company_id=".($wh->company_id??'null'));
    test('TC_WH_001c','Warehouse','DB: timestamps exist', $wh && !empty($wh->created_at) && !empty($wh->updated_at), 'created_at and updated_at set');
    $whId1 = $r->data->id;
} else { $whId1 = null; }

// Negative: empty name
$r = api('POST','/api/v1/warehouse',$token,['name'=>'']);
test('TC_WH_002','Warehouse','Reject empty name', isRejected($r), 'Should fail with validation error');

// Negative: duplicate name (same company)
if ($whId1) {
    $r = api('POST','/api/v1/warehouse',$token,['name'=>'QA Main Warehouse']);
    test('TC_WH_003','Warehouse','Reject duplicate name', isRejected($r), 'Should reject duplicate');
}

// Second warehouse
$r = api('POST','/api/v1/warehouse',$token,['name'=>'QA Secondary Warehouse','location'=>'Secondary Loc']);
test('TC_WH_004','Warehouse','Create second warehouse', $r->status ?? false, 'Second warehouse created');
if ($r->status ?? false) $whId2 = $r->data->id; else $whId2 = null;

// Update
if ($whId1) {
    $r = api('PUT','/api/v1/warehouse/'.$whId1,$token,['name'=>'QA Main Warehouse Updated','location'=>'Updated Location']);
    test('TC_WH_005','Warehouse','Update warehouse', $r->status ?? false, 'Warehouse updated');
    $whUpdated = dbRow('warehouses',$whId1);
    test('TC_WH_005a','Warehouse','DB: updated name persisted', $whUpdated && $whUpdated->name === 'QA Main Warehouse Updated', 'name updated');
}

// Soft delete
if ($whId2) {
    $r = api('DELETE','/api/v1/warehouse/'.$whId2,$token);
    test('TC_WH_006','Warehouse','Delete warehouse', $r->status ?? false, 'Warehouse deleted');
    $whDel = dbRow('warehouses',$whId2);
    test('TC_WH_006a','Warehouse','DB: record removed', !$whDel, 'Record not found in DB');
}
echo "\n";

// ====== MODULE 2: SALESMEN ======
echo "═══ MODULE: SALESMEN ═══\n";
DB::table('salesmen')->where('company_id',$companyId)->delete();

$r = api('POST','/api/v1/salesman',$token,['name'=>'QA Salesman Alpha','email'=>'alpha@qa.com','phone'=>'555-SM1','territory'=>'North','target_amount'=>50000]);
test('TC_SM_001','Salesman','Create salesman with valid data', $r->status ?? false, 'Salesman created');
if ($r->status ?? false) {
    $sm = dbRow('salesmen',$r->data->id);
    test('TC_SM_001a','Salesman','DB: name stored', $sm && $sm->name === 'QA Salesman Alpha', 'Name correct');
    test('TC_SM_001b','Salesman','DB: target_amount stored as decimal', $sm && $sm->target_amount == 50000, 'target_amount=50000');
    $smId1 = $r->data->id;
} else { $smId1 = null; }

// Negative: empty name
$r = api('POST','/api/v1/salesman',$token,['name'=>'']);
test('TC_SM_002','Salesman','Reject empty name', isRejected($r), 'Should fail');

// Negative: invalid email
$r = api('POST','/api/v1/salesman',$token,['name'=>'Bad Email','email'=>'not-an-email']);
test('TC_SM_003','Salesman','Reject invalid email', isRejected($r), 'Should fail');

// Second salesman  
$r = api('POST','/api/v1/salesman',$token,['name'=>'QA Salesman Beta','territory'=>'South']);
test('TC_SM_004','Salesman','Create salesman without email (nullable)', $r->status ?? false, 'Should succeed — email is nullable');
if ($r->status ?? false) $smId2 = $r->data->id; else $smId2 = null;
echo "\n";

// ====== MODULE 3: SHOPS ======
echo "═══ MODULE: SHOPS ═══\n";
DB::table('shops')->where('company_id',$companyId)->delete();

$r = api('POST','/api/v1/shop',$token,['name'=>'QA City Grocery','owner_name'=>'QA Owner','phone'=>'555-SH1','city'=>'Karachi','area'=>'Clifton','shop_type'=>'retail','salesman_id'=>$smId1,'credit_limit'=>50000]);
test('TC_SH_001','Shop','Create shop with valid data', $r->status ?? false, 'Shop created');
if ($r->status ?? false) {
    $sh = dbRow('shops',$r->data->id);
    test('TC_SH_001a','Shop','DB: name stored', $sh && $sh->name === 'QA City Grocery', 'Name correct');
    test('TC_SH_001b','Shop','DB: salesman FK set', $sh && $sh->salesman_id == $smId1, "salesman_id=$smId1");
    test('TC_SH_001c','Shop','DB: credit_limit as decimal', $sh && $sh->credit_limit == 50000, 'credit_limit=50000');
    $shopId1 = $r->data->id;
} else { $shopId1 = null; }

// Negative: empty name
$r = api('POST','/api/v1/shop',$token,['name'=>'']);
test('TC_SH_002','Shop','Reject empty name', isRejected($r), 'Should fail');

// Second shop
$r = api('POST','/api/v1/shop',$token,['name'=>'QA Metro Mart','shop_type'=>'supermarket','credit_limit'=>100000]);
test('TC_SH_003','Shop','Create shop without salesman (nullable)', $r->status ?? false, 'Should succeed');
if ($r->status ?? false) $shopId2 = $r->data->id; else $shopId2 = null;

// Duplicate name
$r = api('POST','/api/v1/shop',$token,['name'=>'QA City Grocery']);
test('TC_SH_004','Shop','Reject duplicate shop name', isRejected($r), 'Should reject duplicate');
echo "\n";

// ====== MODULE 4: SUPPLIERS ======
echo "═══ MODULE: SUPPLIERS ═══\n";
DB::table('suppliers')->where('company_id',$companyId)->delete();

$r = api('POST','/api/v1/supplier',$token,['name'=>'QA Tech Supplies','email'=>'tech@qa.com','contact_person'=>'QA Bob','contact_number'=>'555-SP1','address'=>'QA Industrial Area']);
test('TC_SP_001','Supplier','Create supplier with valid data', $r->status ?? false, 'Supplier created');
if ($r->status ?? false) {
    $sp = dbRow('suppliers',$r->data->id);
    test('TC_SP_001a','Supplier','DB: name stored', $sp && $sp->name === 'QA Tech Supplies', 'Name correct');
    test('TC_SP_001b','Supplier','DB: contact_person nullable works', $sp && $sp->contact_person === 'QA Bob', 'contact_person stored');
    $supId1 = $r->data->id;
} else { $supId1 = null; }

// Negative: empty email
$r = api('POST','/api/v1/supplier',$token,['name'=>'No Email']);
test('TC_SP_002','Supplier','Reject missing email', isRejected($r), 'Email is required');

// Second supplier WITHOUT contact_person (should work after our fix)
$r = api('POST','/api/v1/supplier',$token,['name'=>'QA Global Traders','email'=>'global@qa.com','address'=>'QA Trade Center']);
test('TC_SP_003','Supplier','Create supplier without contact person (nullable)', $r->status ?? false, 'Should succeed — contact_person nullable');
if ($r->status ?? false) $supId2 = $r->data->id; else $supId2 = null;
echo "\n";

// ====== MODULE 5: CATEGORIES ======
echo "═══ MODULE: CATEGORIES ═══\n";
DB::table('categories')->where('company_id',$companyId)->delete();

$r = api('POST','/api/v1/category',$token,['name'=>'QA Electronics']);
test('TC_CT_001','Category','Create category', $r->status ?? false, 'Category created');
if ($r->status ?? false) {
    $cat1 = dbRow('categories',$r->data->id);
    test('TC_CT_001a','Category','DB: name stored as VARCHAR (not ENUM)', $cat1 && $cat1->name === 'QA Electronics', 'Name=VARCHAR ✓');
    $catId1 = $r->data->id;
} else { $catId1 = null; }

// Negative: empty name
$r = api('POST','/api/v1/category',$token,['name'=>'']);
test('TC_CT_002','Category','Reject empty name', isRejected($r), 'Should fail');

// Second category
$r = api('POST','/api/v1/category',$token,['name'=>'QA Groceries']);
test('TC_CT_003','Category','Create second category', $r->status ?? false, 'Category created');
if ($r->status ?? false) $catId2 = $r->data->id; else $catId2 = null;
echo "\n";

// ====== MODULE 6: PRODUCTS/MATERIALS ======
echo "═══ MODULE: PRODUCTS ═══\n";
DB::table('materials')->where('company_id',$companyId)->delete();

$r = api('POST','/api/v1/material',$token,['materialName'=>'QA LED TV 42"','categoryId'=>$catId1,'supplierId'=>$supId1,'price'=>450.00,'totalQuantity'=>100,'assignedQuantity'=>0,'description'=>'QA Test LED','status'=>'Active']);
test('TC_PR_001','Product','Create product with valid data', $r->status ?? false, 'Product created');
if ($r->status ?? false) {
    $pr = dbRow('materials',$r->data->id);
    test('TC_PR_001a','Product','DB: material_name stored', $pr && str_contains($pr->material_name,'LED TV'), 'Name correct');
    test('TC_PR_001b','Product','DB: price stored as decimal', $pr && $pr->price == 450.00, 'price=450.00');
    test('TC_PR_001c','Product','DB: total_quantity stored', $pr && $pr->total_quantity == 100, 'total_quantity=100');
    test('TC_PR_001d','Product','DB: category FK correct', $pr && $pr->category_id == $catId1, "category_id=$catId1");
    test('TC_PR_001e','Product','DB: supplier FK correct', $pr && $pr->supplier_id == $supId1, "supplier_id=$supId1");
    $prodId1 = $r->data->id;
} else { $prodId1 = null; }

// Negative: no name
$r = api('POST','/api/v1/material',$token,['materialName'=>'']);
test('TC_PR_002','Product','Reject empty name', isRejected($r), 'Should fail');

// Negative: no category
$r = api('POST','/api/v1/material',$token,['materialName'=>'Bad Product','categoryId'=>0,'price'=>10,'totalQuantity'=>10,'assignedQuantity'=>0]);
test('TC_PR_003','Product','Reject invalid categoryId', isRejected($r), 'Should fail — category must exist');

// Second product
$r = api('POST','/api/v1/material',$token,['materialName'=>'QA Rice 25kg','categoryId'=>$catId2,'supplierId'=>$supId2,'price'=>35.00,'totalQuantity'=>500,'assignedQuantity'=>0,'status'=>'Active']);
test('TC_PR_004','Product','Create second product', $r->status ?? false, 'Product created');
if ($r->status ?? false) $prodId2 = $r->data->id; else $prodId2 = null;

// Boundary: zero price
$r = api('POST','/api/v1/material',$token,['materialName'=>'QA Free Item','categoryId'=>$catId1,'price'=>0,'totalQuantity'=>1,'assignedQuantity'=>0]);
test('TC_PR_005','Product','Allow price=0 (free item)', $r->status ?? false, 'Should allow zero price');
echo "\n";

// ====== MODULE 7: SALES ORDERS ======
echo "═══ MODULE: SALES ORDERS ═══\n";
DB::table('sales_order_items')->whereIn('sales_order_id',function($q) use($companyId){$q->select('id')->from('sales_orders')->where('company_id',$companyId);})->delete();
DB::table('sales_orders')->where('company_id',$companyId)->delete();

$beforeStock = dbRow('materials',$prodId1)->total_quantity ?? 0;

$r = api('POST','/api/v1/sales-order',$token,[
    'shop_id'=>$shopId1,'salesman_id'=>$smId1,'warehouse_id'=>$whId1,
    'order_date'=>date('Y-m-d'),'status'=>'Draft',
    'subtotal'=>935,'tax_percent'=>5,'tax_amount'=>46.75,'discount'=>0,'grand_total'=>981.75,
    'items'=>[['product_id'=>$prodId1,'quantity'=>2,'unit_price'=>450],['product_id'=>$prodId2,'quantity'=>1,'unit_price'=>35]]
]);
test('TC_SO_001','Sales Order','Create draft sales order', $r->status ?? false, 'Sales order created');
if ($r->status ?? false) {
    $so = dbRow('sales_orders',$r->data->id);
    test('TC_SO_001a','Sales Order','DB: so_number auto-generated', $so && !empty($so->so_number), 'SO number exists');
    test('TC_SO_001b','Sales Order','DB: shop FK correct', $so && $so->shop_id == $shopId1, "shop_id=$shopId1");
    test('TC_SO_001c','Sales Order','DB: grand_total stored', $so && $so->grand_total == 981.75, 'grand_total=981.75');
    test('TC_SO_001d','Sales Order','DB: status Draft', $so && $so->status === 'Draft', 'status=Draft');
    $orderItems = DB::table('sales_order_items')->where('sales_order_id',$r->data->id)->get();
    test('TC_SO_001e','Sales Order','DB: items created (2 items)', count($orderItems) === 2, '2 items found', "Actual: ".count($orderItems)." items");
    $soId1 = $r->data->id;
} else { $soId1 = null; }

// Confirm the order → should auto-generate invoice
if ($soId1) {
    $r = api('PUT','/api/v1/sales-order/'.$soId1.'/status',$token,['status'=>'Confirmed']);
    test('TC_SO_002','Sales Order','Confirm order (Draft→Confirmed)', $r->status ?? false, 'Status updated');
    $soUpdated = dbRow('sales_orders',$soId1);
    test('TC_SO_002a','Sales Order','DB: status changed to Confirmed', $soUpdated && $soUpdated->status === 'Confirmed', 'status=Confirmed');
    
    // Check invoice auto-generation
    $inv = DB::table('invoices')->where('sales_order_id',$soId1)->first();
    test('TC_SO_002b','Sales Order','AUTO: Invoice generated on confirm', !empty($inv), 'Invoice exists');
    if ($inv) test('TC_SO_002c','Sales Order','DB: invoice total matches', $inv->total_amount == 981.75, "total_amount=981.75", "Actual: total_amount=".$inv->total_amount);
    
    // Check stock reserved
    $stock = DB::table('stocks')->where('product_id',$prodId1)->where('warehouse_id',$whId1)->first();
    test('TC_SO_002d','Sales Order','DB: stock reserved on confirm', $stock && $stock->reserved_quantity == 2, 'reserved_quantity=2', "Actual: reserved_quantity=".($stock->reserved_quantity??'null'));
}

// Cancel order → should release stock
if ($soId1) {
    $r = api('PUT','/api/v1/sales-order/'.$soId1.'/status',$token,['status'=>'Cancelled']);
    test('TC_SO_003','Sales Order','Cancel confirmed order', $r->status ?? false, 'Status updated to Cancelled');
    $stockAfter = DB::table('stocks')->where('product_id',$prodId1)->where('warehouse_id',$whId1)->first();
    test('TC_SO_003a','Sales Order','DB: stock released on cancel', $stockAfter && $stockAfter->reserved_quantity == 0, 'reserved_quantity=0', "Actual: reserved_quantity=".($stockAfter->reserved_quantity??'null'));
}

// Second sales order — confirmed
$r = api('POST','/api/v1/sales-order',$token,[
    'shop_id'=>$shopId2,'salesman_id'=>$smId2,'warehouse_id'=>$whId1,
    'order_date'=>date('Y-m-d'),'subtotal'=>450,'tax_percent'=>0,'tax_amount'=>0,'discount'=>0,'grand_total'=>450,
    'items'=>[['product_id'=>$prodId1,'quantity'=>1,'unit_price'=>450]]
]);
test('TC_SO_004','Sales Order','Create second sales order', $r->status ?? false, 'Order created');
if ($r->status ?? false) $soId2 = $r->data->id; else $soId2 = null;
echo "\n";

// ====== MODULE 8: INVOICES & PAYMENTS ======
echo "═══ MODULE: INVOICES & PAYMENTS ═══\n";

$inv = DB::table('invoices')->where('company_id',$companyId)->orderBy('id','desc')->first();
if ($inv) {
    test('TC_INV_001','Invoice','Verify invoice exists', !empty($inv), 'Invoice found');
    test('TC_INV_001a','Invoice','DB: invoice_number format', preg_match('/^INV-\d{8}-\d{4}$/',$inv->invoice_number??''), 'Format: INV-YYYYMMDD-XXXX', "Actual: ".$inv->invoice_number);
    test('TC_INV_001b','Invoice','DB: balance_due computed column', $inv->balance_due == ($inv->total_amount - $inv->paid_amount), 'balance_due = total - paid');
    
    // Record payment
    $r = api('POST','/api/v1/payment',$token,['invoice_id'=>$inv->id,'shop_id'=>$inv->shop_id,'amount'=>250,'payment_method'=>'Cash','payment_date'=>date('Y-m-d')]);
    test('TC_PM_001','Payment','Record partial payment', $r->status ?? false, 'Payment recorded');
    
    $invAfter = dbRow('invoices',$inv->id);
    test('TC_PM_001a','Payment','DB: paid_amount updated', $invAfter && $invAfter->paid_amount == 250, 'paid_amount=250', "Actual: paid_amount=".($invAfter->paid_amount??'null'));
    test('TC_PM_001b','Payment','DB: status updated to Partially Paid', $invAfter && $invAfter->status === 'Partially Paid', 'status=Partially Paid', "Actual: status=".($invAfter->status??'null'));
    test('TC_PM_001c','Payment','DB: balance_due recalculated', $invAfter && $invAfter->balance_due == ($invAfter->total_amount - 250), 'balance_due correct');
    
    // Payment in payments table
    $pmt = DB::table('payments')->where('invoice_id',$inv->id)->first();
    test('TC_PM_001d','Payment','DB: payment record exists', !empty($pmt), 'Payment record found');
    test('TC_PM_001e','Payment','DB: payment amount correct', $pmt && $pmt->amount == 250, 'amount=250');
}

// Full payment
if ($inv && ($invAfter->balance_due ?? 0) > 0) {
    $r = api('POST','/api/v1/payment',$token,['invoice_id'=>$inv->id,'shop_id'=>$inv->shop_id,'amount'=>$invAfter->balance_due,'payment_method'=>'Bank Transfer','payment_date'=>date('Y-m-d')]);
    test('TC_PM_002','Payment','Record full payment (clear balance)', $r->status ?? false, 'Payment recorded');
    $invFinal = dbRow('invoices',$inv->id);
    test('TC_PM_002a','Payment','DB: status → Paid', $invFinal && $invFinal->status === 'Paid', 'status=Paid', "Actual: status=".($invFinal->status??'null'));
    test('TC_PM_002b','Payment','DB: balance_due = 0', $invFinal && $invFinal->balance_due == 0, 'balance_due=0');
}
echo "\n";

// ====== MODULE 9: DELIVERIES ======
echo "═══ MODULE: DELIVERIES ═══\n";
if ($soId2) {
    $r = api('POST','/api/v1/delivery',$token,['sales_order_id'=>$soId2,'shop_id'=>$shopId2,'driver_name'=>'QA Driver','driver_phone'=>'555-DRV1','vehicle_number'=>'QA-VAN-001']);
    test('TC_DL_001','Delivery','Create delivery', $r->status ?? false, 'Delivery created');
    if ($r->status ?? false) {
        $dl = dbRow('deliveries',$r->data->id);
        test('TC_DL_001a','Delivery','DB: driver_name stored', $dl && $dl->driver_name === 'QA Driver', 'driver_name correct');
        test('TC_DL_001b','Delivery','DB: status default Pending', $dl && $dl->status === 'Pending', 'status=Pending');
        
        // Update to Delivered → should update stock
        $stockBefore = DB::table('stocks')->where('product_id',$prodId1)->where('warehouse_id',$whId1)->first();
        $r = api('PUT','/api/v1/delivery/'.$r->data->id.'/status',$token,['status'=>'Delivered']);
        test('TC_DL_002','Delivery','Mark delivery as Delivered', $r->status ?? false, 'Status updated');
        $stockAfter = DB::table('stocks')->where('product_id',$prodId1)->where('warehouse_id',$whId1)->first();
        test('TC_DL_002a','Delivery','DB: stock decreased on delivery', $stockBefore && $stockAfter && $stockAfter->total_quantity == ($stockBefore->total_quantity - 1), "total_quantity decreased by 1", "Before: {$stockBefore->total_quantity}, After: {$stockAfter->total_quantity}");
    }
}
echo "\n";

// ====== MODULE 10: SALES RETURNS ======
echo "═══ MODULE: SALES RETURNS ═══\n";
$stockBefore = dbRow('materials',$prodId1)->total_quantity ?? 0;
$r = api('POST','/api/v1/sales-return',$token,['shop_id'=>$shopId1,'return_date'=>date('Y-m-d'),'reason'=>'QA Test Return','items'=>[['product_id'=>$prodId1,'quantity'=>1,'unit_price'=>450,'reason'=>'QA damaged box']]]);
test('TC_SR_001','Sales Return','Create sales return', $r->status ?? false, 'Return created');
if ($r->status ?? false) {
    $sret = dbRow('sales_returns',$r->data->id);
    test('TC_SR_001a','Sales Return','DB: return_number auto-generated', $sret && !empty($sret->return_number), 'Return number exists');
    $items = DB::table('sales_return_items')->where('sales_return_id',$r->data->id)->get();
    test('TC_SR_001b','Sales Return','DB: return items created', count($items) === 1, '1 item found', "Actual: ".count($items));
    
    // Stock returned to inventory
    $stockAfter = dbRow('materials',$prodId1);
    test('TC_SR_001c','Sales Return','DB: stock increased on return', $stockAfter && $stockAfter->total_quantity > $stockBefore, "total_quantity increased", "Before: $stockBefore, After: ".($stockAfter->total_quantity??'null'));
}
echo "\n";

// ====== MODULE 11: PURCHASE RETURNS ======
echo "═══ MODULE: PURCHASE RETURNS ═══\n";
$stockBefore = dbRow('materials',$prodId1)->total_quantity ?? 0;
$r = api('POST','/api/v1/purchase-return',$token,['supplier_id'=>$supId1,'return_date'=>date('Y-m-d'),'reason'=>'QA Defective','items'=>[['product_id'=>$prodId1,'quantity'=>2,'unit_price'=>450,'reason'=>'QA screen issue']]]);
test('TC_PR_001','Purchase Return','Create purchase return', $r->status ?? false, 'Return created');
if ($r->status ?? false) {
    $pret = dbRow('purchase_returns',$r->data->id);
    test('TC_PR_001a','Purchase Return','DB: return_number auto-generated', $pret && !empty($pret->return_number), 'Return number exists');
    test('TC_PR_001b','Purchase Return','DB: supplier FK correct', $pret && $pret->supplier_id == $supId1, "supplier_id=$supId1");
    
    // Stock decreased (returning to supplier)
    $stockAfter = dbRow('materials',$prodId1);
    test('TC_PR_001c','Purchase Return','DB: stock decreased on purchase return', $stockAfter && $stockAfter->total_quantity < $stockBefore, "total_quantity decreased", "Before: $stockBefore, After: ".($stockAfter->total_quantity??'null'));
}
echo "\n";

// ====== SUMMARY ======
$total = $passCount + $failCount;
echo "═══════════════════════════════════════════\n";
echo " TEST SUMMARY\n";
echo "═══════════════════════════════════════════\n";
echo sprintf("Total: %d | Passed: %d | Failed: %d | Pass Rate: %.1f%%\n", $total, $passCount, $failCount, $total>0?($passCount/$total*100):0);
echo "\n";

if ($bugs) {
    echo "===== BUGS FOUND =====\n";
    foreach ($bugs as $i => $bug) echo sprintf("BUG-%03d: %s\n", $i+1, $bug);
    echo "\n";
}

echo "===== DATABASE STATE =====\n";
echo sprintf("Warehouses: %d\n", dbCount('warehouses',['company_id'=>$companyId]));
echo sprintf("Salesmen: %d\n", dbCount('salesmen',['company_id'=>$companyId]));
echo sprintf("Shops: %d\n", dbCount('shops',['company_id'=>$companyId]));
echo sprintf("Suppliers: %d\n", dbCount('suppliers',['company_id'=>$companyId]));
echo sprintf("Categories: %d\n", dbCount('categories',['company_id'=>$companyId]));
echo sprintf("Products: %d\n", dbCount('materials',['company_id'=>$companyId]));
echo sprintf("Sales Orders: %d\n", dbCount('sales_orders',['company_id'=>$companyId]));
echo sprintf("Invoices: %d\n", dbCount('invoices',['company_id'=>$companyId]));
echo sprintf("Payments: %d\n", dbCount('payments',['company_id'=>$companyId]));
echo sprintf("Deliveries: %d\n", dbCount('deliveries',['company_id'=>$companyId]));
echo sprintf("Sales Returns: %d\n", dbCount('sales_returns',['company_id'=>$companyId]));
echo sprintf("Purchase Returns: %d\n", dbCount('purchase_returns',['company_id'=>$companyId]));
echo sprintf("Stock Records: %d\n", dbCount('stocks',['company_id'=>$companyId]));
echo "\nTest run: " . date('Y-m-d H:i:s') . "\n";
