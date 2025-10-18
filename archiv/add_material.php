<?php
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF');
    if (trim($_POST['type'] ?? '') === '') $error = 'Type obligatoire';
    else {
        $stmt = $pdo->prepare("INSERT INTO materials (asset_tag,type,brand,model,serial_number,purchase_date,warranty_end,status,notes)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['asset_tag'] ?? null, $_POST['type'], $_POST['brand'] ?? null, $_POST['model'] ?? null, $_POST['serial_number'] ?? null, $_POST['purchase_date'] ?: null, $_POST['warranty_end'] ?: null, $_POST['status'] ?? 'available', $_POST['notes'] ?? null]);
        header('Location: add_material.php?ok=1');
        exit;
    }
}
include 'header.php';
?>
<h2>Ajouter un matériel</h2>
<?php if(!empty($error)) echo "<p class='error'>".e($error)."</p>"; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Étiquette (asset tag): <input name="asset_tag"></label><br>
  <label>Type (ex: PC, écran, téléphone): <input name="type" required></label><br>
  <label>Marque: <input name="brand"></label><br>
  <label>Modèle: <input name="model"></label><br>
  <label>Numéro de série: <input name="serial_number"></label><br>
  <label>Date d'achat: <input name="purchase_date" type="date"></label><br>
  <label>Fin garantie: <input name="warranty_end" type="date"></label><br>
  <label>Status:
    <select name="status">
      <option value="available">Disponible</option>
      <option value="assigned">Attribué</option>
      <option value="maintenance">Maintenance</option>
      <option value="retired">Retiré</option>
    </select>
  </label><br>
  <label>Notes:<br><textarea name="notes"></textarea></label><br>
  <button type="submit">Créer</button>
</form>
<?php include 'footer.php'; ?>
