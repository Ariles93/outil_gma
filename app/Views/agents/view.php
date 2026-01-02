<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <div class="d-flex items-center gap-4">
        <a href="<?= url('agents') ?>" class="btn btn-secondary btn-sm">
            &larr; Retour
        </a>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">
            <?= e($agent['first_name'] . ' ' . $agent['last_name']) ?>
        </h2>
        <span class="badge badge-neutral"><?= e($agent['position'] ?? 'N/A') ?></span>
    </div>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <div>
            <a href="<?= url('agents/edit?id=' . $agent['id']) ?>" class="btn btn-primary">
                Modifier l'agent
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="d-flex flex-column gap-4">

    <!-- Section 1: Fiche Identité (Horizontal on Top) -->
    <div class="card">
        <h3 class="mb-4"
            style="border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted);">
            Fiche Identité
        </h3>

        <div class="d-flex flex-wrap gap-6 items-center">
            <!-- Avatar -->
            <div class="text-center" style="flex-shrink: 0;">
                <?php $initials = substr($agent['first_name'], 0, 1) . substr($agent['last_name'], 0, 1); ?>
                <div
                    style="width: 80px; height: 80px; background: #F1F5F9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748B; font-size: 1.75rem; border: 4px solid #FFF; box-shadow: var(--shadow-sm);">
                    <?= strtoupper($initials) ?>
                </div>
            </div>

            <!-- Info Grid -->
            <div
                style="flex: 1; min-width: 250px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div>
                    <label class="text-sm text-muted font-bold uppercase mb-1">Département</label>
                    <div style="font-weight: 500; font-size: 1.1rem;"><?= e($agent['department'] ?? '-') ?></div>
                </div>

                <div>
                    <label class="text-sm text-muted font-bold uppercase mb-1">Email</label>
                    <div style="overflow-wrap: break-word; word-break: break-all;">
                        <a href="mailto:<?= e($agent['email']) ?>" style="color: var(--color-primary);">
                            <?= e($agent['email']) ?>
                        </a>
                    </div>
                </div>

                <div>
                    <label class="text-sm text-muted font-bold uppercase mb-1">Téléphone</label>
                    <div><?= e($agent['phone'] ?? '-') ?></div>
                </div>

                <div>
                    <label class="text-sm text-muted font-bold uppercase mb-1">Matricule</label>
                    <div
                        style="font-family: monospace; background: #F1F5F9; padding: 0.25rem 0.5rem; border-radius: 4px; display: inline-block;">
                        <?= e($agent['employee_id'] ?? '-') ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($agent['notes'])): ?>
            <div class="mt-4 pt-4 border-t" style="border-top: 1px dashed var(--color-border);">
                <label class="text-sm text-muted font-bold uppercase mb-1">Notes</label>
                <div
                    style="background: #FFFBEB; padding: 0.75rem; border-radius: var(--radius-sm); font-size: 0.875rem; color: #92400E; border: 1px solid #FDE68A;">
                    <?= nl2br(e($agent['notes'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section 2: Matériel en possession -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="d-flex justify-between items-center p-4 border-b"
            style="border-bottom: 1px solid var(--color-border);">
            <h3 style="margin:0; font-size: 1.1rem;">Matériel en possession</h3>
            <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                <a href="<?= url('assignments/create') ?>?agent_id=<?= $agent['id'] ?>" class="btn btn-sm btn-primary">
                    + Attribuer
                </a>
            <?php endif; ?>
        </div>

        <div id="current-assignments-container" class="table-responsive">
            <?php if (empty($current)): ?>
                <div class="text-center p-8 text-muted">
                    L'agent n'a aucun matériel attribué pour le moment.
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Matériel</th>
                            <th>Détails</th>
                            <th>Assigné le</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current as $c): ?>
                            <tr id="assignment-row-<?= e($c['assign_id']) ?>">
                                <td>
                                    <div style="font-weight: 500;">
                                        <a href="<?= url('materials/view?id=' . $c['id']) ?>"
                                            style="color: var(--color-text-main);">
                                            <?= e($c['category_name']) ?>
                                        </a>
                                    </div>
                                    <div class="text-sm text-muted"><?= e($c['brand'] . ' ' . $c['model']) ?></div>
                                </td>
                                <td>
                                    <div class="text-sm">
                                        S/N: <?= e($c['serial_number'] ?? '-') ?>
                                    </div>
                                    <div class="text-xs text-muted">
                                        Tag: <?= e($c['asset_tag'] ?? '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">
                                        <?= e((new DateTime($c['assigned_at']))->format('d/m/Y')) ?>
                                    </div>
                                    <div class="text-xs text-muted">État: <?= e($c['condition_on_assign']) ?></div>
                                </td>
                                <td class="text-right">
                                    <?php if (in_array($_SESSION['user_role'], ['admin', 'gestionnaire'])): ?>
                                        <form method="post" action="<?= url('assignments/return') ?>" class="return-form"
                                            style="display:inline;">
                                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="assignment_id" value="<?= e($c['assign_id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-success"
                                                title="Marquer comme retourné et générer PDF">
                                                Retourner
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 3: Historique -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="p-4 border-b" style="border-bottom: 1px solid var(--color-border); background-color: #F8FAFC;">
            <h3
                style="margin:0; font-size: 1rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                Historique des mouvements</h3>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Matériel</th>
                        <th>Assigné</th>
                        <th>Retourné</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="4" class="text-center p-4 text-muted">Aucun historique disponible.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 500; font-size: 0.875rem;">
                                        <?= e($h['brand'] . ' ' . $h['model']) ?>
                                    </div>
                                    <div class="text-xs text-muted">
                                        <?= e($h['asset_tag'] ?: '#' . $h['material_id']) ?>
                                    </div>
                                </td>
                                <td class="text-sm">
                                    <?= e((new DateTime($h['assigned_at']))->format('d/m/Y')) ?>
                                </td>
                                <td class="text-sm">
                                    <?php if ($h['returned_at']): ?>
                                        <span class="badge badge-neutral">
                                            <?= (new DateTime($h['returned_at']))->format('d/m/Y') ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm text-muted">
                                    <?= e($h['note'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- PARTIE 1 : GESTION DU TÉLÉCHARGEMENT POST-ATTRIBUTION ---
        const urlParams = new URLSearchParams(window.location.search);
        const pdfToDownload = urlParams.get('download_pdf');

        if (pdfToDownload) {
            window.location.href = '<?= url('assignments/pdf') ?>?id=' + pdfToDownload;
            // Clean URL after 1s
            setTimeout(() => {
                const newUrl = window.location.pathname + '?id=' + urlParams.get('id');
                window.history.replaceState({}, document.title, newUrl);
            }, 1000);
        }

        // --- PARTIE 2 : GESTION DU RETOUR DE MATÉRIEL EN AJAX ---
        const returnForms = document.querySelectorAll('.return-form');
        returnForms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!confirm('Confirmer le retour de ce matériel ?\nUn PDF de retour sera généré.')) {
                    return;
                }

                const formData = new FormData(this);
                const actionUrl = this.action;
                const rowId = 'assignment-row-' + formData.get('assignment_id');
                const row = document.getElementById(rowId);
                const originalBtnText = this.querySelector('button').innerHTML;

                // Loading state
                this.querySelector('button').innerHTML = '...';
                this.querySelector('button').disabled = true;

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (row) {
                                row.style.backgroundColor = '#f0fdf4';
                                setTimeout(() => {
                                    row.remove();
                                    // Check if table empty
                                    const container = document.getElementById('current-assignments-container');
                                    const tbody = container.querySelector('tbody');
                                    if (!tbody || tbody.children.length === 0) {
                                        container.innerHTML = '<div class="text-center p-8 text-muted">L\'agent n\'a aucun matériel attribué pour le moment.</div>';
                                    }
                                }, 500);
                            }
                            // Download PDF
                            if (data.assignment_id) {
                                window.location.href = '<?= url('assignments/return-pdf') ?>?id=' + data.assignment_id;
                            }
                        } else {
                            alert('Erreur : ' + (data.message || 'Une erreur est survenue.'));
                            this.querySelector('button').innerHTML = originalBtnText;
                            this.querySelector('button').disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur de communication est survenue.');
                        this.querySelector('button').innerHTML = originalBtnText;
                        this.querySelector('button').disabled = false;
                    });
            });
        });
    });
</script>