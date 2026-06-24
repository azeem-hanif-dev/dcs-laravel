<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait for enforcing distributor-level row visibility.
 *
 * Superadmin and admin see ALL records in the company.
 * Distributors see:
 *   - Records they created (user_id / ordered_by)
 *   - Records assigned to their distributor entity (distributor_id)
 */
trait DistributorVisibility
{
    /**
     * Apply visibility filter by creator column (user_id or ordered_by).
     *
     * @return Builder
     */
    protected function applyVisibility(Builder $query, Request $request, string $ownerColumn = 'user_id'): Builder
    {
        $user = $request->auth_user;

        if (!$user) {
            return $query;
        }

        // Superadmin and admin see everything
        if (in_array($user->role, ['superadmin', 'admin'])) {
            return $query;
        }

        // Distributor sees only their own records
        if ($user->role === 'distributor') {
            return $query->where($ownerColumn, $user->id);
        }

        // Other roles — restrict to own
        return $query->where($ownerColumn, $user->id);
    }

    /**
     * Apply visibility by the user's distributor_id column on the target table.
     *
     * Use this for models that have a direct `distributor_id` column (e.g. salesmen).
     *
     * @return Builder
     */
    protected function applyDistributorScope(Builder $query, Request $request): Builder
    {
        $user = $request->auth_user;

        if (!$user) {
            return $query;
        }

        if (in_array($user->role, ['superadmin', 'admin'])) {
            return $query;
        }

        if ($user->role === 'distributor' && $user->distributor_id) {
            // Show records EITHER created by the user OR assigned to their distributor
            return $query->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('distributor_id', $user->distributor_id);
            });
        }

        return $query->where('user_id', $user->id);
    }

    /**
     * Apply visibility for models linked through a belongsTo chain to distributor.
     *
     * For example, Shops are linked to Salesmen who have distributor_id.
     * This filters shops whose salesman.distributor_id matches the user's distributor_id,
     * OR shops the user directly created.
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  string   $relationPath  Dot-notation path to the distributor_id:
     *                                  e.g. 'salesman' for shops
     * @return Builder
     */
    protected function applyDistributorThroughScope(Builder $query, Request $request, string $relationPath): Builder
    {
        $user = $request->auth_user;

        if (!$user) {
            return $query;
        }

        if (in_array($user->role, ['superadmin', 'admin'])) {
            return $query;
        }

        if ($user->role === 'distributor' && $user->distributor_id) {
            return $query->where(function (Builder $q) use ($user, $relationPath) {
                // Either the user created it directly
                $q->where('user_id', $user->id);
                // OR it belongs to a related entity owned by the distributor
                $q->orWhereHas($relationPath, function (Builder $sub) use ($user) {
                    $sub->where('distributor_id', $user->distributor_id);
                });
            });
        }

        return $query->where('user_id', $user->id);
    }

    /**
     * Check if the authenticated user can update the status of a record.
     */
    protected function canUpdateStatus(object $record, Request $request): bool
    {
        $user = $request->auth_user;

        if (!$user) {
            return false;
        }

        // Superadmin and admin can update any status
        if (in_array($user->role, ['superadmin', 'admin'])) {
            return true;
        }

        // Distributor can update status if they created it
        $ownerId = $record->user_id ?? $record->ordered_by ?? null;

        if ($ownerId !== null && (int) $ownerId === (int) $user->id) {
            return true;
        }

        // OR if the record's distributor_id matches the user's distributor record
        if ($user->role === 'distributor' && $user->distributor_id && isset($record->distributor_id)) {
            return (int) $record->distributor_id === (int) $user->distributor_id;
        }

        return false;
    }

    /**
     * Abort with 403 if the user cannot update the status of a record.
     */
    protected function authorizeStatusUpdate(object $record, Request $request): void
    {
        if (!$this->canUpdateStatus($record, $request)) {
            abort(403, 'You are not authorized to update the status of this record.');
        }
    }
}
