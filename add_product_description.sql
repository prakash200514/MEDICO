-- Add product description column
ALTER TABLE products ADD COLUMN description TEXT DEFAULT NULL;

-- Add second image column
ALTER TABLE products ADD COLUMN image2 VARCHAR(100) DEFAULT NULL;

-- Update existing products to have sample descriptions
UPDATE products SET description = 'High-quality medicine for effective treatment.' WHERE description IS NULL;
