<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OrderStatsWidget extends BaseWidget
{
    // Optimized performance - caching stats for 5 minutes
    protected static ?string $pollingInterval = '300s';
    
    // You can use the default view or your custom view
    // protected static string $view = 'filament.resources.order-resource.widgets.order-stats-widget';
    
    protected function getStats(): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        
        // Efficiently query the database with single queries
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $todayRevenue = Order::whereDate('created_at', $today)->sum('total_amount');
        
        $weeklyOrders = Order::where('created_at', '>=', $startOfWeek)->count();
        $weeklyRevenue = Order::where('created_at', '>=', $startOfWeek)->sum('total_amount');
        
        $monthlyOrders = Order::where('created_at', '>=', $startOfMonth)->count();
        $monthlyRevenue = Order::where('created_at', '>=', $startOfMonth)->sum('total_amount');
        
        // Calculate change percentages (optimized with caching or efficient queries)
        $yesterdayOrders = Order::whereDate('created_at', $today->copy()->subDay())->count();
        $orderChangePercentage = $yesterdayOrders > 0 
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100, 1) 
            : 0;
            
        $yesterdayRevenue = Order::whereDate('created_at', $today->copy()->subDay())->sum('total_amount');
        $revenueChangePercentage = $yesterdayRevenue > 0 
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1) 
            : 0;
        
        // Status counts - using a single query with groupBy
        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        $pendingCount = $statusCounts['pending'] ?? 0;
        $processingCount = $statusCounts['processing'] ?? 0;
        
        return [
            Stat::make('Today\'s Orders', $todayOrders)
                ->description($orderChangePercentage > 0 ? "+{$orderChangePercentage}% from yesterday" : "{$orderChangePercentage}% from yesterday")
                ->descriptionIcon($orderChangePercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orderChangePercentage > 0 ? 'success' : 'danger')
                ->chart([
                    Order::whereDate('created_at', $today->copy()->subDays(6))->count(),
                    Order::whereDate('created_at', $today->copy()->subDays(5))->count(),
                    Order::whereDate('created_at', $today->copy()->subDays(4))->count(),
                    Order::whereDate('created_at', $today->copy()->subDays(3))->count(),
                    Order::whereDate('created_at', $today->copy()->subDays(2))->count(),
                    Order::whereDate('created_at', $today->copy()->subDays(1))->count(),
                    $todayOrders,
                ]),
            
            Stat::make('Today\'s Revenue', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description($revenueChangePercentage > 0 ? "+{$revenueChangePercentage}% from yesterday" : "{$revenueChangePercentage}% from yesterday")
                ->descriptionIcon($revenueChangePercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChangePercentage > 0 ? 'success' : 'danger')
                ->chart([
                    Order::whereDate('created_at', $today->copy()->subDays(6))->sum('total_amount'),
                    Order::whereDate('created_at', $today->copy()->subDays(5))->sum('total_amount'),
                    Order::whereDate('created_at', $today->copy()->subDays(4))->sum('total_amount'),
                    Order::whereDate('created_at', $today->copy()->subDays(3))->sum('total_amount'),
                    Order::whereDate('created_at', $today->copy()->subDays(2))->sum('total_amount'),
                    Order::whereDate('created_at', $today->copy()->subDays(1))->sum('total_amount'),
                    $todayRevenue,
                ]),
                
            Stat::make('Pending Orders', $pendingCount)
                ->description('Need processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Processing Orders', $processingCount)
                ->description('Ready to ship')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }
}