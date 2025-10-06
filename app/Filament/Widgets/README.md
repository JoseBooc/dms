# Dashboard Widgets

This directory contains the Filament dashboard widgets that provide statistics and visualizations for the Dormitory Management System.

## Widgets Overview

### 1. RoomOccupancyWidget
**File**: `RoomOccupancyWidget.php`  
**Type**: Stats Overview Widget  
**Sort Order**: 1 (displays first)
**Purpose**: Displays key statistics in card format

**Metrics Displayed**:
- Total Rooms
- Occupied Rooms  
- Available Rooms
- Occupancy Rate (%)
- Total Tenants
- Monthly Revenue

### 2. MonthlyRevenueChart
**File**: `MonthlyRevenueChart.php`  
**Type**: Line Chart Widget
**Sort Order**: 2 (displays second)
**Purpose**: Revenue trends over the last 12 months

**Features**:
- Shows monthly revenue in Philippine Pesos (₱)
- 12-month historical data
- Only includes paid bills
- Filled area under curve

### 3. DashboardOverview
**File**: `DashboardOverview.php`  
**Type**: Pie Chart Widget
**Sort Order**: 3 (displays third)
**Purpose**: Maintenance and complaint status overview

**Data Visualized**:
- Pending tasks (Maintenance + Complaints) - Red
- In Progress tasks - Yellow
- Completed/Resolved tasks - Green

## Widget Configuration

Widgets are automatically registered in `config/filament.php`:

```php
'register' => [
    \App\Filament\Widgets\RoomOccupancyWidget::class,
    \App\Filament\Widgets\MonthlyRevenueChart::class,
    \App\Filament\Widgets\DashboardOverview::class,
    Widgets\AccountWidget::class,
],
```

## Team Setup

When team members clone the repository, these widgets will be automatically available after:

1. Running `composer install`
2. Setting up the database with `php artisan migrate`
3. Clearing caches with `php artisan optimize:clear`
4. Starting the server with `php artisan serve`

No additional setup is required - the widgets are automatically loaded and displayed on the dashboard at `/dashboard`.

## Customization

To modify widget appearance or data:

1. **Colors**: Update the `color()` method calls or chart backgroundColor arrays
2. **Data Sources**: Modify the queries in the `getData()` or `getCards()` methods
3. **Display Order**: Change the `$sort` property value
4. **Chart Types**: Update the widget type (extend different base classes)

## Dependencies

These widgets depend on the following models:
- `App\Models\Room`
- `App\Models\Tenant` 
- `App\Models\Bill`
- `App\Models\MaintenanceRequest`
- `App\Models\Complaint`

Ensure these models exist and have proper data for the widgets to display correctly.

## Color Scheme

The widgets use a consistent color scheme:
- **Primary Blue**: #0D2D63 (main theme)
- **Success Green**: #10B981 (positive metrics)
- **Warning Yellow**: #F59E0B (attention needed)
- **Danger Red**: #EF4444 (critical items)
- **Info Blue**: #3B82F6 (informational)