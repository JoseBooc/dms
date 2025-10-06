# Icon Issues and Table Column Compatibility Fixes

## Problems Fixed

### 1. Icon Compatibility Issues
Filament v2 uses different icon naming conventions than v3. Several icons were causing errors:

- `heroicon-o-wrench` → doesn't exist in v2
- `heroicon-o-home` → not available in v2  
- `heroicon-o-user-group` → not available in v2
- `heroicon-o-currency-dollar` → not available in v2
- `heroicon-o-clipboard-list` → not available in v2
- Various solid icons (`heroicon-s-*`) with incorrect names

### 2. Table Column Method Compatibility
Filament v2 uses different methods for table columns:

- `->placeholder('text')` → doesn't exist in v2, use `->formatStateUsing()`
- `->default('text')` → doesn't exist for table columns in v2, use `->formatStateUsing()`
- `->toggleable(isToggledHiddenByDefault: true)` → v3 syntax, use `->toggleable()` in v2
- `->hiddenByDefault()` → doesn't exist in v2, use `->toggleable()` only
- `->numeric()` → doesn't exist for table columns in v2, use `->formatStateUsing(fn ($state) => number_format($state, 2))` instead

## Fixed Icons

### Navigation Icons (Resources)
- **MaintenanceRequestResource**: `heroicon-o-wrench` → `heroicon-o-cog`
- **RoomResource**: `heroicon-o-home` → `heroicon-o-office-building`  
- **UserResource**: `heroicon-o-user-group` → `heroicon-o-users`
- **BillResource**: `heroicon-o-currency-dollar` → `heroicon-o-cash`
- **RoomAssignmentResource**: `heroicon-o-clipboard-list` → `heroicon-o-clipboard`
- **UtilityTypeResource**: `heroicon-o-collection` → `heroicon-o-lightning-bolt`
- **UtilityReadingResource**: `heroicon-o-collection` → `heroicon-o-calculator`

### Widget Icons
- **RoomOccupancyWidget**: Fixed multiple icon references:
  - `heroicon-s-home` → `heroicon-s-office-building`
  - `heroicon-s-user-group` → `heroicon-s-users`  
  - `heroicon-s-currency-dollar` → `heroicon-s-cash`

## Available Icons in Filament v2
Common safe icons to use:
- `heroicon-o-users`
- `heroicon-o-office-building`
- `heroicon-o-cash`
- `heroicon-o-clipboard`
- `heroicon-o-cog`
- `heroicon-o-calculator`
- `heroicon-o-lightning-bolt`
- `heroicon-o-chart-bar`
- `heroicon-o-check-circle`

### 3. Table Column Method Fixes
- **RoomAssignmentResource**: `->placeholder('Ongoing')` → `->formatStateUsing(fn ($state) => $state ? $state->format('M j, Y') : 'Ongoing')`
- **MaintenanceRequestResource**: `->default('Not assigned')` → `->formatStateUsing(fn ($state) => $state ?? 'Not assigned')`
- **All Resources**: `->toggleable(isToggledHiddenByDefault: true)` → `->toggleable()` (removed hiddenByDefault)
- **All Resources**: Removed `->hiddenByDefault()` method calls (not available in v2)
- **UtilityReadingResource**: `->numeric()` for table columns → `->formatStateUsing(fn ($state) => number_format($state, 2))`

All icon and table column compatibility issues have been resolved and the application should now work without errors.