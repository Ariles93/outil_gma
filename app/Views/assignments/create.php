<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Attribuer matériel</h2>
    <a href="<?= url('materials/create') ?>" class="btn btn-primary">+Ajouter matériel</a>
</div>

<div class="form-container">
    <?php if (!empty($error)): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" id="assignmentForm" action="<?= url('assignments/create') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="agent_id">Agent:</label>
        <select id="agent_id" name="agent_id" required>
            <option value="">-- choisir un agent --</option>
            <?php foreach ($agents as $a): ?>
                <option value="<?= e($a['id']) ?>"><?= e($a['last_name'] . ' ' . $a['first_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <hr style="border: none; border-top: 1px solid var(--color-border); margin: 2rem 0;">

        <label for="material_search">Rechercher un matériel disponible:</label>
        <input type="text" id="material_search" placeholder="Commencez à taper pour filtrer...">
        <div id="material_results"></div>
        <input type="hidden" name="material_id" id="selected_material_id"><br>

        <hr style="border: none; border-top: 1px solid var(--color-border); margin: 2rem 0;">

        <label for="assigned_at">Date d'attribution:</label>
        <input id="assigned_at" name="assigned_at" type="date" value="<?= date('Y-m-d') ?>" required><br>

        <label for="condition_on_assign">État lors de l'attribution:</label>
        <input id="condition_on_assign" type="text" name="condition_on_assign" required><br>

        <label for="note">Note:</label>
        <textarea id="note" name="note" placeholder="Ajouter d'autres informations sur l'attribution ..."></textarea>

        <button type="submit" class="btn btn-primary">Attribuer le matériel</button>
    </form>
</div>

<style>
    #material_results {
        border: 1px solid var(--color-border);
        max-height: 250px;
        overflow-y: auto;
        margin-top: 5px;
        border-radius: var(--radius);
    }

    .result-item {
        padding: 10px;
        border-bottom: 1px solid var(--color-border);
        cursor: pointer;
    }

    .result-item:last-child {
        border-bottom: none;
    }

    .result-item:hover {
        background-color: var(--color-bg);
    }

    .result-item.selected {
        background-color: #DBEAFE;
    }

    .result-item span {
        display: block;
        font-size: 0.9em;
        color: var(--color-text-muted);
    }

    .result-item .status-assigned {
        color: var(--color-danger);
        font-weight: bold;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('material_search');
        const resultsContainer = document.getElementById('material_results');
        const selectedMaterialId = document.getElementById('selected_material_id');
        const form = document.getElementById('assignmentForm');
        let selectedDiv = null;

        function displayResults(materials) {
            resultsContainer.innerHTML = '';
            if (materials.length === 0) {
                resultsContainer.innerHTML = '<div class="result-item">Aucun matériel trouvé.</div>';
            } else {
                materials.forEach(mat => {
                    const div = document.createElement('div');
                    div.classList.add('result-item');

                    let statusText = '';
                    if (mat.status !== 'available') {
                        statusText = ` <span class="status-assigned">(Déjà attribué)</span>`;
                        div.style.opacity = '0.6';
                    } else {
                        div.dataset.id = mat.id; // Only add data-id if available
                    }

                    div.innerHTML = `
                    <strong>${mat.type} ${mat.brand} ${mat.model}</strong> ${statusText}
                    <span>S/N: ${mat.serial_number || '-'} | Asset: ${mat.asset_tag || '-'}</span>
                `;

                    if (mat.status === 'available') {
                        div.addEventListener('click', function () {
                            if (selectedDiv) {
                                selectedDiv.classList.remove('selected');
                            }
                            this.classList.add('selected');
                            selectedDiv = this;
                            selectedMaterialId.value = this.dataset.id;
                            searchInput.value = `${mat.type} ${mat.brand} (S/N: ${mat.serial_number})`;
                            resultsContainer.style.display = 'none'; // Cacher les résultats après sélection
                        });
                    }
                    resultsContainer.appendChild(div);
                });
            }
        }

        function performSearch(query) {
            fetch(`<?= url('api/materials/search') ?>?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.style.display = 'block';
                    displayResults(data.results);
                });
        }

        // 1. Logique au chargement de la page
        if (selectedMaterialId.value > 0) {
            resultsContainer.style.display = 'none';
        } else {
            performSearch('');
        }

        // 2. Logique quand l'utilisateur clique dans la barre de recherche
        searchInput.addEventListener('focus', function () {
            resultsContainer.style.display = 'block';
        });

        // 3. Logique quand l'utilisateur tape du texte
        searchInput.addEventListener('keyup', function () {
            const query = searchInput.value;
            selectedMaterialId.value = ''; // Reset on new search
            if (selectedDiv) {
                selectedDiv.classList.remove('selected');
                selectedDiv = null;
            }
            performSearch(query);
        });

        // === NOUVEAU : On charge la liste initiale au chargement de la page ===
        performSearch('');
    });
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>