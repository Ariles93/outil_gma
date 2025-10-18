<?php
require_once 'db.php';
$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $like = '%' . str_replace(' ', '%', $q) . '%';
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone FROM agents
        WHERE CONCAT(first_name,' ',last_name) LIKE ? OR first_name LIKE ? OR last_name LIKE ?
        ORDER BY last_name, first_name LIMIT 200");
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll();
}
include 'header.php';
?>
<h2>Recherche agent</h2>
<form method="get">
  <input name="q" placeholder="Prénom, nom ou partie de nom" value="<?= e($q) ?>">
  <button type="submit">Rechercher</button>
</form>
<?php if ($q !== ''): ?>
  <h3>Résultats</h3>
  <?php if(empty($results)) echo "<p>Aucun résultat</p>"; else; ?>
    <ul>
      <?php foreach($results as $r): ?>
        <li><a href="view_agent.php?id=<?= e($r['id']) ?>"><?= e($r['last_name'].' '.$r['first_name']) ?></a>
        — <?= e($r['email'] ?? '') ?> <?= e($r['phone'] ?? '') ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
<?php endif; ?>
<?php include 'footer.php'; ?>
