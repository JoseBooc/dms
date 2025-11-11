# Tenant-Specific Utility Readings Enhancement

## Overview
This document describes the enhancement to the utility readings system to support tenant-specific tracking, especially for shared rooms.

## Key Features

### 1. Tenant-Specific Readings
- ✅ **tenant_id field** added to utility_readings table
- ✅ Each utility reading is now associated with a specific tenant
- ✅ Supports both single and shared room scenarios

### 2. Smart Room Selection
When creating a utility reading:
1. **Select Room** → Shows room number with current tenant(s) names
2. **Select Tenant** → Auto-populated with active tenants in that room
   - Single tenant: Auto-selected automatically
   - Multiple tenants: Admin selects which tenant the reading is for
   - Helper text shows room status (single/shared)

### 3. Shared Room Support

#### Scenario A: Individual Tenant Readings
- Each tenant has their own utility reading
- Charges are tracked separately per tenant
- Bills pull tenant-specific readings

#### Scenario B: Room-Level Reading (Backward Compatible)
- One reading for the entire room (tenant_id = NULL)
- Charges automatically split among active tenants
- Division: charges ÷ number of active tenants

## Implementation Details

### Database Structure
```sql
-- utility_readings table already has:
tenant_id (nullable, foreign key to users)
room_id (required, foreign key to rooms)
status (pending → billed → paid)
bill_id (links to bills table)
```

### Form Enhancements

#### UtilityReadingResource.php
```php
// Room selection shows tenant names
Forms\Components\Select::make('room_id')
    ->getOptionLabelFromRecordUsing(function ($record) {
        $tenantInfo = '';
        $activeAssignments = $record->assignments()->where('status', 'active')->get();
        if ($activeAssignments->count() > 0) {
            $tenantNames = $activeAssignments->map(...)
            $tenantInfo = ' (' . $tenantNames . ')';
        }
        return $record->room_number . $tenantInfo;
    })

// Tenant selection filters by room
Forms\Components\Select::make('tenant_id')
    ->options(function (callable $get) {
        $roomId = $get('room_id');
        // Returns only active tenants for selected room
    })
    ->helperText(function (callable $get) {
        // Shows: Single tenant / Shared room / No tenants
    })
```

### Billing Integration

#### BillResource.php
Smart utility charge calculation:

```php
// Priority 1: Tenant-specific reading
$latestReading = UtilityReading::where('room_id', $roomId)
    ->where('tenant_id', $tenantId) // Tenant-specific
    ->whereNull('bill_id')
    ->latest('reading_date')
    ->first();

if ($latestReading) {
    // Use tenant's specific charges
    $set('electricity', $latestReading->electric_charge);
    $set('water', $latestReading->water_charge);
} else {
    // Priority 2: Room-level reading (backward compatibility)
    $roomReading = UtilityReading::where('room_id', $roomId)
        ->whereNull('tenant_id') // Room-level
        ->whereNull('bill_id')
        ->latest('reading_date')
        ->first();
    
    if ($roomReading) {
        // Split charges among active tenants
        $activeTenantCount = RoomAssignment::where('room_id', $roomId)
            ->where('status', 'active')
            ->count();
        
        $divisor = max(1, $activeTenantCount);
        
        $set('electricity', $roomReading->electric_charge / $divisor);
        $set('water', $roomReading->water_charge / $divisor);
    }
}
```

### Model Enhancements

#### UtilityReading.php - New Methods

```php
// Get all readings for a shared room
public static function getSharedRoomReadings($roomId, $billingPeriod)
{
    return static::where('room_id', $roomId)
        ->where('billing_period', $billingPeriod)
        ->whereNotNull('tenant_id')
        ->with('tenant')
        ->get();
}

// Check if room is shared
public function isSharedRoom(): bool
{
    return $this->room->assignments()->where('status', 'active')->count() > 1;
}

// Calculate tenant's share in shared room
public function calculateTenantShare(): float
{
    if (!$this->isSharedRoom()) {
        return $this->total_utility_charge;
    }
    
    $sharedReadings = static::getSharedRoomReadings($this->room_id, $this->billing_period);
    $tenantCount = $sharedReadings->count();
    
    return $this->total_utility_charge / $tenantCount;
}

// Scopes
public function scopeForTenant($query, $tenantId)
public function scopeUnbilled($query)
public function scopeForBillingPeriod($query, $period)
```

## Usage Scenarios

