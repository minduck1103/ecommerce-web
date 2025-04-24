-- Add slug column to products table if it doesn't exist
ALTER TABLE products ADD COLUMN IF NOT EXISTS slug VARCHAR(255) UNIQUE AFTER name;
 
-- Update existing products with slugs
UPDATE products SET slug = LOWER(REGEXP_REPLACE(name, '[^A-Za-z0-9]+', '-')); 