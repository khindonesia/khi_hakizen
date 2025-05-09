<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersChartWidget extends ChartWidget
{
    
    protected static ?string $heading = 'Orders Overview';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '300s';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get orders for the last 30 days with efficient query
        // Using a single query with SQL date functions for performance
        $orders = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Prepare the data structure
        $dates = [];
        $counts = [];
        $revenues = [];
        
        // Fill in any missing dates with zeros for a complete 30-day chart
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');
            
            $dayData = $orders->firstWhere('date', $date);
            $counts[] = $dayData ? $dayData->count : 0;
            $revenues[] = $dayData ? $dayData->revenue / 1000 : 0; // Divide by 1000 for better scale
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $counts,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                    'tension' => 0.3,
                    'fill' => false,
                ],
                [
                    'label' => 'Revenue (in thousands)',
                    'data' => $revenues,
                    'backgroundColor' => '#4BC0C0',
                    'borderColor' => '#4BC0C0',
                    'tension' => 0.3,
                    'fill' => false,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Orders',
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (in thousands)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}