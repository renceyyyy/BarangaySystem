# Blotter Validation System - Implementation Guide

## Overview
The Blotter Validation System prevents users with active blotter records from accessing Barangay System services. Users must contact the barangay office to validate their accounts before accessing any services.

## Features Implemented

### 1. **Blotter Record Detection**
- Automatically checks if a user has an active blotter record when they log in
- Searches the `blottertbl` and `blotter_participantstbl` tables for matching records
- Matches users by first name and last name

### 2. **Service Access Restriction**
Users with active blotter records **cannot access**:
- Request Government Documents
- Request Business Permit / Closure
- Complain
- Apply for Scholar
- No fix income/No income
- Guardianship
- Cohabitation

### 3. **User Notifications**
- **Red Notice Banner**: Displayed at the top of the navbar showing the account is restricted
- **Modal Dialog**: Appears when users try to access services, explaining the restriction
- **Service Link Notifications**: Disabled service links show a warning message when clicked

### 4. **Non-Invasive Design**
- Blotter validation does **NOT** change any existing functionality
- All existing features work as before
- Blotter check is an additional layer added on top

## Files Created

### `Process/blotter_validation.php`
Contains the core validation functions:

```php
checkUserBlotterRecord($conn, $user_id)
```
- **Purpose**: Checks if a user has an active blotter record
- **Returns**: Array with `has_blotter` boolean and `blotter_record` details
- **Database Tables Used**:
  - `userloginfo` - Get user information
  - `blottertbl` - Check for active blotter records
  - `blotter_participantstbl` - Check participant records

```php
refreshBlotterStatus($conn, $user_id)
```
- **Purpose**: Updates session with current blotter status
- **Stores**: Sets `$_SESSION['has_blotter_record']` and `$_SESSION['blotter_record']`

```php
getBlotterBlockMessage()
```
- **Purpose**: Provides formatted message for display
- **Returns**: HTML-formatted restriction message

## Files Modified

### `Pages/Navbar/navbar.php`

**Changes Made**:

1. **Include Validation Module** (Line 7)
   ```php
   require_once '../Process/blotter_validation.php';
   ```

2. **Refresh Blotter Status on Each Page Load** (After line 60)
   ```php
   refreshBlotterStatus($conn, $userId);
   ```

3. **Added Blotter Notice Styles** (Lines 507-530)
   - Red gradient background (#ffcdd2 to #ef9a9a)
   - Clear warning icon and messaging

4. **Display Blotter Notice Banner** (Lines 687-692)
   - Shows only if user has active blotter record
   - Red warning style to catch user attention
   - Links to blotter information modal

5. **Service Access Control Logic** (Lines 758-810)
   - Added condition to check for `$_SESSION['has_blotter_record']`
   - Blotter check happens BEFORE verification status check
   - All service links show warning message when clicked

6. **Blotter Modal Dialog** (Lines 854-945)
   - Styled modal with error/warning appearance
   - Shows when user clicks on service link or notification
   - Explains restriction and guidance on resolution

7. **JavaScript Functions** (Lines 1431-1455)
   ```javascript
   showBlotterModal()     // Display the modal
   closeBlotterModal()    // Close the modal
   ```

## Database Requirements

The system uses existing database tables:

### `userloginfo` Table
- Fields used: `UserID`, `FirstName`, `LastName`

### `blottertbl` Table
- Fields used: `blotter_id`, `incident_type`, `blotter_details`, `status`, `created_at`
- Condition: Only checks for records with `status = 'Active'`

### `blotter_participantstbl` Table
- Fields used: `blotter_participant_id`, `blotter_id`, `participant_type`, `firstname`, `lastname`
- Condition: Matches against first and last names

## User Experience Flow

### For Users WITH Blotter Records:

1. **Login** → Blotter check runs automatically
2. **Dashboard** → Red warning banner appears at top
3. **Try to Access Service** → Warning notification appears
4. **Click "Contact Barangay"** → Modal explains restriction and guidance
5. **Visit Barangay Office** → Staff validates account and clears record
6. **System** → Blotter status automatically updates on next page load

### For Users WITHOUT Blotter Records:

- No change to existing flow
- All services accessible as normal (if verified)

## Configuration & Customization

### To Modify Blotter Notice Appearance:
Edit `.blotter-notice` CSS class in `navbar.php` (lines 507-530)

### To Change Modal Message:
Edit the `.blotter-modal-body` content in `navbar.php` (lines 934-942)

### To Modify Validation Logic:
Edit functions in `Process/blotter_validation.php`:
- Line 59: Change name matching logic
- Line 49: Change status condition from 'Active' to other statuses

## Error Handling

The validation system includes:
- Try-catch blocks for database errors
- Fallback to `has_blotter = false` on any errors
- Error logging for debugging
- Graceful degradation (if validation fails, user can still access if verified)

## Security Considerations

1. **SQL Injection Prevention**
   - All queries use prepared statements
   - Parameters bound with proper types

2. **Session Management**
   - Blotter status stored in session
   - Refreshed on each navbar load
   - Checked against user_id from session

3. **Authorization**
   - Validation only for logged-in users
   - Session-based user verification maintained

## Testing Checklist

- [ ] Blotter banner displays for users with records
- [ ] Modal opens when clicking service links
- [ ] Service links are disabled/show warning
- [ ] Warning notification appears on navbar
- [ ] Users without records can access services normally
- [ ] Blotter status updates after admin clears record
- [ ] All existing functionality works unchanged
- [ ] Mobile responsive design works
- [ ] Modal closes on ESC key and background click

## Admin Actions Required

To clear a blotter record:

1. Admin navigates to blotter management
2. Changes `status` from 'Active' to 'Cleared' or similar
3. User's session refreshes on next page load
4. Restriction is automatically removed

## Notes

- No database schema changes required
- No existing functionality altered
- Only adds restriction layer on top
- Can be easily disabled by commenting out the include statement in navbar.php

