<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Utilisateurs DSI</h2>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= url('users/create') ?>" class="btn btn-primary">
            + Ajouter un utilisateur
        </a>
    <?php endif; ?>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="font-weight: 500;"><?= e($user['first_name']) ?></td>
                        <td style="font-weight: 500;"><?= e($user['last_name']) ?></td>
                        <td class="text-muted"><?= e($user['email']) ?></td>
                        <td>
                            <?php
                            $roleClass = match ($user['role']) {
                                'admin' => 'badge-danger',
                                'gestionnaire' => 'badge-warning',
                                'user' => 'badge-success',
                                default => 'badge-neutral'
                            };
                            $roleLabel = match ($user['role']) {
                                'admin' => 'Administrateur',
                                'gestionnaire' => 'Gestionnaire',
                                'user' => 'Utilisateur',
                                default => $user['role']
                            };
                            ?>
                            <span class="badge <?= $roleClass ?>"><?= $roleLabel ?></span>
                        </td>
                        <td class="text-right">
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <a href="<?= url('users/edit?id=' . $user['id']) ?>" class="btn btn-sm btn-secondary"
                                    style="margin-right: 0.5rem;">Modifier</a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="post" action="<?= url('users/delete') ?>" style="display:inline;"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e($user['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">×</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted text-sm">Lecture seule</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>