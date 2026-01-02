<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Modifier un utilisateur</h2>
    <a href="<?= url('users') ?>" class="btn btn-secondary">&larr; Retour</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('users/update?id=' . $user['id']) ?>" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="d-flex gap-4">
            <div class="form-group w-full">
                <label for="first_name">Prénom <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="first_name" name="first_name" value="<?= e($user['first_name']) ?>" required>
            </div>
            <div class="form-group w-full">
                <label for="last_name">Nom <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="last_name" name="last_name" value="<?= e($user['last_name']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email <span style="color: var(--color-danger);">*</span></label>
            <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer">
            <p class="text-muted mt-2" style="font-size: 0.85rem;">Ne remplissez ce champ que si vous souhaitez modifier
                le mot de passe.</p>
        </div>

        <div class="form-group">
            <label for="role">Rôle</label>
            <select id="role" name="role">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                <option value="gestionnaire" <?= $user['role'] === 'gestionnaire' ? 'selected' : '' ?>>Gestionnaire
                </option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
            </select>
        </div>

        <div class="d-flex justify-between items-center mt-6">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="<?= url('users') ?>" class="text-muted">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>