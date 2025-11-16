@echo off
echo DMS Laragon Management Script
echo =============================

:menu
echo.
echo 1. Start DMS Development Server
echo 2. Open DMS in Browser  
echo 3. Access MySQL Database
echo 4. Clear Laravel Cache
echo 5. Run Migrations
echo 6. Exit
echo.
set /p choice="Choose an option (1-6): "

if %choice%==1 (
    echo Starting Laravel development server...
    cd /d "C:\laragon\www\dms"
    start cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
    echo Development server started at http://localhost:8000
    goto menu
)

if %choice%==2 (
    echo Opening DMS in browser...
    start http://dms.test
    goto menu
)

if %choice%==3 (
    echo Opening MySQL command line...
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -p
    goto menu
)

if %choice%==4 (
    echo Clearing Laravel cache...
    cd /d "C:\laragon\www\dms"
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    echo Cache cleared successfully!
    goto menu
)

if %choice%==5 (
    echo Running Laravel migrations...
    cd /d "C:\laragon\www\dms"
    php artisan migrate
    echo Migrations completed!
    goto menu
)

if %choice%==6 (
    exit
)

echo Invalid choice. Please try again.
goto menu