### Scenario 1: Single Tenant Room
```
1. Create utility reading
2. Select Room 101 → Shows "(John Doe)"
3. Tenant auto-selected: John Doe
4. Enter water/electric readings
5. Save

When billing John Doe:
→ System pulls John's specific reading
→ Full charges applied to his bill
```

### Scenario 2: Shared Room (Individual Readings)
```
1. Create reading for Tenant A
   - Room 102 → Shows "(Alice Smith, Bob Jones)"
   - Select: Alice Smith
   - Water: 10 m³, Electric: 150 kWh
   - Save

2. Create reading for Tenant B
   - Room 102 → Shows "(Alice Smith, Bob Jones)"
   - Select: Bob Jones
   - Water: 8 m³, Electric: 120 kWh
   - Save

When billing:
→ Alice's bill: 10 m³ water + 150 kWh electric
→ Bob's bill: 8 m³ water + 120 kWh electric
```

### Scenario 3: Shared Room (Split Charges - Backward Compatible)
```
1. Create ONE room-level reading
   - Room 102 → Shows "(Alice Smith, Bob Jones)"
   - Leave tenant_id = NULL (old method)
   - Water: 18 m³, Electric: 270 kWh
   - Save

When billing Alice:
→ Water: 18 ÷ 2 = 9 m³
→ Electric: 270 ÷ 2 = 135 kWh

When billing Bob:
→ Water: 18 ÷ 2 = 9 m³
→ Electric: 270 ÷ 2 = 135 kWh
```

## Table Columns

### Updated Utility Readings Table
| Column | Description |
|--------|-------------|
| Room | Room number |
| **Tenant** | 🆕 Tenant name (shows who the reading is for) |
| Date | Reading date |
| Billing Period | Month/Year |
| Status | pending → billed → paid |
| Water Usage | Consumption in m³ |
| Water Charge | Cost in ₱ |
| Electric Usage | Consumption in kWh |
| Electric Charge | Cost in ₱ |

## Status Synchronization

Automatic status updates when linked to bills:

```
Utility Reading Created
        ↓
   [pending]  ← tenant_id can be NULL (room-level) or specific tenant ID
        ↓
Bill Created for Tenant
        ↓
System finds: UtilityReading
  WHERE tenant_id = bill.tenant_id  (tenant-specific)
  OR tenant_id IS NULL (room-level, will be split)
        ↓
    [billed]  ← Linked to bill via bill_id
        ↓
Bill Fully Paid
        ↓
     [paid]   ← Utility charges confirmed paid
```

## Backward Compatibility

### Old System (Room-based only)
- tenant_id = NULL
- One reading per room
- Manual charge splitting

### New System (Tenant-aware)
- tenant_id = specific tenant
- Multiple readings per shared room
- Automatic tenant assignment
- Falls back to room-based splitting if no tenant-specific readings

**Both methods work together seamlessly!**

## Benefits

1. ✅ **Accurate Billing**: Each tenant billed for their actual usage
2. ✅ **Shared Room Support**: Handles multiple tenants per room
3. ✅ **Flexibility**: Choose individual or split charging method
4. ✅ **Backward Compatible**: Old room-based readings still work
5. ✅ **Automatic Status**: Readings sync with bill payment status
6. ✅ **Audit Trail**: Track which tenant each reading belongs to

## Migration Guide

### For Existing Data
All existing utility readings have tenant_id = NULL (room-level).
System automatically handles them by:
1. Finding the room
2. Counting active tenants
3. Splitting charges equally

### For New Readings
Recommended approach:
- **Single rooms**: System auto-selects the tenant
- **Shared rooms**: Create individual readings per tenant for accuracy
- **Alternative**: Continue using room-level readings (charges auto-split)

## Testing Checklist

- [ ] Create reading for single-tenant room → tenant auto-selected
- [ ] Create reading for shared room → tenant dropdown shows both tenants
- [ ] Create individual readings for each tenant in shared room
- [ ] Create bill for tenant A → pulls tenant A's specific reading
- [ ] Create bill for tenant B → pulls tenant B's specific reading
- [ ] Create room-level reading (tenant_id NULL) → charges split on billing
- [ ] Verify table shows tenant column with tenant names
- [ ] Verify status changes: pending → billed → paid
- [ ] Test backward compatibility with old NULL tenant_id readings

## Future Enhancements

Potential improvements:
- [ ] Custom split percentages (e.g., 60/40 instead of 50/50)
- [ ] Utility reading approval workflow
- [ ] Tenant can view their own utility history
- [ ] Comparative reports: tenant vs room average
- [ ] Alert if shared room has incomplete readings (missing tenants)
- [ ] Bulk reading entry for multiple rooms
