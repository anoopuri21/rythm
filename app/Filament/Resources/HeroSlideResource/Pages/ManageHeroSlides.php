<?php

declare(strict_types=1);

namespace App\Filament\Resources\HeroSlideResource\Pages;

use App\Filament\Resources\HeroSlideResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHeroSlides extends ManageRecords
{
    protected static string $resource = HeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New slide'),
        ];
    }
}
