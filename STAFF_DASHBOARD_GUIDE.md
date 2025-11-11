# Staff Dashboard - User Guide

## Overview
Staff members can now access a dedicated dashboard to view and manage their assigned maintenance work and complaints.

## Accessing the Staff Dashboard

### Login Credentials
You can use any of these staff accounts to test the functionality:
- **Email:** staff@areja.com
- **Email:** maria.garcia@areja.com
- **Email:** robert.johnson@areja.com
- **Password:** password (for all accounts)

### Dashboard Access
1. Navigate to your application URL
2. Log in with a staff account
3. You will be directed to `/dashboard/staff-dashboard`
4. The dashboard will show as "My Work" in the navigation menu

## Dashboard Features

### Statistics Overview
The dashboard displays 8 key metrics:

**Maintenance Statistics:**
- Total Maintenance - All tasks assigned to you
- Pending - Tasks waiting to be started
- In Progress - Tasks currently being worked on
- Completed - Finished tasks

**Complaint Statistics:**
- Total Complaints - All complaints assigned to you
- Pending - Complaints needing attention
- In Progress - Complaints being handled
- Resolved - Completed complaints

### Maintenance Requests Section
Shows all maintenance requests assigned to you with:
- **Priority Level:** High (red), Medium (yellow), Low (green)
- **Status:** Pending, In Progress, Completed
- **Room Information:** Room number and affected area
- **Description:** Details of the maintenance work needed
- **Tenant Information:** Who submitted the request
- **Action Buttons:**
  - "Start Work" - Changes status from Pending to In Progress
  - "Mark Complete" - Changes status from In Progress to Completed

### Complaints Section
Shows all complaints assigned to you with:
- **Priority Level:** High (red), Medium (yellow), Low (green)
- **Status:** Pending, In Progress, Resolved
- **Category:** Type of complaint
- **Title & Description:** Complaint details
- **Room & Tenant Information**
- **Resolution Notes:** If the complaint has been resolved
- **Action Buttons:**
  - "Start Handling" - Changes status from Pending to In Progress
  - "Mark Resolved" - Changes status from In Progress to Resolved

## How to Update Work Status

### For Maintenance Requests:
1. Find the maintenance request in the list
2. Click "Start Work" to begin working on it (status changes to In Progress)
3. Once done, click "Mark Complete" to finish (status changes to Completed)

### For Complaints:
1. Find the complaint in the list
2. Click "Start Handling" to begin addressing it (status changes to In Progress)
3. Once resolved, click "Mark Resolved" to close it (status changes to Resolved)

## Navigation
Staff members have access to:
- **My Work** (Staff Dashboard) - Your assigned tasks
- **Admin Resources** - Full access to manage system data (if staff have admin permissions)

## Additional Widgets
Two widgets are available for the main dashboard view:
- **StaffMaintenanceWidget** - Quick view of your maintenance tasks
- **StaffComplaintsWidget** - Quick view of your assigned complaints

## Technical Details

### Files Created:
1. **Controller/Page:**
   - `app/Filament/Pages/StaffDashboard.php`
   - View: `resources/views/filament/pages/staff-dashboard.blade.php`

2. **Widgets:**
   - `app/Filament/Widgets/StaffMaintenanceWidget.php`
   - View: `resources/views/filament/widgets/staff-maintenance-widget.blade.php`
   - `app/Filament/Widgets/StaffComplaintsWidget.php`
   - View: `resources/views/filament/widgets/staff-complaints-widget.blade.php`

3. **Database Seeder:**
   - `database/seeders/StaffUserSeeder.php`

### Access Control:
- Only users with `role = 'staff'` can access the staff dashboard
- Uses the existing `CheckRole` middleware
- Filament's built-in `canAccess()` method controls page visibility

### Database Relationships:
- MaintenanceRequest model uses `assigned_to` field to link to staff users
- Complaint model uses `assigned_to` field to link to staff users
- User model has `assignedMaintenanceRequests()` and `assignedComplaints()` relationships

## Testing the Setup

### Create Test Data:
1. Log in as admin
2. Create some maintenance requests
3. Assign them to staff members using the "Assigned To" dropdown
4. Create some complaints
5. Assign them to staff members

### Test as Staff:
1. Log out and log in as a staff member
2. Navigate to "My Work" dashboard
3. Verify you can see your assigned tasks
4. Test the status update buttons
5. Verify statistics update correctly

## Troubleshooting

### Staff Dashboard Not Showing:
- Clear cache: `php artisan optimize:clear`
- Verify user role is set to 'staff' in the database
- Check that user is logged in

### No Assignments Showing:
- Verify maintenance requests/complaints have `assigned_to` set to your user ID
- Check database: `SELECT * FROM maintenance_requests WHERE assigned_to = YOUR_USER_ID`

### Cannot Update Status:
- Verify you're assigned to the task (assigned_to field matches your user ID)
- Check browser console for JavaScript errors
- Ensure Livewire is properly loaded

## Future Enhancements
Possible improvements for the staff dashboard:
- Add filtering and sorting options
- Add search functionality
- Add calendar view for scheduled maintenance
- Add time tracking for tasks
- Add comments/notes on tasks
- Add photo upload for completed work
- Email notifications for new assignments
- Mobile-responsive improvements
