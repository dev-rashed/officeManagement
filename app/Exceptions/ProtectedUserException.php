<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when something tries to delete or demote an account that has to
 * survive -- the protected superadmin, or the last superadmin standing.
 */
class ProtectedUserException extends RuntimeException
{
    public static function cannotDelete(string $name): self
    {
        return new self("{$name} is a protected superadmin account and cannot be deleted.");
    }

    public static function cannotDemote(string $name): self
    {
        return new self("{$name} is a protected superadmin account and cannot be moved to another role.");
    }

    public static function cannotDisable(string $name): self
    {
        return new self("{$name} is a protected superadmin account and cannot be disabled.");
    }

    public static function lastSuperadmin(string $action): self
    {
        return new self("This is the only superadmin left. {$action} it would lock everyone out of roles and permissions.");
    }
}
