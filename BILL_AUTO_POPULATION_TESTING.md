# Bill Auto-Population Testing Checklist

## ✅ Pre-Testing Setup

- [ ] Server is running (`php artisan serve`)
- [ ] Database is accessible
- [ ] Cache has been cleared (`php artisan optimize:clear`)
- [ ] Cache has been rebuilt (`php artisan optimize`)
- [ ] Test script executed successfully
- [ ] At least one tenant with active room assignment exists
- [ ] At least one unbilled utility reading exists

## 🧪 Test Scenarios

### Test 1: Basic Auto-Population (Happy Path)
**Prerequisites:** Tenant with active room assignment and utility readings

- [ ] Navigate to Billing → Create
- [ ] Click on Tenant dropdown
- [ ] Select a tenant
- [ ] **Verify:** Room field auto-fills
- [ ] **Verify:** Room Rate shows correct amount
- [ ] **Verify:** Electricity shows utility charge
- [ ] **Verify:** Water shows utility charge
- [ ] **Verify:** Total Amount calculates correctly
- [ ] **Verify:** All fields are editable
- [ ] Click Create
- [ ] **Verify:** Bill is created successfully

**Expected Total Formula:**
```
Total = Room Rate + Electricity + Water + Other Charges (0)
```

---

### Test 2: Tenant Without Utility Readings
**Prerequisites:** Tenant with active room but no utility readings

- [ ] Navigate to Billing → Create
- [ ] Select tenant without utility readings
- [ ] **Verify:** Room field auto-fills
- [ ] **Verify:** Room Rate shows correct amount
- [ ] **Verify:** Electricity shows ₱0.00
- [ ] **Verify:** Water shows ₱0.00
- [ ] **Verify:** Total Amount = Room Rate only
- [ ] Manually enter electricity amount (e.g., ₱500)
- [ ] **Verify:** Total updates automatically
- [ ] Manually enter water amount (e.g., ₱200)
- [ ] **Verify:** Total updates again
- [ ] Click Create
- [ ] **Verify:** Bill created with manual utility amounts

---

### Test 3: Manual Room Change
**Prerequisites:** Multiple rooms available

- [ ] Navigate to Billing → Create
- [ ] Select a tenant (Room A auto-fills)
- [ ] Note the current room rate and utility charges
- [ ] Manually change the Room dropdown to different room (Room B)
- [ ] **Verify:** Room Rate updates to Room B's price
- [ ] **Verify:** Electricity updates to Room B's reading (or ₱0)
- [ ] **Verify:** Water updates to Room B's reading (or ₱0)
- [ ] **Verify:** Total recalculates automatically
- [ ] Click Create
- [ ] **Verify:** Bill created with Room B's data

---

### Test 4: Manual Charge Editing
**Prerequisites:** Tenant with auto-populated data

- [ ] Navigate to Billing → Create
- [ ] Select a tenant (all fields auto-fill)
- [ ] Edit Room Rate (e.g., divide by 2 for prorated)
- [ ] **Verify:** Total updates immediately
- [ ] Edit Electricity amount
- [ ] **Verify:** Total updates immediately
- [ ] Edit Water amount
- [ ] **Verify:** Total updates immediately
- [ ] Add Other Charges (e.g., ₱500 maintenance)
- [ ] **Verify:** Total updates immediately
- [ ] **Verify:** Total = sum of all charges
- [ ] Click Create
- [ ] **Verify:** Bill created with custom amounts

---

### Test 5: Other Charges Addition
**Prerequisites:** Tenant with auto-populated data

- [ ] Navigate to Billing → Create
- [ ] Select a tenant
- [ ] Note the auto-calculated total
- [ ] Enter ₱500 in Other Charges
- [ ] **Verify:** Total increases by ₱500
- [ ] Change Other Charges to ₱1000
- [ ] **Verify:** Total increases by additional ₱500
- [ ] Add description: "Maintenance fee"
- [ ] Click Create
- [ ] **Verify:** Bill includes other charges in total

---

### Test 6: Tenant Without Active Room
**Prerequisites:** Tenant with no active room assignment

- [ ] Navigate to Billing → Create
- [ ] Select tenant without active room
- [ ] **Verify:** Room field remains empty
- [ ] **Verify:** Room Rate shows ₱0.00
- [ ] **Verify:** Electricity shows ₱0.00
- [ ] **Verify:** Water shows ₱0.00
- [ ] **Verify:** Total shows ₱0.00
- [ ] Manually select a room
- [ ] **Verify:** Room Rate updates
- [ ] **Verify:** Utility charges update (if readings exist)
- [ ] **Verify:** Can still create bill manually

---

### Test 7: Real-Time Calculation Accuracy
**Prerequisites:** Tenant with known values

**Setup:**
- Tenant: [Your test tenant]
- Expected Room Rate: [e.g., ₱5,000]
- Expected Electricity: [e.g., ₱1,200]
- Expected Water: [e.g., ₱350]

- [ ] Navigate to Billing → Create
- [ ] Select the tenant
- [ ] **Verify:** Room Rate = expected amount
- [ ] **Verify:** Electricity = expected amount
- [ ] **Verify:** Water = expected amount
- [ ] **Verify:** Total = ₱6,550 (sum of above)
- [ ] Change Room Rate to ₱2,500
- [ ] **Verify:** Total updates to ₱4,050
- [ ] Change Electricity to ₱1,000
- [ ] **Verify:** Total updates to ₱3,850
- [ ] Change Water to ₱500
- [ ] **Verify:** Total updates to ₱4,000
- [ ] Add Other Charges ₱1,000
- [ ] **Verify:** Total updates to ₱5,000

