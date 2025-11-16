-- Add EducationLevel column to scholarship table
-- Run this SQL in your database to add the new field

ALTER TABLE scholarship
ADD COLUMN EducationLevel VARCHAR(50) NULL AFTER Reason;

-- Update existing records to have a default value (optional)
-- UPDATE scholarship SET EducationLevel = 'Not Specified' WHERE EducationLevel IS NULL;
