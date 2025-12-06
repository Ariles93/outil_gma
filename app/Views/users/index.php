<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Utilisateurs DSI</h2>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= url('users/create') ?>" class="btn btn-primary">Ajouter un utilisateur</a>
    <?php endif; ?>
</div>

<div class="content-box">
    <table class="data-table">
        <thead>
            <tr>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['first_name']) ?></td>
                    <td><?= e($user['last_name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td>
                        <?php
                        $roles = [
                            'admin' => '<span class="badge badge-danger">Administrateur</span>',
                            'gestionnaire' => '<span class="badge badge-warning">Gestionnaire</span>',
                            'user' => '<span class="badge badge-success">Utilisateur</span>'
                        ];
                        echo $roles[$user['role']] ?? $user['role'];
                        ?>
                    </td>
                    <td>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="<?= url('users/edit?id=' . $user['id']) ?>" class="btn btn-sm btn-primary">Modifier</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="post" action="<?= url('users/delete') ?>" style="display:inline;"
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= e($user['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>