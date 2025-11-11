# Philippine Dormitory Penalty System - Implementation Guide

## Overview
The penalty system has been redesigned to follow realistic Philippine dormitory/boarding house rules and practices.

## ✅ Completed Changes

### 1. Penalty Types
Three penalty calculation methods are now available:

- **`daily_fixed`** (Recommended) 
  - Fixed peso amount charged per day after grace period
  - Example: ₱50/day
  - Most common in Philippine dormitories

- **`percentage`**
  - One-time percentage of the total bill amount
  - Example: 3% of bill total
  - Applied once when bill becomes overdue (not per day)

- **`flat_fee`**
  - One-time flat penalty fee
  - Example: ₱200 fixed penalty
  - Applied once when bill becomes overdue

### 2. Penalty Settings Fields

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `penalty_type` | Enum | Type of penalty calculation | `daily_fixed` |
| `penalty_rate` | Decimal | Rate/amount for penalty | 50.00 |
| `grace_period_days` | Integer | Days after due date before penalty applies | 3 |
| `max_penalty` | Decimal | Maximum penalty cap | 500.00 |
| `active` | Boolean | Enable/disable penalty system | true |

### 3. Penalty Calculation Logic

```php
// Only apply penalties AFTER grace period
if (overdue_days <= grace_period_days) {
    return 0; // No penalty
}

applicable_days = overdue_days - grace_period_days;

switch (penalty_type) {
    case 'daily_fixed':
        penalty = penalty_rate × applicable_days;
        break;
        
    case 'percentage':
        penalty = bill_total × (penalty_rate / 100);
        break;
        
    case 'flat_fee':
        penalty = penalty_rate;
        break;
}

// Apply maximum cap
penalty = min(penalty, max_penalty);
```

### 4. Example Calculations

**Bill Amount:** ₱5,000.00  
**Settings:** Daily Fixed ₱50/day, 3-day grace period, ₱500 max

| Days Overdue | Status | Penalty | Calculation |
|--------------|--------|---------|-------------|
| 0 days | On time | ₱0 | No penalty |
| 2 days | Grace period | ₱0 | Within grace |
| 3 days | Grace end | ₱0 | Last day of grace |
| 5 days | 2 days late | ₱100 | 50 × 2 = 100 |
| 10 days | 7 days late | ₱350 | 50 × 7 = 350 |
| 15 days | 12 days late | ₱500 | 50 × 12 = 600, capped at 500 |
| 20 days | 17 days late | ₱500 | Maximum cap applied |

### 5. UI Improvements

✅ **Removed unrealistic defaults** (e.g., 500% penalties)  
✅ **Clear labeling** with Philippine peso (₱) symbols  
✅ **Contextual help text** for each field  
✅ **Dynamic field labels** based on penalty type  
✅ **Validation:**
   - No negative values allowed
   - Percentage type capped at 100%
   - Maximum penalty enforced
✅ **Pre-filled forms** with current settings  

### 6. Database Changes

**Migration:** `2025_11_10_142026_update_penalty_settings_table_for_philippine_rules.php`

- Renamed columns for clarity:
  - `type` → `penalty_type`
  - `value` → `penalty_rate`
  - `is_active` → `active`
  - `max_penalty_amount` → `max_penalty`
- Removed unused `max_penalty_days` column
- Updated existing data to new format

**Seeder:** `DefaultPenaltySettingsSeeder.php`
- Sets recommended Philippine dormitory defaults
- Run with: `php artisan db:seed --class=DefaultPenaltySettingsSeeder`

## 📋 Usage

### For Administrators

1. **Access Penalty Settings:**
   - Navigate to "Financial Management" → "Penalties"
   - Click "Edit Penalty Settings" button

2. **Configure Penalty Rules:**
   - Choose penalty type (recommended: Daily Fixed)
   - Set penalty rate (recommended: ₱50 for daily fixed)
   - Set grace period (recommended: 3 days)
   - Set maximum penalty cap (recommended: ₱500)
   - Toggle active status

3. **Apply Penalties:**
   - Penalties are calculated automatically
   - Use "Calculate Penalty" action on overdue bills
   - Bills within grace period show ₱0 penalty

4. **Waive Penalties:**
   - Use "Waive Penalty" action for special cases
   - Provide reason for audit trail

### For Developers

```php
// Get active penalty setting
$setting = PenaltySetting::getActiveSetting('late_payment_penalty');

// Calculate penalty
$billAmount = 5000;
$overdueDays = 10;
$penalty = $setting->calculatePenalty($billAmount, $overdueDays);

// Apply penalty to bill
$bill->calculatePenalty();
```

## 🎯 Recommended Settings for Philippines

**Small Dormitories (5-20 beds):**
- Type: Daily Fixed
- Rate: ₱30-50 per day
- Grace: 3-5 days
- Max: ₱300-500

**Medium Dormitories (20-50 beds):**
- Type: Daily Fixed or Percentage
- Rate: ₱50-100 per day OR 3-5%
- Grace: 3 days
- Max: ₱500-1000

**Large Dormitories (50+ beds):**
- Type: Percentage or Daily Fixed
- Rate: 5% OR ₱100 per day
- Grace: 3 days
- Max: ₱1000-2000

## 🔍 Testing

Run the test script to verify calculations:
```bash
php test-penalty-calculator.php
```

## ⚠️ Important Notes

1. **Grace Period is Enforced:** No penalties during grace period
2. **Maximum Cap is Applied:** Penalties never exceed max_penalty
3. **Percentage is One-Time:** Not multiplied by days (realistic)
4. **Audit Trail:** All penalty waivers are logged
5. **Active Status:** Toggle to temporarily disable penalties

## 📞 Support

For questions or issues with the penalty system, contact the system administrator.
