<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use Filament\Resources\Pages\ManageRecords;

class ManageContactMessages extends ManageRecords
{
    protected static string $resource = ContactMessageResource::class;
}
