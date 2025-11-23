-- Besthomesespana Database Schema
-- Spanish Real Estate Website Database
-- IMPORTANT: Import this into the existing database via phpMyAdmin (c88384bhenew)

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password, email, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@besthomesespana.com', 'Admin User');

-- Property types table
CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    name_hu VARCHAR(50) NOT NULL COMMENT 'Hungarian translation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO property_types (name, name_hu) VALUES
('Villa', 'Villa'),
('Apartment', 'Apartman'),
('Penthouse', 'Penthouse'),
('Townhouse', 'Sorház'),
('Bungalow', 'Bungaló'),
('Land', 'Telek');

-- Locations/Regions table
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL COMMENT 'e.g., Costa Blanca, Costa del Sol',
    province VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO locations (city, region, province) VALUES
('Alicante', 'Costa Blanca', 'Alicante'),
('Benidorm', 'Costa Blanca', 'Alicante'),
('Torrevieja', 'Costa Blanca', 'Alicante'),
('Calpe', 'Costa Blanca', 'Alicante'),
('Marbella', 'Costa del Sol', 'Málaga'),
('Málaga', 'Costa del Sol', 'Málaga'),
('Estepona', 'Costa del Sol', 'Málaga'),
('Valencia', 'Valencia Region', 'Valencia');

-- Main properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    property_type_id INT NOT NULL,
    location_id INT NOT NULL,

    -- Price and area
    price DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    area_sqm INT NOT NULL COMMENT 'Area in square meters',
    plot_size_sqm INT DEFAULT NULL COMMENT 'Plot size for villas/houses',

    -- Property details
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    built_year INT DEFAULT NULL,

    -- Features (boolean flags)
    has_pool BOOLEAN DEFAULT FALSE,
    has_garden BOOLEAN DEFAULT FALSE,
    has_terrace BOOLEAN DEFAULT FALSE,
    has_garage BOOLEAN DEFAULT FALSE,
    has_sea_view BOOLEAN DEFAULT FALSE,
    distance_to_beach_m INT DEFAULT NULL COMMENT 'Distance to beach in meters',

    -- Images (stored as JSON array or comma-separated)
    main_image VARCHAR(255) DEFAULT NULL,
    images TEXT DEFAULT NULL COMMENT 'JSON array of image URLs',

    -- Status
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    status ENUM('available', 'reserved', 'sold') DEFAULT 'available',

    -- SEO
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE RESTRICT,
    INDEX idx_price (price),
    INDEX idx_location (location_id),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample properties
INSERT INTO properties (
    title, slug, description, property_type_id, location_id,
    price, area_sqm, plot_size_sqm, bedrooms, bathrooms, built_year,
    has_pool, has_garden, has_terrace, has_garage, has_sea_view, distance_to_beach_m,
    main_image, is_featured, is_active
) VALUES
(
    'Luxus Villa Tengerre Néző Kilátással - Calpe',
    'luxus-villa-calpe',
    '<p>Lenyűgöző modern villa a Costa Blanca szívében, lélegzetelállító tengerre néző kilátással. Ez a 4 hálószobás, 3 fürdőszobás ingatlan a mediterrán életstílus csúcsát képviseli.</p><p><strong>Főbb jellemzők:</strong></p><ul><li>Exkluzív elhelyezkedés Calpéban</li><li>Privát medence és gondozott kert</li><li>Modern konyha és nappali nyitott térrel</li><li>Panorámás terasz sunset kilátással</li><li>Mindössze 500m a strandtól</li></ul>',
    1, 4,
    895000.00, 280, 600, 4, 3, 2020,
    TRUE, TRUE, TRUE, TRUE, TRUE, 500,
    '/assets/images/properties/villa-calpe-1.jpg', TRUE, TRUE
),
(
    'Modern Penthouse Benidorm Központjában',
    'modern-penthouse-benidorm',
    '<p>Gyönyörű 2 hálószobás penthouse a benidormi skyline csodálatos kilátásával. Tökéletes befektetési lehetőség vagy második otthon.</p><p><strong>Előnyök:</strong></p><ul><li>Tetőterasz 80 m² napozó területtel</li><li>Közösségi medence és edzőterem</li><li>Légkondicionálás minden szobában</li><li>Parkolóhely az árban</li></ul>',
    3, 2,
    385000.00, 120, NULL, 2, 2, 2019,
    FALSE, FALSE, TRUE, TRUE, TRUE, 300,
    '/assets/images/properties/penthouse-benidorm-1.jpg', TRUE, TRUE
),
(
    'Családi Villa Kerttel - Torrevieja',
    'csaladi-villa-torrevieja',
    '<p>Tágas 3 hálószobás villa csendes környéken, ideális családoknak. Nagy kert, medence és fedett terasz.</p>',
    1, 3,
    425000.00, 180, 450, 3, 2, 2018,
    TRUE, TRUE, TRUE, TRUE, FALSE, 1200,
    '/assets/images/properties/villa-torrevieja-1.jpg', TRUE, TRUE
),
(
    'Tengerparti Apartman - Marbella',
    'tengerparti-apartman-marbella',
    '<p>Elegáns 2 hálószobás apartman közvetlenül a tengerparton. Luxus komplexum minden szolgáltatással.</p>',
    2, 5,
    650000.00, 95, NULL, 2, 2, 2021,
    FALSE, FALSE, TRUE, TRUE, TRUE, 50,
    '/assets/images/properties/apartment-marbella-1.jpg', TRUE, TRUE
),
(
    'Renovált Sorház Alicante Óvárosában',
    'renovalt-sorhaz-alicante',
    '<p>Gyönyörűen felújított 3 szintes sorház az óváros szívében. Autentikus spanyol stílus modern kényelemmel.</p>',
    4, 1,
    310000.00, 145, 80, 3, 2, 1970,
    FALSE, TRUE, TRUE, FALSE, FALSE, 2000,
    '/assets/images/properties/townhouse-alicante-1.jpg', FALSE, TRUE
),
(
    'Új Építésű Apartman Komplex - Valencia',
    'uj-apartman-valencia',
    '<p>Modern építésű 2 hálószobás apartman új komplexumban. Kész 2025 nyarán.</p>',
    2, 8,
    275000.00, 85, NULL, 2, 1, 2025,
    FALSE, FALSE, TRUE, TRUE, FALSE, 800,
    '/assets/images/properties/apartment-valencia-1.jpg', FALSE, TRUE
);

