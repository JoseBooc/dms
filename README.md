# Dormitory Management System (DMS)

A comprehensive web-based dormitory management system built with Laravel and Filament Admin Panel. This system helps administrators efficiently manage student housing, billing, maintenance requests, and penalties.

## Features

### 🏠 **Room Management**
- Room inventory and availability tracking
- Room assignment and tenant management
- Occupancy reporting and analytics

### 💰 **Financial Management**
- Automated billing system for rent and utilities
- Penalty management for overdue payments
- Payment tracking and financial reporting
- Utility rate management and billing

### 🔧 **Maintenance System**
- Maintenance request submission and tracking
- Work order management
- Maintenance history and reporting
- Status updates and notifications

### 🔔 **Notification System**
- Real-time notifications for administrators
- Email notifications for important events
- Maintenance request alerts
- Payment reminders and overdue notifications

### 📊 **Reports & Analytics**
- Occupancy reports and trends
- Financial summaries and analytics
- Maintenance logs and statistics
- Comprehensive dashboard with key metrics

### 👥 **User Management**
- Role-based access control (Admin/Tenant)
- User authentication and authorization
- Profile management

## Technology Stack

- **Backend**: Laravel 9.x
- **Admin Panel**: Filament v2.17
- **Database**: MySQL/MariaDB
- **Frontend**: Livewire, Alpine.js, Tailwind CSS
- **Authentication**: Laravel Sanctum

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js 16+ and NPM
- MySQL 8.0+ or MariaDB 10.3+
- Web server (Apache/Nginx)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/dnpimperio/dms-lab.git
cd dms-lab
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the environment file and configure your settings:

```bash
copy .env.example .env
```

Edit `.env` file with your database and application settings:

```env
APP_NAME="Dormitory Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dms_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

Create your database and run migrations:

```bash
php artisan migrate
```

### 7. Seed the Database (Optional)

Run seeders to populate with sample data:

```bash
php artisan db:seed
```

### 8. Create Admin User

Create an admin user account:

```bash
php artisan make:filament-user
```

Or run the custom admin creation script:

```bash
php create-admin-user.php
```

### 9. Build Assets

Compile the frontend assets:

```bash
npm run build
```

For development with hot reloading:

```bash
npm run dev
```

### 10. Set Permissions (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 11. Start the Application

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Configuration

### Admin Panel Access

- Access the admin panel at: `http://localhost:8000/admin`
- Login with the admin credentials created in step 8

### Initial Setup

1. **Configure Penalty Settings**:
   - Go to Financial Management → Penalties
   - Click "Edit Penalty Settings" to configure late payment penalties

2. **Set Up Utility Rates**:
   - Navigate to Financial Management → Utility Rates
   - Configure electricity, water, and other utility rates

3. **Add Rooms**:
   - Go to Room Management → Rooms
   - Add dormitory rooms and set their capacities

4. **Create Sample Data** (Optional):
   Run the sample data creation scripts:
   ```bash
   php create-sample-complaints.php
   php create-sample-maintenance.php
   php create-sample-overdue-bills.php
   ```

## Usage

### For Administrators

1. **Dashboard**: View system overview and key metrics
2. **Room Management**: Manage rooms, assignments, and occupancy
3. **Financial Management**: Handle billing, payments, and penalties
4. **Maintenance**: Track and manage maintenance requests
5. **Reports**: Generate comprehensive reports and analytics
6. **Notifications**: Stay updated with system alerts

### For Tenants

1. **Dashboard**: View personal information and current bills
2. **Bills**: Check payment status and history
3. **Maintenance**: Submit and track maintenance requests
4. **Profile**: Update personal information

## Development

### Running Tests

```bash
php artisan test
```

### Code Style

```bash
./vendor/bin/pint
```

### Database Refresh

To reset the database with fresh data:

```bash
php artisan migrate:fresh --seed
```

## Troubleshooting

### Common Issues

1. **Permission Errors**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Cache Issues**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Asset Issues**:
   ```bash
   npm run build
   php artisan storage:link
   ```

4. **Database Connection**:
   - Verify database credentials in `.env`
   - Ensure database server is running
   - Check database exists

### Support

For issues and questions:
- Check the application logs in `storage/logs/`
- Review Laravel documentation: https://laravel.com/docs
- Check Filament documentation: https://filamentphp.com/docs

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Acknowledgments

- Built with [Laravel](https://laravel.com/)
- Admin interface powered by [Filament](https://filamentphp.com/)
- UI components from [Tailwind CSS](https://tailwindcss.com/)