-- Add invoice_number column to orders table
ALTER TABLE orders ADD COLUMN invoice_number VARCHAR(20) UNIQUE DEFAULT NULL;

-- Create index for faster lookups
CREATE INDEX idx_invoice_number ON orders(invoice_number);