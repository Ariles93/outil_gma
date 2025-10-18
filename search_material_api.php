<?php
require_once 'db.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$response = ['results' => []];

// La requête de base sélectionne toujours tout le matériel disponible
$sql = "
    SELECT m.id, m.asset_tag, c.name as type, m.brand, m.model, m.serial_number, m.status
    FROM materials m
    JOIN categories c ON m.category_id = c.id
    WHERE m.status = 'available' AND m.deleted_at IS NULL
";

$params = [];

// Si une recherche est active, on ajoute un tri par pertinence
if ($q !== '') {
    $exact_q = strtolower($q);
    $like_q = '%' . strtolower($q) . '%';
    
    // CORRECTION : On ajoute une condition WHERE pour filtrer les résultats
    $sql .= "
        AND (LOWER(m.serial_number) LIKE :like_q 
             OR LOWER(m.asset_tag) LIKE :like_q 
             OR LOWER(CONCAT(c.name, ' ', m.brand, ' ', m.model)) LIKE :like_q)
    ";

    $sql .= "
        ORDER BY
            CASE
                EN m.serial_number LIKE :q COLLATE utf8mb4_general_ci THEN 1
                WHEN m.asset_tag LIKE :q COLLATE utf8mb4_general_ci THEN 2
                WHEN CONCAT(c.name, ' ', m.brand, ' ', m.model) LIKE :like_q COLLATE utf8mb4_general_ci THEN 3
                ELSE 4 -- Les éléments non correspondants sont mis à la fin
            END, 
            c.name, m.brand
    ";
    $params = [':exact_q' => $exact_q, ':like_q' => $like_q];
} else {
    // S'il n'y a pas de recherche, on trie simplement par nom
    $sql .= " ORDER BY c.name, m.brand, m.model";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$response['results'] = $stmt->fetchAll();

echo json_encode($response);
?>