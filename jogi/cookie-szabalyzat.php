<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Süti (Cookie) szabályzat';
$pageDescription = 'GDN Homes Espana S.L. (Best Homes Espana) süti (cookie) használati szabályzata és tájékoztatója.';

include __DIR__ . '/../public/partials/header.php';
?>

<style>
/* Legal page specific styles - same as previous pages */
.legal-page {
    background: var(--white);
    padding: 6rem 0 4rem;
    min-height: 80vh;
}

.legal-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 2rem;
}

.legal-page h1 {
    font-size: 2.5rem;
    color: var(--primary-blue);
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--accent-gold);
}

.legal-page h2 {
    font-size: 1.75rem;
    color: var(--text-dark);
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
}

.legal-page h3 {
    font-size: 1.35rem;
    color: var(--text-dark);
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.legal-page p,
.legal-page li {
    line-height: 1.8;
    color: var(--text-medium);
    margin-bottom: 1rem;
}

.legal-page ul, .legal-page ol {
    margin-left: 2rem;
    margin-bottom: 1.5rem;
}

.cookie-table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: var(--white);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cookie-table th {
    background: var(--primary-blue);
    color: var(--white);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}

.cookie-table td {
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.cookie-table tr:last-child td {
    border-bottom: none;
}

.cookie-table tr:nth-child(even) {
    background: #f8f9fa;
}

.highlight-box {
    background: #e8f4f8;
    border-left: 4px solid var(--primary-blue);
    padding: 1.5rem;
    margin: 2rem 0;
    border-radius: var(--radius-md);
}

@media (max-width: 768px) {
    .legal-page h1 {
        font-size: 2rem;
    }

    .cookie-table {
        font-size: 0.9rem;
    }

    .cookie-table th,
    .cookie-table td {
        padding: 0.75rem;
    }
}
</style>

<div class="legal-page">
    <div class="legal-container">
        <h1>Süti (Cookie) szabályzat</h1>

        <p>
            A <strong>besthomesespana.com</strong> weboldal (üzemeltető: <strong>GDN Homes Espana S.L.</strong>)
            sütiket (cookie-kat) használ a felhasználói élmény javítása, a weboldal működésének
            biztosítása és statisztikák készítése céljából.
        </p>

        <h2>1. Mi az a süti?</h2>
        <p>
            A sütik (angolul: cookies) kis szöveges fájlok, amelyeket a weboldal az Ön böngészőjében tárol,
            amikor meglátogatja az oldalt. Ezek a fájlok információkat tárolnak a látogatásról,
            és lehetővé teszik, hogy a weboldal "emlékezzen" bizonyos beállításokra vagy preferenciákra.
        </p>

        <div class="highlight-box">
            <h3>Miért használunk sütiket?</h3>
            <ul>
                <li>A weboldal megfelelő működésének biztosítása</li>
                <li>A felhasználói élmény javítása (pl. beállítások megjegyzése)</li>
                <li>Statisztikák készítése a látogatottságról és az oldal használatáról</li>
                <li>Az oldal teljesítményének mérése és fejlesztése</li>
            </ul>
        </div>

        <h2>2. Milyen sütiket használunk?</h2>
        <p>
            <em>Ide kerül a használt sütik részletes listája kategóriánként.</em>
        </p>

        <h3>2.1. Feltétlenül szükséges sütik</h3>
        <p>
            Ezek a sütik elengedhetetlenek a weboldal alapvető funkcióinak működéséhez,
            mint például a biztonságos bejelentkezés vagy a kosár funkció.
        </p>

        <table class="cookie-table">
            <thead>
                <tr>
                    <th>Süti neve</th>
                    <th>Cél</th>
                    <th>Lejárat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>session_id</code></td>
                    <td>Munkamenet azonosítója, bejelentkezés fenntartása</td>
                    <td>Munkamenet vége</td>
                </tr>
                <tr>
                    <td><code>cookie_consent</code></td>
                    <td>Süti hozzájárulás tárolása</td>
                    <td>1 év</td>
                </tr>
            </tbody>
        </table>

        <h3>2.2. Statisztikai / Analitikai sütik</h3>
        <p>
            Ezek a sütik névtelen statisztikák gyűjtésére szolgálnak,
            hogy megértsük, hogyan használják látogatóink a weboldalt.
        </p>

        <table class="cookie-table">
            <thead>
                <tr>
                    <th>Süti neve</th>
                    <th>Szolgáltató</th>
                    <th>Cél</th>
                    <th>Lejárat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>_ga</code></td>
                    <td>Google Analytics</td>
                    <td>Látogatók megkülönböztetése</td>
                    <td>2 év</td>
                </tr>
                <tr>
                    <td><code>_gid</code></td>
                    <td>Google Analytics</td>
                    <td>Látogatók megkülönböztetése</td>
                    <td>24 óra</td>
                </tr>
            </tbody>
        </table>

        <h3>2.3. Funkcionális sütik</h3>
        <p>
            Ezek a sütik lehetővé teszik a weboldal számára, hogy megjegyezze
            az Ön által tett választásokat (pl. nyelv, régiós beállítások).
        </p>

        <h3>2.4. Marketing / Hirdetési sütik</h3>
        <p>
            <em>Ide kerül, ha használunk marketing sütiket (pl. Facebook Pixel, Google Ads).
            Jelenleg a weboldal nem használ ilyen sütiket.</em>
        </p>

        <h2>3. Sütik kezelése és letiltása</h2>
        <p>
            Ön bármikor módosíthatja a sütikre vonatkozó beállításokat böngészőjében.
            A legtöbb böngésző alapértelmezés szerint elfogadja a sütiket, de
            ezt megváltoztathatja, vagy törölheti a már létrejött sütiket.
        </p>

        <div class="highlight-box">
            <h3>Hogyan tilthatja le a sütiket?</h3>
            <p>
                A sütik kezelése böngészőnként:
            </p>
            <ul>
                <li>
                    <strong>Google Chrome:</strong>
                    <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">
                        Sütik kezelése Chrome-ban
                    </a>
                </li>
                <li>
                    <strong>Mozilla Firefox:</strong>
                    <a href="https://support.mozilla.org/hu/kb/sutik-informacio-amelyet-weboldalak-tarolnak-szami" target="_blank" rel="noopener">
                        Sütik kezelése Firefoxban
                    </a>
                </li>
                <li>
                    <strong>Microsoft Edge:</strong>
                    <a href="https://support.microsoft.com/hu-hu/microsoft-edge/cookie-k-t%C3%B6rl%C3%A9se-a-microsoft-edge-ben-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">
                        Sütik kezelése Edge-ben
                    </a>
                </li>
                <li>
                    <strong>Safari:</strong>
                    <a href="https://support.apple.com/hu-hu/guide/safari/sfri11471/mac" target="_blank" rel="noopener">
                        Sütik kezelése Safariban
                    </a>
                </li>
            </ul>
        </div>

        <p>
            <strong>Fontos:</strong> Ha letiltja a sütiket, előfordulhat,
            hogy a weboldal egyes funkciói nem működnek megfelelően.
        </p>

        <h2>4. Harmadik féltől származó sütik</h2>
        <p>
            <em>Ide kerül, ha használunk harmadik fél által kezelt sütiket
            (pl. Google Analytics, Facebook, YouTube beágyazott videók).
            Ezek a szolgáltatók saját adatvédelmi szabályzattal rendelkeznek.</em>
        </p>

        <h2>5. Süti szabályzat módosítása</h2>
        <p>
            Fenntartjuk a jogot, hogy jelen Süti szabályzatot bármikor módosítsuk.
            A változásokról ezen az oldalon tájékoztatjuk látogatóinkat.
        </p>

        <h2>6. További információ</h2>
        <p>
            Az adatkezelésről részletes információkat az
            <a href="/jogi/adatkezelesi-tajekoztato.php">Adatkezelési tájékoztatóban</a> talál.
        </p>
        <p>
            Kérdés esetén vegye fel velünk a kapcsolatot:
        </p>
        <ul>
            <li>E-mail: <a href="mailto:besthomesespana@gmail.com">besthomesespana@gmail.com</a></li>
            <li>Telefon: <a href="tel:+36706310000">+36 70 631 0000</a></li>
        </ul>

        <p style="margin-top: 3rem; font-size: 0.9rem; color: var(--text-light);">
            Utolsó frissítés: <?= date('Y. m. d.') ?><br>
            Hatályos: <?= date('Y. m. d.') ?> napjától
        </p>

    </div>
</div>

<?php include __DIR__ . '/../public/partials/footer.php'; ?>
