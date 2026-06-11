<?php

namespace App\Models;

/**
 * @deprecated Use User model directly. Kept for backward compatibility.
 */
class Admin extends User
{
    // Alias for the unified users table
    // All existing code referencing Admin will still work
}
