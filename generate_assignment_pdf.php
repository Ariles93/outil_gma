<?php
require_once 'db.php';
require_once 'protect.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$allowed_roles = ['admin', 'gestionnaire'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    die("Accès refusé. Vous n'avez pas les permissions pour effectuer cette action.");
}

$assignment_id = (int)($_GET['id'] ?? 0);
if ($assignment_id <= 0) die("ID d'attribution invalide.");

$stmt = $pdo->prepare("
    SELECT
        a.assigned_at,
        a.condition_on_assign,
        ag.first_name, ag.last_name, ag.position, ag.department,
        m.asset_tag, m.serial_number, m.brand, m.model,
        c.name as category_name
    FROM assignments a
    JOIN agents ag ON a.agent_id = ag.id
    JOIN materials m ON a.material_id = m.id
    JOIN categories c ON m.category_id = c.id
    WHERE a.id = ?
");
$stmt->execute([$assignment_id]);
$data = $stmt->fetch();

if (!$data) die("Attribution introuvable.");

// Récupérer le nom de l'utilisateur admin qui génère le document
$admin_user_name = $_SESSION['user_name'] ?? 'N/A';
$crous_text = "Vous êtes agent du CROUS de l’académie de Versailles ; dans ce cadre, la DSI vous a remis du matériel nécessaire pour mener à bien vos missions. Il vous est demandé de rendre ce matériel dans un parfait état de fonctionnement et rappelé qu’il s’agit d’un outil de travail dont l’usage doit rester strictement professionnel.";

// Pour le logo, nous le convertissons en base64 pour l'intégrer directement
// Assurez-vous que le chemin vers votre logo est correct
$logo_path = 'images/logo.svg';
$logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
$logo_data = file_get_contents($logo_path);
$logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);

$html = '
<!doctype html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Bon d\'attribution</title>
<style>
    @font-face {
        font-family: \'DejaVu Sans\';
        src: url(vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf) format("truetype");
    }
    body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #374151; }
    .header { margin-bottom: 30px; }
    .header img { width: 100px; height: 100px; }
    .header h1 { margin: 0; font-size: 16px; color: #041b4dff; text-align: center; }
    h2 { font-size: 14px; color: #064db0ff; border-bottom: 1px solid #D1D5DB; padding-bottom: 5px; margin-top: 25px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #E5E7EB; padding: 8px; text-align: left; }
    th { background-color: #F9FAFB; font-weight: bold; width: 30%; }
    .notice { 
        margin-top: 25px; 
        padding: 15px; 
        background-color: #F3F4F6; 
        border-left: 3px solid #6B7280; 
        font-size: 14px; 
        line-height: 1.5;
        color: #4B5563;
    }
    .footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 11px; color: #211334ff; }
    .signatures { margin-top: 60px; width: 100%; }
    .signature-box { width: 45%; text-align: center; }
    .signature-line { border-top: 1px solid #333; margin-top: 50px; }
</style>
</head>
<body>
    <div class="header">
        <img src="'.$logo_base64.'" alt="Logo"><br>
        <h1>Bon d\'attribution de matériel - Crous de Versailles</h1>
    </div>
    <p>Date d\'attribution : <strong>'.date("d/m/Y", strtotime($data['assigned_at'])).'</strong></p>

    <h2>Bénéficiaire</h2>
    <table>
        <tr><th>Nom</th><td>'.e($data['first_name']).' '.e($data['last_name']).'</td></tr>
        <tr><th>Poste</th><td>'.e($data['position'] ?? '-').'</td></tr>
        <tr><th>Département</th><td>'.e($data['department'] ?? '-').'</td></tr>
    </table>

    <h2>Matériel attribué</h2>
    <table>
        <tr><th>Type</th><td>'.e($data['category_name']).'</td></tr>
        <tr><th>Marque / Modèle</th><td>'.e($data['brand']).' '.e($data['model']).'</td></tr>
        <tr><th>Numéro de série</th><td>'.e($data['serial_number'] ?? '-').'</td></tr>
        <tr><th>Étiquette (Asset Tag)</th><td>'.e($data['asset_tag'] ?? '-').'</td></tr>
        <tr><th>État lors de l\'attribution</th><td>'.e($data['condition_on_assign'] ?? '-').'</td></tr>
    </table>

    <div class="notice">
        <p><strong>Conditions d\'utilisation :</strong></p>
        <p>'.e($crous_text).'</p>
    </div>
    
    <table style="border: none; margin-top: 30px;">
        <tr>
            <td style="border: none; text-align: center;">
                Signature avec mention "Lu et Approuvée"
                <div class="signature-line"></div>
            </td>
            <td style="border: none; text-align: center;">
                Remis par : '.e($admin_user_name).'
                <div class="signature-line"></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Document généré par '.e($admin_user_name).' le '.date("d/m/Y à H:i").' via l\'application de gestion de parc.
    </div>
</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = strtoupper(
    "Remise-" 
    . e($data['category_name']) . "-" 
    . e($data['last_name']) . "-" 
    . e($data['first_name']) . "-" 
    . date("Y-m-d")
);
//$filename = "Remise-" . e($data['category_name']) . "-" . e($data['last_name']) . "-" . e($data['first_name']) . "-" . date("Y-m-d") . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
?>