<?php

declare(strict_types=1);

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

final class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $reason = Arr::pull($data, 'audit_reason');
        request()->merge(['audit_reason' => $reason]);

        $user = new User;
        $user->forceFill($data + ['email_verified_at' => now()]);
        $user->save();

        return $user;
    }
}
