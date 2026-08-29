<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Support\AdminAccess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user() instanceof User;
    }

    protected function getStats(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $stats = [];

        if ($user->hasAdminPermission(AdminAccess::FINANCE_VIEW)) {
            $stats[] = Stat::make('Revenue (7d)', '₹'.number_format((float) Order::where('payment_status', 'paid')->where('created_at', '>=', now()->startOfWeek())->sum('total')))
                ->description('Paid orders this week')->descriptionIcon('heroicon-m-banknotes')->color('success');
            $stats[] = Stat::make('Payment attention', Payment::whereIn('status', [Payment::STATUS_FAILED, Payment::STATUS_INITIATED])->count())
                ->description('Failed or still-initiated payments')->descriptionIcon('heroicon-m-credit-card')->color('warning');
        }

        if ($user->hasAdminPermission(AdminAccess::ORDERS_VIEW)) {
            $stats[] = Stat::make('Orders (today)', Order::where('created_at', '>=', now()->startOfDay())->count())
                ->description('All statuses')->descriptionIcon('heroicon-m-shopping-bag')->color('info');
        }

        if ($user->hasAdminPermission(AdminAccess::CUSTOMERS_VIEW)) {
            $stats[] = Stat::make('Customers', User::where('role', User::ROLE_CUSTOMER)->count())
                ->description('Registered customer accounts')->descriptionIcon('heroicon-m-users')->color('primary');
        }

        if ($user->hasAdminPermission(AdminAccess::CATALOGUE_VIEW)) {
            $stats[] = Stat::make('Low stock', Product::whereColumn('stock', '<=', 'low_stock_threshold')->where('is_active', true)->count())
                ->description('Active products at/below threshold')->descriptionIcon('heroicon-m-exclamation-triangle')->color('danger');
            $stats[] = Stat::make('Product health', Product::where('is_active', false)->orWhereDoesntHave('media')->count())
                ->description('Inactive or missing media')->descriptionIcon('heroicon-m-wrench-screwdriver')->color('warning');
        }

        return $stats;
    }
}
