-- Add prescription_path column to orders table
ALTER TABLE orders ADD COLUMN prescription_path VARCHAR(255) DEFAULT NULL;

-- Update existing orders to have NULL prescription_path
UPDATE orders SET prescription_path = NULL WHERE prescription_path IS NULL;
