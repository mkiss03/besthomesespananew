# TM Grupo New Build Properties Importer

## Overview

This is a **ONE-TIME** importer that pulls new-build developments from TM Grupo Inmobiliario with their explicit written permission for the following URLs:

1. **Benidorm - TM Tower**: https://www.tmgrupoinmobiliario.com/en/properties/U68-04_beach-apartments-sea-views-spain-benidorm-costa-blanca-north-tm-tower-by-tm
2. **Benidorm - Sunset Sailors**: https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U68-03_apartamentos-benidorm-primera-linea-playa-poniente-sunset-sailors-by-tm
3. **Calpe - Azure Icons**: https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U92-01_apartamentos-alicante-calpe-playa-de-la-fossa-azure-icons-by-tm
4. **Valencia/El Puig - Santa Maria Sea**: https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U94-01_vivienda-unifamiliar-adosado-apartamento-atico-obra-nueva-playa-el-puig-valencia-santa-maria-sea
5. **Almería/Mar de Pulpí**: https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U17-07_apartamentos-playa-almeria-mar-de-pulpi-7

## Prerequisites

1. PHP 7.4+ with cURL and DOM extensions
2. MySQL/MariaDB database access
3. Write permissions to `assets/images/properties/` directory

## How to Run

### Step 1: Database Preparation (Optional)

The script will automatically create the "New Build" property type if it doesn't exist. However, you can manually run the SQL file if preferred:

```bash
mysql -u [username] -p [database_name] < db/add_new_build_type.sql
```

### Step 2: Run the Importer

From the project root directory:

```bash
php tools/import_tm_new_builds.php
```

### Step 3: Monitor Output

The script will output progress for each property:
- URL being processed
- Property title and ID
- Number of images downloaded
- Any warnings or errors

## What the Script Does

1. **Downloads HTML** from each TM Grupo URL
2. **Parses property data** using DOMDocument/DOMXPath:
   - Title
   - Description (with HTML formatting preserved)
   - Price (EUR)
   - Bedrooms and bathrooms
   - Area (m²)
   - Features (pool, terrace, garage, sea view)
3. **Downloads gallery images** from the property page
4. **Saves images** to `assets/images/properties/` with format: `newbuild-{slug}-01.jpg`
5. **Inserts property** into `properties` table with type "New Build"
6. **Inserts images** into `property_images` table with proper sort order

## Database Structure

### New Property Type
- **name**: New Build
- **name_hu**: Új építésű

### Properties Inserted
- Type: New Build (Új építésű)
- Status: Available
- Active: Yes
- Featured: Yes (to highlight new developments)

### Images
- Stored in: `/assets/images/properties/`
- Filename format: `newbuild-{slug}-{number}.jpg`
- Database: Only filename stored (e.g., `newbuild-tm-tower-01.jpg`)

## Safety Features

- **Duplicate prevention**: Checks for existing slug before inserting
- **Graceful failure**: If one property fails, continues with others
- **Image validation**: Verifies MIME type before saving
- **Error logging**: All errors logged to PHP error log
- **No external translations**: Keeps original language (English or Spanish)

## After Import

Once imported, properties will:
- ✓ Appear in property listings/search
- ✓ Be fully visible on detail pages with image gallery
- ✓ Be editable in admin panel (`/admin-uk0i3/properties.php`)
- ✓ Work with existing drag-and-drop image reordering
- ✓ Be filterable by "New Build" type

## Troubleshooting

**Issue**: Images not downloading
- Check internet connectivity
- Verify cURL is enabled in PHP
- Check write permissions on `assets/images/properties/`

**Issue**: Database errors
- Verify database credentials in `config/config.php`
- Check that `property_images` table exists (run setup script if needed)

**Issue**: Properties not appearing
- Check `is_active = 1` in database
- Verify property type ID is correct
- Clear any caches

## Legal Notice

This importer is used with explicit written permission from TM Grupo Inmobiliario for the specific URLs listed above. Do not modify to scrape other websites without permission.
