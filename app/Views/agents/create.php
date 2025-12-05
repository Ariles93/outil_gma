<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Ajouter un agent</h2>

<div class="form-container">
    <?php if (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= url('agents/create') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="first_name">Prénom:</label>
        <input id="first_name" name="first_name" type="text" required
            value="<?= e($agent['first_name'] ?? '') ?>"><br><br>

        <label for="last_name">Nom:</label>
        <input id="last_name" name="last_name" type="text" required value="<?= e($agent['last_name'] ?? '') ?>"><br><br>

        <label for="email">Email:</label>
        <input id="email" name="email" type="email" required value="<?= e($agent['email'] ?? '') ?>"><br><br>

        <label for="phone">Téléphone:</label>
        <input id="phone" name="phone" type="tel" value="<?= e($agent['phone'] ?? '') ?>"><br><br>

        <label for="department">Service:</label>
        <input id="department" name="department" type="text" required
            value="<?= e($agent['department'] ?? '') ?>"><br><br>

        <label for="position">Poste:</label>
        <input id="position" name="position" type="text" required value="<?= e($agent['position'] ?? '') ?>"><br><br>

        <label for="employee_id">ID employé:</label>
        <input id="employee_id" name="employee_id" type="number" value="<?= e($agent['employee_id'] ?? '') ?>"><br><br>

        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"
            placeholder="Lieu d'affectation, bureau ... etc"><?= e($agent['notes'] ?? '') ?></textarea>

        <button type="submit" class="btn btn-primary">Créer l'agent</button>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>