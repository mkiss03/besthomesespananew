# Besthomesespana - Spanyol Ingatlan Weboldal

Modern, reszponzív weboldal spanyol ingatlanok hirdetésére magyar ügyfelek felé. PHP 8+ és MySQL alapú, admin felülettel és teljes CRUD funkciókkal.

## 🌟 Főbb Jellemzők

### Frontend
- ✅ Modern, gyors, reszponzív design (mobil → desktop)
- ✅ Spanyol tengerparti hangulatú UI/UX (tengerkék, homokbézs színek)
- ✅ Hero szekció kereső űrlappal
- ✅ Ingatlan listázás szűrőkkel és lapozással
- ✅ Részletes ingatlan oldalak képgalériával
- ✅ Kapcsolatfelvételi űrlapok
- ✅ SEO optimalizált (meta címek, leírások)

### Admin Panel
- ✅ Biztonságos bejelentkezés session kezeléssel
- ✅ Dashboard statisztikákkal
- ✅ Teljes CRUD ingatlankezelés
- ✅ Érdeklődések kezelése státusz frissítéssel
- ✅ Modern admin UI reszponzív designnal
- ✅ REST API végpontok

### Technológia
- PHP 8+ (framework nélkül, tiszta PHP)
- MySQL adatbázis PDO kapcsolattal
- Prepared statements (SQL injection védelem)
- XSS védelem
- Session alapú autentikáció
- AJAX funkciók (fetch API)

## 📁 Projekt Struktúra

```
newbhe/
├── config/
│   └── config.php              # Adatbázis konfig és helper funkciók
├── db/
│   └── besthomesespana.sql     # Adatbázis séma és minta adatok
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css       # Fő stíluslap
│   │   ├── js/
│   │   └── images/
│   │       ├── hero/
│   │       └── properties/
│   ├── partials/
│   │   ├── header.php          # Fejléc és navigáció
│   │   └── footer.php          # Lábléc
│   ├── index.php               # Főoldal
│   ├── properties.php          # Ingatlan lista
│   ├── property.php            # Ingatlan részletek
│   ├── contact.php             # Kapcsolat
│   └── about.php               # Rólunk
├── admin/
│   ├── partials/
│   │   ├── header.php          # Admin fejléc
│   │   └── footer.php          # Admin lábléc
│   ├── api/
│   │   ├── properties.php      # Ingatlan API
│   │   └── inquiries.php       # Érdeklődés API
│   ├── login.php               # Bejelentkezés
│   ├── logout.php              # Kijelentkezés
│   ├── index.php               # Admin dashboard
│   ├── properties.php          # Ingatlan lista (admin)
│   ├── property-edit.php       # Ingatlan szerkesztő
│   └── inquiries.php           # Érdeklődések
└── README.md                   # Ez a fájl
```

## 🚀 Telepítés és Beállítás

### 1. Követelmények
- PHP 8.0 vagy újabb
- MySQL 5.7 vagy újabb
- Apache/Nginx webszerver
- mod_rewrite engedélyezve (Apache esetén)

### 2. Telepítési Lépések

**Adatbázis létrehozása:**
```bash
mysql -u root -p < db/besthomesespana.sql
```

Vagy phpMyAdmin-on keresztül importálja a `db/besthomesespana.sql` fájlt.

**Konfiguráció beállítása:**

