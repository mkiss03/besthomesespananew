-- Add "New Build Villas" property type
INSERT INTO property_types (name, name_hu)
SELECT 'New Build Villa', 'Új építésű villa'
WHERE NOT EXISTS (
    SELECT 1 FROM property_types WHERE name = 'New Build Villa'
);
