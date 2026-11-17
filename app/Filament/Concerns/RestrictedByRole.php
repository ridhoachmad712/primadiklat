<?php

namespace App\Filament\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

/**
 * Simple role gate for resources. Super admin always passes.
 * Override allowedRoles() in the resource to change who may manage it.
 */
trait RestrictedByRole
{
    public static function allowedRoles(): array
    {
        return [User::ROLE_EDITOR];
    }

    public static function getAuthorizationResponse(string $action, ?Model $record = null): Response
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasRole(...static::allowedRoles())
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke menu ini.');
    }
}