Szerkessze a `config/config.php` fájlt az adatbázis elérési adatokkal:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'besthomesespana');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('SITE_URL', 'http://your-domain.com');
```

**Jogosultságok beállítása:**
```bash
chmod -R 755 public/assets/images/
```

### 3. Webszerver Konfiguráció

**Apache (.htaccess):**

A `public/` mappába hozzon létre `.htaccess` fájlt:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Redirect to public directory if accessing root
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L]
</IfModule>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/newbhe/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

## 👤 Admin Bejelentkezés

**URL:** `/admin/login.php`

**Alapértelmezett bejelentkezési adatok:**
- Felhasználónév: `admin`
- Jelszó: `admin123`

⚠️ **FONTOS:** Éles környezetben azonnal változtassa meg az alapértelmezett jelszót!

Jelszó módosítása:
```php
// Futtassa ezt a PHP kódot új jelszó generálásához
echo password_hash('your_new_password', PASSWORD_DEFAULT);
```

Majd frissítse az adatbázisban:
```sql
UPDATE admins SET password = 'generated_hash' WHERE username = 'admin';
```

## 📊 Adatbázis Struktúra

### Főbb Táblák

**properties** - Ingatlanok
- Alapadatok: cím, leírás, típus, helyszín
- Ár és méret információk
- Szobák száma (hálószobák, fürdőszobák)
- Jellemzők (medence, kert, terasz, stb.)
- Státusz (aktív, kiemelt, eladva, stb.)

**property_types** - Ingatlan típusok
- Villa, Apartman, Penthouse, Sorház, Bungaló, Telek

**locations** - Helyszínek
- Város, régió, tartomány
- Costa Blanca, Costa del Sol helyszínek

**inquiries** - Érdeklődések
- Kapcsolatfelvételi űrlapok
- Státusz követés (új, kapcsolatba lépve, lezárva)

**admins** - Admin felhasználók
- Bejelentkezési adatok

**pages** - Statikus oldalak tartalma

## 🎨 Design és Stílusok

### Színpaletta
- **Tengerkék:** `#1b5e9f` (primary)
- **Világos kék:** `#2980b9` (secondary)
- **Arany:** `#f39c12` (accent)
- **Narancs:** `#e67e22` (accent)
- **Homokbézs:** `#f5f1e8` (háttér)

### Betűtípusok
- **Poppins** - Törzsszöveg
- **Playfair Display** - Címek

### Reszponzív Breakpointok
- **Desktop:** 1200px+
- **Tablet:** 768px - 1199px
- **Mobile:** < 768px

## 🔒 Biztonság

- ✅ SQL injection védelem (PDO prepared statements)
- ✅ XSS védelem (htmlspecialchars output escape)
- ✅ Session biztonság
- ✅ CSRF token védelem a formokban
- ✅ Password hashing (PHP password_hash)
- ✅ Admin autentikáció minden védett oldalon

## 🌐 SEO Optimalizálás

- Meta címek és leírások minden oldalon
- Szemantikus HTML5 struktúra
- Képek alt szöveggel
- Lazy loading képeknél
- Reszponzív, mobile-friendly design
- Gyors betöltési idő

## 📝 Használat

### Új Ingatlan Hozzáadása

1. Jelentkezzen be az admin panelba
2. Kattintson az "Ingatlanok" menüre
3. "Új ingatlan" gomb
4. Töltse ki az űrlapot:
   - Alapadatok (cím, leírás, típus, helyszín)
   - Ár és méret
   - Részletek (szobák, építés éve)
   - Jellemzők (medence, kert, stb.)
   - Kép URL
5. Jelölje be, ha "Kiemelt" és "Aktív"
6. Mentés

### Érdeklődések Kezelése

1. Admin panel → "Érdeklődések"
2. Tekintse meg az új üzeneteket (sárga háttér)
3. Frissítse a státuszt:
   - **Új** - Még nem foglalkoztunk vele
   - **Kapcsolatba lépve** - Már felvettük a kapcsolatot
   - **Lezárva** - Lezárt ügy

## 🔧 Testreszabás

### Színek Módosítása

Szerkessze a `public/assets/css/style.css` fájl `:root` szekcióját:

```css
:root {
    --primary-blue: #1b5e9f;
    --secondary-blue: #2980b9;
    --accent-gold: #f39c12;
    /* ... */
}
```

### Logo Hozzáadása

1. Másolja a logo képet a `public/assets/images/` mappába
2. Szerkessze a `public/partials/header.php` fájlt:

```php
<a href="/index.php" class="navbar-logo">
    <img src="/assets/images/logo.png" alt="Besthomesespana">
</a>
```

### Hero Háttérkép Módosítása

Szerkessze a `public/index.php` fájlt:

```php
<div class="hero-background" style="background: url('/assets/images/hero/your-image.jpg') center/cover;"></div>
```

## 📞 Támogatás

Kérdések vagy problémák esetén:
- Email: [info@besthomesespana.com](mailto:info@besthomesespana.com)
- GitHub Issues: [Jelentse be a hibát](https://github.com/yourusername/besthomesespana/issues)

## 📄 Licenc

Ez a projekt magáncélú használatra készült. Kereskedelmi felhasználás esetén kérjük, vegye fel a kapcsolatot a fejlesztővel.

## 🙏 Köszönetnyilvánítás

- **Képek:** Unsplash.com
- **Ikonok:** Font Awesome
- **Betűtípusok:** Google Fonts

---

**Verzió:** 1.0.0
**Utolsó frissítés:** 2025-01-21
**Készítette:** Besthomesespana Development Team
