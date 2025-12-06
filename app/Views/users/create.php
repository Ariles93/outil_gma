<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Ajouter un utilisateur</h2>

<div class="form-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="<?= url('users/store') ?>" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" id="first_name" name="first_name">
        </div>

        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" id="last_name" name="last_name">
        </div>

        <div class="form-group">
            <label for="role">Rôle</label>
            <select id="role" name="role">
                <option value="user">Utilisateur</option>
                <option value="gestionnaire">Gestionnaire</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="<?= url('users') ?>" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>