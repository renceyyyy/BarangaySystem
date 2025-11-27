-- Add damage_resolution column to tblitemrequest table
-- This column stores whether the resident will Replace or Pay for damaged items

ALTER TABLE tblitemrequest 
ADD COLUMN IF NOT EXISTS damage_resolution VARCHAR(20) NULL 
COMMENT 'Resolution for damaged items: Replace or Pay';

-- Update existing records with NULL to 'N/A' for clarity (optional)
-- UPDATE tblitemrequest SET damage_resolution = 'N/A' WHERE damage_resolution IS NULL;
