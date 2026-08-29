<?php

declare(strict_types=1);

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

final class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $reason = Arr::pull($data, 'audit_reason');
        request()->merge(['audit_reason' => $reason]);

        $privilegedRoles = [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN];
        $isCurrentlyPrivileged = in_array($record->role, $privilegedRoles, true);
        $willRemainPrivileged = in_array($data['role'] ?? null, $privilegedRoles, true);

        if ($isCurrentlyPrivileged && ! $willRemainPrivileged && User::query()->whereIn('role', $privilegedRoles)->count() <= 1) {
            throw ValidationException::withMessages(['data.role' => 'The final Super Admin cannot be demoted.']);
        }

        $record->forceFill($data);
        $record->save();

        return $record;
    }
}
