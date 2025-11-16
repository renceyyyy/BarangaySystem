-- Complete Database Schema Updates for Scholarship System Enhancement
-- Run this SQL in your MySQL database (barangaydb) via phpMyAdmin or MySQL Workbench

-- 1. Add EducationLevel column if it doesn't exist
ALTER TABLE scholarship
ADD COLUMN IF NOT EXISTS EducationLevel VARCHAR(50) NULL AFTER Reason;

-- 2. Add PassedNotified column to track if user has been notified of passing
ALTER TABLE scholarship
ADD COLUMN IF NOT EXISTS PassedNotified TINYINT(1) DEFAULT 0 AFTER ScholarshipGrant;

-- 3. Update existing records to have default value for PassedNotified
UPDATE scholarship
SET PassedNotified = 0
WHERE PassedNotified IS NULL;

-- 4. Add index for faster notification queries
CREATE INDEX IF NOT EXISTS idx_passed_notified ON scholarship(UserID, RequestStatus, PassedNotified);

-- 5. Add index for education level queries
CREATE INDEX IF NOT EXISTS idx_education_level ON scholarship(EducationLevel);

-- Verify the changes
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'barangaydb'
AND TABLE_NAME = 'scholarship'
AND COLUMN_NAME IN ('EducationLevel', 'PassedNotified');
