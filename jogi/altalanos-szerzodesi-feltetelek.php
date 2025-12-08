<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Általános Szerződési Feltételek';
$pageDescription = 'GDN Homes Espana S.L. (Best Homes Espana) általános szerződési feltételei és szolgáltatási szabályai.';

include __DIR__ . '/../public/partials/header.php';
?>

<style>
/* Legal page specific styles - same as impresszum.php */
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
        <h1>Általános Szerződési Feltételek</h1>

        <h2>1. Bevezetés</h2>
        <p>
            Jelen Általános Szerződési Feltételek (a továbbiakban: ÁSZF) tartalmazzák
            a <strong>GDN Homes Espana S.L.</strong> (márkanév: <strong>Best Homes Espana</strong>)
            által nyújtott ingatlanközvetítői és kapcsolódó szolgáltatások igénybevételének feltételeit.
        </p>
        <p>
            Az ÁSZF elfogadásával az ügyfél kijelenti, hogy megismerte és elfogadja a jelen dokumentumban
            foglalt feltételeket, és azokat magára nézve kötelezőnek ismeri el.
        </p>

        <div class="company-data">
            <h3>A szolgáltató adatai</h3>
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

                <dt>Kapcsolat:</dt>
                <dd>
                    <a href="tel:+36706310000">+36 70 631 0000</a><br>
                    <a href="mailto:besthomesespana@gmail.com">besthomesespana@gmail.com</a>
                </dd>
            </dl>
        </div>

        <h2>2. A szolgáltatás igénybevétele</h2>
        <p>
            <em>Ide kerül az ÁSZF részletes szövege a szolgáltatás igénybevételéről,
            a szerződéskötés folyamatáról, az ügyfél és szolgáltató jogairól és kötelezettségeiről.</em>
        </p>
        <ul>
            <li>Ingatlanközvetítési szolgáltatások igénybevételének feltételei</li>
            <li>Szerződéskötés menete és formája</li>
            <li>Ajánlatok érvényessége</li>
            <li>Foglalási feltételek</li>
        </ul>

        <h2>3. Szolgáltatási díjak és fizetési feltételek</h2>
        <p>
            <em>Részletek a szolgáltatási díjakról, fizetési módo król, határidőkről.</em>
        </p>

        <h2>4. Felek jogai és kötelezettségei</h2>
        <h3>4.1. A szolgáltató kötelezettségei</h3>
        <p>
            <em>Ide kerül a szolgáltató vállalásainak, kötelezettségeinek részletes leírása.</em>
        </p>

        <h3>4.2. Az ügyfél kötelezettségei</h3>
        <p>
            <em>Ide kerül az ügyfél kötelezettségeinek, együttműködési kötelezettségének leírása.</em>
        </p>

        <h2>5. Felelősség és felelősség-korlátozás</h2>
        <p>
            <em>Részletes felelősségi szabályok, felelősség kizárása vagy korlátozása bizonyos esetekben.</em>
        </p>

        <h2>6. Szerződés módosítása és megszüntetése</h2>
        <p>
            <em>A szerződés módosításának és megszüntetésének feltételei, felmondási jog.</em>
        </p>

        <h2>7. Panaszkezelés</h2>
        <p>
            Amennyiben a szolgáltatással kapcsolatban panasza van, kérjük, jelezze felénk
            az alábbi elérhetőségeken:
        </p>
        <ul>
            <li>Telefon: <a href="tel:+36706310000">+36 70 631 0000</a></li>
            <li>E-mail: <a href="mailto:besthomesespana@gmail.com">besthomesespana@gmail.com</a></li>
            <li>Postai cím: 107–119 Nagy Lajos király útja, 1149 Budapest, Magyarország</li>
        </ul>
        <p>
            Panaszát 30 napon belül kivizsgáljuk és írásban válaszolunk.
        </p>

        <h2>8. Adatvédelem</h2>
        <p>
            Az adatkezelésre vonatkozó részletes információkat az
            <a href="/jogi/adatkezelesi-tajekoztato.php">Adatkezelési tájékoztatóban</a> találja.
        </p>

        <h2>9. Irányadó jog és jogviták rendezése</h2>
        <p>
            <em>Ide kerül a jogvitákra irányadó jog, illetékes bíróság meghatározása.</em>
        </p>

        <h2>10. Egyéb rendelkezések</h2>
        <p>
            <em>További rendelkezések, hatálybalépés, módosítás szabályai.</em>
        </p>

        <p style="margin-top: 3rem; font-size: 0.9rem; color: var(--text-light);">
            Utolsó frissítés: <?= date('Y. m. d.') ?><br>
            Hatályos: <?= date('Y. m. d.') ?> napjától
        </p>

    </div>
</div>

<?php include __DIR__ . '/../public/partials/footer.php'; ?>
