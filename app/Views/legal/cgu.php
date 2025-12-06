<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="cgu-container"
    style="max-width: 800px; margin: 0 auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h1 style="text-align: center; margin-bottom: 2rem;">Conditions Générales d'Utilisation (CGU)</h1>

    <p>Mise à jour : <?= date('d/m/Y') ?></p>

    <section>
        <h3>1. Objet</h3>
        <p>Les présentes Conditions Générales d'Utilisation (ci-après "CGU") ont pour objet de définir les modalités de
            mise à disposition et les conditions d'utilisation de l'application de <strong>Gestion de Matériel du Parc
                du Crous de Versailles</strong> (ci-après "le Service"). L'accès et l'utilisation du Service supposeront
            l'acceptation sans réserve et le respect de l'ensemble des termes des présentes CGU.</p>
    </section>

    <section>
        <h3>2. Accès au Service</h3>
        <p>Le Service est accessible gratuitement à tout utilisateur disposant d'un compte autorisé par l'administration
            du Crous de Versailles. Tous les coûts afférents à l'accès au Service (matériel informatique, logiciels,
            accès Internet, etc.) sont exclusivement à la charge de l'utilisateur.</p>
        <p>L'accès au Service est strictement réservé à un usage professionnel interne.</p>
    </section>

    <section>
        <h3>3. Responsabilité de l'Utilisateur</h3>
        <p>L'utilisateur est responsable de la confidentialité de son identifiant et de son mot de passe. Toute action
            effectuée via son compte est réputée avoir été effectuée par lui-même. L'utilisateur s'engage à informer
            immédiatement l'administration de toute utilisation non autorisée de son compte.</p>
        <p>L'utilisateur s'engage à utiliser le Service conformément à sa destination et à ne pas tenter de porter
            atteinte au bon fonctionnement, à la sécurité ou à l'intégrité du système.</p>
    </section>

    <section>
        <h3>4. Gestion et Attribution du Matériel</h3>
        <p>Le Service permet le suivi, l'attribution et la restitution de matériel informatique et bureautique.
            L'utilisateur (Gestionnaire ou Admin) s'engage à :</p>
        <ul>
            <li>Saisir des informations exactes et à jour concernant le matériel et les agents.</li>
            <li>Respecter les procédures de validation des attributions et des retours.</li>
            <li>Ne pas utiliser le Service pour attribuer du matériel à des fins personnelles non autorisées.</li>
        </ul>
    </section>

    <section>
        <h3>5. Propriété Intellectuelle</h3>
        <p>L'ensemble des éléments constituant le Service (textes, graphismes, logiciels, bases de données, etc.) est la
            propriété exclusive du Crous de Versailles ou de ses partenaires. Toute reproduction ou représentation
            totale ou partielle de ce Service sans autorisation est interdite.</p>
    </section>

    <section>
        <h3>6. Données Personnelles (RGPD)</h3>
        <p>Dans le cadre de l'utilisation du Service, des données à caractère personnel (Noms, Prénoms, Emails, Logs
            d'activité) sont collectées et traitées pour les besoins de la gestion du parc matériel.</p>
        <p>Le traitement est nécessaire à l'exécution de la mission de service public et à la gestion administrative
            interne. Conformément au RGPD et à la loi Informatique et Libertés, les agents disposent d'un droit d'accès,
            de rectification et d'effacement de leurs données, qu'ils peuvent exercer auprès du Délégué à la Protection
            des Données (DPO) de l'établissement.</p>
    </section>

    <section>
        <h3>7. Limitation de Responsabilité</h3>
        <p>L'administration s'efforce d'assurer la disponibilité du Service 24h/24 et 7j/7, mais ne saurait être tenue
            responsable en cas d'interruption pour maintenance, incident technique ou force majeure.</p>
        <p>Les informations présentes sur le Service sont fournies à titre indicatif. L'administration ne saurait être
            tenue responsable des erreurs ou omissions dans les données saisies par les utilisateurs.</p>
    </section>

    <section>
        <h3>8. Modification des CGU</h3>
        <p>L'administration se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront
            informés de toute modification substantielle.</p>
    </section>

    <section>
        <h3>9. Droit Applicable</h3>
        <p>Les présentes CGU sont soumises au droit français. Tout litige relatif à leur interprétation et à leur
            exécution relève de la compétence des tribunaux administratifs compétents.</p>
    </section>

    <div style="margin-top: 3rem; text-align: center;">
        <a href="<?= url('/') ?>" class="btn btn-secondary">Retour à l'accueil</a>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>