# 🚀 Team Setup Guide - Complete Dashboard Version

## Quick Setup for Team Members

### 1. Clone and Install Dependencies
```bash
# Clone the repository
git clone <repository-url>
cd dms

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Configure `.env` file:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dms
DB_USERNAME=your_username
DB_PASSWORD=your_password

APP_URL=http://localhost:8000
```

### 3. Database Setup
```bash
# Create database
mysql -u your_username -p
CREATE DATABASE dms;
exit

# Run migrations
php artisan migrate

# Create storage link
php artisan storage:link
```

### 4. Create Admin User
```bash
# Create Filament admin user
php artisan make:filament-user
```

### 5. Compile Assets and Start Server
```bash
# Compile assets
npm run build

# Start Laravel server
php artisan serve

# Optional: Start dev server for hot reloading
npm run dev
```

### 6. Verify Setup
```bash
# Run verification script
php verify-setup.php
```

## 📊 Dashboard Features

Once set up, you'll have access to:

### Main Dashboard (`/dashboard`)

**Statistics Cards (Row 1)**:
- Total Rooms, Occupied Rooms, Available Rooms
- Occupancy Rate (%), Total Tenants, Monthly Revenue

**Monthly Revenue Chart (Row 2)**:
- 12-month revenue trend visualization
- Philippine Peso (₱) formatting
- Smooth line with filled area

**Maintenance & Complaints Overview (Row 3)**:
- Pie chart showing task status breakdown
- Pending (Red), In Progress (Yellow), Completed (Green)
- Combined maintenance requests and complaints data

### Management Resources
- **Users**: User account management with role-based access
- **Tenants**: Student information with emergency contacts
- **Rooms**: Room management with status tracking
- **Room Assignments**: Tenant-room assignments with history
- **Bills**: Billing and payment tracking
- **Maintenance Requests**: Student maintenance submissions
- **Complaints**: Student complaint management
- **Utility Types & Readings**: Utility management system

## 🎯 URL Structure

| URL | Purpose |
|-----|----------|
| `http://localhost:8000/dashboard` | Main Filament dashboard |
| `http://localhost:8000/dashboard/login` | Filament login |
| `http://localhost:8000/dashboard/tenants` | Tenant management |
| `http://localhost:8000/dashboard/rooms` | Room management |
| `http://localhost:8000/dashboard/bills` | Billing management |

**Legacy URL Redirects** (automatically redirect to Filament):
- `/login` → `/dashboard/login`
- `/admin` → `/dashboard`
- `/filament-admin` → `/dashboard`

## 🔐 User Roles

- **Admin**: Full access to all features
- **Staff**: Full access to all features  
- **Tenant**: Limited access (dashboard view only)

## 🎨 Widget Details

### 1. RoomOccupancyWidget (Stats Cards)
- **File**: `app/Filament/Widgets/RoomOccupancyWidget.php`
- **Sort Order**: 1 (displays first)
- **Data**: Room counts, occupancy rate, tenant count, monthly revenue

### 2. MonthlyRevenueChart (Line Chart)
- **File**: `app/Filament/Widgets/MonthlyRevenueChart.php`
- **Sort Order**: 2 (displays second)
- **Data**: 12-month revenue trend from paid bills

### 3. DashboardOverview (Pie Chart)
- **File**: `app/Filament/Widgets/DashboardOverview.php`
- **Sort Order**: 3 (displays third)
- **Data**: Combined maintenance and complaint status counts

## 🚨 Troubleshooting

### Empty Dashboard
If you see an empty dashboard:
```bash
php artisan optimize:clear
php verify-setup.php
```

### Permission Errors
```bash
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

### Widget Not Showing
Check if widgets are registered in `config/filament.php`:
```php
'register' => [
    \App\Filament\Widgets\RoomOccupancyWidget::class,
    \App\Filament\Widgets\MonthlyRevenueChart::class,
    \App\Filament\Widgets\DashboardOverview::class,
    // ...
],
```

### Database Issues
```bash
# Check migration status
php artisan migrate:status

# Reset database if needed
php artisan migrate:fresh
```

## 📞 Support

If you encounter issues:
1. Run `php verify-setup.php` to check your setup
2. Check logs in `storage/logs/laravel.log`
3. Ensure all dependencies are installed
4. Clear all caches: `php artisan optimize:clear`

## 🆕 What's New in This Version

- ✅ Complete Filament admin panel with dashboard widgets
- ✅ Statistics cards showing real-time room and revenue data
- ✅ Monthly revenue trend visualization
- ✅ Maintenance and complaint status tracking
- ✅ Unified URL structure with automatic redirects
- ✅ Role-based access control
- ✅ Historical data preservation
- ✅ Modern responsive design

Welcome to the enhanced Dormitory Management System! 🎉