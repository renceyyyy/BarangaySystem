# College Verification Document Upload Feature

## Overview
Added document upload capability to the "Examination Passed" modal for College applicants to verify if they are College A (₱3,000) or College B (₱1,500).

## What Changed

### 1. Frontend (SKpage.php)
✅ **Modal Enhancement**
- Added file upload input field to College Passed modal
- File type validation: PDF, JPG, PNG, DOC, DOCX
- File size validation: Maximum 5MB
- Real-time file name preview with size display
- Required field - users must upload before confirming

✅ **JavaScript Updates**
- File input change handler with validation
- Converted from GET redirect to AJAX POST with FormData
- Loading state on submit button ("Uploading...")
- Proper error handling with custom alerts
- Auto-reload on success

### 2. Backend (process_passed_examination.php)
✅ **Dual Request Handling**
- Supports legacy GET requests (JHS/SHS without file upload)
- Supports new POST requests (College with file upload via AJAX)
- Returns JSON for AJAX, session messages for redirects

✅ **File Upload Processing**
- Validates file type (MIME type checking)
- Validates file size (5MB max)
- Generates unique filename: `college_verify_{ApplicationID}_{timestamp}.{ext}`
- Stores in: `uploads/college_verification/`
- Cleans up file on transaction failure

✅ **Database Integration**
- Checks if VerificationDocument column exists
- Dynamically builds SQL based on available columns
- Stores filename in database for record keeping
- Logs activity with document reference

### 3. Database Migration
✅ **New Column Added**
- File: `add_verification_document_column.php`
- Column: `VerificationDocument VARCHAR(255) NULL`
- Location: After `ScholarshipGrant` column
- Purpose: Store verification document filename

### 4. File Storage
✅ **Directory Created**
- Path: `uploads/college_verification/`
- Permissions: 755 (auto-created if missing)
- Naming: Unique per application with timestamp

## How to Deploy

### Step 1: Run Database Migration
1. Open browser: `localhost/BarangaySystem/BarangaySystem/Pages/add_verification_document_column.php`
2. Verify success message
3. Optional: Delete migration file after successful run

### Step 2: Test the Feature
1. Go to SK Scholarship Management page
2. Find a College applicant with "For Examination" status
3. Click "Passed" button
4. Modal should show:
   - College A / College B radio options
   - File upload field (REQUIRED)
   - File format guidance
5. Select College level
6. Upload a verification document
7. Click "Confirm & Approve"
8. Should see "Uploading..." then success message

### Step 3: Verify Upload
1. Check `uploads/college_verification/` folder for uploaded file
2. Check database `scholarship` table for `VerificationDocument` value
3. Check activity logs for approval record

## User Flow

### College Examination Passed:
1. Admin clicks "Passed" on College applicant
2. Modal appears with:
   - Education Level: College
   - Radio buttons: College A (₱3,000) / College B (₱1,500)
   - **File upload field** (NEW!)
   - Confirm & Approve button

3. Admin must:
   - Select College A or B
   - Upload verification document (PDF/Image/Doc)
   
4. On submit:
   - Validates selection and file
   - Shows "Uploading..." on button
   - Uploads via AJAX
   - Shows success/error alert
   - Reloads page on success

### JHS/SHS Examination Passed:
- No file upload required (unchanged behavior)
- Uses legacy GET redirect method
- Shows fixed ₱1,200 grant

## Validation Rules

### File Upload:
- **Required**: Yes (for College only)
- **File Types**: PDF, JPG, JPEG, PNG, DOC, DOCX
- **Max Size**: 5MB
- **Validation**: Client-side + Server-side

### College Level Selection:
- **Required**: Yes
- **Options**: College A (₱3,000) or College B (₱1,500)
- **Validation**: Must select before submitting

## Error Handling

### Client-Side:
- Missing College level selection → Warning alert
- Missing file upload → Warning alert
- File too large (>5MB) → Error alert, clears input
- Network error → Error alert

### Server-Side:
- Invalid file type → JSON error response
- File size exceeded → JSON error response
- Database error → Rollback + delete uploaded file + JSON error
- Missing parameters → JSON error response

## Security Features
1. **Authentication**: Login required
2. **File Type Validation**: MIME type checking
3. **File Size Limit**: 5MB maximum
4. **Unique Filenames**: Prevents overwrites
5. **Transaction Safety**: Rollback on error
6. **File Cleanup**: Deletes on failure

## Backward Compatibility
✅ JHS/SHS functionality unchanged (GET method still works)
✅ Existing approval workflows unaffected
✅ Database migration checks for existing column
✅ Code handles missing VerificationDocument column gracefully

## Files Modified
1. `Pages/SKpage.php` - Modal UI + JavaScript handler
2. `Pages/process_passed_examination.php` - Backend processing
3. `Pages/add_verification_document_column.php` - Migration script (NEW)
4. `uploads/college_verification/` - Upload directory (NEW)

## Testing Checklist
- [ ] Database migration runs successfully
- [ ] College modal shows file upload field
- [ ] File validation works (type and size)
- [ ] Upload shows loading state
- [ ] Success shows alert and reloads
- [ ] File appears in uploads folder
- [ ] Database stores filename correctly
- [ ] Activity log records upload
- [ ] JHS/SHS still works without file upload
- [ ] Error handling works properly

## Notes
- File upload is **REQUIRED** for College A/B approval
- Uploaded files help verify scholarship classification
- Filenames include ApplicationID for easy tracking
- Files persist even if application is later modified
- Consider adding file viewer in approval history (future enhancement)
