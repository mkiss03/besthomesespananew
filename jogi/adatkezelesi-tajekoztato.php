<?php
require_once __DIR__ . '/../../config/config.php';

$pageTitle = 'Adatkezelési tájékoztató';
$pageDescription = 'GDN Homes Espana S.L. (Best Homes Espana) adatkezelési tájékoztatója és adatvédelmi szabályzata.';

include __DIR__ . '/../partials/header.php';
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

.company-data {
    background: #f8f9fa;
    border-left: 4px solid var(--primary-blue);
    padding: 2rem;
    margin: 2rem 0;
    border-radius: var(--radius-md);
}

.company-data dl {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1rem 2rem;
    margin: 0;
}

.company-data dt {
    font-weight: 600;
    color: var(--text-dark);
}

.company-data dd {
    color: var(--text-medium);
    margin: 0;
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

    .company-data dl {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .company-data dt {
        margin-top: 1rem;
    }
}
</style>

<div class="legal-page">
    <div class="legal-container">
        <h1>Adatkezelési tájékoztató</h1>

        <p>
            A <strong>GDN Homes Espana S.L.</strong> (márkanév: <strong>Best Homes Espana</strong>)
            elkötelezett az Ön személyes adatainak védelme iránt. Jelen tájékoztató
            az Európai Unió Általános Adatvédelmi Rendelete (GDPR) alapján készült.
        </p>

        <div class="company-data">
            <h3>Adatkezelő adatai</h3>
            <dl>
                <dt>Cégnév:</dt>
                <dd>GDN Homes Espana S.L.</dd>

                <dt>Márkanév:</dt>
                <dd>Best Homes Espana</dd>

                <dt>Bejegyzett cím:</dt>
                <dd>Calles Sierra Dorada 19., 03503 Benidorm, Spanyolország</dd>

                <dt>Magyar iroda:</dt>
                <dd>107–119 Nagy Lajos király útja, 1149 Budapest, Magyarország</dd>

                <dt>NIF / CIF:</dt>
                <dd>B13834437</dd>

                <dt>Kapcsolat (adatvédelem):</dt>
                <dd>
                    <a href="tel:+36706310000">+36 70 631 0000</a><br>
                    <a href="mailto:besthomesespana@gmail.com">besthomesespana@gmail.com</a>
                </dd>
            </dl>
        </div>

        <h2>1. Kezelt adatok köre</h2>
        <p>
            <em>Ide kerül az adatkezelés részletes leírása: milyen adatokat gyűjtünk,
            honnan származnak (pl. weboldalon való regisztráció, kapcsolatfelvétel, stb.).</em>
        </p>

        <h3>1.1. Kapcsolatfelvételi adatok</h3>
        <ul>
            <li>Név (vezetéknév, keresztnév)</li>
            <li>E-mail cím</li>
            <li>Telefonszám</li>
            <li>Üzenet tartalma</li>
        </ul>

        <h3>1.2. Ingatlankeresési preferenciák</h3>
        <ul>
            <li>Keresett ingatlan típusa, helyszíne</li>
            <li>Árkategória, szoba szám, egyéb preferenciák</li>
        </ul>

        <h3>1.3. Technikai adatok</h3>
        <ul>
            <li>IP cím</li>
            <li>Böngésző típusa</li>
            <li>Látogatási adatok (időpont, megtekintett oldalak)</li>
        </ul>

        <h2>2. Adatkezelés célja és jogalapja</h2>
        <p>
            <em>Itt részletezzük az adatkezelés célját és jogalapját (pl. szerződés teljesítése,
            jogos érdek, hozzájárulás, stb.).</em>
        </p>

        <h3>2.1. Kapcsolattartás</h3>
        <p>
            <strong>Cél:</strong> Az Ön által megkeresésünkre adott válaszküldés, ajánlat készítése.<br>
            <strong>Jogalap:</strong> Az Ön önkéntes hozzájárulása (GDPR 6. cikk (1) a) pont).
        </p>

        <h3>2.2. Ingatlanközvetítési szolgáltatás nyújtása</h3>
        <p>
            <strong>Cél:</strong> Az ingatlanközvetítési szerződés teljesítése.<br>
            <strong>Jogalap:</strong> Szerződés teljesítése (GDPR 6. cikk (1) b) pont).
        </p>

        <h3>2.3. Weboldalunk működtetése, fejlesztése</h3>
        <p>
            <strong>Cél:</strong> A weboldal biztonságos működtetése, statisztikák készítése.<br>
            <strong>Jogalap:</strong> Jogos érdek (GDPR 6. cikk (1) f) pont).
        </p>

        <h2>3. Adattárolás időtartama</h2>
        <p>
            <em>Ide kerül, hogy mennyi ideig tároljuk az egyes adatkategóriákat,
            mikor és hogyan töröljük őket.</em>
        </p>
        <ul>
            <li><strong>Kapcsolatfelvételi adatok:</strong> maximum 2 év a kapcsolatfelvételtől számítva, vagy hozzájárulás visszavonásáig.</li>
            <li><strong>Szerződéses adatok:</strong> a vonatkozó jogszabályok szerinti megőrzési kötelezettség erejéig (általában 8 év).</li>
            <li><strong>Technikai adatok (naplófájlok):</strong> maximum 90 nap.</li>
        </ul>

        <h2>4. Adatfeldolgozók, adattovábbítás</h2>
        <p>
            <em>Itt részletezzük, hogy kik férhetnek hozzá az adatokhoz, mely
            harmadik félnek adjuk át az adatokat (pl. tárhelyszolgáltató, e-mail szolgáltató).</em>
        </p>

        <h2>5. Érintetti jogok és jogorvoslat</h2>
        <p>
            Az adatkezeléssel kapcsolatban Önt az alábbi jogok illetik meg:
        </p>

        <div class="highlight-box">
            <h3>Ön jogosult:</h3>
            <ul>
                <li><strong>Tájékoztatás kérésére:</strong> Kérheti, hogy tájékoztassuk Önt az általunk kezelt személyes adatairól.</li>
                <li><strong>Helyesbítésre:</strong> Kérheti az adatai javítását, ha azok pontatlanok.</li>
                <li><strong>Törlésre ("elfeledtetéshez való jog"):</strong> Kérheti adatai törlését bizonyos feltételek mellett.</li>
                <li><strong>Adatkezelés korlátozására:</strong> Kérheti az adatkezelés korlátozását.</li>
                <li><strong>Adathordozhatóságra:</strong> Kérheti, hogy adatait tagolt, géppel olvasható formában megkapja.</li>
                <li><strong>Tiltakozásra:</strong> Tiltakozhat az adatkezelés ellen.</li>
                <li><strong>Hozzájárulás visszavonására:</strong> Bármikor visszavonhatja hozzájárulását.</li>
            </ul>
        </div>

        <p>
            <strong>Jogorvoslat:</strong> Amennyiben úgy érzi, hogy megsértettük adatvédelmi jogait,
            panasszal fordulhat a Nemzeti Adatvédelmi és Információszabadság Hatósághoz
            (<a href="https://naih.hu" target="_blank" rel="noopener">www.naih.hu</a>),
            illetve a spanyol adatvédelmi hatósághoz (AEPD).
        </p>

        <h2>6. Adatbiztonság</h2>
        <p>
            <em>Ide kerül, milyen technikai és szervezési intézkedéseket tettünk
            az adatok biztonságának megőrzése érdekében (SSL titkosítás, hozzáférés-korlátozás, stb.).</em>
        </p>

        <h2>7. Sütik (Cookies) használata</h2>
        <p>
            Weboldalunk sütiket használ a felhasználói élmény javítása érdekében.
            A sütikről részletes tájékoztatást a
            <a href="/jogi/cookie-szabalyzat.php">Süti (Cookie) szabályzatban</a> talál.
        </p>

        <h2>8. Kapcsolat</h2>
        <p>
            Amennyiben kérdése van az adatkezeléssel kapcsolatban, vagy élni szeretne valamelyik jogával,
            kérjük, vegye fel velünk a kapcsolatot:
        </p>
        <ul>
            <li>E-mail: <a href="mailto:besthomesespana@gmail.com">besthomesespana@gmail.com</a></li>
            <li>Telefon: <a href="tel:+36706310000">+36 70 631 0000</a></li>
            <li>Postai cím: 107–119 Nagy Lajos király útja, 1149 Budapest, Magyarország</li>
        </ul>

        <p style="margin-top: 3rem; font-size: 0.9rem; color: var(--text-light);">
            Utolsó frissítés: <?= date('Y. m. d.') ?><br>
            Hatályos: <?= date('Y. m. d.') ?> napjától
        </p>

    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
