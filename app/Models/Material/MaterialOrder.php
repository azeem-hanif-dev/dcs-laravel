<?php

namespace App\Models\Material;

/**
 * @deprecated Use PurchaseOrder model instead. Kept for backward compatibility.
 */
class MaterialOrder extends \App\Models\PurchaseOrder
{
    // This extends PurchaseOrder which already points to the 'purchase_orders' table.
    // All existing code referencing MaterialOrder will still work.
}
