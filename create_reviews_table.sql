-- Create reviews table for product feedback and ratings
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    order_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_user_email (user_email),
    INDEX idx_rating (rating)
);

-- Add description column to products table if it doesn't exist
ALTER TABLE products ADD COLUMN IF NOT EXISTS description TEXT;

-- Add average rating column to products table
ALTER TABLE products ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT 0.00;

-- Add total reviews count to products table
ALTER TABLE products ADD COLUMN IF NOT EXISTS total_reviews INT DEFAULT 0;

