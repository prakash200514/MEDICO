-- Add missing columns to orders table
ALTER TABLE orders ADD COLUMN address VARCHAR(255) DEFAULT NULL;
ALTER TABLE orders ADD COLUMN phone VARCHAR(20) DEFAULT NULL;
ALTER TABLE orders ADD COLUMN delivery_date DATE DEFAULT NULL;