---

### Test 8: Helper Text Visibility
**Prerequisites:** Any tenant

- [ ] Navigate to Billing → Create
- [ ] **Verify:** Room Rate shows helper text about auto-population
- [ ] **Verify:** Electricity shows helper text about utility readings
- [ ] **Verify:** Water shows helper text about utility readings
- [ ] **Verify:** Total Amount shows helper text about calculation
- [ ] **Verify:** Description shows placeholder text
- [ ] **Verify:** All helper texts are helpful and accurate

---

### Test 9: Validation Still Works
**Prerequisites:** Any tenant

- [ ] Navigate to Billing → Create
- [ ] Select a tenant
- [ ] Clear the Room Rate field (make it empty)
- [ ] Try to create bill
- [ ] **Verify:** Validation error appears
- [ ] Clear the Electricity field
- [ ] Try to create bill
- [ ] **Verify:** Validation error appears
- [ ] **Verify:** All required field validations still work
- [ ] Fill in all required fields
- [ ] Click Create
- [ ] **Verify:** Bill created successfully

---

### Test 10: Multiple Tenants Comparison
**Prerequisites:** At least 2 tenants with different rooms

- [ ] Run test script: `php test-bill-auto-population.php`
- [ ] **Verify:** Script shows data for multiple tenants
- [ ] Note the expected values from script output
- [ ] Navigate to Billing → Create
- [ ] Select first tenant
- [ ] **Verify:** Values match script output for Tenant 1
- [ ] Click Cancel
- [ ] Click Create again
- [ ] Select second tenant
- [ ] **Verify:** Values match script output for Tenant 2
- [ ] **Verify:** Different rooms show different rates

---

## 📊 Data Verification

### Check Database Consistency

- [ ] Open database client (e.g., phpMyAdmin)
- [ ] Verify `room_assignments` table has active assignments
- [ ] Verify `rooms` table has prices set
- [ ] Verify `utility_readings` table has unbilled readings (bill_id = NULL)
- [ ] Create a test bill through the form
- [ ] Verify the bill saved correctly in `bills` table
- [ ] Verify all charge columns are populated
- [ ] Verify total_amount matches sum of charges

---

## 🐛 Error Testing

### Test Error Handling

- [ ] Test with tenant ID that doesn't exist (shouldn't happen in UI)
- [ ] Test with room that has no price set
- [ ] **Verify:** System handles gracefully (shows ₱0 or default)
- [ ] Test with corrupted utility reading data
- [ ] **Verify:** System doesn't crash
- [ ] Test with very large numbers
- [ ] **Verify:** Calculations still accurate
- [ ] Test with decimal places
- [ ] **Verify:** Formatting is correct (2 decimal places)

---

## 🔄 Integration Testing

### Test with Related Features

- [ ] Create utility reading for a room
- [ ] Create bill for tenant in that room
- [ ] **Verify:** Utility reading shows in bill
- [ ] Mark bill as paid
- [ ] Create another utility reading
- [ ] Create new bill
- [ ] **Verify:** Uses new unbilled reading (not previous billed one)
- [ ] Archive (soft delete) a utility reading
- [ ] Create bill
- [ ] **Verify:** Archived reading is not used

---

## 📱 UI/UX Testing

### User Experience

- [ ] **Verify:** Dropdowns load quickly
- [ ] **Verify:** Auto-population happens instantly (< 1 second)
- [ ] **Verify:** No page flicker or reload
- [ ] **Verify:** Fields update smoothly
- [ ] **Verify:** Total calculation is instant
- [ ] **Verify:** Form is intuitive to use
- [ ] **Verify:** Helper texts are visible and helpful
- [ ] **Verify:** No console errors in browser dev tools

---

## 📚 Documentation Review

- [ ] Read BILL_AUTO_POPULATION.md
- [ ] Read BILL_AUTO_POPULATION_QUICKREF.md
- [ ] Read BILL_AUTO_POPULATION_DIAGRAM.md
- [ ] **Verify:** Documentation matches actual behavior
- [ ] **Verify:** All examples in docs work as described
- [ ] **Verify:** Troubleshooting section is accurate

---

## ✅ Final Verification

### Complete System Check

- [ ] All tests above passed
- [ ] No errors in console or logs
- [ ] Performance is acceptable (< 2 seconds per bill)
- [ ] Data is accurate and consistent
- [ ] Manual editing still works
- [ ] Validation still works
- [ ] Previous billing functionality not broken
- [ ] Test script runs without errors
- [ ] Documentation is complete and accurate

---

## 📝 Test Results Summary

**Date Tested:** _______________  
**Tested By:** _______________  
**Browser:** _______________  
**Total Tests:** 10 scenarios + additional checks  
**Tests Passed:** _____ / _____  
**Tests Failed:** _____ / _____  

### Issues Found (if any):
```
1. 
2. 
3. 
```

### Notes:
```




```

---

## 🎯 Sign-Off

- [ ] All critical tests passed
- [ ] All documentation reviewed
- [ ] Feature is ready for production use
- [ ] Users have been trained
- [ ] Support team is aware of new feature

**Approved By:** _______________  
**Date:** _______________

---

**Status:** 
- [ ] ✅ READY FOR PRODUCTION
- [ ] ⚠️ NEEDS FIXES
- [ ] ❌ NOT READY

