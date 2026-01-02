<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Ajouter un utilisateur</h2>
    <a href="<?= url('users') ?>" class="btn btn-secondary">&larr; Retour</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('users/store') ?>" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="d-flex gap-4">
            <div class="form-group w-full">
                <label for="first_name">Prénom <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="first_name" name="first_name" required placeholder="Ex: Jean">
            </div>
            <div class="form-group w-full">
                <label for="last_name">Nom <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="last_name" name="last_name" required placeholder="Ex: Dupont">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email <span style="color: var(--color-danger);">*</span></label>
            <input type="email" id="email" name="email" required placeholder="Ex: jean.dupont@exemple.fr">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe <span style="color: var(--color-danger);">*</span></label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>

        <div class="form-group">
            <label for="role">Rôle</label>
            <select id="role" name="role">
                <option value="user">Utilisateur</option>
                <option value="gestionnaire">Gestionnaire</option>
                <option value="admin">Administrateur</option>
            </select>
            <p class="text-muted mt-2" style="font-size: 0.85rem;">
                <strong>Utilisateur:</strong> Accès en lecture seule.<br>
                <strong>Gestionnaire:</strong> Peut créer et modifier le matériel.<br>
                <strong>Administrateur:</strong> Accès complet (incluant la gestion des utilisateurs).
            </p>
        </div>

        <div class="d-flex justify-between items-center mt-6">
            <button type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
            <a href="<?= url('users') ?>" class="text-muted">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>