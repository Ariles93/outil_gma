<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Modifier un utilisateur</h2>

<div class="form-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="<?= url('users/update?id=' . $user['id']) ?>" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="first_name">Nom complet</label>
            <input type="text" id="first_name" name="first_name" value="<?= e($user['first_name']) ?>">
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

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="<?= url('users') ?>" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>