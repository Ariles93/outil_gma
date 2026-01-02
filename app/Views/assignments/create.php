<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-between items-center mb-4">
    <h2>Attribuer matériel</h2>
    <a href="<?= url('materials/create') ?>" class="btn btn-secondary">
        + Ajouter matériel
    </a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" id="assignmentForm" action="<?= url('assignments/create') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="agent_id">Agent <span style="color: var(--color-danger);">*</span></label>
            <select id="agent_id" name="agent_id" required>
                <option value="">-- Choisir un agent --</option>
                <?php foreach ($agents as $a): ?>
                    <option value="<?= e($a['id']) ?>"><?= e($a['last_name'] . ' ' . $a['first_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="border-top: 1px solid var(--color-border); margin: 1.5rem 0;"></div>

        <div class="form-group relative">
            <label for="material_search">Rechercher un matériel disponible <span
                    style="color: var(--color-danger);">*</span></label>
            <input type="text" id="material_search" placeholder="Commencez à taper pour filtrer (Ex: Dell Latitude...)"
                autocomplete="off">
            <!-- Autocomplete Results Container (uses styles from style.css) -->
            <div id="material_results" class="autocomplete-results"></div>

            <input type="hidden" name="material_id" id="selected_material_id">
            <p class="text-muted" style="font-size: 0.8rem; margin-top: 0.25rem;">Tapez le nom, la marque ou le numéro
                de série du matériel.</p>
        </div>

        <div style="border-top: 1px solid var(--color-border); margin: 1.5rem 0;"></div>

        <div class="dashboard-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 0;">
            <div class="form-group">
                <label for="assigned_at">Date d'attribution <span style="color: var(--color-danger);">*</span></label>
                <input id="assigned_at" name="assigned_at" type="date" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label for="condition_on_assign">État lors de l'attribution <span
                        style="color: var(--color-danger);">*</span></label>
                <input id="condition_on_assign" type="text" name="condition_on_assign" required
                    placeholder="Ex: Neuf, Bon état, etc.">
            </div>
        </div>

        <div class="form-group mt-4">
            <label for="note">Note</label>
            <textarea id="note" name="note" rows="3"
                placeholder="Ajouter d'autres informations sur l'attribution..."></textarea>
        </div>

        <div class="d-flex justify-between items-center mt-6">
            <button type="submit" class="btn btn-primary">Attribuer le matériel</button>
            <a href="<?= url('assignments') ?>" class="text-muted">Annuler</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('material_search');
        const resultsContainer = document.getElementById('material_results');
        const selectedMaterialId = document.getElementById('selected_material_id');
        const form = document.getElementById('assignmentForm');
        let selectedDiv = null;
        let debounceTimer;

        function displayResults(materials) {
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'block';

            if (materials.length === 0) {
                resultsContainer.innerHTML = '<div class="result-item text-muted" style="cursor: default;">Aucun matériel disponible trouvé.</div>';
            } else {
                materials.forEach(mat => {
                    const div = document.createElement('div');
                    div.classList.add('result-item');

                    if (mat.status !== 'available') {
                        div.style.opacity = '0.6';
                        div.style.cursor = 'not-allowed';
                        div.innerHTML = `<strong>${mat.type} ${mat.brand} ${mat.model}</strong> <span class="status-assigned">(Déjà attribué)</span>`;
                    } else {
                        div.dataset.id = mat.id;
                        div.innerHTML = `
                            <strong>${mat.type} ${mat.brand} ${mat.model}</strong>
                            <span>S/N: ${mat.serial_number || '-'} | Asset: ${mat.asset_tag || '-'}</span>
                        `;

                        div.addEventListener('click', function () {
                            if (selectedDiv) {
                                selectedDiv.classList.remove('selected');
                            }
                            this.classList.add('selected');
                            selectedDiv = this;
                            selectedMaterialId.value = this.dataset.id;
                            searchInput.value = `${mat.type} ${mat.brand} ${mat.model} (${mat.serial_number})`;
                            resultsContainer.style.display = 'none';
                        });
                    }
                    resultsContainer.appendChild(div);
                });
            }
        }

        function performSearch(query) {
            resultsContainer.innerHTML = '<div class="result-item text-muted" style="cursor: default;">Recherche en cours...</div>';
            resultsContainer.style.display = 'block';

            fetch(`<?= url('api/materials/search') ?>?q=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error || 'Erreur serveur'); });
                    }
                    return response.json();
                })
                .then(data => {
                    displayResults(data.results);
                })
                .catch(err => {
                    console.error('Erreur:', err);
                    resultsContainer.innerHTML = `<div class="result-item" style="color: var(--color-danger); cursor: default;">Erreur: ${err.message}</div>`;
                });
        }

        searchInput.addEventListener('input', function () {
            const query = searchInput.value;

            if (query === '') {
                selectedMaterialId.value = '';
                resultsContainer.style.display = 'none';
                if (selectedDiv) {
                    selectedDiv.classList.remove('selected');
                    selectedDiv = null;
                }
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (query.length > 0) performSearch(query);
            }, 300);
        });

        // Close results when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    });
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>