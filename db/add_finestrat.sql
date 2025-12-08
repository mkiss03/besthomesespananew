-- Add Finestrat location
INSERT INTO locations (city, region, province)
SELECT 'Finestrat', 'Costa Blanca', 'Alicante'
WHERE NOT EXISTS (
    SELECT 1 FROM locations WHERE city = 'Finestrat'
);
