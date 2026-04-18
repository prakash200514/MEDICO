-- Add subcategory column to products table if it doesn't exist
-- MySQL compatible version
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'subcategory') = 0,
    'ALTER TABLE products ADD COLUMN subcategory VARCHAR(100) DEFAULT NULL',
    'SELECT "Column subcategory already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing Baby Products to have appropriate subcategories
-- This is optional and can be run manually if needed
-- UPDATE products SET subcategory = 'Baby Care' WHERE category = 'Baby Products' AND subcategory IS NULL; 