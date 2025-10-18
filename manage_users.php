<?php
require_once 'db.php';
require_once 'protect.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

$stmt = $pdo->query("SELECT id, email, first_name, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Gestion des utilisateurs</h2>
    <a href="add_user.php" class="btn btn-primary">+ Ajouter un utilisateur</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Non et Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['first_name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e($user['role']) ?></td>
                    <td><?= e( (new DateTime($user['created_at']))->format('d/m/Y H:i') ) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= e($user['id']) ?>" class="btn btn-success" style="text-decoration:none; padding: 0.4rem 0.8rem; font-size: 0.8rem; color: whitesmoke;">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
