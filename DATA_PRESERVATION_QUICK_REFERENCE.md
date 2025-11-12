# Quick Reference: Data Preservation Policy

## 🚫 What Was Removed
- All delete buttons from UI
- All DeleteAction from Filament resources
- All delete() calls in controllers
- SoftDeletes from UtilityReading model
- Delete forms from views

## ✅ What To Do Instead

### Room Assignments
❌ Delete → ✅ Use "End Assignment" button
- Updates status to 'ended'
- Preserves historical assignment data
- Properly handles room occupancy

### Maintenance Requests
❌ Delete → ✅ Change status to:
- 'completed' - for finished work
- 'cancelled' - for withdrawn requests

### Complaints
❌ Delete → ✅ Change status to:
- 'resolved' - for addressed complaints
- 'closed' - for archived complaints

### Users
❌ Delete → ✅ Contact Administrator
- Admin can deactivate accounts
- User data preserved for audit trail

### Tenants
❌ Delete → ✅ End their room assignments
- Tenant record preserved
- Assignment history maintained

### Bills
❌ Delete → ✅ Mark as 'cancelled' if needed
- Bill history preserved
- Audit trail maintained

### Utility Types
❌ Delete → ✅ Mark as inactive
- Type preserved for historical readings
- No longer shown in new entries

### Rooms
❌ Delete → ✅ Mark as:
- 'unavailable' - temporarily out of service
- 'hidden' - permanently out of service

### Utility Readings
❌ No deletion allowed → ✅ Keep all readings
- Historical data required for billing
- Corrections made via adjustments

## ⚠️ Exception: Deposit Deductions
✅ CAN be soft deleted (reversible)
- Used for refund corrections
- Allows recalculation of refundable amounts
- Maintains audit trail

## 📋 Error Messages You'll See

If you try to delete through old bookmarks/forms:

**Room Assignments:**
> "Deleting room assignments is not allowed. Please use the 'End Assignment' feature instead."

**Maintenance Requests:**
> "Deleting maintenance requests is not allowed. Please cancel or mark as completed instead."

**Utility Types:**
> "Deleting utility types is not allowed. Please mark as inactive instead."

**User Account:**
> "Account deletion is not allowed. Please contact an administrator to deactivate your account."

## 🎯 Benefits
- ✅ Complete audit trail
- ✅ Historical data preserved
- ✅ No accidental data loss
- ✅ Better reporting accuracy
- ✅ Compliance with data retention policies

## 🔧 For Developers

### Models Without Delete
- User
- Tenant
- Room
- RoomAssignment
- Bill
- UtilityReading (SoftDeletes removed)
- MaintenanceRequest
- Complaint
- UtilityType

### Model WITH Soft Delete (Exception)
- DepositDeduction ✅ (business requirement)

### Database Changes
- Dropped `deleted_at` from `utility_readings` table
- Added performance indexes to `tenants` and `rooms` tables

### Code Comments Used
```php
// No delete action - data preservation policy
```

```blade
{{-- Delete button disabled - data preservation policy --}}
```
