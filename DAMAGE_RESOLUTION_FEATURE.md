# Item Request Damage Resolution Feature

## Overview
Added functionality to handle damaged items when residents return borrowed items. When an item is returned as "Damaged", the system now requires the resident to choose how they will resolve the issue.

## Changes Made

### 1. Database Schema Update
- **File**: `add_damage_resolution_column.sql`
- **Change**: Added `damage_resolution` column to `tblitemrequest` table
- **Type**: VARCHAR(20)
- **Values**: 'Replace' or 'Pay'

**To apply the database change, run:**
```sql
ALTER TABLE tblitemrequest 
ADD COLUMN IF NOT EXISTS damage_resolution VARCHAR(20) NULL 
COMMENT 'Resolution for damaged items: Replace or Pay';
```

### 2. Return Modal Enhancement
- **Location**: Return Item Modal in `Adminpage.php`
- **New Features**:
  - Damage Status dropdown (Good/Damaged) - existing
  - **NEW**: Damage Resolution dropdown (appears only when "Damaged" is selected)
    - Option 1: Replace the Item
    - Option 2: Pay for the Damage
  - Dynamic validation: Resolution is required when item is damaged
  - JavaScript toggle function to show/hide resolution options

### 3. Business Logic Updates

#### Processing Logic (`processReturn` section):
```php
if ($damageStatus === 'Damaged') {
    if ($damageResolution === 'Pay') {
        // Deduct from inventory (item is lost)
        UPDATE inventory SET total_stock = total_stock - quantity
    }
    if ($damageResolution === 'Replace') {
        // Don't deduct from inventory (resident will replace)
    }
    // Save resolution to database
    UPDATE tblitemrequest SET damage_resolution = 'Replace' or 'Pay'
}
```

#### Inventory Impact:
- **Good Condition**: No deduction from inventory
- **Damaged + Pay**: Deduct quantity from total_stock (item is lost)
- **Damaged + Replace**: No deduction from total_stock (resident will replace item)

### 4. UI/UX Updates

#### Table Display:
- Added new column: **RESOLUTION**
- Shows:
  - "Replace" or "Pay" for damaged items
  - "N/A" for items in good condition or not yet returned

#### View Details Modal:
- Enhanced damage status section
- Shows resolution information with icons:
  - 🔄 Replace the Item (blue color)
  - 💰 Pay for Damage (gold color)

### 5. Alert Messages
Updated success messages to be more informative:
- "Item returned as Damaged. Resident will replace the item."
- "Item returned as Damaged. Resident will pay for the damage."
- "Item returned successfully in good condition."

## User Workflow

### When Returning a Damaged Item:
1. Admin clicks "Return" button for item "On Loan"
2. Return modal opens
3. Admin selects "Damaged" from Damage Status dropdown
4. **NEW**: Damage Resolution section appears automatically
5. Admin must select either:
   - "Replace the Item" - Resident will bring replacement
   - "Pay for the Damage" - Resident will pay for item value
6. Admin submits return
7. System updates inventory accordingly:
   - Replace: Item count stays same (awaiting replacement)
   - Pay: Item count decreases (item lost, payment expected)
8. Alert confirms action taken
9. Table and details show resolution status

## Benefits

1. **Clear Accountability**: Documents how damaged items will be resolved
2. **Inventory Accuracy**: Proper inventory adjustments based on resolution
3. **Tracking**: Full audit trail of damaged items and resolutions
4. **Flexibility**: Accommodates different resolution methods
5. **Transparency**: All stakeholders see how damage is being handled

## Testing Checklist

- [ ] Run SQL migration to add `damage_resolution` column
- [ ] Test returning item in good condition (should work as before)
- [ ] Test returning damaged item without selecting resolution (should show error)
- [ ] Test returning damaged item with "Replace" option (inventory unchanged)
- [ ] Test returning damaged item with "Pay" option (inventory decreases)
- [ ] Verify table shows resolution status correctly
- [ ] Verify details modal shows resolution information
- [ ] Check reports include resolution data

## Future Enhancements (Optional)

1. Add payment tracking for "Pay" resolution
2. Add replacement tracking for "Replace" resolution
3. Send notifications to residents about their resolution commitment
4. Add deadline dates for replacements/payments
5. Generate financial reports for damaged items
6. Add image upload for damage documentation
