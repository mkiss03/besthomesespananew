-- Add "New Build" property type to property_types table
-- Run this SQL if the type doesn't already exist

INSERT INTO property_types (name, name_hu)
SELECT 'New Build', 'Új építésű'
WHERE NOT EXISTS (
    SELECT 1 FROM property_types WHERE name = 'New Build'
);

-- Add missing locations for TM Grupo properties

INSERT INTO locations (city, region, province)
SELECT 'El Puig', 'Valencia Region', 'Valencia'
WHERE NOT EXISTS (
    SELECT 1 FROM locations WHERE city = 'El Puig' AND province = 'Valencia'
);

INSERT INTO locations (city, region, province)
SELECT 'Mar de Pulpí', 'Costa de Almería', 'Almería'
WHERE NOT EXISTS (
    SELECT 1 FROM locations WHERE city = 'Mar de Pulpí' AND province = 'Almería'
);