-- Contact inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'contacted', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Page content table (for static pages like "About Us")
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pages (slug, title, content) VALUES
('about', 'Rólunk', '<h2>Besthomesespana - Az Ön Megbízható Partnere Spanyolországban</h2><p>Több mint 10 éve segítünk magyar ügyfeleknek megtalálni álmaik ingatlanát a Costa Blanca és Costa del Sol régióban.</p>'),
('privacy', 'Adatkezelési Tájékoztató', '<h2>Adatkezelési Tájékoztató</h2><p>Az adatkezelés részletei...</p>');

-- Site content table (for editable homepage content)
CREATE TABLE IF NOT EXISTS site_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_key VARCHAR(191) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_content_key (content_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default content for homepage sections
INSERT INTO site_content (content_key, value) VALUES
-- Hero section
('home.hero_title', 'Találd meg álomotthonod Costa Blancán'),
('home.hero_subtitle', 'Prémium spanyol ingatlanok magyar ügyfeleknek. Villák, apartmanok, penthouse-ok a legszebb mediterrán helyszíneken.'),
('home.hero_search_location_label', 'Helyszín'),
('home.hero_search_type_label', 'Ingatlantípus'),
('home.hero_search_status_label', 'Státusz'),
('home.hero_search_price_label', 'Maximum ár'),
('home.hero_search_bedrooms_label', 'Hálószobák'),
('home.hero_search_button', 'Keresés'),

-- Featured properties section
('home.featured_title', 'Kiemelt Ingatlanok'),
('home.featured_subtitle', 'Válogatott ajánlataink a legjobb spanyol ingatlanokból. Villák, apartmanok és penthouse-ok kivételes helyszíneken.'),
('home.featured_filter_id', 'Azonosító'),
('home.featured_filter_area', 'Alapterület'),
('home.featured_filter_rooms', 'Szobaszám'),
('home.featured_filter_condition', 'Állapot'),
('home.featured_filter_pool', 'Medence'),
('home.featured_filter_seaview', 'Tengerre néző'),
('home.featured_load_more', 'További ingatlanok betöltése'),

-- Costa Blanca section
('home.costa_title', 'Fedezd fel Costa Blancát'),
('home.costa_description_1', 'A Costa Blanca Spanyolország egyik legkedveltebb régiója, ahol az évszázados hagyományok találkoznak a modern mediterrán életstílussal. Kristálytiszta víz, aranyló homokos strandok és több mint 300 napsütéses nap évente.'),
('home.costa_description_2', 'Ez a varázslatos régió ideális helyszín mind állandó lakhatásra, mind befektetési célokra. A kiváló infrastruktúra, nemzetközi iskolák, egészségügyi ellátás és a magyar közösség jelenléte miatt egyre többen választják új otthonuknak.'),
('home.costa_description_3', 'Alicante, Benidorm, Torrevieja és Calpe egyaránt kínál egyedülálló lehetőségeket - a csendes családi környezettől a pezsgő városi élet élményéig.'),
('home.costa_feature_1_icon', 'fa-sun'),
('home.costa_feature_1_title', '320+ napsütéses nap'),
('home.costa_feature_1_text', 'Évente átlagosan több mint 320 napon süt a nap'),
('home.costa_feature_2_icon', 'fa-water'),
('home.costa_feature_2_title', 'Kristálytiszta víz'),
('home.costa_feature_2_text', 'Zászlós díjas strandok és tiszta mediterrán tenger'),
('home.costa_feature_3_icon', 'fa-chart-line'),
('home.costa_feature_3_title', 'Befektetési potenciál'),
('home.costa_feature_3_text', 'Kiváló megtérülés és értéknövekedés'),
('home.costa_feature_4_icon', 'fa-utensils'),
('home.costa_feature_4_title', 'Mediterrán konyha'),
('home.costa_feature_4_text', 'Friss tengeri herkentyűk és helyi specialitások'),

-- Agent section
('home.agent_title', 'Ismerd meg Balogh Esztert'),
('home.agent_subtitle', 'Megbízható ingatlanszakértőd Spanyolországban'),
('home.agent_image', '/assets/images/agent-eszter.jpg'),
('home.agent_description_1', 'Több mint 15 éve élek Spanyolországban, és több mint 10 éve segítek magyar ügyfeleknek megtalálni álmaik otthonát a Costa Blanca és Costa del Sol régióban.'),
('home.agent_description_2', 'Szakmai tapasztalatom és helyi kapcsolatrendszerem révén biztosítom, hogy minden ügyfelemnek a lehető legsimábban és legbiztonságosabban menjen a vásárlási folyamat. Magyar anyanyelvű szakemberként teljes körű támogatást nyújtok az első kereséstől az ügyintézés befejezéséig.'),
('home.agent_description_3', 'Célom, hogy ne csak egy ingatlant találj, hanem egy új életérzést és otthont Spanyolországban.'),
('home.agent_feature_1_icon', 'fa-certificate'),
('home.agent_feature_1_title', 'Engedéllyel rendelkező szakember'),
('home.agent_feature_1_text', 'Hivatalos spanyol ingatlanos engedély és biztosítás'),
('home.agent_feature_2_icon', 'fa-language'),
('home.agent_feature_2_title', 'Többnyelvű szolgáltatás'),
('home.agent_feature_2_text', 'Magyar, spanyol, angol és német nyelven'),
('home.agent_feature_3_icon', 'fa-clock'),
('home.agent_feature_3_title', '24/7 elérhetőség'),
('home.agent_feature_3_text', 'Mindig elérhető vagyok ügyfeleimnek'),
('home.agent_feature_4_icon', 'fa-handshake'),
('home.agent_feature_4_title', 'Személyes odafigyelés'),
('home.agent_feature_4_text', 'Minden ügyféllel személyesen foglalkozom'),
('home.agent_phone', '+34 123 456 789'),
('home.agent_phone_label', 'Hívj most'),
('home.agent_contact_label', 'Üzenet küldése'),

-- Contact section
('home.contact_title', 'Vedd fel velünk a kapcsolatot'),
('home.contact_subtitle', 'Válaszolunk minden kérdésedre és segítünk megtalálni az ideális ingatlant'),
('home.contact_form_name', 'Teljes név'),
('home.contact_form_email', 'E-mail cím'),
('home.contact_form_phone', 'Telefonszám'),
('home.contact_form_message', 'Üzenet'),
('home.contact_form_submit', 'Üzenet küldése'),
('home.contact_office_title', 'Irodánk'),
('home.contact_office_address', 'Calle Ejemplo 123, 03001 Alicante, Spanyolország'),
('home.contact_office_phone', '+34 123 456 789'),
('home.contact_office_phone_hu', '+36 20 123 4567'),
('home.contact_office_email', 'info@besthomesespana.com'),
('home.contact_office_hours_title', 'Nyitvatartás'),
('home.contact_office_hours', 'Hétfő - Péntek: 9:00 - 18:00<br>Szombat: 10:00 - 14:00<br>Vasárnap: Zárva'),
('home.contact_map_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3131.234567890!2d-0.4906855!3d38.3452381!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzjCsDIwJzQyLjkiTiAwwrAyOSczMC41Ilc!5e0!3m2!1sen!2ses!4v1234567890'),

-- Footer content
('footer.company_description', 'Prémium spanyol ingatlanok magyar ügyfeleknek. Több mint 10 év tapasztalat a Costa Blanca és Costa del Sol régióban.'),
('footer.services_title', 'Szolgáltatásaink'),
('footer.services_1', 'Ingatlan keresés'),
('footer.services_2', 'Jogi tanácsadás'),
('footer.services_3', 'Finanszírozási segítség'),
('footer.services_4', 'Utólagos ügyintézés'),
('footer.legal_title', 'Jogi információk'),
('footer.contact_address', 'Alicante, Spanyolország'),
('footer.contact_phone_es', '+34 123 456 789'),
('footer.contact_phone_hu', '+36 20 123 4567');
