<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $week = now()->startOfWeek();

        return [
            Stat::make('Revenue (7d)', '₹'.number_format((float) Order::where('payment_status', 'paid')->where('created_at', '>=', $week)->sum('total')))
                ->description('Paid orders this week')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Orders (today)', Order::whereDate('created_at', $today)->count())
                ->description('All statuses')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
            Stat::make('Customers', User::count())
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Low stock', Product::where('stock', '<=', 5)->where('is_active', true)->count())
                ->description('Products at/below threshold')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
