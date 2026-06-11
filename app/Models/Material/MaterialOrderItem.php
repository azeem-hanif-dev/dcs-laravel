<?php

namespace App\Models\Material;

/**
 * @deprecated Use PurchaseOrderItem model instead. Kept for backward compatibility.
 */
class MaterialOrderItem extends \App\Models\PurchaseOrderItem
{
    // This extends PurchaseOrderItem which already points to 'purchase_order_items'.
}
