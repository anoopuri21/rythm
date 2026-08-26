<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class PermissionPolicy
{
    protected string $viewPermission;

    protected string $managePermission;

    public function viewAny(User $user): bool
    {
        return $user->hasAdminPermission($this->viewPermission);
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminPermission($this->managePermission);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->create($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->create($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->create($user);
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $this->create($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->create($user);
    }

    public function replicate(User $user, Model $record): bool
    {
        return $this->create($user);
    }

    public function reorder(User $user): bool
    {
        return $this->create($user);
    }
}
