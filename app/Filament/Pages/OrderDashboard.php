<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OrderResource\Widgets\LatestOrdersWidget;
use App\Filament\Resources\OrderResource\Widgets\OrdersChartWidget;
use App\Filament\Resources\OrderResource\Widgets\OrderStatsWidget;
use Filament\Pages\Page;



class OrderDashboard extends Page
{

    protected static string $view = 'filament.pages.order-dashboard';


    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Merchandise Management';

    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Dashboard';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('order-dashboard.view') ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            OrdersChartWidget::class,
            LatestOrdersWidget::class,
        ];
    }
}
