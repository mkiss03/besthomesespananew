<?php
require_once __DIR__ . '/../../config/config.php';

$pageTitle = 'Impresszum';
$pageDescription = 'GDN Homes Espana S.L. (Best Homes Espana) jogi és céginformációk, elérhetőségek.';

include __DIR__ . '/../partials/header.php';
?>

<style>
/* Legal page specific styles */
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
        <h1>Impresszum</h1>

        <div class="company-data">
            <h2>Cégadatok</h2>
            <dl>
                <dt>Cégnév:</dt>
                <dd>GDN Homes Espana S.L.</dd>

                <dt>Márkanév:</dt>
                <dd>Best Homes Espana</dd>

                <dt>Bejegyzett cím:</dt>
                <dd>Calles Sierra Dorada 19., 03503 Benidorm, Spanyolország</dd>

                <dt>Irodai cím (magyar iroda):</dt>
                <dd>107–119 Nagy Lajos király útja, 1149 Budapest, Magyarország</dd>

                <dt>NIF / CIF (spanyol adószám):</dt>
                <dd>B13834437</dd>

                <dt>Cégnyilvántartási adatok:</dt>
                <dd>Registro Mercantil de Alicante, Tomo 4861, Folio 217, Hoja A 195916</dd>

                <dt>Telefon:</dt>
                <dd><a href="tel:+36706310000">+36 70 631 0000</a></dd>

                <dt>E-mail:</dt>
                <dd><a href="mailto:besthomesespana@gmail.com">besthomesespana@gmail.com</a></dd>
            </dl>
        </div>

        <h2>Felelősség kizárása</h2>
        <p>
            A weboldalon megjelenő információk tájékoztató jellegűek. A GDN Homes Espana S.L.
            nem vállal felelősséget az oldalon található információk pontosságáért, teljességéért
            vagy időszerűségéért.
        </p>
        <p>
            Az ingatlanok adatai harmadik féltől származhatnak, ezért azok pontossága változhat.
            Kérjük, minden esetben egyeztessen ügyintézőinkkel a konkrét ingatlan részleteiről.
        </p>

        <h2>Szerzői jogok</h2>
        <p>
            Az oldalon található tartalmak (szövegek, képek, logók, grafikai elemek)
            szerzői jogi védelem alatt állnak. A tartalmak jogtalan felhasználása, másolása
            vagy terjesztése jogszabályba ütközik.
        </p>

        <h2>Oldal üzemeltetője</h2>
        <p>
            A <strong>besthomesespana.com</strong> weboldal üzemeltetője és tartalomért felelős:
            <strong>GDN Homes Espana S.L.</strong>
        </p>
        <p>
            Minden jog fenntartva © <?= date('Y') ?> GDN Homes Espana S.L.
        </p>

        <h2>Jogviták rendezése</h2>
        <p>
            Az oldalon és a szolgáltatásokkal kapcsolatos jogvitákra spanyol jog az irányadó.
            Jogviták esetén az illetékes bíróság a Registro Mercantil de Alicante szerint kerül meghatározásra.
        </p>

    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
