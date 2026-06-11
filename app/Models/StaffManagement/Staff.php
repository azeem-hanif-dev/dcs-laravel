<?php

namespace App\Models\StaffManagement;

/**
 * @deprecated Use User model directly. Kept for backward compatibility.
 */
class Staff extends \App\Models\User
{
    // Alias for the unified users table
    // All existing code referencing Staff will still work
}
