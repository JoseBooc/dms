# Setup Script for New DMS Updates
# Run these commands after starting your database service

# 1. Run new migrations
php artisan migrate

# 2. Run new seeders for penalty settings and staff users
php artisan db:seed --class=DefaultPenaltySettingsSeeder
php artisan db:seed --class=StaffUserSeeder

# 3. Clear caches to ensure all changes are loaded
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 4. Optimize application
php artisan optimize

# 5. Start the development server (if not already running)
php artisan serve