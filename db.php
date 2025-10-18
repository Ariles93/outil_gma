<?php
// db.php
declare(strict_types=1);

// Inclure l'autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Charger les variables d'environnement
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    http_response_code(500);
    // Ce message s'affichera si le fichier .env est manquant ou illisible
    die("Erreur critique : Impossible de charger la configuration de l'environnement. Assurez-vous que le fichier .env existe et est correct.");
}


// --- DÉBUT BLOC HTTPS ---
// ... (le bloc de redirection HTTPS reste identique) ...
if (!isset($_SERVER['HTTPS']) && $_SERVER['HTTP_HOST'] !== 'localhost' && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false) {
    $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirect_url");
    exit();
}
// --- FIN BLOC HTTPS ---

session_start();

// --- DÉBUT BLOC ANTI-CACHE ---

// Force le navigateur à ne jamais utiliser de cache pour cette page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
// --- FIN BLOC ANTI-CACHE ---

// On utilise maintenant les variables d'environnement
$DB_HOST = $_ENV['DB_HOST'] ?? null;
$DB_NAME = $_ENV['DB_NAME'] ?? null;
$DB_USER = $_ENV['DB_USER'] ?? null;
$DB_PASS = $_ENV['DB_PASS'] ?? null;

// Vérifier que les variables ont bien été chargées
if (!$DB_HOST || !$DB_NAME || !$DB_USER) {
    http_response_code(500);
    die("Erreur critique : Les variables de base de données ne sont pas définies. Vérifiez votre fichier .env.");
}

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur de connexion à la base de données. Vérifiez vos identifiants dans le fichier .env. Message : " . htmlspecialchars($e->getMessage());
    exit;
}

// Les fonctions helpers restent identiques
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf_token'];
}
function check_csrf($token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Enregistre une action dans le journal d'audit.
 *
 * @param PDO $pdo L'objet de connexion PDO.
 * @param string $action La description de l'action à enregistrer.
 */
function log_action(PDO $pdo, string $action): void {
    try {
        // On ne peut enregistrer que si un utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $action]);
    } catch (PDOException $e) {
        // En cas d'erreur de log, on ne bloque pas l'utilisateur.
        // On pourrait enregistrer cette erreur dans un fichier si nécessaire.
    }
}
?>
